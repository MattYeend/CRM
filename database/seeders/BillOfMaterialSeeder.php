<?php

namespace Database\Seeders;

use App\Models\Part;
use App\Models\Product;
use App\Models\BillOfMaterial;
use App\Models\User;
use Illuminate\Database\Seeder;

class BillOfMaterialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedPartBoms();
        $this->seedProductBoms();
    }

    /**
     * Seed BOMs for manufactured parts.
     */
    protected function seedPartBoms(): void
    {
        $parts = Part::where('is_manufactured', true)->get();
        $childParts = Part::where('is_manufactured', false)->pluck('id');

        if ($parts->isEmpty() || $childParts->isEmpty()) {
            return;
        }

        BillOfMaterial::factory(50)
            ->make()
            ->each(function ($bom) {
                if($bom->manufacturable_id) {
                    $bom->save();
                }
            });
    }

    /**
     * Seed BOMs for products that are assembled from parts.
     */
    protected function seedProductBoms(): void
    {
        $products = Product::whereNotNull('sku')->get();
        $childParts = Part::pluck('id');

        if ($products->isEmpty() || $childParts->isEmpty()) {
            return;
        }

        BillOfMaterial::factory(50)
            ->make()
            ->each(function ($bom) {
                if($bom->manufacturable_id) {
                    $bom->save();
                }
            });
    }
}
