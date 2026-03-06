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
        // 'name' => 'x.view.own' = individual model
        $permissions = [
            // Activities
            ['name' => 'activities.view.all', 'label' => 'View activities'],
            ['name' => 'activities.view.own', 'label' => 'View own activities'],
            ['name' => 'activities.create', 'label' => 'Create activities'],
            ['name' => 'activities.update.any', 'label' => 'Update any activities'],
            ['name' => 'activities.update.own', 'label' => 'Update own activities'],
            ['name' => 'activities.delete.any', 'label' => 'Delete any activities'],
            ['name' => 'activities.delete.own', 'label' => 'Delete own activities'],

            // Attachments
            ['name' => 'attachments.view.all', 'label' => 'View attachments'],
            ['name' => 'attachments.view.own', 'label' => 'View own attachments'],
            ['name' => 'attachments.create', 'label' => 'Create attachments'],
            ['name' => 'attachments.update.any', 'label' => 'Update any attachments'],
            ['name' => 'attachments.update.own', 'label' => 'Update own attachments'],
            ['name' => 'attachments.upload.any', 'label' => 'Upload any attachments'],
            ['name' => 'attachments.upload.own', 'label' => 'Upload own attachments'],
            ['name' => 'attachments.delete.any', 'label' => 'Delete any attachments'],
            ['name' => 'attachments.delete.own', 'label' => 'Delete own attachments'],

            // Companies
            ['name' => 'companies.view.all', 'label' => 'View companies'],
            ['name' => 'companies.view.own', 'label' => 'View own companies'],
            ['name' => 'companies.create', 'label' => 'Create companies'],
            ['name' => 'companies.update.any', 'label' => 'Update any companies'],
            ['name' => 'companies.update.own', 'label' => 'Update own companies'],
            ['name' => 'companies.delete.any', 'label' => 'Delete any companies'],
            ['name' => 'companies.delete.own', 'label' => 'Delete own companies'],
            ['name' => 'companies.restore.any', 'label' => 'Restore any companies'],
            ['name' => 'companies.restore.own', 'label' => 'Restore own companies'],
            
            // Contacts
            ['name' => 'contacts.view.all', 'label' => 'View contacts'],
            ['name' => 'contacts.view.own', 'label' => 'View own contacts'],
            ['name' => 'contacts.create', 'label' => 'Create contacts'],
            ['name' => 'contacts.update.any', 'label' => 'Update any contacts'],
            ['name' => 'contacts.update.own', 'label' => 'Update own contacts'],
            ['name' => 'contacts.delete.any', 'label' => 'Delete any contacts'],
            ['name' => 'contacts.delete.own', 'label' => 'Delete own contacts'],

            // Deals
            ['name' => 'deals.view.all', 'label' => 'View deals'],
            ['name' => 'deals.view.own', 'label' => 'View own deals'],
            ['name' => 'deals.create', 'label' => 'Create deals'],
            ['name' => 'deals.update.any', 'label' => 'Update any deals'],
            ['name' => 'deals.update.own', 'label' => 'Update own deals'],
            ['name' => 'deals.delete.any', 'label' => 'Delete any deals'],
            ['name' => 'deals.delete.own', 'label' => 'Delete own deals'],
            ['name' => 'deals.restore.any', 'label' => 'Restore any deals'],
            ['name' => 'deals.restore.own', 'label' => 'Restore own delas'],

            // Invoices / finance
            ['name' => 'invoices.view.all', 'label' => 'View invoices'],
            ['name' => 'invoices.view.own', 'label' => 'View own invoices'],
            ['name' => 'invoices.create', 'label' => 'Create invoices'],
            ['name' => 'invoices.update.any', 'label' => 'Update any invoices'],
            ['name' => 'invoices.update.own', 'label' => 'Update own invoices'],
            ['name' => 'invoices.delete.any', 'label' => 'Delete any invoices'],
            ['name' => 'invoices.delete.own', 'label' => 'Delete own invoices'],

            ['name' => 'invoiceItems.view.all', 'label' => 'View invoice items'],
            ['name' => 'invoiceItems.view.own', 'label' => 'View own invoice items'],
            ['name' => 'invoiceItems.create', 'label' => 'Create invoice items'],
            ['name' => 'invoiceItems.update.any', 'label' => 'Update any invoice items'],
            ['name' => 'invoiceItems.update.own', 'label' => 'Update own invoice items'],
            ['name' => 'invoiceItems.delete.any', 'label' => 'Delete any invoice items'],
            ['name' => 'invoiceItems.delete.own', 'label' => 'Delete own invoice items'],

            // Leads 
            ['name' => 'leads.view.all', 'label' => 'View leads'],
            ['name' => 'leads.view.own', 'label' => 'View own leads'],
            ['name' => 'leads.create', 'label' => 'Create leads'],
            ['name' => 'leads.update.any', 'label' => 'Update any leads'],
            ['name' => 'leads.update.own', 'label' => 'Update  own leads'],
            ['name' => 'leads.delete.any', 'label' => 'Delete any leads'],
            ['name' => 'leads.delete.own', 'label' => 'Delete own leads'],

            // Learning Material
            ['name' => 'learning.view.all', 'label' => 'View learning materials'],
            ['name' => 'learning.view.own', 'label' => 'View own learning materials'],
            ['name' => 'learning.create', 'label' => 'Create learning materials'],
            ['name' => 'learning.update.any', 'label' => 'Update any learning materials'],
            ['name' => 'learning.update.own', 'label' => 'Update own learning materials'],
            ['name' => 'learning.delete.any', 'label' => 'Delete any learning materials'],
            ['name' => 'learning.delete.own', 'label' => 'Delete own learning materials'],
            ['name' => 'learning.manage', 'label' => 'Manage learning materials'],
            ['name' => 'learning.access', 'label' => 'Access learning materials'],

            // Notes
            ['name' => 'notes.view.all', 'label' => 'View notes'],
            ['name' => 'notes.view.own', 'label' => 'View own notes'],
            ['name' => 'notes.create', 'label' => 'Create notes'],
            ['name' => 'notes.update.any', 'label' => 'Update any notes'],
            ['name' => 'notes.update.own', 'label' => 'Update own notes'],
            ['name' => 'notes.delete.any', 'label' => 'Delete any notes'],
            ['name' => 'notes.delete.own', 'label' => 'Delete own notes'],

            // Permissions
            ['name' => 'permissions.view.all', 'label' => 'View permissions'],
            ['name' => 'permissions.view.own', 'label' => 'View own permissions'],
            ['name' => 'permissions.create', 'label' => 'Create permissions'],
            ['name' => 'permissions.update.any', 'label' => 'Update any permissions'],
            ['name' => 'permissions.update.own', 'label' => 'Update own permissions'],
            ['name' => 'permissions.delete.any', 'label' => 'Delete any permissions'],
            ['name' => 'permissions.delete.own', 'label' => 'Detele own permissions'],

            // Pipelines
            ['name' => 'pipelines.view.all', 'label' => 'View pipelines'],
            ['name' => 'pipelines.view.own', 'label' => 'View own pipelines'],
            ['name' => 'pipelines.create', 'label' => 'Create pipelines'],
            ['name' => 'pipelines.update.any', 'label' => 'Update any pipelines'],
            ['name' => 'pipelines.update.own', 'label' => 'Update own pipelines'],
            ['name' => 'pipelines.delete.any', 'label' => 'Delete any pipelines'],
            ['name' => 'pipelines.delete.own', 'label' => 'Delete own pipelines'],
            ['name' => 'pipelines.manage', 'label' => 'Manage pipelines'],
            ['name' => 'pipelines.assign', 'label' => 'Assign pipelines to entities'],

            // Pipeline Stages
            ['name' => 'pipelineStages.view.all', 'label' => 'View pipeline stages'],
            ['name' => 'pipelineStages.view.own', 'label' => 'View own pipeline stages'],
            ['name' => 'pipelineStages.create', 'label' => 'Create pipeline stages'],
            ['name' => 'pipelineStages.update.any', 'label' => 'Update any pipeline stages'],
            ['name' => 'pipelineStages.update.own', 'label' => 'Update own pipeline stages'],
            ['name' => 'pipelineStages.delete.any', 'label' => 'Delete any pipeline stages'],
            ['name' => 'pipelineStages.delete.own', 'label' => 'Detele own pipeline stages'],
            ['name' => 'pipelineStages.manage', 'label' => 'Manage pipeline stages'],
            ['name' => 'pipelineStages.assign', 'label' => 'Assign pipeline stages to pipelines'],

            // Products
            ['name' => 'products.view.all', 'label' => 'View products'],
            ['name' => 'products.view.own', 'label' => 'View own products'],
            ['name' => 'products.create', 'label' => 'Create products'],
            ['name' => 'products.update.any', 'label' => 'Update any products'],
            ['name' => 'products.update.own', 'label' => 'Update own products'],
            ['name' => 'products.delete.any', 'label' => 'Delete any products'],
            ['name' => 'products.delete.own', 'label' => 'Delete own products'],

            // Reports & exports
            ['name' => 'reports.view', 'label' => 'View reports'],
            ['name' => 'data.export', 'label' => 'Export data'],

            // Roles
            ['name' => 'roles.view.all', 'label' => 'View roles'],
            ['name' => 'roles.view.own', 'label' => 'View own roles'],

            // Tasks
            ['name' => 'tasks.view.all', 'label' => 'View tasks'],
            ['name' => 'tasks.view.own', 'label' => 'View own tasks'],
            ['name' => 'tasks.create', 'label' => 'Create tasks'],
            ['name' => 'tasks.update.any', 'label' => 'Update any tasks'],
            ['name' => 'tasks.update.own', 'label' => 'Update own tasks'],
            ['name' => 'tasks.delete.any', 'label' => 'Delete any tasks'],
            ['name' => 'tasks.delete.own', 'label' => 'Delete own tasks'],

            // Settings
            ['name' => 'settings.view', 'label' => 'View settings'],
            ['name' => 'settings.manage', 'label' => 'Manage settings'],

            // Users
            ['name' => 'users.view.all', 'label' => 'View users'],
            ['name' => 'users.view.own', 'label' => 'View own user'],
            ['name' => 'users.create', 'label' => 'Create users'],
            ['name' => 'users.update.any', 'label' => 'Update any user'],
            ['name' => 'users.update.own', 'label' => 'Update own user'],
            ['name' => 'users.delete.any', 'label' => 'Delete any users'],
            ['name' => 'users.delete.own', 'label' => 'Delete own users'],
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
