<?php

namespace Modules\Posts\App\Actions;

use Modules\Posts\App\Models\Post;
use Modules\Posts\App\Services\PostService;

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
