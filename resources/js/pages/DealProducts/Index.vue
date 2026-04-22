<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, onMounted } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { fetchDeal, removeDealProduct } from '@/services/dealService'

interface Product {
    id: number
    name: string
    pivot?: {
        quantity: number
        price: number
        total: number
    }
}

interface Deal {
    id: number
    title: string
    currency: string
    products: Product[]
}

const props = defineProps<{ deal: any }>()

const deal = ref<Deal>({
    id: props.deal.id,
    title: props.deal.title,
    currency: props.deal.currency ?? 'USD',
    products: props.deal.products ?? [],
})

function formatCurrency(value: number, currency: string) {
    return new Intl.NumberFormat('en-GB', { style: 'currency', currency }).format(value)
}

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Deals', href: route('deals.index') },
    { title: deal.value.title || 'Deal', href: route('deals.show', { deal: deal.value.id }) },
    { title: 'Products', href: route('deals.products.index', { deal: deal.value.id }) },
]

async function loadDeal() {
    const data = await fetchDeal(deal.value.id)
    deal.value.currency = data.currency ?? 'USD'
    deal.value.products = data.products ?? []
    deal.value.title = data.title
}

async function handleRemove(productId: number) {
    if (!confirm('Remove this product from the deal?')) return
    await removeDealProduct(deal.value.id, productId)
    loadDeal()
}

onMounted(() => loadDeal())
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="`${deal.title} — Products`" />
        <div class="p-6">
            <div class="flex justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold">{{ deal.title }}</h1>
                    <p class="text-gray-500 text-sm mt-0.5">Deal Products</p>
                </div>
                <div class="flex items-center space-x-2">
                    <Link
                        :href="route('deals.products.add', { deal: deal.id })"
                        class="bg-blue-600 text-white px-4 py-2 rounded"
                    >
                        Add Products
                    </Link>
                    <Link
                        :href="route('deals.show', { deal: deal.id })"
                        class="bg-gray-200 text-gray-700 px-4 py-2 rounded"
                    >
                        Back to Deal
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
                    <tr v-for="product in deal.products" :key="product.id" class="border-t">
                        <td class="p-2">{{ product.name }}</td>
                        <td class="p-2 text-right">{{ product.pivot?.quantity ?? 1 }}</td>
                        <td class="p-2 text-right">{{ formatCurrency(product.pivot?.price ?? 0, deal.currency) }}</td>
                        <td class="p-2 text-right">{{ formatCurrency(product.pivot?.total ?? 0, deal.currency) }}</td>
                        <td class="p-2 space-x-2 whitespace-nowrap text-right">
                            <Link
                                :href="route('deals.products.edit', { deal: deal.id, product: product.id })"
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

                    <tr v-if="deal.products.length === 0">
                        <td colspan="5" class="p-4 text-center text-gray-500">
                            No products on this deal yet.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>