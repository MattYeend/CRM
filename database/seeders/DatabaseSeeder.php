<?php

namespace Database\Seeders;

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
            LeadSeeder::class,
            LearningSeeder::class,
            LearningUserSeeder::class,
            NoteSeeder::class,
            NoteUserSeeder::class,
            OrderSeeder::class,
            OrderProductSeeder::class,
            PermissionSeeder::class,
            RoleSeeder::class,
            PermissionRoleSeeder::class,
            QuoteSeeder::class,
            QuoteProductSeeder::class,
            TaskSeeder::class,
            ActivitySeeder::class,
            AttachmentSeeder::class,
        ]);
    }
}
