<?php

namespace Modules\Comments\App\Actions;

use Modules\Comments\App\Models\Comment;
use Modules\Comments\App\Services\CommentService;

class DeleteCommentAction
{
    public function __construct(private CommentService $commentService) {}

    public function handle(Comment $comment): bool
    {
        return $this->commentService->delete($comment);
    }
}
