<?php

namespace App\Http\Controllers;

use App\Actions\Comment\CreateCommentAction;
use App\Actions\Comment\DeleteCommentAction;
use App\Actions\Comment\ForceDeleteCommentAction;
use App\Actions\Comment\RestoreCommentAction;
use App\DTOs\CreateCommentData;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CommentController extends Controller
{
    /**
     * Display a listing of comments for a post.
     */
    public function index(Post $post): AnonymousResourceCollection
    {
        $comments = $post->comments()->with('user')->latest()->get();

        return CommentResource::collection($comments);
    }

    /**
     * Store a newly created comment in storage.
     */
    public function store(StoreCommentRequest $request, Post $post, CreateCommentAction $action): JsonResponse
    {
        $dto = new CreateCommentData(
            comment: $request->validated()['comment'],
            userId: $request->user()->id,
            postId: $post->id
        );

        $comment = $action($post, $dto);

        return response()->json([
            'data' => new CommentResource($comment),
        ], 201);
    }

    /**
     * Remove the specified comment from storage.
     */
    public function destroy(Comment $comment, DeleteCommentAction $action): JsonResponse
    {
        $this->authorize('delete', $comment);

        $action($comment);

        return response()->json(null, 204);
    }

    /**
     * Restore a soft-deleted comment.
     */
    public function restore(string $id, RestoreCommentAction $action): JsonResponse
    {
        $comment = Comment::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $comment);

        $comment = $action($comment);

        return response()->json([
            'message' => 'Comment restored successfully.',
            'data' => new CommentResource($comment),
        ]);
    }

    /**
     * Permanently delete a soft-deleted comment.
     */
    public function forceDelete(string $id, ForceDeleteCommentAction $action): JsonResponse
    {
        $comment = Comment::onlyTrashed()->findOrFail($id);

        $this->authorize('forceDelete', $comment);

        $action($comment);

        return response()->json([
            'message' => 'Comment permanently deleted.',
        ], 200);
    }
}
