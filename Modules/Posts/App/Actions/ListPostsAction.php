<?php

namespace Modules\Posts\App\Actions;

use Modules\Posts\App\Models\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListPostsAction
{
    public function handle(?string $search = null): LengthAwarePaginator
    {
        $query = Post::with(['user', 'media'])
            ->withCount('comments')
            ->latest();

        // Search functionality
        if ($search) {
            $query->search($search);
        }

        return $query->paginate(15);
    }
}
