<?php

namespace Modules\Comments\Actions;

use Modules\Posts\Models\Post;
use Illuminate\Database\Eloquent\Collection;

class ListCommentsAction
{
    public function handle(Post $post): Collection
    {
        return $post->comments()->with('user')->latest()->get();
    }
}
