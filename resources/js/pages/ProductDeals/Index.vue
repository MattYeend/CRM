<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, onMounted } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { fetchProduct, removeProductDeal } from '@/services/productService'

interface Deal {
    id: number
    title: string
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
    deals: Deal[]
}

const props = defineProps<{ product: any }>()

const product = ref<Product>({
    id: props.product.id,
    name: props.product.name,
    currency: props.product.currency ?? 'GBP',
    deals: props.product.deals ?? []
})

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Products', href: route('products.index') },
    { title: product.value.name, href: route('products.show', { product: product.value.id }) },
    { title: 'Deals', href: route('products.deals.index', { product: product.value.id }) },
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
    product.value.deals = data.deals ?? []
    product.value.name = data.name
}

async function remove(dealId: number) {
    if (!confirm('Remove deal?')) return
    await removeProductDeal(product.value.id, dealId)
    loadProduct()
}
onMounted(() => loadProduct())
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="`${product.name} — Deals`" />

        <div class="p-6">
            <div class="flex justify-between mb-6">
                <h1 class="text-2xl font-bold">{{ product.name }}</h1>

                <Link
                    :href="route('products.deals.add', { product: product.id })"
                    class="bg-blue-600 text-white px-4 py-2 rounded"
                >
                    Add Deals
                </Link>
            </div>

            <table class="w-full border">
                <thead>
                    <tr>
                        <th class="p-2 text-left">Deal</th>
                        <th class="p-2 text-right">Qty</th>
                        <th class="p-2 text-right">Price</th>
                        <th class="p-2 text-right">Total</th>
                        <th></th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="d in product.deals" :key="d.id" class="border-t">
                        <td class="p-2">
                            <Link
                                :href="route('deals.show', { deal: d.id })"
                            >
                                {{ d.title }}
                            </Link>
                        </td>
                        <td class="p-2 text-right">{{ d.pivot?.quantity }}</td>
                        <td class="p-2 text-right">{{ formatCurrency(d.pivot?.price ?? 0, product.currency) }}</td>
                        <td class="p-2 text-right">{{ formatCurrency(d.pivot?.total ?? 0, product.currency) }}</td>
                        <td class="p-2 text-right space-x-2">
                            <Link
                                :href="route('products.deals.edit', { product: product.id, deal: d.id })"
                            >
                                Edit
                            </Link>

                            <button @click="remove(d.id)" class="text-red-600">
                                Remove
                            </button>
                        </td>
                    </tr>

                    <tr v-if="product.deals.length === 0">
                        <td colspan="5" class="text-center p-4 text-gray-500">
                            No deals
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>