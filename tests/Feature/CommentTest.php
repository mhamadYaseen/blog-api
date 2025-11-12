<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_comments_for_post(): void
    {
        $post = Post::factory()->create();
        Comment::factory()->count(3)->create(['post_id' => $post->id]);

        $response = $this->getJson("/api/posts/{$post->id}/comments");

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'comment', 'user', 'post_id', 'created_at'],
                ],
            ]);
    }

    public function test_authenticated_user_can_create_comment(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/posts/{$post->id}/comments", [
                'comment' => 'This is a test comment',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'comment', 'user', 'post_id'],
            ])
            ->assertJson([
                'data' => [
                    'comment' => 'This is a test comment',
                ],
            ]);

        $this->assertDatabaseHas('comments', [
            'comment' => 'This is a test comment',
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);
    }

    public function test_guest_cannot_create_comment(): void
    {
        $post = Post::factory()->create();

        $response = $this->postJson("/api/posts/{$post->id}/comments", [
            'comment' => 'Test comment',
        ]);

        $response->assertStatus(401);
    }

    public function test_comment_creation_validates_required_fields(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/posts/{$post->id}/comments", [
                'comment' => '',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['comment']);
    }

    public function test_owner_can_delete_their_comment(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson("/api/comments/{$comment->id}");

        $response->assertStatus(204);
        $this->assertSoftDeleted('comments', ['id' => $comment->id]);
    }

    public function test_non_owner_cannot_delete_comment(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $post = Post::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $owner->id,
            'post_id' => $post->id,
        ]);
        $token = $otherUser->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson("/api/comments/{$comment->id}");

        $response->assertStatus(403);
    }

    public function test_comments_are_ordered_by_latest(): void
    {
        $post = Post::factory()->create();
        $oldComment = Comment::factory()->create([
            'post_id' => $post->id,
            'created_at' => now()->subDays(2),
        ]);
        $newComment = Comment::factory()->create([
            'post_id' => $post->id,
            'created_at' => now(),
        ]);

        $response = $this->getJson("/api/posts/{$post->id}/comments");

        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertEquals($newComment->id, $data[0]['id']);
        $this->assertEquals($oldComment->id, $data[1]['id']);
    }

    public function test_owner_can_restore_deleted_comment(): void
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $user->id]);
        $comment->delete();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/comments/{$comment->id}/restore");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Comment restored successfully.',
            ]);

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'deleted_at' => null,
        ]);
    }

    public function test_owner_can_force_delete_comment(): void
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $user->id]);
        $comment->delete();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson("/api/comments/{$comment->id}/force");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Comment permanently deleted.',
            ]);

        $this->assertDatabaseMissing('comments', [
            'id' => $comment->id,
        ]);
    }
}
