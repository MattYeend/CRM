<?php

namespace Database\Seeders;

use App\Models\Industry;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class IndustrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $industries = [
            'Accounting',
            'Advertising',
            'Aerospace',
            'Agriculture',
            'Architecture',
            'Automotive',
            'Banking',
            'Biotechnology',
            'Construction',
            'Consulting',
            'Defence',
            'Education',
            'Energy',
            'Engineering',
            'Entertainment',
            'Environmental Services',
            'Fashion',
            'Finance',
            'Food & Beverage',
            'Government',
            'Healthcare',
            'Hospitality',
            'Human Resources',
            'Insurance',
            'IT Services',
            'Legal',
            'Logistics',
            'Manufacturing',
            'Marketing',
            'Media',
            'Mining',
            'Non-Profit',
            'Pharmaceuticals',
            'Property',
            'Public Relations',
            'Real Estate',
            'Recruitment',
            'Retail',
            'Sports',
            'Telecommunications',
            'Technology',
            'Tourism',
            'Transportation',
            'Utilities',
            'Wholesale',
            'Other',
        ];

        foreach ($industries as $name) {
            Industry::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name]
            );
        }
    }
}
