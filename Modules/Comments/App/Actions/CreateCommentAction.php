<?php

namespace Modules\Comments\App\Actions;

use Modules\Comments\App\Models\Comment;
use Modules\Posts\App\Models\Post;
use Modules\Comments\App\Services\CommentService;

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
