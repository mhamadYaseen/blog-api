<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PostService
{
    /**
     * Create a new post with optional image upload.
     */
    public function create(array $data, ?UploadedFile $image = null): Post
    {
        return DB::transaction(function () use ($data, $image) {
            // Handle image upload if provided
            if ($image) {
                $data['image'] = $image->store('images', 'public');
            }

            return Post::create($data);
        });
    }

    /**
     * Update an existing post with optional image upload.
     */
    public function update(Post $post, array $data, ?UploadedFile $image = null): Post
    {
        return DB::transaction(function () use ($post, $data, $image) {
            // Handle image upload if provided
            if ($image) {
                // Delete old image if exists
                if ($post->image) {
                    Storage::disk('public')->delete($post->image);
                }
                $data['image'] = $image->store('images', 'public');
            }

            $post->update($data);
            return $post->refresh();
        });
    }

    /**
     * Delete a post and its associated image.
     */
    public function delete(Post $post): bool
    {
        return DB::transaction(function () use ($post) {
            // Delete image if exists
            if ($post->image) {
                Storage::disk('public')->delete($post->image);
            }

            return $post->delete();
        });
    }

    /**
     * Permanently delete a post and its associated image.
     */
    public function forceDelete(Post $post): bool
    {
        return DB::transaction(function () use ($post) {
            // Delete image if exists (check it's not a URL)
            if ($post->image && ! filter_var($post->image, FILTER_VALIDATE_URL)) {
                Storage::disk('public')->delete($post->image);
            }

            return $post->forceDelete();
        });
    }
}
