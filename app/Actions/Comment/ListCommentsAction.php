<?php

namespace App\Actions\Comment;

use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;

class ListCommentsAction
{
    public function handle(Post $post): Collection
    {
        return $post->comments()->with('user')->latest()->get();
    }
}
