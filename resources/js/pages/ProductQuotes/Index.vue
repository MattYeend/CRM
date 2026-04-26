<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { ref, onMounted } from 'vue'
import { fetchProduct, removeProductQuote } from '@/services/productService'

interface Quote {
    id: number
    currency: string
    pivot?: {
        quantity: number
        price: number
        total: number
    }
}

interface Product {
    id: number
    name: string
    currency: string
    quotes: Quote[]
}

const props = defineProps<{
    product: Product
}>()

const product = ref<Product>({
    id: props.product.id,
    name: props.product.name,
    currency: props.product.currency ?? 'GBP',
    quotes: props.product.quotes ?? []
})

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Products', href: route('products.index') },
    { title: props.product.name, href: route('products.show', { product: props.product.id }) },
    { title: 'Quotes', href: route('products.quotes.index', { product: props.product.id }) },
]

function formatCurrency(value: number, currency: string) {
    return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency,
    }).format(value)
}

async function loadProduct() {
    const data = await fetchProduct(product.value.id)
    product.value.currency = data.currency ?? 'GBP'
    product.value.quotes = data.quotes ?? []
    product.value.name = data.name
}

async function handleRemove(quoteId: number) {
    if (!confirm('Are you sure you want to remove this quote from the product?')) return
    await removeProductQuote(props.product.id, quoteId)
    window.location.reload()
}

onMounted(() => loadProduct())
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="`${product.name} - Quotes`" />

        <div class="p-6">
            <div class="flex justify-between mb-6">
                <h1 class="text-2xl font-bold">Quotes for {{ product.name }}</h1>
                <Link
                    :href="route('products.quotes.add', { product: product.id })"
                    class="bg-blue-600 text-white px-4 py-2 rounded"
                >
                    Add Quote
                </Link>
            </div>

            <table class="w-full border">
                <thead>
                    <tr>
                        <th class="p-2 text-left">Quote ID</th>
                        <th class="p-2 text-right">Qty</th>
                        <th class="p-2 text-right">Price</th>
                        <th class="p-2 text-right">Total</th>
                        <th class="p-2"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="quote in product.quotes" :key="quote.id" class="border-t">
                        <td class="p-2">
                            <Link
                                :href="route('quotes.show', { quote: quote.id })"
                            >
                                #{{ quote.id }}
                            </Link>
                        </td>
                        <td class="p-2 text-right">{{ quote.pivot?.quantity }}</td>
                        <td class="p-2 text-right">{{ formatCurrency(quote.pivot?.price ?? 0, product.currency) }}</td>
                        <td class="p-2 text-right">{{ formatCurrency(quote.pivot?.total ?? 0, product.currency) }}</td>
                        <td class="p-2 space-x-2 text-right">
                            <Link
                                :href="route('products.quotes.edit', { product: product.id, quote: quote.id })"
                            >
                                Edit
                            </Link>
                            <button
                                @click="handleRemove(quote.id)"
                                class="text-red-600"
                            >
                                Remove
                            </button>
                        </td>
                    </tr>

                    <tr v-if="product.quotes.length === 0">
                        <td colspan="4" class="p-4 text-center text-gray-500">
                            No quotes associated with this product.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>