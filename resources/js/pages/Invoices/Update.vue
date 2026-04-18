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

const props = defineProps<{
    invoice: any
    companies: SelectOption[]
}>()

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Invoices', href: route('invoices.index') },
    { title: props.invoice.number, href: route('invoices.show', { invoice: props.invoice.id }) },
    { title: `Edit ${props.invoice.number}`, href: route('invoices.edit', { invoice: props.invoice.id }) },
]
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Edit Invoice" />
        <div class="p-6">
            <h1 class="text-2xl font-bold mb-6">Edit Invoice</h1>
            <InvoiceForm
                :invoice="invoice"
                :companies="companies"
                :submit-route="`/api/invoices/${invoice.id}`"
                method="put"
            />
        </div>
    </AppLayout>
</template>