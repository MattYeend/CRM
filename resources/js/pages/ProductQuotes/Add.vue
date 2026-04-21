<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { addProductQuotes } from '@/services/productService'

interface SelectOption {
    id: number
}

interface Product {
    id: number
    name: string
}

interface QuoteLine {
    quote_id: number | null
    quantity: number
    price: number
}

const props = defineProps<{
    product: Product
    quotes: SelectOption[]
}>()

const lines = ref<QuoteLine[]>([
    { quote_id: null, quantity: 1, price: 0 },
])

const submitting = ref(false)
const errors = ref<string | null>(null)

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Products', href: route('products.index') },
    { title: props.product.name, href: route('products.show', { product: props.product.id }) },
    { title: 'Quotes', href: route('products.quotes.index', { product: props.product.id }) },
    { title: 'Add Quotes', href: route('products.quotes.add', { product: props.product.id }) },
]

function addLine() {
    lines.value.push({ quote_id: null, quantity: 1, price: 0 })
}

function removeLine(index: number) {
    lines.value.splice(index, 1)
}

async function submit() {
    errors.value = null

    const validLines = lines.value.filter(l => l.quote_id !== null)

    if (!validLines.length) {
        errors.value = 'Please select at least one quote.'
        return
    }

    submitting.value = true

    try {
        await addProductQuotes(props.product.id, { quotes: validLines })

        router.visit(route('products.quotes.index', { product: props.product.id }))
    } catch (err: any) {
        errors.value = err.response?.data?.message ?? 'An error occurred.'
    } finally {
        submitting.value = false
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="`Add Quotes — ${product.name}`" />

        <div class="p-6">
            <div class="flex justify-between mb-6">
                <h1 class="text-2xl font-bold">
                    Add Quotes to {{ product.name }}
                </h1>

                <Link
                    :href="route('products.quotes.index', { product: product.id })"
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
                        <label class="block text-sm font-medium mb-1">Quote</label>
                        <select v-model="line.quote_id" class="w-full border rounded px-3 py-2">
                            <option :value="null">— Select —</option>
                            <option v-for="q in quotes" :key="q.id" :value="q.id">
                                Quote #{{ q.id }}
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
                    + Add another quote
                </button>
            </div>

            <div class="mt-6">
                <button @click="submit" :disabled="submitting" class="bg-blue-600 text-white px-5 py-2 rounded">
                    {{ submitting ? 'Saving...' : 'Add Quotes' }}
                </button>
            </div>
        </div>
    </AppLayout>
</template>