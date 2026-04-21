<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { updateProductQuotes } from '@/services/productService'

interface Product {
    id: number
    name: string
}

interface Quote {
    id: number
    pivot?: {
        quantity: number
        price: number
        total: number
    }
}

const props = defineProps<{
    product: Product
    quote: Quote
}>()

const form = ref({
    quantity: props.quote.pivot?.quantity ?? 1,
    price: props.quote.pivot?.price ?? 0,
})

const submitting = ref(false)
const errors = ref<string | null>(null)

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Products', href: route('products.index') },
    { title: props.product.name, href: route('products.show', { product: props.product.id }) },
    { title: 'Quotes', href: route('products.quotes.index', { product: props.product.id }) },
    { title: `Edit Quote #${props.quote.id}`, href: route('products.quotes.edit', { product: props.product.id, quote: props.quote.id }) },
]

async function submit() {
    errors.value = null
    submitting.value = true

    try {
        await updateProductQuotes(props.product.id, {
            quotes: [
                {
                    quote_id: props.quote.id,
                    quantity: form.value.quantity,
                    price: form.value.price,
                },
            ],
        })

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
        <Head :title="`Edit Quote — ${product.name}`" />

        <div class="p-6">
            <div class="flex justify-between mb-6">
                <h1 class="text-2xl font-bold">Edit: Quote #{{ quote.id }}</h1>

                <Link
                    :href="route('products.quotes.index', { product: product.id })"
                    class="bg-gray-200 text-gray-700 px-4 py-2 rounded"
                >
                    Back
                </Link>
            </div>

            <p class="text-gray-500 mb-6">
                Updating quote on
                <span class="font-medium">{{ product.name }}</span>
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
                    {{ submitting ? 'Saving...' : 'Update Quote' }}
                </button>
            </div>
        </div>
    </AppLayout>
</template>