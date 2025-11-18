<?php

namespace App\Actions\Post;

use App\Models\Post;

class RestorePostAction
{
    /**
     * Execute the action to restore a soft-deleted post.
     */
    public function __invoke(Post $post): Post
    {
        $post->restore();
        $post->load('user')->loadCount('comments');

        return $post;
    }
}
