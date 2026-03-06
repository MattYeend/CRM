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
            ['name' => 'activities.view.all', 'label' => 'View activities'],
            ['name' => 'activities.view', 'label' => 'View activity'],
            ['name' => 'activities.create', 'label' => 'Create activities'],
            ['name' => 'activities.update.any', 'label' => 'Update any activities'],
            ['name' => 'activities.update.own', 'label' => 'Update own activities'],
            ['name' => 'activities.delete', 'label' => 'Delete activities'],

            // Attachments
            ['name' => 'attachments.view.all', 'label' => 'View attachments'],
            ['name' => 'attachments.view', 'label' => 'View attachment'],
            ['name' => 'attachments.create', 'label' => 'Create attachments'],
            ['name' => 'attachments.update.own', 'label' => 'Update own attachments'],
            ['name' => 'attachments.update.any', 'label' => 'Update any attachments'],
            ['name' => 'attachments.upload.own', 'label' => 'Upload own attachments'],
            ['name' => 'attachments.upload.any', 'label' => 'Upload any attachments'],
            ['name' => 'attachments.delete', 'label' => 'Delete attachments'],

            // Companies
            ['name' => 'companies.view.all', 'label' => 'View companies'],
            ['name' => 'companies.view', 'label' => 'View company'],
            ['name' => 'companies.create', 'label' => 'Create companies'],
            ['name' => 'companies.update.any', 'label' => 'Update any companies'],
            ['name' => 'companies.update.own', 'label' => 'Update own companies'],
            ['name' => 'companies.delete', 'label' => 'Delete companies'],
            
            // Contacts
            ['name' => 'contacts.view.all', 'label' => 'View contacts'],
            ['name' => 'contacts.view', 'label' => 'View contact'],
            ['name' => 'contacts.create', 'label' => 'Create contacts'],
            ['name' => 'contacts.update.any', 'label' => 'Update any contacts'],
            ['name' => 'contacts.update.own', 'label' => 'Update own contacts'],
            ['name' => 'contacts.delete', 'label' => 'Delete contacts'],

            // Deals
            ['name' => 'deals.view.all', 'label' => 'View deals'],
            ['name' => 'deals.view', 'label' => 'View deal'],
            ['name' => 'deals.create', 'label' => 'Create deals'],
            ['name' => 'deals.update.own', 'label' => 'Update own deals'],
            ['name' => 'deals.update.any', 'label' => 'Update any deals'],
            ['name' => 'deals.delete', 'label' => 'Delete deals'],

            // Invoices / finance
            ['name' => 'invoices.view.all', 'label' => 'View invoices'],
            ['name' => 'invoices.view', 'label' => 'View invoice'],
            ['name' => 'invoices.create', 'label' => 'Create invoices'],
            ['name' => 'invoices.update.own', 'label' => 'Update own invoices'],
            ['name' => 'invoices.update.any', 'label' => 'Update any invoices'],
            ['name' => 'invoices.delete', 'label' => 'Delete invoices'],

            ['name' => 'invoiceItems.view.all', 'label' => 'View invoice items'],
            ['name' => 'invoiceItems.view', 'label' => 'View invoice item'],
            ['name' => 'invoiceItems.create', 'label' => 'Create invoice items'],
            ['name' => 'invoiceItems.update.own', 'label' => 'Update own invoice items'],
            ['name' => 'invoiceItems.update.any', 'label' => 'Update any invoice items'],
            ['name' => 'invoiceItems.delete', 'label' => 'Delete invoice items'],

            // Leads 
            ['name' => 'leads.view.all', 'label' => 'View leads'],
            ['name' => 'leads.view', 'label' => 'View lead'],
            ['name' => 'leads.create', 'label' => 'Create leads'],
            ['name' => 'leads.update.own', 'label' => 'Update  own leads'],
            ['name' => 'leads.update.any', 'label' => 'Update any leads'],
            ['name' => 'leads.delete', 'label' => 'Delete leads'],

            // Learning Material
            ['name' => 'learning.view.all', 'label' => 'View learning materials'],
            ['name' => 'learning.view', 'label' => 'View learning material'],
            ['name' => 'learning.create', 'label' => 'Create learning materials'],
            ['name' => 'learning.update.own', 'label' => 'Update own learning materials'],
            ['name' => 'learning.update.any', 'label' => 'Update any learning materials'],
            ['name' => 'learning.delete', 'label' => 'Delete learning materials'],
            ['name' => 'learning.manage', 'label' => 'Manage learning materials'],
            ['name' => 'learning.access', 'label' => 'Access learning materials'],

            // Notes
            ['name' => 'notes.view.all', 'label' => 'View notes'],
            ['name' => 'notes.view', 'label' => 'View note'],
            ['name' => 'notes.create', 'label' => 'Create notes'],
            ['name' => 'notes.update.own', 'label' => 'Update own notes'],
            ['name' => 'notes.update.any', 'label' => 'Update any notes'],
            ['name' => 'notes.delete', 'label' => 'Delete notes'],

            // Permissions
            ['name' => 'permissions.view.all', 'label' => 'View permissions'],
            ['name' => 'permissions.view', 'label' => 'View permission'],
            ['name' => 'permissions.create', 'label' => 'Create permissions'],
            ['name' => 'permissions.update.any', 'label' => 'Update any permissions'],
            ['name' => 'permissions.delete', 'label' => 'Delete permissions'],

            // Pipelines
            ['name' => 'pipelines.view.all', 'label' => 'View pipelines'],
            ['name' => 'pipelines.view', 'label' => 'View pipeline'],
            ['name' => 'pipelines.create', 'label' => 'Create pipelines'],
            ['name' => 'pipelines.update.own', 'label' => 'Update own pipelines'],
            ['name' => 'pipelines.update.any', 'label' => 'Update any pipelines'],
            ['name' => 'pipelines.delete', 'label' => 'Delete pipelines'],
            ['name' => 'pipelines.manage', 'label' => 'Manage pipelines'],
            ['name' => 'pipelines.assign', 'label' => 'Assign pipelines to entities'],

            // Pipeline Stages
            ['name' => 'pipelineStages.view.all', 'label' => 'View pipeline stages'],
            ['name' => 'pipelineStages.view', 'label' => 'View pipeline stage'],
            ['name' => 'pipelineStages.create', 'label' => 'Create pipeline stages'],
            ['name' => 'pipelineStages.update.own', 'label' => 'Update own pipeline stages'],
            ['name' => 'pipelineStages.update.any', 'label' => 'Update any pipeline stages'],
            ['name' => 'pipelineStages.delete', 'label' => 'Delete pipeline stages'],
            ['name' => 'pipelineStages.manage', 'label' => 'Manage pipeline stages'],
            ['name' => 'pipelineStages.assign', 'label' => 'Assign pipeline stages to pipelines'],

            // Products
            ['name' => 'products.view.all', 'label' => 'View products'],
            ['name' => 'products.view', 'label' => 'View products'],
            ['name' => 'products.create', 'label' => 'Create products'],
            ['name' => 'products.update.own', 'label' => 'Update own products'],
            ['name' => 'products.update.any', 'label' => 'Update any products'],
            ['name' => 'products.delete', 'label' => 'Delete products'],

            // Reports & exports
            ['name' => 'reports.view', 'label' => 'View reports'],
            ['name' => 'data.export', 'label' => 'Export data'],

            // Roles
            ['name' => 'roles.view.all', 'label' => 'View roles'],
            ['name' => 'roles.view', 'label' => 'View role'],

            // Tasks
            ['name' => 'tasks.view.all', 'label' => 'View tasks'],
            ['name' => 'tasks.view', 'label' => 'View task'],
            ['name' => 'tasks.create', 'label' => 'Create tasks'],
            ['name' => 'tasks.update.own', 'label' => 'Update own tasks'],
            ['name' => 'tasks.update.any', 'label' => 'Update any tasks'],
            ['name' => 'tasks.delete', 'label' => 'Delete tasks'],

            // Settings
            ['name' => 'settings.view', 'label' => 'View settings'],
            ['name' => 'settings.manage', 'label' => 'Manage settings'],

            // Users
            ['name' => 'users.view.all', 'label' => 'View users'],
            ['name' => 'users.view', 'label' => 'View user'],
            ['name' => 'users.create', 'label' => 'Create users'],
            ['name' => 'users.update.own', 'label' => 'Update own user'],
            ['name' => 'users.update.any', 'label' => 'Update any user'],
            ['name' => 'users.delete', 'label' => 'Delete users'],
            ['name' => 'users.manage', 'label' => 'Manage users'],
            ['name' => 'users.assign.roles', 'label' => 'Assign roles to users'],
            ['name' => 'users.assign.permissions', 'label' => 'Assign permissions to users'],
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
