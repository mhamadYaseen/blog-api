<?php

namespace Modules\Comments\Actions;

use Modules\Comments\Models\Comment;
use Modules\Comments\Services\CommentService;

class DeleteCommentAction
{
    public function __construct(private CommentService $commentService) {}

    public function handle(Comment $comment): bool
    {
        return $this->commentService->delete($comment);
    }
}
