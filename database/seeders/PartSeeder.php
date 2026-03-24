<?php

namespace Database\Seeders;

use App\Models\Part;
use Illuminate\Database\Seeder;

class PartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Bulk of active, healthy stock
        Part::factory()
            ->count(60)
            ->active()
            ->create();

        // Low-stock parts to trigger reorder scenarios
        Part::factory()
            ->count(10)
            ->lowStock()
            ->create();

        // Out-of-stock parts
        Part::factory()
            ->count(10)
            ->outOfStock()
            ->create();

        // Discontinued parts
        Part::factory()
            ->count(10)
            ->discontinued()
            ->create();

        // Serialised parts (e.g. electronics, high-value items)
        Part::factory()
            ->count(5)
            ->active()
            ->serialised()
            ->create();

        // Batch-tracked parts (e.g. chemicals, consumables)
        Part::factory()
            ->count(5)
            ->active()
            ->batchTracked()
            ->create();

        // Manufactured / sub-assembly parts with BOMs
        Part::factory()
            ->count(5)
            ->manufactured()
            ->create();
    }
}
