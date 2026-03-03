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
            // Activities
            ['name' => 'activities.view', 'label' => 'View activities'],
            ['name' => 'activities.create', 'label' => 'Create activities'],
            ['name' => 'activities.update.any', 'label' => 'Update any activities'],
            ['name' => 'activities.update.own', 'label' => 'Update own activities'],
            ['name' => 'activities.delete', 'label' => 'Delete activities'],

            // Attachments
            ['name' => 'attachments.view', 'label' => 'View attachments'],
            ['name' => 'attachments.create', 'label' => 'Create attachments'],
            ['name' => 'attachments.update.own', 'label' => 'Update own attachments'],
            ['name' => 'attachments.update.any', 'label' => 'Update any attachments'],
            ['name' => 'attachments.upload.own', 'label' => 'Upload own attachments'],
            ['name' => 'attachments.upload.any', 'label' => 'Upload any attachments'],
            ['name' => 'attachments.delete', 'label' => 'Delete attachments'],

            // Companies
            ['name' => 'companies.view', 'label' => 'View companies'],
            ['name' => 'companies.create', 'label' => 'Create companies'],
            ['name' => 'companies.update.any', 'label' => 'Update any companies'],
            ['name' => 'companies.update.own', 'label' => 'Update own companies'],
            ['name' => 'companies.delete', 'label' => 'Delete companies'],
            
            // Contacts
            ['name' => 'contacts.view', 'label' => 'View contacts'],
            ['name' => 'contacts.create', 'label' => 'Create contacts'],
            ['name' => 'contacts.update.any', 'label' => 'Update any contacts'],
            ['name' => 'contacts.update.own', 'label' => 'Update own contacts'],
            ['name' => 'contacts.delete', 'label' => 'Delete contacts'],

            // Deals
            ['name' => 'deals.view', 'label' => 'View deals'],
            ['name' => 'deals.create', 'label' => 'Create deals'],
            ['name' => 'deals.update.own', 'label' => 'Update own deals'],
            ['name' => 'deals.update.any', 'label' => 'Update any deals'],
            ['name' => 'deals.delete', 'label' => 'Delete deals'],

            // Invoices / finance
            ['name' => 'invoices.view', 'label' => 'View invoices'],
            ['name' => 'invoices.create', 'label' => 'Create invoices'],
            ['name' => 'invoices.update.own', 'label' => 'Update own invoices'],
            ['name' => 'invoices.update.any', 'label' => 'Update any invoices'],
            ['name' => 'invoices.delete', 'label' => 'Delete invoices'],
            ['name' => 'invoiceItems.view', 'label' => 'View invoice items'],
            ['name' => 'invoiceItems.create', 'label' => 'Create invoice items'],
            ['name' => 'invoiceItems.update.own', 'label' => 'Update own invoice items'],
            ['name' => 'invoiceItems.update.any', 'label' => 'Update any invoice items'],
            ['name' => 'invoiceItems.delete', 'label' => 'Delete invoice items'],

            // Leads 
            ['name' => 'leads.view', 'label' => 'View leads'],
            ['name' => 'leads.create', 'label' => 'Create leads'],
            ['name' => 'leads.update.own', 'label' => 'Update  own leads'],
            ['name' => 'leads.update.any', 'label' => 'Update any leads'],
            ['name' => 'leads.delete', 'label' => 'Delete leads'],

            // Learning Material
            ['name' => 'learning.view', 'label' => 'View learning material'],
            ['name' => 'learning.create', 'label' => 'Create learning material'],
            ['name' => 'learning.update', 'label' => 'Update learning material'],
            ['name' => 'learning.delete', 'label' => 'Delete learning material'],
            ['name' => 'learning.manage', 'label' => 'Manage learning material'],
            ['name' => 'learning.access', 'label' => 'Access learning material'],

            // Notes
            ['name' => 'notes.view', 'label' => 'View notes'],
            ['name' => 'notes.create', 'label' => 'Create notes'],
            ['name' => 'notes.update', 'label' => 'Update notes'],
            ['name' => 'notes.delete', 'label' => 'Delete notes'],

            // Tasks
            ['name' => 'tasks.view', 'label' => 'View tasks'],
            ['name' => 'tasks.create', 'label' => 'Create tasks'],
            ['name' => 'tasks.update', 'label' => 'Update tasks'],
            ['name' => 'tasks.delete', 'label' => 'Delete tasks'],

            // Pipelines
            ['name' => 'pipelines.view', 'label' => 'View pipelines'],
            ['name' => 'pipelines.manage', 'label' => 'Manage pipelines'],

            // Reports & exports
            ['name' => 'reports.view', 'label' => 'View reports'],
            ['name' => 'data.export', 'label' => 'Export data'],

            // Settings
            ['name' => 'settings.view', 'label' => 'View settings'],
            ['name' => 'settings.manage', 'label' => 'Manage settings'],

            // Users
            ['name' => 'users.view', 'label' => 'View users'],
            ['name' => 'users.create', 'label' => 'Create users'],
            ['name' => 'users.update', 'label' => 'Update users'],
            ['name' => 'users.delete', 'label' => 'Delete users'],
            ['name' => 'users.manage', 'label' => 'Manage users'],
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
