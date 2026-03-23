<?php

namespace Database\Seeders;

use App\Models\Learning;
use App\Models\LearningAnswer;
use App\Models\LearningQuestion;
use Illuminate\Database\Seeder;

class LearningQuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Learning::all()->each(function (Learning $learning) {
            LearningQuestion::factory()
                ->count(3)
                ->create(['learning_id' => $learning->id])
                ->each(function (LearningQuestion $question) {
                    // Create one guaranteed correct answer
                    LearningAnswer::factory()
                        ->correct()
                        ->create(['question_id' => $question->id]);

                    // Create two incorrect answers
                    LearningAnswer::factory()
                        ->count(2)
                        ->create(['question_id' => $question->id]);
                });
        });
    }
}
