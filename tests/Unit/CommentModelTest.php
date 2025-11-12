<?php

namespace Tests\Unit;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_comment_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $comment->user);
        $this->assertEquals($user->id, $comment->user->id);
    }

    public function test_comment_belongs_to_post(): void
    {
        $post = Post::factory()->create();
        $comment = Comment::factory()->create(['post_id' => $post->id]);

        $this->assertInstanceOf(Post::class, $comment->post);
        $this->assertEquals($post->id, $comment->post->id);
    }

    public function test_comment_can_be_soft_deleted(): void
    {
        $comment = Comment::factory()->create();
        $commentId = $comment->id;

        $comment->delete();

        $this->assertSoftDeleted('comments', ['id' => $commentId]);
        $this->assertNotNull($comment->fresh()->deleted_at);
    }

    public function test_comment_can_be_restored_after_soft_delete(): void
    {
        $comment = Comment::factory()->create();
        $comment->delete();

        $comment->restore();

        $this->assertNull($comment->fresh()->deleted_at);
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'deleted_at' => null,
        ]);
    }

    public function test_comment_fillable_attributes(): void
    {
        $comment = new Comment;
        $fillable = $comment->getFillable();

        $this->assertContains('post_id', $fillable);
        $this->assertContains('user_id', $fillable);
        $this->assertContains('comment', $fillable);
    }
}
