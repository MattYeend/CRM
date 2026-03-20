<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JobTitle>
 */
class JobTitleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $groups = [
            'C-Suite',
            'Executive',
            'Management',
            'Engineering',
            'Sales',
            'Marketing',
            'Finance',
            'HR',
            'Operations',
            'Support',
        ];

        $titlesByGroup = [
            'C-Suite' => ['Chief Executive Officer', 'Chief Technology Officer', 'Chief Financial Officer', 'Chief Operating Officer', 'Chief Marketing Officer'],
            'Executive' => ['Vice President', 'Senior Vice President', 'Director', 'Senior Director', 'Managing Director'],
            'Management' => ['Manager', 'Senior Manager', 'Mid-level Manager', 'Junior Manager', 'Project Manager', 'Product Manager'],
            'Engineering' => ['Software Engineer', 'Senior Software Engineer', 'Junior Software Engineer', 'DevOps Engineer', 'Data Engineer', 'Machine Learning Engineer'],
            'Sales' => ['Sales Executive', 'Senior Sales Executive', 'Account Executive', 'Business Development Manager'],
            'Marketing' => ['Marketing Executive', 'Senior Marketing Executive', 'Digital Marketing Manager', 'SEO Specialist'],
            'Finance' => ['Accountant', 'Senior Accountant', 'Financial Analyst', 'Finance Manager', 'Controller'],
            'HR' => ['HR Specialist', 'Senior HR Specialist', 'Recruiter', 'HR Business Partner'],
            'Operations' => ['Operations Coordinator', 'Operations Manager', 'Supply Chain Manager', 'Logistics Manager'],
            'Support' => ['Customer Support Specialist', 'Technical Support Engineer', 'Office Manager', 'Administrative Assistant'],
        ];

        $group = fake()->randomElement($groups);

        $title = fake()->randomElement($titlesByGroup[$group]);

        $short_code = strtoupper(implode('_', array_map(fn($w) => substr($w, 0, 3), explode(' ', $title))));

        return [
            'title' => $title,
            'short_code' => $short_code,
            'group' => $group,
            'is_test' => true,
            'meta' => [],
            'created_by' => User::inRandomOrder()->value('id'),
        ];
    }
}
