<?php

namespace App\Http\Controllers;

use App\Actions\Post\CreatePostAction;
use App\Actions\Post\DeletePostAction;
use App\Actions\Post\ListPostsAction;
use App\Actions\Post\UpdatePostAction;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, ListPostsAction $action): AnonymousResourceCollection
    {
        $posts = $action->handle($request->get('search'));

        return PostResource::collection($posts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostRequest $request, CreatePostAction $action): PostResource
    {
        $validated = $request->validated();

        $post = $action->handle(
            title: $validated['title'],
            content: $validated['content'],
            userId: $request->user()->id,
            image: $request->file('image')
        );

        return new PostResource($post);
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post): PostResource
    {
        $post->load(['user', 'media'])->loadCount('comments');

        return new PostResource($post);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePostRequest $request, Post $post, UpdatePostAction $action): PostResource
    {
        $this->authorize('update', $post);

        $validated = $request->validated();

        $post = $action->handle(
            post: $post,
            title: $validated['title'],
            content: $validated['content'],
            image: $request->file('image')
        );

        return new PostResource($post);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post, DeletePostAction $action): Response
    {
        $this->authorize('delete', $post);

        $action->handle($post);

        return response()->noContent();
    }

    /**
     * Search for posts by title or content.
     */
    public function search(Request $request): AnonymousResourceCollection
    {
        $search = $request->input('q', '');

        $posts = Post::with(['user', 'media'])
            ->withCount('comments')
            ->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15);

        return PostResource::collection($posts);
    }
}
