<?php

namespace Database\Seeders;

use App\Models\PartCategory;
use Illuminate\Database\Seeder;

class PartCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $parents = [
            ['name' => 'Mechanical', 'slug' => 'mechanical'],
            ['name' => 'Electrical', 'slug' => 'electrical'],
            ['name' => 'Consumables', 'slug' => 'consumables'],
            ['name' => 'Raw Material', 'slug' => 'raw-material'],
        ];

        foreach ($parents as $parent) {
            $category = PartCategory::create([
                ...$parent,
                'description' => "Parts in the {$parent['name']} category.",
            ]);

            PartCategory::factory()->count(3)->withParent($category->id)->create();
        }
    }
}
