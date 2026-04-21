<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { addProductOrders } from '@/services/productService'

interface SelectOption {
    id: number
}

interface Product {
    id: number
    name: string
}

interface OrderLine {
    order_id: number | null
    quantity: number
    price: number
}

const props = defineProps<{
    product: Product
    orders: SelectOption[]
}>()

const lines = ref<OrderLine[]>([
    { order_id: null, quantity: 1, price: 0 },
])

const submitting = ref(false)
const errors = ref<string | null>(null)

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Products', href: route('products.index') },
    { title: props.product.name, href: route('products.show', { product: props.product.id }) },
    { title: 'Orders', href: route('products.orders.index', { product: props.product.id }) },
    { title: 'Add Orders', href: route('products.orders.add', { product: props.product.id }) },
]

function addLine() {
    lines.value.push({ order_id: null, quantity: 1, price: 0 })
}

function removeLine(index: number) {
    lines.value.splice(index, 1)
}

async function submit() {
    errors.value = null

    const validLines = lines.value.filter(l => l.order_id !== null)

    if (!validLines.length) {
        errors.value = 'Please select at least one order.'
        return
    }

    submitting.value = true

    try {
        await addProductOrders(props.product.id, { orders: validLines })

        router.visit(route('products.orders.index', { product: props.product.id }))
    } catch (err: any) {
        errors.value = err.response?.data?.message ?? 'An error occurred.'
    } finally {
        submitting.value = false
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="`Add Orders — ${product.name}`" />

        <div class="p-6">
            <div class="flex justify-between mb-6">
                <h1 class="text-2xl font-bold">
                    Add Orders to {{ product.name }}
                </h1>

                <Link
                    :href="route('products.orders.index', { product: product.id })"
                    class="bg-gray-200 text-gray-700 px-4 py-2 rounded"
                >
                    Back
                </Link>
            </div>

            <p v-if="errors" class="text-red-600 mb-4">{{ errors }}</p>

            <div class="space-y-3 max-w-2xl">
                <div
                    v-for="(line, index) in lines"
                    :key="index"
                    class="grid grid-cols-12 gap-3 items-end border p-3 rounded"
                >
                    <div class="col-span-5">
                        <label class="block text-sm font-medium mb-1">Order</label>
                        <select v-model="line.order_id" class="w-full border rounded px-3 py-2">
                            <option :value="null">— Select —</option>
                            <option v-for="o in orders" :key="o.id" :value="o.id">
                                Order #{{ o.id }}
                            </option>
                        </select>
                    </div>

                    <div class="col-span-3">
                        <label class="block text-sm font-medium mb-1">Quantity</label>
                        <input v-model.number="line.quantity" type="number" min="1" class="w-full border rounded px-3 py-2" />
                    </div>

                    <div class="col-span-3">
                        <label class="block text-sm font-medium mb-1">Price</label>
                        <input v-model.number="line.price" type="number" min="0" step="0.01" class="w-full border rounded px-3 py-2" />
                    </div>

                    <div class="col-span-1 flex justify-end">
                        <button v-if="lines.length > 1" @click="removeLine(index)" class="text-red-500" type="button">
                            ✕
                        </button>
                    </div>
                </div>

                <button @click="addLine" type="button" class="text-blue-600 text-sm">
                    + Add another order
                </button>
            </div>

            <div class="mt-6">
                <button @click="submit" :disabled="submitting" class="bg-blue-600 text-white px-5 py-2 rounded">
                    {{ submitting ? 'Saving...' : 'Add Orders' }}
                </button>
            </div>
        </div>
    </AppLayout>
</template>