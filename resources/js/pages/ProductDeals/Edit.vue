<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { updateProductDeals } from '@/services/productService'

interface Product {
    id: number
    name: string
}

interface Deal {
    id: number
    title: string
    pivot?: {
        quantity: number
        price: number
        total: number
    }
}

const props = defineProps<{
    product: Product
    deal: Deal
}>()

const form = ref({
    quantity: props.deal.pivot?.quantity ?? 1,
    price: props.deal.pivot?.price ?? 0,
})

const submitting = ref(false)
const errors = ref<string | null>(null)

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Products', href: route('products.index') },
    { title: props.product.name, href: route('products.show', { product: props.product.id }) },
    { title: 'Deals', href: route('products.deals.index', { product: props.product.id }) },
    { title: `Edit ${props.deal.title}`, href: route('products.deals.edit', { product: props.product.id, deal: props.deal.id }) },
]

async function submit() {
    errors.value = null
    submitting.value = true

    try {
        await updateProductDeals(props.product.id, {
            deals: [
                {
                    deal_id: props.deal.id,
                    quantity: form.value.quantity,
                    price: form.value.price,
                },
            ],
        })

        router.visit(route('products.deals.index', { product: props.product.id }))
    } catch (err: any) {
        errors.value = err.response?.data?.message ?? 'An error occurred.'
    } finally {
        submitting.value = false
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="`Edit Deal — ${product.name}`" />

        <div class="p-6">
            <div class="flex justify-between mb-6">
                <h1 class="text-2xl font-bold">Edit: {{ deal.title }}</h1>

                <Link
                    :href="route('products.deals.index', { product: product.id })"
                    class="bg-gray-200 text-gray-700 px-4 py-2 rounded"
                >
                    Back
                </Link>
            </div>

            <p class="text-gray-500 mb-6">
                Updating deal on
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
                    {{ submitting ? 'Saving...' : 'Update Deal' }}
                </button>
            </div>
        </div>
    </AppLayout>
</template>