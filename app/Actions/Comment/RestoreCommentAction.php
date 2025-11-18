<?php

namespace App\Actions\Comment;

use App\Models\Comment;
use App\Services\CommentService;

class RestoreCommentAction
{
    public function __construct(private CommentService $commentService) {}

    public function __invoke(Comment $comment): Comment
    {
        $comment = $this->commentService->restore($comment);
        return $comment->load('user', 'post');
    }
}
