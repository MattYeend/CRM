<script setup lang="ts">
import axios from 'axios'
import { useForm, router } from '@inertiajs/vue3'

import ProductDetailsSection from './ProductDetailsSection.vue'
import ProductStockSection from './ProductStockSection.vue'

interface Product {
    id?: number
    sku?: string
    name?: string
    description?: string | null
    price?: number
    currency?: string
    status?: 'active' | 'inactive' | 'discontinued'
    quantity?: number
    min_stock_level?: number | null
    max_stock_level?: number | null
    reorder_point?: number | null
    reorder_quantity?: number | null
    lead_time_days?: number | null
    is_test?: boolean
}

const props = defineProps<{
    product?: Product
    method?: 'post' | 'put'
    submitLabel?: string
    submitRoute: string
}>()

const form = useForm({
    sku: props.product?.sku ?? '',
    name: props.product?.name ?? '',
    description: props.product?.description ?? '',
    price: props.product?.price ?? 0,
    currency: props.product?.currency ?? 'USD',
    status: props.product?.status ?? 'active',
    quantity: props.product?.quantity ?? 0,
    min_stock_level: props.product?.min_stock_level ?? null,
    max_stock_level: props.product?.max_stock_level ?? null,
    reorder_point: props.product?.reorder_point ?? null,
    reorder_quantity: props.product?.reorder_quantity ?? null,
    lead_time_days: props.product?.lead_time_days ?? null,
    is_test: props.product?.is_test ?? false,
})

async function submit() {
    try {
        await axios.get('/sanctum/csrf-cookie', { withCredentials: true })

        const response = await axios({
            method: props.method === 'put' ? 'put' : 'post',
            url: props.submitRoute,
            data: form.data(),
            withCredentials: true,
        })

        router.visit(`/products/${response.data.id}`)
    } catch (err: any) {
        if (err.response?.status === 422) {
            const raw = err.response.data.errors
            const flat = Object.fromEntries(
                Object.entries(raw).map(([k, v]: any) => [k, v[0]])
            )
            form.setError(flat)
        }
    }
}
</script>

<template>
    <form @submit.prevent="submit" class="space-y-8 max-w-2xl">
        <ProductDetailsSection v-model="form" />

        <ProductStockSection v-model="form" />

        <button
            type="submit"
            class="bg-blue-600 text-white px-5 py-2 rounded disabled:opacity-50"
            :disabled="form.processing"
        >
            {{ form.processing ? 'Saving...' : (submitLabel ?? 'Save Product') }}
        </button>
    </form>
</template>