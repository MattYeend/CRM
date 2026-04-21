<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { removeProductOrder } from '@/services/productService'

interface Product {
    id: number
    name: string
    orders: Order[]
}

interface Order {
    id: number
    status: string
    total: number
    formatted_total?: string
}

const props = defineProps<{
    product: Product
}>()

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Products', href: route('products.index') },
    { title: props.product.name, href: route('products.show', { product: props.product.id }) },
    { title: 'Orders', href: route('products.orders.index', { product: props.product.id }) },
]

async function handleRemove(orderId: number) {
    if (!confirm('Are you sure you want to remove this order from the product?')) return
    await removeProductOrder(props.product.id, orderId)
    window.location.reload()
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="`${product.name} - Orders`" />

        <div class="p-6">
            <div class="flex justify-between mb-6">
                <h1 class="text-2xl font-bold">Orders for {{ product.name }}</h1>
                <Link
                    :href="route('products.orders.add', { product: product.id })"
                    class="bg-blue-600 text-white px-4 py-2 rounded"
                >
                    Add Order
                </Link>
            </div>

            <table class="w-full border">
                <thead>
                    <tr>
                        <th class="p-2 text-left">Order ID</th>
                        <th class="p-2 text-right">Total</th>
                        <th class="p-2 text-left">Status</th>
                        <th class="p-2"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="order in product.orders" :key="order.id" class="border-t">
                        <td class="p-2">
                            <Link
                                :href="route('orders.show', { order: order.id })"
                                class="text-blue-600 underline"
                            >
                                #{{ order.id }}
                            </Link>
                        </td>
                        <td class="p-2 text-right">{{ order.formatted_total }}</td>
                        <td class="p-2">{{ order.status }}</td>
                        <td class="p-2 space-x-2 text-right">
                            <Link
                                :href="route('products.orders.edit', { product: product.id, order: order.id })"
                            >
                                Edit
                            </Link>
                            <button
                                @click="handleRemove(order.id)"
                                class="text-red-600"
                            >
                                Remove
                            </button>
                        </td>
                    </tr>

                    <tr v-if="product.orders.length === 0">
                        <td colspan="4" class="p-4 text-center text-gray-500">
                            No orders associated with this product.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>