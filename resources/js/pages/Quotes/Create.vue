<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import QuoteForm from './components/QuoteForm.vue'
import { route } from 'ziggy-js'

interface SelectOption {
    id: number
    name?: string
    title?: string
}

defineProps<{
    deals: SelectOption[]
    products: SelectOption[]
}>()

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Quotes', href: route('quotes.index') },
    { title: 'Create Quote', href: route('quotes.create') },
]
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Create Quote" />
        <div class="p-6">
            <h1 class="text-2xl font-bold mb-6">Create Quote</h1>
            <QuoteForm
                :deals="deals"
                :products="products"
                submit-route="/api/quotes"
                method="post"
                submitLabel="Save Quote"
            />
        </div>
    </AppLayout>
</template>