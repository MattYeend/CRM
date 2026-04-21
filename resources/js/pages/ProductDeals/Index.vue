<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { removeProductDeal } from '@/services/productService'

interface Product {
    id: number
    name: string
    deals: Deal[]
}

interface Deal {
    id: number
    title: string
    value: number
    formatted_value?: string
    status: string
}

const props = defineProps<{
    product: Product
}>()

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Products', href: route('products.index') },
    { title: props.product.name, href: route('products.show', { product: props.product.id }) },
    { title: 'Deals', href: route('products.deals.index', { product: props.product.id }) },
]

async function handleRemove(dealId: number) {
    if (!confirm('Are you sure you want to remove this deal from the product?')) return
    await removeProductDeal(props.product.id, dealId)
    window.location.reload()
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="`${product.name} - Deals`" />

        <div class="p-6">
            <div class="flex justify-between mb-6">
                <h1 class="text-2xl font-bold">Deals for {{ product.name }}</h1>
                <Link
                    :href="route('products.deals.add', { product: product.id })"
                    class="bg-blue-600 text-white px-4 py-2 rounded"
                >
                    Add Deal
                </Link>
            </div>

            <table class="w-full border">
                <thead>
                    <tr>
                        <th class="p-2 text-left">Title</th>
                        <th class="p-2 text-right">Value</th>
                        <th class="p-2 text-left">Status</th>
                        <th class="p-2"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="deal in product.deals" :key="deal.id" class="border-t">
                        <td class="p-2">
                            <Link
                                :href="route('deals.show', { deal: deal.id })"
                                class="text-blue-600 underline"
                            >
                                {{ deal.title }}
                            </Link>
                        </td>
                        <td class="p-2 text-right">{{ deal.formatted_value }}</td>
                        <td class="p-2">{{ deal.status }}</td>
                        <td class="p-2 space-x-2 text-right">
                            <Link
                                :href="route('products.deals.edit', { product: product.id, deal: deal.id })"
                            >
                                Edit
                            </Link>
                            <button
                                @click="handleRemove(deal.id)"
                                class="text-red-600"
                            >
                                Remove
                            </button>
                        </td>
                    </tr>

                    <tr v-if="product.deals.length === 0">
                        <td colspan="4" class="p-4 text-center text-gray-500">
                            No deals associated with this product.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>