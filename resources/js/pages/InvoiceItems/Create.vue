<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import InvoiceItemForm from './components/InvoiceItemForm.vue'
import { route } from 'ziggy-js'

interface SelectOption {
    id: number
    name: string
}

interface InvoiceSelectOption {
    id: number
}

defineProps<{
    invoices: InvoiceSelectOption[]
    products: SelectOption[]
}>()

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Invoice Items', href: route('invoice-items.index') },
    { title: 'Create Invoice Item', href: route('invoice-items.create') },
]
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Create Invoice Item" />
        <div class="p-6">
            <h1 class="text-2xl font-bold mb-6">Create Invoice Item</h1>
            <InvoiceItemForm
                :invoices="invoices"
                :products="products"
                submit-route="/api/invoice-items"
                method="post"
            />
        </div>
    </AppLayout>
</template>