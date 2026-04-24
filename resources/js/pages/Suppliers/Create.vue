<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import SupplierForm from './components/SupplierForm.vue'
import { route } from 'ziggy-js'

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Suppliers', href: route('suppliers.index') },
    { title: 'Create Supplier', href: route('suppliers.create') },
]
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Create Supplier" />
        <div class="p-6">
            <h1 class="text-2xl font-bold mb-6">Create Supplier</h1>
            <SupplierForm
                submit-route="/api/suppliers"
                method="post"
                submitLabel="Save Supplier"
            />
        </div>
    </AppLayout>
</template>