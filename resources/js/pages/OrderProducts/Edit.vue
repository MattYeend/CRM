<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { updateOrderProducts } from '@/services/orderService'

interface Order {
    id: number
    name?: string
    currency?: string
}

interface Product {
    id: number
    name: string
    pivot?: {
        quantity: number
        price: number
        total: number
    }
}

const props = defineProps<{
    order: Order
    product: Product
}>()

const form = ref({
    quantity: props.product.pivot?.quantity ?? 1,
    price: props.product.pivot?.price ?? 0,
})

const submitting = ref(false)
const errors = ref<string | null>(null)

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Orders', href: route('orders.index') },
    { title: props.order.name || `Order #${props.order.id}`, href: route('orders.show', { order: props.order.id }) },
    { title: 'Products', href: route('orders.products.index', { order: props.order.id }) },
    { title: `Edit ${props.product.name}`, href: route('orders.products.edit', { order: props.order.id, product: props.product.id }) },
]

async function submit() {
    errors.value = null
    submitting.value = true

    try {
        await updateOrderProducts(props.order.id, {
            products: [
                {
                    product_id: props.product.id,
                    quantity: form.value.quantity,
                    price: form.value.price,
                },
            ],
        })

        router.visit(route('orders.products.index', { order: props.order.id }))
    } catch (err: any) {
        errors.value = err.response?.data?.message ?? 'An error occurred.'
    } finally {
        submitting.value = false
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="`Edit Product — ${order.name || 'Order #' + order.id}`" />

        <div class="p-6">
            <div class="flex justify-between mb-6">
                <h1 class="text-2xl font-bold">Edit: {{ product.name }}</h1>

                <Link
                    :href="route('orders.products.index', { order: order.id })"
                    class="bg-gray-200 text-gray-700 px-4 py-2 rounded"
                >
                    Back
                </Link>
            </div>

            <p class="text-gray-500 mb-6">
                Updating product on
                <span class="font-medium">{{ order.name || `Order #${order.id}` }}</span>
            </p>

            <p v-if="errors" class="text-red-600 mb-4">{{ errors }}</p>

            <div class="space-y-4 max-w-sm">
                <div>
                    <label class="block text-sm font-medium mb-1">Quantity</label>
                    <input v-model.number="form.quantity" type="number" min="1" class="w-full border rounded px-3 py-2" />
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Unit Price</label>
                    <input v-model.number="form.price" type="number" min="0" step="0.01" class="w-full border rounded px-3 py-2" />
                </div>

                <button @click="submit" :disabled="submitting" class="bg-blue-600 text-white px-5 py-2 rounded">
                    {{ submitting ? 'Saving...' : 'Update Product' }}
                </button>
            </div>
        </div>
    </AppLayout>
</template>