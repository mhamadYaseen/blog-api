<?php

namespace Modules\Comments\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Comments\App\Actions\CreateCommentAction;
use Modules\Comments\App\Actions\DeleteCommentAction;
use Modules\Comments\App\Actions\ListCommentsAction;
use Modules\Comments\App\Http\Requests\StoreCommentRequest;
use Modules\Comments\App\Http\Resources\CommentResource;
use Modules\Comments\App\Models\Comment;
use Modules\Posts\App\Models\Post;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class CommentController extends Controller
{
    /**
     * Display a listing of comments for a post.
     */
    public function index(Post $post, ListCommentsAction $action): AnonymousResourceCollection
    {
        $comments = $action->handle($post);

        return CommentResource::collection($comments);
    }

    /**
     * Store a newly created comment in storage.
     */
    public function store(StoreCommentRequest $request, Post $post, CreateCommentAction $action): CommentResource
    {
        $comment = $action->handle(
            post: $post,
            comment: $request->validated()['comment'],
            userId: $request->user()->id
        );

        return new CommentResource($comment);
    }

    /**
     * Remove the specified comment from storage.
     */
    public function destroy(Comment $comment, DeleteCommentAction $action): Response
    {
        $this->authorize('delete', $comment);

        $action->handle($comment);

        return response()->noContent();
    }
}
