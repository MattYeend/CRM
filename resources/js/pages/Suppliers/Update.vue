<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import SupplierForm from './components/SupplierForm.vue'
import { route } from 'ziggy-js'

const props = defineProps<{
    supplier: any
}>()

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Suppliers', href: route('suppliers.index') },
    { title: props.supplier.name, href: route('suppliers.show', { supplier: props.supplier.id }) },
    { title: `Edit ${props.supplier.name}`, href: route('suppliers.edit', { supplier: props.supplier.id }) },
]
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Edit Supplier" />
        <div class="p-6">
            <h1 class="text-2xl font-bold mb-6">Edit Supplier</h1>
            <SupplierForm
                :supplier="supplier"
                :submit-route="`/api/suppliers/${supplier.id}`"
                method="put"
                submitLabel="Update Supplier"
            />
        </div>
    </AppLayout>
</template>