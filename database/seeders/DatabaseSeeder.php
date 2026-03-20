<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CompanySeeder::class,
            ContactSeeder::class,
            ProductSeeder::class,
            DealSeeder::class,
            DealProductSeeder::class,
            PipelineSeeder::class,
            PipelineStageSeeder::class,
            InvoiceSeeder::class,
            InvoiceItemSeeder::class,
            JobTitleSeeder::class,
            PermissionSeeder::class,
            RoleSeeder::class,
            PermissionRoleSeeder::class,
            ActivitySeeder::class,
            AttachmentSeeder::class,
        ]);
    }
}
