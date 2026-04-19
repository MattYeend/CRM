<script setup lang="ts">
import axios from 'axios'
import { useForm, router } from '@inertiajs/vue3'
import InvoiceItemDetailsSection from './InvoiceItemDetailsSection.vue'
import InvoiceItemAssociationsSection from './InvoiceItemAssociationsSection.vue'

interface SelectOption {
    id: number
    name: string
}

interface InvoiceSelectOption {
    id: number
}

interface InvoiceItem {
    id?: number
    invoice_id?: number | null
    product_id?: number | null
    description?: string
    quantity?: number
    unit_price?: number
    is_test?: boolean
    meta?: Record<string, any> | null
}

const props = defineProps<{
    invoiceItem?: InvoiceItem
    invoices: InvoiceSelectOption[]
    products: SelectOption[]
    method?: 'post' | 'put'
    submitLabel?: string
    submitRoute: string
}>()

const form = useForm({
    invoice_id: props.invoiceItem?.invoice_id ?? null,
    product_id: props.invoiceItem?.product_id ?? null,
    description: props.invoiceItem?.description ?? '',
    quantity: props.invoiceItem?.quantity ?? 1,
    unit_price: props.invoiceItem?.unit_price ?? 0,
    is_test: props.invoiceItem?.is_test ?? false,
})

async function submit() {
    try {
        await axios.get('/sanctum/csrf-cookie', { withCredentials: true })

        const payload = { ...form.data() }

        const response = await axios({
            method: props.method === 'put' ? 'put' : 'post',
            url: props.submitRoute,
            data: payload,
            withCredentials: true,
            headers: { 'Content-Type': 'application/json' },
        })

        router.visit(`/invoice-items/${response.data.id}`)
    } catch (err: any) {
        console.error(err.response?.data ?? err);

        if (err.response?.status === 422) {
            const raw = err.response.data.errors as Record<string, string[]>
            const flat = Object.fromEntries(
                Object.entries(raw).map(([key, messages]) => [key, messages[0]])
            ) as Record<string, string>
            form.setError(flat)
        }
    }
}
</script>

<template>
    <form @submit.prevent="submit" class="space-y-8 max-w-2xl">

        <InvoiceItemDetailsSection
            :description="form.description"
            :quantity="form.quantity"
            :unit-price="form.unit_price"
            :errors="form.errors"
            @update:description="form.description = $event"
            @update:quantity="form.quantity = $event"
            @update:unit-price="form.unit_price = $event"
        />

        <InvoiceItemAssociationsSection
            :invoice-id="form.invoice_id"
            :product-id="form.product_id"
            :invoices="invoices"
            :products="products"
            :errors="form.errors"
            @update:invoice-id="form.invoice_id = $event"
            @update:product-id="form.product_id = $event"
        />

        <button
            type="submit"
            class="bg-blue-600 text-white px-5 py-2 rounded disabled:opacity-50"
            :disabled="form.processing"
        >
            {{ form.processing ? 'Saving...' : (submitLabel ?? 'Save Line Item') }}
        </button>

    </form>
</template>