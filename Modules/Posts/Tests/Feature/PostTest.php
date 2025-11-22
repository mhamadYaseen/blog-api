<?php

namespace Modules\Posts\Tests\Feature;

use Modules\Posts\App\Models\Post;
use Modules\Users\App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_guest_can_view_posts_list(): void
    {
        Post::factory()->count(5)->create();

        $response = $this->getJson('/api/posts');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'content', 'image', 'image_thumb', 'user', 'created_at'],
                ],
                'links',
                'meta',
            ]);
    }

    public function test_guest_can_view_single_post(): void
    {
        $post = Post::factory()->create();

        $response = $this->getJson("/api/posts/{$post->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'title', 'content', 'user', 'comments_count'],
            ])
            ->assertJson([
                'data' => [
                    'id' => $post->id,
                    'title' => $post->title,
                ],
            ]);
    }

    public function test_authenticated_user_can_create_post(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/posts', [
                'title' => 'My Test Post',
                'content' => 'This is test content for my blog post.',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'title', 'content', 'user'],
            ])
            ->assertJson([
                'data' => [
                    'title' => 'My Test Post',
                ],
            ]);

        $this->assertDatabaseHas('posts', [
            'title' => 'My Test Post',
            'user_id' => $user->id,
        ]);
    }

    public function test_guest_cannot_create_post(): void
    {
        $response = $this->postJson('/api/posts', [
            'title' => 'Test Post',
            'content' => 'Test content',
        ]);

        $response->assertStatus(401);
    }

    public function test_post_creation_validates_required_fields(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/posts', [
                'title' => '',
                'content' => '',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'content']);
    }

    public function test_authenticated_user_can_create_post_with_image(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;
        $file = UploadedFile::fake()->image('test.jpg', 800, 600);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->post('/api/posts', [
                'title' => 'Post with Image',
                'content' => 'Content here',
                'image' => $file,
            ]);

        $response->assertStatus(201);

        $postId = $response->json('data.id');

        $this->assertNotNull($response->json('data.image'));
        $this->assertNotNull($response->json('data.image_thumb'));

        $this->assertDatabaseHas('media', [
            'model_type' => Post::class,
            'model_id' => $postId,
            'collection_name' => Post::COVER_IMAGE_COLLECTION,
        ]);
    }

    public function test_updating_post_replaces_cover_image(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $createResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->post('/api/posts', [
                'title' => 'Initial Title',
                'content' => 'Initial content',
                'image' => UploadedFile::fake()->image('initial.jpg'),
            ]);

        $createResponse->assertStatus(201);

        $postId = $createResponse->json('data.id');
        $post = Post::findOrFail($postId);
        $initialMediaId = $post->getFirstMedia(Post::COVER_IMAGE_COLLECTION)->id;

        $updateResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->post("/api/posts/{$postId}", [
                '_method' => 'PUT',
                'title' => 'Updated Title',
                'content' => 'Updated content',
                'image' => UploadedFile::fake()->image('updated.jpg'),
            ]);

        $updateResponse->assertStatus(200);

        $post->refresh();
        $newMedia = $post->getFirstMedia(Post::COVER_IMAGE_COLLECTION);

        $this->assertNotNull($newMedia);
        $this->assertNotEquals($initialMediaId, $newMedia->id);
        $this->assertCount(1, $post->getMedia(Post::COVER_IMAGE_COLLECTION));
    }

    public function test_owner_can_update_their_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson("/api/posts/{$post->id}", [
                'title' => 'Updated Title',
                'content' => 'Updated content',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'title' => 'Updated Title',
                ],
            ]);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Updated Title',
        ]);
    }

    public function test_non_owner_cannot_update_post(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $owner->id]);
        $token = $otherUser->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson("/api/posts/{$post->id}", [
                'title' => 'Trying to Update',
                'content' => 'Updated content',
            ]);

        $response->assertStatus(403);
    }

    public function test_owner_can_delete_their_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson("/api/posts/{$post->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_deleting_post_removes_media_records(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $createResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->post('/api/posts', [
                'title' => 'Media Post',
                'content' => 'Media content',
                'image' => UploadedFile::fake()->image('cover.jpg'),
            ]);

        $postId = $createResponse->json('data.id');

        $deleteResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson("/api/posts/{$postId}");

        $deleteResponse->assertStatus(204);

        $this->assertDatabaseMissing('media', [
            'model_type' => Post::class,
            'model_id' => $postId,
        ]);
    }

    public function test_non_owner_cannot_delete_post(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $owner->id]);
        $token = $otherUser->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson("/api/posts/{$post->id}");

        $response->assertStatus(403);
    }

    public function test_can_search_posts_by_title(): void
    {
        Post::factory()->create(['title' => 'Laravel Testing Guide']);
        Post::factory()->create(['title' => 'PHP Best Practices']);
        Post::factory()->create(['title' => 'Another Laravel Post']);

        $response = $this->getJson('/api/posts/search?q=Laravel');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_can_search_posts_by_content(): void
    {
        Post::factory()->create(['content' => 'This post talks about Laravel framework']);
        Post::factory()->create(['content' => 'This is about PHP programming']);

        $response = $this->getJson('/api/posts/search?q=Laravel');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_posts_are_paginated(): void
    {
        Post::factory()->count(20)->create();

        $response = $this->getJson('/api/posts');

        $response->assertStatus(200)
            ->assertJsonCount(15, 'data') // 15 per page
            ->assertJsonStructure([
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);
    }
}
