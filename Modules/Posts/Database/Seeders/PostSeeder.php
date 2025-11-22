<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 5 users
        $users = User::factory(5)->create();

        // Create 20 posts distributed among users
        $users->each(function ($user) {
            Post::factory(4)->create([
                'user_id' => $user->id,
            ]);
        });
    }
}
