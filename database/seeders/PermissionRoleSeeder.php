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
            'attachments.upload.any',
            'attachments.delete',
            'attachments.view.any',
            'attachments.view',
            'attachments.create',
            'attachments.update.any',

            'activities.view.any',
            'activities.view',
            'activities.create',
            'activities.update.any',
            'activities.delete',

            'companies.view.any',
            'companies.view',
            'companies.create',
            'companies.update.any',
            'companies.delete',

            'contacts.view.any',
            'contacts.view',
            'contacts.create',
            'contacts.update.any',
            'contacts.delete',

            'deals.view.any',
            'deals.view',
            'deals.create',
            'deals.update.any',
            'deals.delete',

            'invoices.view.any',
            'invoices.view',
            'invoices.create',
            'invoices.update.any',
            'invoices.delete',

            'invoiceItems.view.any',
            'invoiceItems.view',
            'invoiceItems.create',
            'invoiceItems.update.any',
            'invoiceItems.delete',

            'leads.view.any',
            'leads.view',
            'leads.create',
            'leads.update.any',
            'leads.delete',

            'learning.view.any',
            'learning.view',
            'learning.create',
            'learning.update.any',
            'learning.delete',
            'learning.manage',
            'learning.access',

            'notes.view.any',
            'notes.view',
            'notes.create',
            'notes.update.any',
            'notes.delete',

            'tasks.view.any',
            'tasks.view',
            'tasks.create',
            'tasks.update.any',
            'tasks.delete',

            'permissions.view.any',
            'permissions.view',
            'permissions.create',
            'permissions.update.any',
            'permissions.delete',
    
            'pipelines.view.any',
            'pipelines.view',
            'pipelines.create',
            'pipelines.update.any',
            'pipelines.delete',
            'pipelines.manage',
            'pipelines.assign',

            'pipelineStages.view.all',
            'pipelineStages.view',
            'pipelineStages.create',
            'pipelineStages.update.any',
            'pipelineStages.delete',
            'pipelineStages.assign',
            'pipelineStages.manage',

            'products.view.all',
            'products.view',
            'products.create',
            'products.update.any',
            'products.delete',

            'tasks.view.all',
            'tasks.view',
            'tasks.create',
            'tasks.update.any',
            'tasks.delete',

            'reports.view',
            'data.export',

            'settings.view',
            'settings.manage',

            'roles.view.all',

            'users.view.all',
            'users.view',
            'users.create',
            'users.update.any',
            'users.delete',
            'users.assign.roles',
            'users.assign.permissions',
            'users.manage',
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
            'attachments.upload.own',
            'attachments.view.all',
            'attachments.view',
            'attachments.create',
            'attachments.update.own',

            'activities.view.all',
            'activities.view',
            'activities.create',
            'activities.update.own',

            'companies.view.all',
            'companies.view',
            'companies.create',
            'companies.update.own',

            'contacts.view.all',
            'contacts.view',
            'contacts.create',
            'contacts.update.own',

            'deals.view.all',
            'deals.view',
            'deals.create',
            'deals.update.own',

            'invoices.view.all',
            'invoices.view',
            'invoices.create',
            'invoices.update.own',

            'invoiceItems.view.all',
            'invoiceItems.view',
            'invoiceItems.create',
            'invoiceItems.update.own',

            'leads.view.all',
            'leads.view',
            'leads.create',
            'leads.update.own',

            'learning.view.all',
            'learning.view',
            'learning.access',
            'learning.create',
            'learning.update.own',

            'notes.view.all',
            'notes.view',
            'notes.create',
            'notes.update.own',

            'tasks.view.all',
            'tasks.view',
            'tasks.create',
            'tasks.update.own',

            'permissions.view',
    
            'pipelines.view.all',
            'pipelines.view',
            'pipelines.create',
            'pipelines.update.own',

            'pipelineStages.view.all',
            'pipelineStages.view',
            'pipelineStages.create',
            'pipelineStages.update.own',
            'pipelineStages.assign',

            'tasks.view.all',
            'tasks.view',
            'tasks.create',
            'tasks.update.own',

            'products.view.all',
            'products.view',
            'products.create',
            'products.update.own',

            'reports.view',
            'roles.view',

            'users.view.all',
            'users.view',
            'users.update.own',
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
