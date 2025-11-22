<?php

namespace Modules\Comments\Tests\Unit;

use Modules\Comments\App\Models\Comment;
use Modules\Posts\App\Models\Post;
use Modules\Users\App\Models\User;
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

    public function test_comment_fillable_attributes(): void
    {
        $comment = new Comment;
        $fillable = $comment->getFillable();

        $this->assertContains('post_id', $fillable);
        $this->assertContains('user_id', $fillable);
        $this->assertContains('comment', $fillable);
    }
}
