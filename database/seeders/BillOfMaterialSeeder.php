<?php

namespace Database\Seeders;

use App\Models\Part;
use App\Models\BillOfMaterial;
use Illuminate\Database\Seeder;

class BillOfMaterialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $parts = Part::where('is_manufactured', true)->get();
        $childParts = Part::where('is_manufactured', false)->pluck('id');

        $parts->each(function (Part $part) use ($childParts) {
            $childParts->random(min(3, $childParts->count()))
                ->each(function ($childId) use ($part) {
                    BillOfMaterial::firstOrCreate(
                        [
                            'parent_part_id' => $part->id,
                            'child_part_id' => $childId,
                        ],
                        [
                            'quantity' => rand(1, 10),
                            'unit_of_measure' => 'each',
                        ]
                    );
                });
        });
    }
}
