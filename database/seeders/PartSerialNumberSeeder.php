<?php

namespace Database\Seeders;

use App\Models\Part;
use App\Models\PartSerialNumber;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PartSerialNumberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Part::where('is_serialised', true)->each(function (Part $part) {
            PartSerialNumber::factory()->count(10)->create(['part_id' => $part->id]);
            PartSerialNumber::factory()->count(2)->sold()->create(['part_id' => $part->id]);
        });
    }
}
