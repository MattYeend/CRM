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

            'attachments.view.all',
            'attachments.view.own',
            'attachments.create',
            'attachments.update.any',
            'attachments.upload.any',
            'attachments.delete.any',
            'attachments.restore.any',

            'companies.view.all',
            'companies.view.own',
            'companies.create',
            'companies.update.any',
            'companies.delete.any',
            'companies.restore.any',
            'companies.restore.any',

            'deals.view.all',
            'deals.view.own',
            'deals.create',
            'deals.update.any',
            'deals.delete.any',
            'deals.restore.any',
            'deals.restore.any',

            'deals.products.add',
            'deals.products.update',
            'deals.products.remove',
            'deals.products.restore',

            'invoices.view.all',
            'invoices.view.own',
            'invoices.create',
            'invoices.update.any',
            'invoices.delete.any',
            'invoices.restore.any',

            'invoiceItems.view.all',
            'invoiceItems.view.own',
            'invoiceItems.create',
            'invoiceItems.update.any',
            'invoiceItems.delete.any',
            'invoiceItems.restore.any',

            'jobTitles.view.all',
            'jobTitles.view.own',
            'jobTitles.create',
            'jobTitles.update.any',
            'jobTitles.delete.any',
            'jobTitles.restore.any',

            'leads.view.all',
            'leads.view.own',
            'leads.create',
            'leads.update.any',
            'leads.delete.any',
            'leads.restore.any',

            'learnings.view.all',
            'learnings.view.own',
            'learnings.create',
            'learnings.update.any',
            'learnings.delete.any',
            'learnings.access',
            'learnings.complete.any',
            'learnings.incomplete.any',
            'learnings.restore.any',

            'notes.view.all',
            'notes.view.own',
            'notes.create',
            'notes.update.any',
            'notes.delete.any',
            'notes.restore.any',

            'orders.view.all',
            'orders.view.own',
            'orders.create',
            'orders.update.any',
            'orders.delete.any',
            'orders.restore.any',

            'orders.products.add',
            'orders.products.update',
            'orders.products.remove',
            'orders.products.restore',

            'parts.view.all',
            'parts.view.own',
            'parts.create',
            'parts.update.any',
            'parts.delete.any',
            'parts.restore.any',

            'partCategories.view.all',
            'partCategories.view.own',
            'partCategories.create',
            'partCategories.update.any',
            'partCategories.delete.any',
            'partCategories.restore.any',

            'partImages.view.all',
            'partImages.view.own',
            'partImages.create',
            'partImages.update.any',
            'partImages.delete.any',
            'partImages.restore.any',

            'partStockMovements.view.all',
            'partStockMovements.view.own',
            'partStockMovements.create',

            'partSerialNumbers.view.all',
            'partSerialNumbers.view.own',
            'partSerialNumbers.create',
            'partSerialNumbers.update.any',
            'partSerialNumbers.delete.any',
            'partSerialNumbers.restore.any',

            'permissions.view.all',
            'permissions.view.own',
            'permissions.create',
            'permissions.update.any',
            'permissions.delete.any',
            'permissions.restore.any',
    
            'pipelines.view.all',
            'pipelines.view.own',
            'pipelines.create',
            'pipelines.update.any',
            'pipelines.delete.any',
            'pipelines.assign',
            'pipelines.restore.any',

            'pipelineStages.view.all',
            'pipelineStages.view.own',
            'pipelineStages.create',
            'pipelineStages.update.any',
            'pipelineStages.delete.any',
            'pipelineStages.assign',
            'pipelineStages.restore.any',

            'products.view.all',
            'products.view.own',
            'products.create',
            'products.update.any',
            'products.delete.any',
            'products.restore.any',

            'quotes.view.all',
            'quotes.view.own',
            'quotes.create',
            'quotes.update.any',
            'quotes.delete.any',
            'quotes.restore.any',

            'quotes.products.add',
            'quotes.products.update',
            'quotes.products.remove',
            'quotes.products.restore',

            'tasks.view.all',
            'tasks.view.own',
            'tasks.create',
            'tasks.update.any',
            'tasks.delete.any',
            'tasks.restore.any',

            'reports.view',
            'data.export',

            'settings.view',
            'settings.manage',

            'roles.view.all',

            'suppliers.view.all',
            'suppliers.view.own',
            'suppliers.create',
            'suppliers.update.any',
            'suppliers.delete.any',
            'suppliers.restore.any',

            'users.view.all',
            'users.view.own',
            'users.create',
            'users.update.any',
            'users.delete.any',
            'users.assign.roles',
            'users.assign.permissions',
            'users.restore.any',
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

            'attachments.upload.own',
            'attachments.view.all',
            'attachments.view.own',
            'attachments.create',
            'attachments.update.own',
            'attachments.delete.own',
            'attachments.restore.own',

            'companies.view.all',
            'companies.view.own',
            'companies.create',
            'companies.update.own',
            'companies.delete.own',
            'companies.restore.own',
            'companies.restore.own',

            'deals.view.all',
            'deals.view.own',
            'deals.create',
            'deals.update.own',
            'deals.delete.own',
            'deals.restore.own',

            'invoices.view.all',
            'invoices.view.own',
            'invoices.create',
            'invoices.update.own',
            'invoices.delete.own',
            'invoices.restore.own',

            'invoiceItems.view.all',
            'invoiceItems.view.own',
            'invoiceItems.create',
            'invoiceItems.update.own',
            'invoiceItems.delete.own',
            'invoiceItems.restore.own',

            'jobTitles.view.all',
            'jobTitles.view.own',
            'jobTitles.create',
            'jobTitles.update.own',
            'jobTitles.delete.own',
            'jobTitles.restore.own',

            'leads.view.all',
            'leads.view.own',
            'leads.create',
            'leads.update.own',
            'leads.delete.own',
            'leads.restore.own',

            'learnings.view.all',
            'learnings.view.own',
            'learnings.access',
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

            'orders.view.all',
            'orders.view.own',
            'orders.create',
            'orders.update.own',
            'orders.delete.own',
            'orders.restore.own',

            'parts.view.all',
            'parts.view.own',
            'parts.create',
            'parts.update.own',
            'parts.delete.own',
            'parts.restore.own',

            'partCategories.view.all',
            'partCategories.view.own',
            'partCategories.create',
            'partCategories.update.own',
            'partCategories.delete.own',
            'partCategories.restore.own',

            'partImages.view.all',
            'partImages.view.own',
            'partImages.create',
            'partImages.update.own',
            'partImages.delete.own',
            'partImages.restore.own',

            'partStockMovements.view.all',
            'partStockMovements.view.own',
            'partStockMovements.create',

            'partSerialNumbers.view.all',
            'partSerialNumbers.view.own',
            'partSerialNumbers.create',
            'partSerialNumbers.update.own',
            'partSerialNumbers.delete.own',
            'partSerialNumbers.restore.own',

            'permissions.view.own',

            'pipelines.view.all',
            'pipelines.view.own',
            'pipelines.create',
            'pipelines.update.own',
            'pipelines.delete.own',
            'pipelines.restore.own',

            'pipelineStages.view.all',
            'pipelineStages.view.own',
            'pipelineStages.create',
            'pipelineStages.update.own',
            'pipelineStages.assign',
            'pipelineStages.delete.own',
            'pipelineStages.restore.own',

            'products.view.all',
            'products.view.own',
            'products.create',
            'products.update.own',
            'products.delete.own',
            'products.restore.own',

            'quotes.view.all',
            'quotes.view.own',
            'quotes.create',
            'quotes.update.own',
            'quotes.delete.own',
            'quotes.restore.own',

            'tasks.view.all',
            'tasks.view.own',
            'tasks.create',
            'tasks.update.own',
            'tasks.delete.own',
            'tasks.restore.own',

            'reports.view',
            'roles.view.own',

            'suppliers.view.all',
            'suppliers.view.own',
            'suppliers.create',
            'suppliers.update.own',
            'suppliers.delete.own',
            'suppliers.restore.own',

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
