<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Post;
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
