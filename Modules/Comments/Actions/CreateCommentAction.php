<?php

namespace Modules\Comments\Actions;

use Modules\Comments\Models\Comment;
use Modules\Posts\Models\Post;
use Modules\Comments\Services\CommentService;

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
