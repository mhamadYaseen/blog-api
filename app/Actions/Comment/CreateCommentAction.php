<?php

namespace App\Actions\Comment;

use App\Models\Comment;
use App\Models\Post;
use App\Services\CommentService;

class CreateCommentAction
{
    public function __construct(private CommentService $commentService) {}

    public function handle(Post $post, string $comment, int $userId): Comment
    {
        $data = [
            'comment' => $comment,
            'user_id' => $userId,
            'post_id' => $post->id,
        ];

        $commentModel = $this->commentService->create($post, $data);
        return $commentModel->load('user');
    }
}
