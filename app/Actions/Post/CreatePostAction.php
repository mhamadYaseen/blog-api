<?php

namespace App\Actions\Post;

use App\DTOs\CreatePostData;
use App\Models\Post;
use App\Services\PostService;
use Illuminate\Http\UploadedFile;

class CreatePostAction
{
    public function __construct(
        private PostService $postService
    ) {}

    /**
     * Execute the action to create a new post.
     */
    public function __invoke(CreatePostData $data, ?UploadedFile $image = null): Post
    {
        $post = $this->postService->create($data->toArray(), $image);

        // Load relationships needed for the response
        $post->load('user');

        return $post;
    }
}
