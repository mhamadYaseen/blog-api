<?php

namespace App\Actions\Comment;

use App\Models\Comment;
use App\Services\CommentService;

class ForceDeleteCommentAction
{
    public function __construct(private CommentService $commentService) {}

    public function __invoke(Comment $comment): bool
    {
        return $this->commentService->forceDelete($comment);
    }
}
