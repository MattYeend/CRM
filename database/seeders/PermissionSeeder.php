<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Users
            ['name' => 'users.view', 'label' => 'View users'],
            ['name' => 'users.create', 'label' => 'Create users'],
            ['name' => 'users.update', 'label' => 'Update users'],
            ['name' => 'users.delete', 'label' => 'Delete users'],
            ['name' => 'users.manage', 'label' => 'Manage users'],

            // Leads
            ['name' => 'leads.view', 'label' => 'View leads'],
            ['name' => 'leads.create', 'label' => 'Create leads'],
            ['name' => 'leads.update.own', 'label' => 'Update own leads'],
            ['name' => 'leads.update.any', 'label' => 'Update any leads'],
            ['name' => 'leads.delete', 'label' => 'Delete leads'],

            // Deals
            ['name' => 'deals.view', 'label' => 'View deals'],
            ['name' => 'deals.create', 'label' => 'Create deals'],
            ['name' => 'deals.update.own', 'label' => 'Update own deals'],
            ['name' => 'deals.update.any', 'label' => 'Update any deals'],
            ['name' => 'deals.delete', 'label' => 'Delete deals'],

            // Contacts
            ['name' => 'contacts.view', 'label' => 'View contacts'],
            ['name' => 'contacts.create', 'label' => 'Create contacts'],
            ['name' => 'contacts.update', 'label' => 'Update contacts'],
            ['name' => 'contacts.delete', 'label' => 'Delete contacts'],

            // Tasks
            ['name' => 'tasks.view', 'label' => 'View tasks'],
            ['name' => 'tasks.create', 'label' => 'Create tasks'],
            ['name' => 'tasks.update', 'label' => 'Update tasks'],
            ['name' => 'tasks.delete', 'label' => 'Delete tasks'],

            // Notes & attachments
            ['name' => 'notes.create', 'label' => 'Create notes'],
            ['name' => 'notes.delete', 'label' => 'Delete notes'],
            ['name' => 'attachments.upload', 'label' => 'Upload attachments'],
            ['name' => 'attachments.delete', 'label' => 'Delete attachments'],

            // Pipelines
            ['name' => 'pipelines.view', 'label' => 'View pipelines'],
            ['name' => 'pipelines.manage', 'label' => 'Manage pipelines'],

            // Invoices / finance
            ['name' => 'invoices.view', 'label' => 'View invoices'],
            ['name' => 'invoices.create', 'label' => 'Create invoices'],
            ['name' => 'invoices.update', 'label' => 'Update invoices'],
            ['name' => 'invoices.delete', 'label' => 'Delete invoices'],

            // Reports & exports
            ['name' => 'reports.view', 'label' => 'View reports'],
            ['name' => 'data.export', 'label' => 'Export data'],

            // Settings
            ['name' => 'settings.view', 'label' => 'View settings'],
            ['name' => 'settings.manage', 'label' => 'Manage settings'],
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $permission['name']],
                [
                    'label' => $permission['label'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
