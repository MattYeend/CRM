<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import InvoiceForm from './components/InvoiceForm.vue'
import { route } from 'ziggy-js'

interface SelectOption {
    id: number
    name: string
}

defineProps<{
    companies: SelectOption[]
}>()

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Invoices', href: route('invoices.index') },
    { title: 'Create Invoice', href: route('invoices.create') },
]
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Create Invoice" />
        <div class="p-6">
            <h1 class="text-2xl font-bold mb-6">Create Invoice</h1>
            <InvoiceForm
                :companies="companies"
                submit-route="/api/invoices"
                method="post"
                submitLabel="Save Invoice"
            />
        </div>
    </AppLayout>
</template>