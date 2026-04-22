<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, onMounted } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { fetchQuote, removeQuoteProduct } from '@/services/quoteService'

interface Product {
    id: number
    name: string
    pivot?: {
        quantity: number
        price: number
        total: number
    }
}

interface Quote {
    id: number
    currency: string
    products: Product[]
}

const props = defineProps<{ quote: any }>()

const quote = ref<Quote>({
    id: props.quote.id,
    currency: props.quote.currency ?? 'USD',
    products: props.quote.products ?? [],
})

function formatCurrency(value: number, currency: string) {
    return new Intl.NumberFormat('en-GB', { style: 'currency', currency }).format(value)
}

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Quotes', href: route('quotes.index') },
    { title: `Quote #${quote.value.id}`, href: route('quotes.show', { quote: quote.value.id }) },
    { title: 'Products', href: route('quotes.products.index', { quote: quote.value.id }) },
]

async function loadQuote() {
    const data = await fetchQuote(quote.value.id)
    quote.value.currency = data.currency ?? 'USD'
    quote.value.products = data.products ?? []
}

async function handleRemove(productId: number) {
    if (!confirm('Remove this product from the quote?')) return
    await removeQuoteProduct(quote.value.id, productId)
    loadQuote()
}

onMounted(() => loadQuote())
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="`Quote #${quote.id} — Products`" />
        <div class="p-6">
            <div class="flex justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold">Quote #{{ quote.id }}</h1>
                    <p class="text-gray-500 text-sm mt-0.5">Quote Products</p>
                </div>
                <div class="flex items-center space-x-2">
                    <Link
                        :href="route('quotes.products.add', { quote: quote.id })"
                        class="bg-blue-600 text-white px-4 py-2 rounded"
                    >
                        Add Products
                    </Link>
                    <Link
                        :href="route('quotes.show', { quote: quote.id })"
                        class="bg-gray-200 text-gray-700 px-4 py-2 rounded"
                    >
                        Back to Quote
                    </Link>
                </div>
            </div>

            <table class="w-full border">
                <thead>
                    <tr>
                        <th class="p-2 text-left">Product</th>
                        <th class="p-2 text-right">Qty</th>
                        <th class="p-2 text-right">Unit Price</th>
                        <th class="p-2 text-right">Total</th>
                        <th class="p-2"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="product in quote.products" :key="product.id" class="border-t">
                        <td class="p-2">{{ product.name }}</td>
                        <td class="p-2 text-right">{{ product.pivot?.quantity ?? 1 }}</td>
                        <td class="p-2 text-right">{{ formatCurrency(product.pivot?.price ?? 0, quote.currency) }}</td>
                        <td class="p-2 text-right">{{ formatCurrency(product.pivot?.total ?? 0, quote.currency) }}</td>
                        <td class="p-2 space-x-2 whitespace-nowrap text-right">
                            <Link
                                :href="route('quotes.products.edit', { quote: quote.id, product: product.id })"
                                class="text-blue-600"
                            >
                                Edit
                            </Link>
                            <button
                                @click="handleRemove(product.id)"
                                class="text-red-600"
                            >
                                Remove
                            </button>
                        </td>
                    </tr>

                    <tr v-if="quote.products.length === 0">
                        <td colspan="5" class="p-4 text-center text-gray-500">
                            No products on this quote yet.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>