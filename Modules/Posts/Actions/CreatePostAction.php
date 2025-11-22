<?php

namespace Modules\Posts\Actions;

use Modules\Posts\Models\Post;
use Modules\Posts\Services\PostService;
use Illuminate\Http\UploadedFile;

class CreatePostAction
{
    public function __construct(
        private PostService $postService
    ) {}

    /**
     * Execute the action to create a new post.
     */
    public function handle(string $title, string $content, int $userId, ?UploadedFile $image = null): Post
    {
        $data = [
            'title' => $title,
            'content' => $content,
            'user_id' => $userId,
        ];

        $post = $this->postService->create($data, $image);

        // Load relationships needed for the response
        $post->load(['user', 'media']);

        return $post;
    }
}
