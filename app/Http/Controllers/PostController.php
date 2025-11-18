<?php

namespace App\Http\Controllers;

use App\Actions\Post\CreatePostAction;
use App\Actions\Post\DeletePostAction;
use App\Actions\Post\ForceDeletePostAction;
use App\Actions\Post\RestorePostAction;
use App\Actions\Post\UpdatePostAction;
use App\DTOs\CreatePostData;
use App\DTOs\UpdatePostData;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Post::with('user')->withCount('comments')->latest();

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $posts = $query->paginate(15);

        return PostResource::collection($posts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostRequest $request, CreatePostAction $action): JsonResponse
    {
        $validated = $request->validated();

        // Build DTO from validated data
        $dto = new CreatePostData(
            title: $validated['title'],
            content: $validated['content'],
            imagePath: null, // Will be handled by service
            userId: $request->user()->id
        );

        // Execute action with optional image file
        $post = $action($dto, $request->file('image'));

        return response()->json([
            'data' => new PostResource($post),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post): JsonResponse
    {
        $post->load('user')->loadCount('comments');

        return response()->json([
            'data' => new PostResource($post),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePostRequest $request, Post $post, UpdatePostAction $action): JsonResponse
    {
        $this->authorize('update', $post);

        $validated = $request->validated();

        // Build DTO from validated data
        $dto = new UpdatePostData(
            title: $validated['title'],
            content: $validated['content']
        );

        // Execute action with optional image file
        $post = $action($post, $dto, $request->file('image'));

        return response()->json([
            'data' => new PostResource($post),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post, DeletePostAction $action): JsonResponse
    {
        $this->authorize('delete', $post);

        $action($post);

        return response()->json(null, 204);
    }

    /**
     * Search for posts by title or content.
     */
    public function search(Request $request): AnonymousResourceCollection
    {
        $search = $request->input('q', '');

        $posts = Post::with('user')
            ->withCount('comments')
            ->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15);

        return PostResource::collection($posts);
    }

    /**
     * Display a listing of trashed posts.
     */
    public function trashed(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Post::class);

        $posts = Post::onlyTrashed()
            ->with('user')
            ->withCount('comments')
            ->latest('deleted_at')
            ->paginate(15);

        return PostResource::collection($posts);
    }

    /**
     * Restore a soft-deleted post.
     */
    public function restore(string $id, RestorePostAction $action): JsonResponse
    {
        $post = Post::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $post);

        $post = $action($post);

        return response()->json([
            'message' => 'Post restored successfully.',
            'data' => new PostResource($post),
        ]);
    }

    /**
     * Permanently delete a soft-deleted post.
     */
    public function forceDelete(string $id, ForceDeletePostAction $action): JsonResponse
    {
        $post = Post::onlyTrashed()->findOrFail($id);

        $this->authorize('forceDelete', $post);

        $action($post);

        return response()->json([
            'message' => 'Post permanently deleted.',
        ], 200);
    }
}
