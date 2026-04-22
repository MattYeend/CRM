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

const props = defineProps<{
    quote: any
    deals: SelectOption[]
    products: SelectOption[]
}>()

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Quotes', href: route('quotes.index') },
    { title: `Quote #${props.quote.id}`, href: route('quotes.show', { quote: props.quote.id }) },
    { title: `Edit Quote #${props.quote.id}`, href: route('quotes.edit', { quote: props.quote.id }) },
]
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Edit Quote" />
        <div class="p-6">
            <h1 class="text-2xl font-bold mb-6">Edit Quote</h1>
            <QuoteForm
                :quote="quote"
                :deals="deals"
                :products="products"
                :submit-route="`/api/quotes/${quote.id}`"
                method="put"
                submitLabel="Update Quote"
            />
        </div>
    </AppLayout>
</template>