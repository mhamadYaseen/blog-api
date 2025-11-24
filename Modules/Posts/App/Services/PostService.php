<?php

namespace Modules\Posts\App\Services;

use Modules\Posts\App\Models\Post;
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
            $post = Post::create($data);

            if ($image) {
                $post->addMedia($image)
                    ->toMediaCollection(Post::COVER_IMAGE_COLLECTION);
            }

            return $post->refresh();
        });
    }

    /**
     * Update an existing post with optional image upload.
     */
    public function update(Post $post, array $data, ?UploadedFile $image = null): Post
    {
        return DB::transaction(function () use ($post, $data, $image) {
            $post->update($data);

            if ($image) {


                $post->clearMediaCollection(Post::COVER_IMAGE_COLLECTION);

                $post->addMedia($image)
                    ->toMediaCollection(Post::COVER_IMAGE_COLLECTION);

                if ($post->image) {
                    $post->forceFill(['image' => null])->save();
                }
            }

            return $post->refresh();
        });
    }

    /**
     * Delete a post and its associated image.
     */
    public function delete(Post $post): bool
    {
        return DB::transaction(function () use ($post) {
            $post->clearMediaCollection(Post::COVER_IMAGE_COLLECTION);


            return $post->delete();
        });
    }
}
