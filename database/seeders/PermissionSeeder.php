<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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
            ['name' => 'activities.restore.any', 'label' => 'Restore any activities'],
            ['name' => 'activities.restore.own', 'label' => 'Restore own activities'],

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
            ['name' => 'attachments.restore.any', 'label' => 'Restore any attachments'],
            ['name' => 'attachments.restore.own', 'label' => 'Restore own attachments'],

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

            // Deals
            ['name' => 'deals.view.all', 'label' => 'View deals'],
            ['name' => 'deals.view.own', 'label' => 'View own deals'],
            ['name' => 'deals.create', 'label' => 'Create deals'],
            ['name' => 'deals.update.any', 'label' => 'Update any deals'],
            ['name' => 'deals.update.own', 'label' => 'Update own deals'],
            ['name' => 'deals.delete.any', 'label' => 'Delete any deals'],
            ['name' => 'deals.delete.own', 'label' => 'Delete own deals'],
            ['name' => 'deals.restore.any', 'label' => 'Restore any deals'],
            ['name' => 'deals.restore.own', 'label' => 'Restore own deals'],

            // Deal Products
            ['name' => 'deals.products.add', 'label' => 'Add products to deals'],
            ['name' => 'deals.products.update', 'label' => 'Update products on deals'],
            ['name' => 'deals.products.remove', 'label' => 'Remove products from deals'],
            ['name' => 'deals.products.restore', 'label' => 'Restore products on deals'],

            // Invoices / finance
            ['name' => 'invoices.view.all', 'label' => 'View invoices'],
            ['name' => 'invoices.view.own', 'label' => 'View own invoices'],
            ['name' => 'invoices.create', 'label' => 'Create invoices'],
            ['name' => 'invoices.update.any', 'label' => 'Update any invoices'],
            ['name' => 'invoices.update.own', 'label' => 'Update own invoices'],
            ['name' => 'invoices.delete.any', 'label' => 'Delete any invoices'],
            ['name' => 'invoices.delete.own', 'label' => 'Delete own invoices'],
            ['name' => 'invoices.restore.any', 'label' => 'Restore any invoices'],
            ['name' => 'invoices.restore.own', 'label' => 'Restore own invoices'],

            // Invoice Items
            ['name' => 'invoiceItems.view.all', 'label' => 'View invoice items'],
            ['name' => 'invoiceItems.view.own', 'label' => 'View own invoice items'],
            ['name' => 'invoiceItems.create', 'label' => 'Create invoice items'],
            ['name' => 'invoiceItems.update.any', 'label' => 'Update any invoice items'],
            ['name' => 'invoiceItems.update.own', 'label' => 'Update own invoice items'],
            ['name' => 'invoiceItems.delete.any', 'label' => 'Delete any invoice items'],
            ['name' => 'invoiceItems.delete.own', 'label' => 'Delete own invoice items'],
            ['name' => 'invoiceItems.restore.any', 'label' => 'Restore any invoices items'],
            ['name' => 'invoiceItems.restore.own', 'label' => 'Restore own invoices items'],

            // Job Titles
            ['name' => 'jobTitles.view.all', 'label' => 'View job titles'],
            ['name' => 'jobTitles.view.own', 'label' => 'View own job titles'],
            ['name' => 'jobTitles.create', 'label' => 'Create job titles'],
            ['name' => 'jobTitles.update.any', 'label' => 'Update any job titles'],
            ['name' => 'jobTitles.update.own', 'label' => 'Update own job titles'],
            ['name' => 'jobTitles.delete.any', 'label' => 'Delete any job titles'],
            ['name' => 'jobTitles.delete.own', 'label' => 'Delete own job titles'],
            ['name' => 'jobTitles.restore.any', 'label' => 'Restore any job titles'],
            ['name' => 'jobTitles.restore.own', 'label' => 'Restore own job titles'],

            // Leads 
            ['name' => 'leads.view.all', 'label' => 'View leads'],
            ['name' => 'leads.view.own', 'label' => 'View own leads'],
            ['name' => 'leads.create', 'label' => 'Create leads'],
            ['name' => 'leads.update.any', 'label' => 'Update any leads'],
            ['name' => 'leads.update.own', 'label' => 'Update own leads'],
            ['name' => 'leads.delete.any', 'label' => 'Delete any leads'],
            ['name' => 'leads.delete.own', 'label' => 'Delete own leads'],
            ['name' => 'leads.restore.any', 'label' => 'Restore any leads'],
            ['name' => 'leads.restore.own', 'label' => 'Restore own leads'],

            // Learning Material
            ['name' => 'learnings.view.all', 'label' => 'View learning materials'],
            ['name' => 'learnings.view.own', 'label' => 'View own learning materials'],
            ['name' => 'learnings.create', 'label' => 'Create learning materials'],
            ['name' => 'learnings.update.any', 'label' => 'Update any learning materials'],
            ['name' => 'learnings.update.own', 'label' => 'Update own learning materials'],
            ['name' => 'learnings.delete.any', 'label' => 'Delete any learning materials'],
            ['name' => 'learnings.delete.own', 'label' => 'Delete own learning materials'],
            ['name' => 'learnings.access', 'label' => 'Access learning materials'],
            ['name' => 'learnings.complete.any', 'label' => 'Can complete any learning materials'],
            ['name' => 'learnings.complete.own', 'label' => 'Can complete own learning materials'],
            ['name' => 'learnings.incomplete.any', 'label' => 'Can incomplete any learning materials'],
            ['name' => 'learnings.incomplete.own', 'label' => 'Can incomplete own learning materials'],
            ['name' => 'learnings.restore.any', 'label' => 'Restore any learning materials'],
            ['name' => 'learnings.restore.own', 'label' => 'Restore own learning materials'],

            // Notes
            ['name' => 'notes.view.all', 'label' => 'View notes'],
            ['name' => 'notes.view.own', 'label' => 'View own notes'],
            ['name' => 'notes.create', 'label' => 'Create notes'],
            ['name' => 'notes.update.any', 'label' => 'Update any notes'],
            ['name' => 'notes.update.own', 'label' => 'Update own notes'],
            ['name' => 'notes.delete.any', 'label' => 'Delete any notes'],
            ['name' => 'notes.delete.own', 'label' => 'Delete own notes'],
            ['name' => 'notes.restore.any', 'label' => 'Restore any notes'],
            ['name' => 'notes.restore.own', 'label' => 'Restore own notes'],

            // Orders
            ['name' => 'orders.view.all', 'label' => 'View orders'],
            ['name' => 'orders.view.own', 'label' => 'View own orders'],
            ['name' => 'orders.create', 'label' => 'Create orders'],
            ['name' => 'orders.update.any', 'label' => 'Update any orders'],
            ['name' => 'orders.update.own', 'label' => 'Update own orders'],
            ['name' => 'orders.delete.any', 'label' => 'Delete any orders'],
            ['name' => 'orders.delete.own', 'label' => 'Delete own orders'],
            ['name' => 'orders.restore.any', 'label' => 'Restore any orders'],
            ['name' => 'orders.restore.own', 'label' => 'Restore own orders'],

            // Order Products
            ['name' => 'orders.products.add', 'label' => 'Add products to orders'],
            ['name' => 'orders.products.update', 'label' => 'Update products on orders'],
            ['name' => 'orders.products.remove', 'label' => 'Remove products from orders'],
            ['name' => 'orders.products.restore', 'label' => 'Restore products on orders'],

            // Parts
            ['name' => 'parts.view.all', 'label' => 'View parts'],
            ['name' => 'parts.view.own', 'label' => 'View own parts'],
            ['name' => 'parts.create', 'label' => 'Create parts'],
            ['name' => 'parts.update.any', 'label' => 'Update any parts'],
            ['name' => 'parts.update.own', 'label' => 'Update own parts'],
            ['name' => 'parts.delete.any', 'label' => 'Delete any parts'],
            ['name' => 'parts.delete.own', 'label' => 'Delete own parts'],
            ['name' => 'parts.restore.any', 'label' => 'Restore any parts'],
            ['name' => 'parts.restore.own', 'label' => 'Restore own parts'],

            // Part Categories
            ['name' => 'part caterogies.view.all', 'label' => 'View part caterogies'],
            ['name' => 'part caterogies.view.own', 'label' => 'View own part caterogies'],
            ['name' => 'part caterogies.create', 'label' => 'Create part caterogies'],
            ['name' => 'part caterogies.update.any', 'label' => 'Update any part caterogies'],
            ['name' => 'part caterogies.update.own', 'label' => 'Update own part caterogies'],
            ['name' => 'part caterogies.delete.any', 'label' => 'Delete any part caterogies'],
            ['name' => 'part caterogies.delete.own', 'label' => 'Delete own part caterogies'],
            ['name' => 'part caterogies.restore.any', 'label' => 'Restore any part caterogies'],
            ['name' => 'part caterogies.restore.own', 'label' => 'Restore own part caterogies'],
    
            // Permissions
            ['name' => 'permissions.view.all', 'label' => 'View permissions'],
            ['name' => 'permissions.view.own', 'label' => 'View own permissions'],
            ['name' => 'permissions.create', 'label' => 'Create permissions'],
            ['name' => 'permissions.update.any', 'label' => 'Update any permissions'],
            ['name' => 'permissions.update.own', 'label' => 'Update own permissions'],
            ['name' => 'permissions.delete.any', 'label' => 'Delete any permissions'],
            ['name' => 'permissions.delete.own', 'label' => 'Delete own permissions'],
            ['name' => 'permissions.restore.any', 'label' => 'Restore any permissions'],
            ['name' => 'permissions.restore.own', 'label' => 'Restore own permissions'],

            // Pipelines
            ['name' => 'pipelines.view.all', 'label' => 'View pipelines'],
            ['name' => 'pipelines.view.own', 'label' => 'View own pipelines'],
            ['name' => 'pipelines.create', 'label' => 'Create pipelines'],
            ['name' => 'pipelines.update.any', 'label' => 'Update any pipelines'],
            ['name' => 'pipelines.update.own', 'label' => 'Update own pipelines'],
            ['name' => 'pipelines.delete.any', 'label' => 'Delete any pipelines'],
            ['name' => 'pipelines.delete.own', 'label' => 'Delete own pipelines'],
            ['name' => 'pipelines.assign', 'label' => 'Assign pipelines to entities'],
            ['name' => 'pipelines.restore.any', 'label' => 'Restore any pipelines'],
            ['name' => 'pipelines.restore.own', 'label' => 'Restore own pipelines'],

            // Pipeline Stages
            ['name' => 'pipelineStages.view.all', 'label' => 'View pipeline stages'],
            ['name' => 'pipelineStages.view.own', 'label' => 'View own pipeline stages'],
            ['name' => 'pipelineStages.create', 'label' => 'Create pipeline stages'],
            ['name' => 'pipelineStages.update.any', 'label' => 'Update any pipeline stages'],
            ['name' => 'pipelineStages.update.own', 'label' => 'Update own pipeline stages'],
            ['name' => 'pipelineStages.delete.any', 'label' => 'Delete any pipeline stages'],
            ['name' => 'pipelineStages.delete.own', 'label' => 'Delete own pipeline stages'],
            ['name' => 'pipelineStages.assign', 'label' => 'Assign pipeline stages to pipelines'],
            ['name' => 'pipelineStages.restore.any', 'label' => 'Restore any pipeline stages'],
            ['name' => 'pipelineStages.restore.own', 'label' => 'Restore own pipeline stages'],

            // Products
            ['name' => 'products.view.all', 'label' => 'View products'],
            ['name' => 'products.view.own', 'label' => 'View own products'],
            ['name' => 'products.create', 'label' => 'Create products'],
            ['name' => 'products.update.any', 'label' => 'Update any products'],
            ['name' => 'products.update.own', 'label' => 'Update own products'],
            ['name' => 'products.delete.any', 'label' => 'Delete any products'],
            ['name' => 'products.delete.own', 'label' => 'Delete own products'],
            ['name' => 'products.restore.any', 'label' => 'Restore any products'],
            ['name' => 'products.restore.own', 'label' => 'Restore own products'],

            // Quotes
            ['name' => 'quotes.view.all', 'label' => 'View quotes'],
            ['name' => 'quotes.view.own', 'label' => 'View own quotes'],
            ['name' => 'quotes.create', 'label' => 'Create quotes'],
            ['name' => 'quotes.update.any', 'label' => 'Update any quotes'],
            ['name' => 'quotes.update.own', 'label' => 'Update own quotes'],
            ['name' => 'quotes.delete.any', 'label' => 'Delete any quotes'],
            ['name' => 'quotes.delete.own', 'label' => 'Delete own quotes'],
            ['name' => 'quotes.restore.any', 'label' => 'Restore any quotes'],
            ['name' => 'quotes.restore.own', 'label' => 'Restore own quotes'],

            // Quote Products
            ['name' => 'quotes.products.add', 'label' => 'Add products to quotes'],
            ['name' => 'quotes.products.update', 'label' => 'Update products on quotes'],
            ['name' => 'quotes.products.remove', 'label' => 'Remove products from quotes'],
            ['name' => 'quotes.products.restore', 'label' => 'Restore products on quotes'],

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
            ['name' => 'tasks.restore.any', 'label' => 'Restore any tasks'],
            ['name' => 'tasks.restore.own', 'label' => 'Restore own tasks'],

            // Settings
            ['name' => 'settings.view', 'label' => 'View settings'],
            ['name' => 'settings.manage', 'label' => 'Manage settings'],

            // Suppliers
            ['name' => 'suppliers.view.all', 'label' => 'View suppliers'],
            ['name' => 'suppliers.view.own', 'label' => 'View own suppliers'],
            ['name' => 'suppliers.create', 'label' => 'Create suppliers'],
            ['name' => 'suppliers.update.any', 'label' => 'Update any suppliers'],
            ['name' => 'suppliers.update.own', 'label' => 'Update own suppliers'],
            ['name' => 'suppliers.delete.any', 'label' => 'Delete any suppliers'],
            ['name' => 'suppliers.delete.own', 'label' => 'Delete own suppliers'],
            ['name' => 'suppliers.restore.any', 'label' => 'Restore any suppliers'],
            ['name' => 'suppliers.restore.own', 'label' => 'Restore own suppliers'],

            // Users
            ['name' => 'users.view.all', 'label' => 'View users'],
            ['name' => 'users.view.own', 'label' => 'View own user'],
            ['name' => 'users.create', 'label' => 'Create users'],
            ['name' => 'users.update.any', 'label' => 'Update any user'],
            ['name' => 'users.update.own', 'label' => 'Update own user'],
            ['name' => 'users.delete.any', 'label' => 'Delete any users'],
            ['name' => 'users.delete.own', 'label' => 'Delete own users'],
            ['name' => 'users.assign.roles', 'label' => 'Assign roles to users'],
            ['name' => 'users.assign.permissions', 'label' => 'Assign permissions to users'],
            ['name' => 'users.restore.any', 'label' => 'Restore any users'],
            ['name' => 'users.restore.own', 'label' => 'Restore own users'],
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
