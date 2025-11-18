<?php

namespace App\Actions\Comment;

use App\Models\Comment;
use App\Services\CommentService;

class DeleteCommentAction
{
    public function __construct(private CommentService $commentService) {}

    public function handle(Comment $comment): bool
    {
        return $this->commentService->delete($comment);
    }
}
