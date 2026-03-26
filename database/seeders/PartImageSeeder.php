<?php

namespace Database\Seeders;

use App\Models\Part;
use App\Models\PartImage;
use Illuminate\Database\Seeder;

class PartImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Part::all()->each(function (Part $part) {
            PartImage::factory()->primary()->create(['part_id' => $part->id]);
            PartImage::factory()->count(4)->create(['part_id' => $part->id]);
        });
    }
}
