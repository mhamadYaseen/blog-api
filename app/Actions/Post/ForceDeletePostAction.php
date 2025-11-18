<?php

namespace App\Actions\Post;

use App\Models\Post;
use App\Services\PostService;

class ForceDeletePostAction
{
    public function __construct(
        private PostService $postService
    ) {}

    /**
     * Execute the action to permanently delete a post.
     */
    public function __invoke(Post $post): bool
    {
        return $this->postService->forceDelete($post);
    }
}
