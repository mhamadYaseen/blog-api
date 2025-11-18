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
     * Execute the action to soft delete a post.
     */
    public function __invoke(Post $post): bool
    {
        return $this->postService->delete($post);
    }
}
