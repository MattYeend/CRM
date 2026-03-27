<?php

namespace Database\Seeders;

use App\Models\Part;
use App\Models\PartStockMovement;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PartStockMovementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Part::all()->each(function (Part $part) {
            PartStockMovement::factory()->count(5)->create(['part_id' => $part->id]);
        });
    }
}
