<?php

namespace Database\Seeders;

use App\Models\Activity;
use Illuminate\Database\Seeder;

class ActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Activity::factory(50)->make()->each(function ($activity) {
            if ($activity->subject_id) {
                $activity->save();
            }
        });
    }
}
