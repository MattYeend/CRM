<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { addDealProducts } from '@/services/dealService'

interface SelectOption {
    id: number
    name: string
}

interface Deal {
    id: number
    title: string
}

interface ProductLine {
    product_id: number | null
    quantity: number
    price: number
}

const props = defineProps<{
    deal: Deal
    products: SelectOption[]
}>()

const lines = ref<ProductLine[]>([
    { product_id: null, quantity: 1, price: 0 },
])

const submitting = ref(false)
const errors = ref<string | null>(null)

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Deals', href: route('deals.index') },
    { title: props.deal.title || 'Deal', href: route('deals.show', { deal: props.deal.id }) },
    { title: 'Products', href: route('deals.products.index', { deal: props.deal.id }) },
    { title: 'Add Products', href: route('deals.products.add', { deal: props.deal.id }) },
]

function addLine() {
    lines.value.push({ product_id: null, quantity: 1, price: 0 })
}

function removeLine(index: number) {
    lines.value.splice(index, 1)
}

async function submit() {
    errors.value = null
    const validLines = lines.value.filter(l => l.product_id !== null)
    if (validLines.length === 0) {
        errors.value = 'Please select at least one product.'
        return
    }

    submitting.value = true
    try {
        await addDealProducts(props.deal.id, { products: validLines })
        window.location.href = route('deals.products.index', { deal: props.deal.id })
    } catch (err: any) {
        errors.value = err.response?.data?.message ?? 'An error occurred.'
    } finally {
        submitting.value = false
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="`Add Products — ${deal.title}`" />
        <div class="p-6">
            <div class="flex justify-between mb-6">
                <h1 class="text-2xl font-bold">Add Products to {{ deal.title }}</h1>
                <Link
                    :href="route('deals.products.index', { deal: deal.id })"
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
                        <label class="block text-sm font-medium mb-1">Product</label>
                        <select
                            v-model="line.product_id"
                            class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option :value="null">— Select —</option>
                            <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                        </select>
                    </div>

                    <div class="col-span-3">
                        <label class="block text-sm font-medium mb-1">Quantity</label>
                        <input
                            v-model.number="line.quantity"
                            type="number"
                            min="1"
                            class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                    </div>

                    <div class="col-span-3">
                        <label class="block text-sm font-medium mb-1">Price</label>
                        <input
                            v-model.number="line.price"
                            type="number"
                            min="0"
                            step="0.01"
                            class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                    </div>

                    <div class="col-span-1 flex justify-end">
                        <button
                            v-if="lines.length > 1"
                            @click="removeLine(index)"
                            class="text-red-500 text-sm"
                            type="button"
                        >
                            ✕
                        </button>
                    </div>
                </div>

                <button
                    @click="addLine"
                    type="button"
                    class="text-blue-600 text-sm mt-1"
                >
                    + Add another product
                </button>
            </div>

            <div class="mt-6">
                <button
                    @click="submit"
                    :disabled="submitting"
                    class="bg-blue-600 text-white px-5 py-2 rounded disabled:opacity-50"
                >
                    {{ submitting ? 'Saving...' : 'Add Products' }}
                </button>
            </div>
        </div>
    </AppLayout>
</template>