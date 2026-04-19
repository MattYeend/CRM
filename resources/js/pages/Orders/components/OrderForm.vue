<script setup lang="ts">
import axios from 'axios'
import { useForm, router } from '@inertiajs/vue3'
import OrderDetailsSection from './OrderDetailsSection.vue'
import OrderAssociationsSection from './OrderAssociationsSection.vue'
import OrderPaymentReferencesSection from './OrderPaymentReferencesSection.vue'

interface SelectOption {
    id: number
    name?: string
    title?: string
}

interface Order {
    id?: number
    status?: string
    amount?: number
    currency?: string
    payment_method?: string | null
    payment_intent_id?: string | null
    charge_id?: string | null
    stripe_payment_intent?: string | null
    stripe_invoice_id?: string | null
    paid_at?: string | null
    user_id?: number | null
    deal_id?: number | null
    assigned_to?: number | null
}

const props = defineProps<{
    order?: Order
    users: SelectOption[]
    deals: SelectOption[]
    method?: 'post' | 'put'
    submitLabel?: string
    submitRoute: string
}>()

const statusOptions = [
    { value: 'pending', label: 'Pending' },
    { value: 'processing', label: 'Processing' },
    { value: 'paid', label: 'Paid' },
    { value: 'failed', label: 'Failed' },
    { value: 'refunded', label: 'Refunded' },
    { value: 'cancelled', label: 'Cancelled' },
]

const currencyOptions = ['USD', 'GBP', 'EUR', 'CAD', 'AUD']

const paymentMethodOptions = [
    { value: 'card', label: 'Card' },
    { value: 'bank_transfer', label: 'Bank Transfer' },
    { value: 'cash', label: 'Cash' },
    { value: 'stripe', label: 'Stripe' },
]

const form = useForm({
    status: props.order?.status ?? 'pending',
    amount: props.order?.amount ?? 0,
    currency: props.order?.currency ?? 'GBP',
    payment_method: props.order?.payment_method ?? null,
    payment_intent_id: props.order?.payment_intent_id ?? '',
    charge_id: props.order?.charge_id ?? '',
    stripe_payment_intent: props.order?.stripe_payment_intent ?? '',
    stripe_invoice_id: props.order?.stripe_invoice_id ?? '',
    paid_at: props.order?.paid_at?.slice(0, 16) ?? '',
    user_id: props.order?.user_id ?? null,
    deal_id: props.order?.deal_id ?? null,
    assigned_to: props.order?.assigned_to ?? null,
})

async function submit() {
    try {
        await axios.get('/sanctum/csrf-cookie', { withCredentials: true })

        const response = await axios({
            method: props.method === 'put' ? 'put' : 'post',
            url: props.submitRoute,
            data: form.data(),
            withCredentials: true,
            headers: { 'Content-Type': 'application/json' },
        })

        router.visit(`/orders/${response.data.id}`)
    } catch (err: any) {
        console.error(err.response?.data ?? err)

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

        <OrderDetailsSection
            :form="form"
            :statusOptions="statusOptions"
            :currencyOptions="currencyOptions"
            :paymentMethodOptions="paymentMethodOptions"
        />

        <OrderAssociationsSection
            :form="form"
            :users="users"
            :deals="deals"
        />

        <OrderPaymentReferencesSection
            :form="form"
            :method="method"
        />

        <button
            type="submit"
            class="bg-blue-600 text-white px-5 py-2 rounded disabled:opacity-50"
            :disabled="form.processing"
        >
            {{ form.processing ? 'Saving...' : (submitLabel ?? 'Save Order') }}
        </button>
    </form>
</template>