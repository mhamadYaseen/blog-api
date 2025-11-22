<?php

namespace Modules\Comments\Database\Seeders;

use Modules\Comments\App\Models\Comment;
use Modules\Posts\App\Models\Post;
use Modules\Users\App\Models\User;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $posts = Post::all();
        $users = User::all();

        // Add 2-5 random comments to each post
        $posts->each(function ($post) use ($users) {
            $commentCount = rand(2, 5);

            for ($i = 0; $i < $commentCount; $i++) {
                Comment::factory()->create([
                    'post_id' => $post->id,
                    'user_id' => $users->random()->id,
                ]);
            }
        });
    }
}
