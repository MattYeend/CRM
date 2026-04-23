<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Fetch roles
        $roles = DB::table('roles')->pluck('id', 'label');
        $permissions = DB::table('permissions')->pluck('id', 'name');

        /*
        |--------------------------------------------------------------------------
        | Super Admin – everything
        |--------------------------------------------------------------------------
        */
        foreach ($permissions as $permissionId) {
            DB::table('permission_role')->updateOrInsert([
                'role_id' => $roles['super_admin'],
                'permission_id' => $permissionId,
            ], [
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Admin – company level power
        |--------------------------------------------------------------------------
        */
        $adminPermissions = [
            'activities.view.all',
            'activities.view.own',
            'activities.create',
            'activities.update.any',
            'activities.delete.any',
            'activities.restore.any',
            'activities.assign',
            'activities.access.any',

            'attachments.view.all',
            'attachments.view.own',
            'attachments.create',
            'attachments.update.any',
            'attachments.upload.any',
            'attachments.delete.any',
            'attachments.restore.any',
            'attachments.access.any',

            'billOfMaterials.view.all',
            'billOfMaterials.create',
            'billOfMaterials.update.any',
            'billOfMaterials.upload.any',
            'billOfMaterials.delete.any',
            'billOfMaterials.restore.any',
            'billOfMaterials.access.any',

            'companies.view.all',
            'companies.view.own',
            'companies.create',
            'companies.update.any',
            'companies.delete.any',
            'companies.restore.any',
            'companies.restore.any',
            'companies.access.any',

            'deals.view.all',
            'deals.view.own',
            'deals.create',
            'deals.update.any',
            'deals.delete.any',
            'deals.restore.any',
            'deals.restore.any',
            'deals.access.any',

            'deals.products.add',
            'deals.products.update',
            'deals.products.remove',
            'deals.products.restore',
            'deals.products.access.any',

            'industries.view.all',
            'industries.view.own',
            'industries.create',
            'industries.update.any',
            'industries.delete.any',
            'industries.restore.any',
            'industries.access.any',

            'invoices.view.all',
            'invoices.view.own',
            'invoices.create',
            'invoices.update.any',
            'invoices.delete.any',
            'invoices.restore.any',
            'invoices.assign',
            'invoices.access.any',

            'invoiceItems.view.all',
            'invoiceItems.view.own',
            'invoiceItems.create',
            'invoiceItems.update.any',
            'invoiceItems.delete.any',
            'invoiceItems.restore.any',
            'invoiceItems.assign',
            'invoiceItems.access.any',

            'jobTitles.view.all',
            'jobTitles.view.own',
            'jobTitles.create',
            'jobTitles.update.any',
            'jobTitles.delete.any',
            'jobTitles.restore.any',
            'jobTitles.access.any',

            'leads.view.all',
            'leads.view.own',
            'leads.create',
            'leads.update.any',
            'leads.delete.any',
            'leads.restore.any',
            'leads.access.any',

            'learnings.view.all',
            'learnings.view.own',
            'learnings.create',
            'learnings.update.any',
            'learnings.delete.any',
            'learnings.assign',
            'learnings.access.any',
            'learnings.complete.any',
            'learnings.incomplete.any',
            'learnings.restore.any',

            'notes.view.all',
            'notes.view.own',
            'notes.create',
            'notes.update.any',
            'notes.delete.any',
            'notes.restore.any',
            'notes.access.any',

            'orders.view.all',
            'orders.view.own',
            'orders.create',
            'orders.update.any',
            'orders.delete.any',
            'orders.restore.any',
            'orders.access.any',

            'orders.products.add',
            'orders.products.update',
            'orders.products.remove',
            'orders.products.restore',
            'orders.products.access.any',

            'parts.view.all',
            'parts.view.own',
            'parts.create',
            'parts.update.any',
            'parts.delete.any',
            'parts.restore.any',
            'parts.access.any',

            'partCategories.view.all',
            'partCategories.view.own',
            'partCategories.create',
            'partCategories.update.any',
            'partCategories.delete.any',
            'partCategories.restore.any',
            'partCategories.access.any',

            'partImages.view.all',
            'partImages.view.own',
            'partImages.create',
            'partImages.update.any',
            'partImages.delete.any',
            'partImages.restore.any',
            'partImages.access.any',

            'partStockMovements.view.all',
            'partStockMovements.view.own',
            'partStockMovements.create',
            'partStockMovements.access.any',

            'partSerialNumbers.view.all',
            'partSerialNumbers.create',
            'partSerialNumbers.update.any',
            'partSerialNumbers.delete.any',
            'partSerialNumbers.restore.any',
            'partSerialNumbers.access.any',

            'permissions.view.all',
            'permissions.view.own',
            'permissions.create',
            'permissions.update.any',
            'permissions.delete.any',
            'permissions.restore.any',
            'permissions.access.any',
    
            'pipelines.view.all',
            'pipelines.view.own',
            'pipelines.create',
            'pipelines.update.any',
            'pipelines.delete.any',
            'pipelines.assign',
            'pipelines.restore.any',
            'pipelines.access.any',

            'pipelineStages.view.all',
            'pipelineStages.view.own',
            'pipelineStages.create',
            'pipelineStages.update.any',
            'pipelineStages.delete.any',
            'pipelineStages.assign',
            'pipelineStages.restore.any',
            'pipelineStages.access.any',

            'products.view.all',
            'products.view.own',
            'products.create',
            'products.update.any',
            'products.delete.any',
            'products.restore.any',
            'products.access.any',

            'quotes.view.all',
            'quotes.view.own',
            'quotes.create',
            'quotes.update.any',
            'quotes.delete.any',
            'quotes.restore.any',
            'quotes.access.any',

            'quotes.products.add',
            'quotes.products.update',
            'quotes.products.remove',
            'quotes.products.restore',
            'quotes.products.access.any',

            'tasks.view.all',
            'tasks.view.own',
            'tasks.create',
            'tasks.update.any',
            'tasks.delete.any',
            'tasks.restore.any',
            'tasks.assign',
            'tasks.access.any',

            'reports.view',
            'data.export',

            'settings.view',
            'settings.manage',
            'settings.access.any',

            'roles.view.all',
            'roles.access.any',

            'suppliers.view.all',
            'suppliers.view.own',
            'suppliers.create',
            'suppliers.update.any',
            'suppliers.delete.any',
            'suppliers.restore.any',
            'suppliers.access.any',

            'users.view.all',
            'users.view.own',
            'users.create',
            'users.update.any',
            'users.delete.any',
            'users.assign.roles',
            'users.assign.permissions',
            'users.restore.any',
            'users.access.any',
        ];

        foreach ($adminPermissions as $permissionName) {
            DB::table('permission_role')->updateOrInsert([
                'role_id' => $roles['admin'],
                'permission_id' => $permissions[$permissionName],
            ], [
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | User – day to day CRM usage
        |--------------------------------------------------------------------------
        */
        $userPermissions = [
            'activities.view.all',
            'activities.view.own',
            'activities.create',
            'activities.update.own',
            'activities.delete.own',
            'activities.restore.own',
            'activities.access.own',

            'attachments.upload.own',
            'attachments.view.all',
            'attachments.view.own',
            'attachments.create',
            'attachments.update.own',
            'attachments.delete.own',
            'attachments.restore.own',
            'attachments.access.own',

            'billOfMaterials.view.own',
            'billOfMaterials.create',
            'billOfMaterials.update.own',
            'billOfMaterials.delete.own',
            'billOfMaterials.restore.own',
            'billOfMaterials.access.own',

            'companies.view.all',
            'companies.view.own',
            'companies.create',
            'companies.update.own',
            'companies.delete.own',
            'companies.restore.own',
            'companies.access.own',

            'deals.view.all',
            'deals.view.own',
            'deals.create',
            'deals.update.own',
            'deals.delete.own',
            'deals.restore.own',
            'deals.access.own',

            'deals.products.access.own',

            'industries.view.all',
            'industries.view.own',
            'industries.create',
            'industries.update.own',
            'industries.delete.own',
            'industries.restore.own',
            'industries.access.own',

            'invoices.view.all',
            'invoices.view.own',
            'invoices.create',
            'invoices.update.own',
            'invoices.delete.own',
            'invoices.restore.own',
            'invoices.access.own',

            'invoiceItems.view.all',
            'invoiceItems.view.own',
            'invoiceItems.create',
            'invoiceItems.update.own',
            'invoiceItems.delete.own',
            'invoiceItems.restore.own',
            'invoiceItems.access.own',

            'jobTitles.view.all',
            'jobTitles.view.own',
            'jobTitles.create',
            'jobTitles.update.own',
            'jobTitles.delete.own',
            'jobTitles.restore.own',
            'jobTitles.access.own',

            'leads.view.all',
            'leads.view.own',
            'leads.create',
            'leads.update.own',
            'leads.delete.own',
            'leads.restore.own',
            'leads.access.own',

            'learnings.view.all',
            'learnings.view.own',
            'learnings.access.own',
            'learnings.create',
            'learnings.update.own',
            'learnings.delete.own',
            'learnings.complete.own',
            'learnings.incomplete.own',
            'learnings.restore.own',

            'notes.view.all',
            'notes.view.own',
            'notes.create',
            'notes.update.own',
            'notes.delete.own',
            'notes.restore.own',
            'notes.access.own',

            'orders.view.all',
            'orders.view.own',
            'orders.create',
            'orders.update.own',
            'orders.delete.own',
            'orders.restore.own',
            'orders.access.own',

            'orders.products.access.own',

            'parts.view.all',
            'parts.view.own',
            'parts.create',
            'parts.update.own',
            'parts.delete.own',
            'parts.restore.own',
            'parts.access.own',

            'partCategories.view.all',
            'partCategories.view.own',
            'partCategories.create',
            'partCategories.update.own',
            'partCategories.delete.own',
            'partCategories.restore.own',
            'partCategories.access.own',

            'partImages.view.all',
            'partImages.view.own',
            'partImages.create',
            'partImages.update.own',
            'partImages.delete.own',
            'partImages.restore.own',
            'partImages.access.own',

            'partStockMovements.view.all',
            'partStockMovements.view.own',
            'partStockMovements.create',
            'partStockMovements.access.own',

            'partSerialNumbers.view.all',
            'partSerialNumbers.create',
            'partSerialNumbers.update.own',
            'partSerialNumbers.delete.own',
            'partSerialNumbers.restore.own',
            'partSerialNumbers.access.own',

            'permissions.view.own',
            'permissions.access.own',

            'pipelines.view.all',
            'pipelines.view.own',
            'pipelines.create',
            'pipelines.update.own',
            'pipelines.delete.own',
            'pipelines.restore.own',
            'pipelines.access.own',

            'pipelineStages.view.all',
            'pipelineStages.view.own',
            'pipelineStages.create',
            'pipelineStages.update.own',
            'pipelineStages.assign',
            'pipelineStages.delete.own',
            'pipelineStages.restore.own',
            'pipelineStages.access.own',

            'products.view.all',
            'products.view.own',
            'products.create',
            'products.update.own',
            'products.delete.own',
            'products.restore.own',
            'products.access.own',

            'quotes.view.all',
            'quotes.view.own',
            'quotes.create',
            'quotes.update.own',
            'quotes.delete.own',
            'quotes.restore.own',
            'quotes.access.own',

            'quotes.products.access.own',

            'tasks.view.all',
            'tasks.view.own',
            'tasks.create',
            'tasks.update.own',
            'tasks.delete.own',
            'tasks.restore.own',
            'tasks.access.own',

            'reports.view',
            'roles.view.own',
            'roles.access.own',

            'settings.access.own',

            'suppliers.view.all',
            'suppliers.view.own',
            'suppliers.create',
            'suppliers.update.own',
            'suppliers.delete.own',
            'suppliers.restore.own',
            'suppliers.access.own',

            'users.view.all',
            'users.view.own',
            'users.update.own',
            'users.delete.own',
            'users.restore.own',
        ];

        foreach ($userPermissions as $permissionName) {
            DB::table('permission_role')->updateOrInsert([
                'role_id' => $roles['user'],
                'permission_id' => $permissions[$permissionName],
            ], [
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
