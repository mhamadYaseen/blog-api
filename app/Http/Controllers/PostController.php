<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;

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
    public function store(StorePostRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;

        // Handle image upload
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('images', 'public');
        }

        $post = Post::create($data);
        $post->load('user');

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
    public function update(UpdatePostRequest $request, Post $post): JsonResponse
    {
        $this->authorize('update', $post);

        $data = $request->validated();

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($post->image) {
                Storage::disk('public')->delete($post->image);
            }
            $data['image'] = $request->file('image')->store('images', 'public');
        }

        $post->update($data);
        $post->load('user');

        return response()->json([
            'data' => new PostResource($post),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post): JsonResponse
    {
        $this->authorize('delete', $post);

        // Delete image if exists
        if ($post->image) {
            Storage::disk('public')->delete($post->image);
        }

        $post->delete();

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
    public function restore(string $id): JsonResponse
    {
        $post = Post::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $post);

        $post->restore();
        $post->load('user')->loadCount('comments');

        return response()->json([
            'message' => 'Post restored successfully.',
            'data' => new PostResource($post),
        ]);
    }

    /**
     * Permanently delete a soft-deleted post.
     */
    public function forceDelete(string $id): JsonResponse
    {
        $post = Post::onlyTrashed()->findOrFail($id);

        $this->authorize('forceDelete', $post);

        // Delete image if exists
        if ($post->image && ! filter_var($post->image, FILTER_VALIDATE_URL)) {
            Storage::disk('public')->delete($post->image);
        }

        $post->forceDelete();

        return response()->json([
            'message' => 'Post permanently deleted.',
        ], 200);
    }
}
