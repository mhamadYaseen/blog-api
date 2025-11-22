<?php

namespace Modules\Posts\Actions;

use Modules\Posts\Models\Post;
use Modules\Posts\Services\PostService;

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
