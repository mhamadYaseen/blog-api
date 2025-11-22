<?php

namespace Modules\Comments\App\Services;

use Modules\Comments\App\Models\Comment;
use Modules\Posts\App\Models\Post;
use Illuminate\Support\Facades\DB;

class CommentService
{
    public function create(Post $post, array $data): Comment
    {
        return DB::transaction(fn() => $post->comments()->create($data));
    }

    public function delete(Comment $comment): bool
    {
        return DB::transaction(fn() => $comment->delete());
    }
}
