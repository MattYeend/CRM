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
            CompanySeeder::class,
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
            LearningQuestionSeeder::class,
            PermissionSeeder::class,
            RoleSeeder::class,
            PermissionRoleSeeder::class,
            UserSeeder::class,
            OrderSeeder::class,
            OrderProductSeeder::class,
            NoteSeeder::class,
            LearningUserSeeder::class,
            NoteUserSeeder::class,
            QuoteSeeder::class,
            QuoteProductSeeder::class,
            TaskSeeder::class,
            ActivitySeeder::class,
            AttachmentSeeder::class,
            PartCategorySeeder::class,
            SupplierSeeder::class,
            PartSeeder::class,
            PartSupplierSeeder::class,
            PartImageSeeder::class,
            // PartStockMovementSeeder::class,
            // PartSerialNumberSeeder::class,
            // BillOfMaterialSeeder::class,
        ]);
    }
}
