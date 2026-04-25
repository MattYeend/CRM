<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, onMounted } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { fetchQuote, deleteQuotes } from '@/services/quoteService'
import QuoteDetailSection from './components/QuoteDetailSection.vue'

interface UserPermissions {
    view: boolean
    update: boolean
    delete: boolean
}

interface Quote {
    id: number
    deal_id?: number | null
    deal?: { id: number; title: string } | null
    currency: string
    subtotal: number
    formatted_subtotal: string
    tax: number
    formatted_tax: string
    total: number
    formatted_total: string
    sent_at?: string | null
    accepted_at?: string | null
    is_sent: boolean
    is_accepted: boolean
    products?: Array<{
        id: number
        name: string
        pivot?: { quantity: number; price: number; total: number }
    }>
    creator?: { name: string }
    permissions: UserPermissions
}

const statusClasses = {
    sent: 'bg-blue-100 text-blue-700',
    accepted: 'bg-green-100 text-green-700',
    draft: 'bg-gray-100 text-gray-600',
}

function getStatusLabel(quote: Quote): string {
    if (quote.is_accepted) return 'Accepted'
    if (quote.is_sent) return 'Sent'
    return 'Draft'
}

function getStatusClass(quote: Quote): string {
    if (quote.is_accepted) return statusClasses.accepted
    if (quote.is_sent) return statusClasses.sent
    return statusClasses.draft
}

function formatCurrency(value: number, currency: string) {
    return new Intl.NumberFormat('en-GB', { style: 'currency', currency }).format(value)
}

const props = defineProps<{ quote: any }>()

const quote = ref<Quote>({
    id: props.quote.id,
    deal_id: props.quote.deal_id,
    deal: props.quote.deal,
    currency: props.quote.currency ?? 'USD',
    subtotal: props.quote.subtotal ?? 0,
    formatted_subtotal: props.quote.formatted_subtotal ?? '',
    tax: props.quote.tax ?? 0,
    formatted_tax: props.quote.formatted_tax ?? '',
    total: props.quote.total ?? 0,
    formatted_total: props.quote.formatted_total ?? '',
    sent_at: props.quote.sent_at,
    accepted_at: props.quote.accepted_at,
    is_sent: props.quote.is_sent ?? false,
    is_accepted: props.quote.is_accepted ?? false,
    products: props.quote.products ?? [],
    creator: props.quote.creator,
    permissions: props.quote.permissions ?? { view: false, update: false, delete: false },
})

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Quotes', href: route('quotes.index') },
    { title: `Quote #${quote.value.id}`, href: route('quotes.show', { quote: quote.value.id }) },
]

async function loadQuote() {
    const data = await fetchQuote(quote.value.id)
    Object.assign(quote.value, data)
}

async function handleDelete() {
    if (!confirm('Are you sure you want to delete this quote?')) return
    await deleteQuotes(quote.value.id)
    window.location.href = route('quotes.index')
}

onMounted(() => loadQuote())
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="`Quote #${quote.id}`" />

        <div class="p-6">
            <div class="mx-auto border p-6 rounded shadow">

                <!-- Header -->
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <h1 class="text-2xl font-bold">Quote #{{ quote.id }}</h1>

                        <span
                            class="mt-1 inline-block px-2 py-0.5 rounded-full text-xs font-semibold"
                            :class="getStatusClass(quote)"
                        >
                            {{ getStatusLabel(quote) }}
                        </span>
                    </div>

                    <div class="flex items-center gap-2">
                        <Link
                            v-if="quote.permissions?.update"
                            :href="route('quotes.edit', { quote: quote.id })"
                            class="bg-blue-600 text-white px-4 py-2 rounded"
                        >
                            Edit
                        </Link>

                        <Link
                            :href="route('quotes.products.index', { quote: quote.id })"
                            class="bg-gray-200 text-gray-700 px-4 py-2 rounded"
                        >
                            Products
                        </Link>

                        <Link
                            :href="route('quotes.index')"
                            class="bg-gray-200 text-gray-700 px-4 py-2 rounded"
                        >
                            Back
                        </Link>

                        <button
                            v-if="quote.permissions?.delete"
                            @click="handleDelete"
                            class="bg-red-600 text-white px-4 py-2 rounded"
                        >
                            Delete
                        </button>
                    </div>
                </div>

                <QuoteDetailSection :quote="quote" />

                <div v-if="quote.products && quote.products.length > 0" class="mt-6">
                    <h2 class="text-lg font-semibold border-b pb-2 mb-3">Products</h2>

                    <table class="w-full border text-sm">
                        <thead>
                            <tr>
                                <th class="p-2 text-left">Product</th>
                                <th class="p-2 text-right">Qty</th>
                                <th class="p-2 text-right">Price</th>
                                <th class="p-2 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="product in quote.products"
                                :key="product.id"
                                class="border-t"
                            >
                                <td class="p-2">{{ product.name }}</td>
                                <td class="p-2 text-right">
                                    {{ product.pivot?.quantity ?? 1 }}
                                </td>
                                <td class="p-2 text-right">
                                    {{ formatCurrency(product.pivot?.price ?? 0, quote.currency) }}
                                </td>
                                <td class="p-2 text-right">
                                    {{ formatCurrency(product.pivot?.total ?? 0, quote.currency) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </AppLayout>
</template>