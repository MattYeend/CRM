<?php

namespace Database\Seeders;

use App\Models\Learning;
use App\Models\LearningUser;
use App\Models\User;
use Illuminate\Database\Seeder;

class LearningUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $learnings = Learning::all();

        foreach ($learnings as $learning) {
            $assignedUsers = $users->random(rand(1, 5));

            foreach ($assignedUsers as $user) {
                LearningUser::create([
                    'learning_id' => $learning->id,
                    'user_id' => $user->id,
                    'is_complete' => rand(0, 1),
                    'completed_at' => now(),
                    'is_test' => true,
                    'score' => rand(20, 100),
                    'meta' => ['score' => rand(50, 100)],
                    'created_by' => User::inRandomOrder()->first()?->id,
                ]);
            }
        }
    }
}
