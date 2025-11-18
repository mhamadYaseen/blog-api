<?php

namespace App\Actions\Comment;

use App\DTOs\CreateCommentData;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Post;
use App\Services\CommentService;

class CreateCommentAction
{
    public function __construct(private CommentService $commentService) {}

    public function __invoke(Post $post, CreateCommentData $data): Comment
    {
        $comment = $this->commentService->create($post, $data->toArray());
        return $comment->load('user');
    }
}
