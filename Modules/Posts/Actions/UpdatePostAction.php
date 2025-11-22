<?php

namespace Modules\Posts\Actions;

use Modules\Posts\Models\Post;
use Modules\Posts\Services\PostService;
use Illuminate\Http\UploadedFile;

class UpdatePostAction
{
    public function __construct(
        private PostService $postService
    ) {}

    /**
     * Execute the action to update a post.
     */
    public function handle(Post $post, string $title, string $content, ?UploadedFile $image = null): Post
    {
        $data = [
            'title' => $title,
            'content' => $content,
        ];

        $post = $this->postService->update($post, $data, $image);

        // Load relationships needed for the response
        $post->load(['user', 'media']);

        return $post;
    }
}
