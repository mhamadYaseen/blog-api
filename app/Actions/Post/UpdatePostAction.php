<?php

namespace App\Actions\Post;

use App\DTOs\UpdatePostData;
use App\Models\Post;
use App\Services\PostService;
use Illuminate\Http\UploadedFile;

class UpdatePostAction
{
    public function __construct(
        private PostService $postService
    ) {}

    /**
     * Execute the action to update a post.
     */
    public function __invoke(Post $post, UpdatePostData $data, ?UploadedFile $image = null): Post
    {
        $post = $this->postService->update($post, $data->toArray(), $image);

        // Load relationships needed for the response
        $post->load('user');

        return $post;
    }
}
