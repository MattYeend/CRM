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
            'users.view',
            'users.create',
            'users.update',
            'users.delete',

            'leads.view',
            'leads.create',
            'leads.update.any',
            'leads.delete',

            'deals.view',
            'deals.create',
            'deals.update.any',
            'deals.delete',

            'contacts.view',
            'contacts.create',
            'contacts.update',
            'contacts.delete',

            'tasks.view',
            'tasks.create',
            'tasks.update',
            'tasks.delete',

            'pipelines.view',
            'pipelines.manage',

            'invoices.view',
            'invoices.create',
            'invoices.update',
            'invoices.delete',

            'reports.view',
            'data.export',

            'settings.view',
            'settings.manage',

            'learning.view',
            'learning.create',
            'learning.update',
            'learning.delete',
            'learning.access',
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
            'leads.view',
            'leads.create',
            'leads.update.own',

            'deals.view',
            'deals.create',
            'deals.update.own',

            'contacts.view',
            'contacts.create',
            'contacts.update',

            'tasks.view',
            'tasks.create',
            'tasks.update',

            'notes.create',
            'attachments.upload',

            'pipelines.view',
            'reports.view',

            'learning.view',
            'learning.access',
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
