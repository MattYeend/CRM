<?php

namespace Database\Seeders;

use App\Models\Pipeline;
use Illuminate\Database\Seeder;

class PipelineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Pipeline::factory(50)->create();
    }
}
