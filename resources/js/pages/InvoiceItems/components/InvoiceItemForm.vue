<script setup lang="ts">
import axios from 'axios'
import { useForm, router } from '@inertiajs/vue3'

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

        <!-- Line Item Details -->
        <div class="space-y-4">
            <h2 class="text-lg font-semibold border-b pb-2">Line Item Details</h2>

            <div>
                <label class="block text-sm font-medium mb-1">
                    Description <span class="text-red-500">*</span>
                </label>
                <input
                    v-model="form.description"
                    type="text"
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Line item description"
                />
                <p v-if="form.errors.description" class="text-red-500 text-sm mt-1">
                    {{ form.errors.description }}
                </p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Quantity <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.quantity"
                        type="number"
                        min="1"
                        step="1"
                        class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                    <p v-if="form.errors.quantity" class="text-red-500 text-sm mt-1">
                        {{ form.errors.quantity }}
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">
                        Unit Price <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.unit_price"
                        type="number"
                        min="0"
                        step="0.01"
                        class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                    <p v-if="form.errors.unit_price" class="text-red-500 text-sm mt-1">
                        {{ form.errors.unit_price }}
                    </p>
                </div>
            </div>

            <!-- Computed line total preview -->
            <div class="bg-gray-50 border rounded px-3 py-2 text-sm text-gray-600">
                Line Total:
                <span class="font-semibold text-gray-900">
                    {{ (Number(form.quantity) * Number(form.unit_price)).toFixed(2) }}
                </span>
            </div>
        </div>

        <!-- Associations -->
        <div class="space-y-4">
            <h2 class="text-lg font-semibold border-b pb-2">Associations</h2>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Invoice <span class="text-red-500">*</span>
                    </label>
                    <select
                        v-model="form.invoice_id"
                        class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option :value="null">— None —</option>
                        <option v-for="inv in invoices" :key="inv.id" :value="inv.id">
                            #{{ inv.id }}
                        </option>
                    </select>
                    <p v-if="form.errors.invoice_id" class="text-red-500 text-sm mt-1">
                        {{ form.errors.invoice_id }}
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Product</label>
                    <select
                        v-model="form.product_id"
                        class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option :value="null">— None (bespoke) —</option>
                        <option v-for="p in products" :key="p.id" :value="p.id">
                            {{ p.name }}
                        </option>
                    </select>
                    <p v-if="form.errors.product_id" class="text-red-500 text-sm mt-1">
                        {{ form.errors.product_id }}
                    </p>
                </div>
            </div>
        </div>

        <div>
            <button
                type="submit"
                class="bg-blue-600 text-white px-5 py-2 rounded disabled:opacity-50"
                :disabled="form.processing"
            >
                {{ form.processing ? 'Saving...' : (submitLabel ?? 'Save Line Item') }}
            </button>
        </div>
    </form>
</template>