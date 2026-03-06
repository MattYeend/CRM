<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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

            'attachments.view.all',
            'attachments.view.own',
            'attachments.create',
            'attachments.update.any',
            'attachments.upload.any',
            'attachments.delete.any',

            'companies.view.all',
            'companies.view.own',
            'companies.create',
            'companies.update.any',
            'companies.delete.any',
            'companies.restore.any',

            'contacts.view.all',
            'contacts.view.own',
            'contacts.create',
            'contacts.update.any',
            'contacts.delete.any',

            'deals.view.all',
            'deals.view.own',
            'deals.create',
            'deals.update.any',
            'deals.delete.any',
            'deals.restore.any',

            'invoices.view.all',
            'invoices.view.own',
            'invoices.create',
            'invoices.update.any',
            'invoices.delete.any',

            'invoiceItems.view.all',
            'invoiceItems.view.own',
            'invoiceItems.create',
            'invoiceItems.update.any',
            'invoiceItems.delete.any',

            'leads.view.all',
            'leads.view.own',
            'leads.create',
            'leads.update.any',
            'leads.delete.any',
            'leads.restore.any',

            'learning.view.all',
            'learning.view.own',
            'learning.create',
            'learning.update.any',
            'learning.delete.any',
            'learning.manage',
            'learning.access',
            'learning.complete.any',
            'learning.incomplete.any',

            'notes.view.all',
            'notes.view.own',
            'notes.create',
            'notes.update.any',
            'notes.delete.any',

            'tasks.view.all',
            'tasks.view.own',
            'tasks.create',
            'tasks.update.any',
            'tasks.delete.any',

            'permissions.view.all',
            'permissions.view.own',
            'permissions.create',
            'permissions.update.any',
            'permissions.delete.any',
    
            'pipelines.view.all',
            'pipelines.view.own',
            'pipelines.create',
            'pipelines.update.any',
            'pipelines.delete.any',
            'pipelines.manage',
            'pipelines.assign',

            'pipelineStages.view.all',
            'pipelineStages.view.own',
            'pipelineStages.create',
            'pipelineStages.update.any',
            'pipelineStages.delete.any',
            'pipelineStages.assign',
            'pipelineStages.manage',

            'products.view.all',
            'products.view.own',
            'products.create',
            'products.update.any',
            'products.delete.any',

            'tasks.view.all',
            'tasks.view.own',
            'tasks.create',
            'tasks.update.any',
            'tasks.delete.any',

            'reports.view',
            'data.export',

            'settings.view',
            'settings.manage',

            'roles.view.all',

            'users.view.all',
            'users.view.own',
            'users.create',
            'users.update.any',
            'users.delete.any',
            'users.assign.roles',
            'users.assign.permissions',
            'users.manage',
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

            'attachments.upload.own',
            'attachments.view.all',
            'attachments.view.own',
            'attachments.create',
            'attachments.update.own',
            'attachments.delete.own',

            'companies.view.all',
            'companies.view.own',
            'companies.create',
            'companies.update.own',
            'companies.delete.own',
            'companies.restore.own',

            'contacts.view.all',
            'contacts.view.own',
            'contacts.create',
            'contacts.update.own',
            'contacts.delete.own',

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

            'invoiceItems.view.all',
            'invoiceItems.view.own',
            'invoiceItems.create',
            'invoiceItems.update.own',
            'invoiceItems.delete.own',

            'leads.view.all',
            'leads.view.own',
            'leads.create',
            'leads.update.own',
            'leads.delete.own',
            'leads.restore.own',

            'learning.view.all',
            'learning.view.own',
            'learning.access',
            'learning.create',
            'learning.update.own',
            'learning.delete.own',
            'learning.complete.own',
            'learning.incomplete.own',

            'notes.view.all',
            'notes.view.own',
            'notes.create',
            'notes.update.own',
            'notes.delete.own',

            'tasks.view.all',
            'tasks.view.own',
            'tasks.create',
            'tasks.update.own',
            'tasks.delete.own',

            'permissions.view.own',
    
            'pipelines.view.all',
            'pipelines.view.own',
            'pipelines.create',
            'pipelines.update.own',
            'pipelines.delete.own',

            'pipelineStages.view.all',
            'pipelineStages.view.own',
            'pipelineStages.create',
            'pipelineStages.update.own',
            'pipelineStages.assign',
            'pipelineStages.delete.own',

            'tasks.view.all',
            'tasks.view.own',
            'tasks.create',
            'tasks.update.own',
            'tasks.delete.own',

            'products.view.all',
            'products.view.own',
            'products.create',
            'products.update.own',
            'products.delete.own',

            'reports.view',
            'roles.view.own',

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
