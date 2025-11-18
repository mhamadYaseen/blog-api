<?php

namespace App\Actions\Post;

use App\Models\Post;
use App\Services\PostService;

class DeletePostAction
{
    public function __construct(
        private PostService $postService
    ) {}

    /**
     * Delete a post and related resources.
     */
    public function handle(Post $post): bool
    {
        return $this->postService->delete($post);
    }
}
