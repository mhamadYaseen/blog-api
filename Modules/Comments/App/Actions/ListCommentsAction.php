<?php

namespace Modules\Comments\App\Actions;

use Modules\Posts\App\Models\Post;
use Illuminate\Database\Eloquent\Collection;

class ListCommentsAction
{
    public function handle(Post $post): Collection
    {
        return $post->comments()->with('user')->latest()->get();
    }
}
