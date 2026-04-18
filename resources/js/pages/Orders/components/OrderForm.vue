<script setup lang="ts">
import axios from 'axios'
import { useForm, router } from '@inertiajs/vue3'

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
    meta?: Record<string, any> | null
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

        const payload = { ...form.data() }

        const response = await axios({
            method: props.method === 'put' ? 'put' : 'post',
            url: props.submitRoute,
            data: payload,
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

        <!-- Core Details -->
        <div class="space-y-4">
            <h2 class="text-lg font-semibold border-b pb-2">Order Details</h2>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <select
                        v-model="form.status"
                        class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option v-for="s in statusOptions" :key="s.value" :value="s.value">
                            {{ s.label }}
                        </option>
                    </select>
                    <p v-if="form.errors.status" class="text-red-500 text-sm mt-1">
                        {{ form.errors.status }}
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">
                        Amount <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.amount"
                        type="number"
                        min="0"
                        step="0.01"
                        class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                    <p v-if="form.errors.amount" class="text-red-500 text-sm mt-1">
                        {{ form.errors.amount }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Currency</label>
                    <select
                        v-model="form.currency"
                        class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option v-for="c in currencyOptions" :key="c" :value="c">{{ c }}</option>
                    </select>
                    <p v-if="form.errors.currency" class="text-red-500 text-sm mt-1">
                        {{ form.errors.currency }}
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Payment Method</label>
                    <select
                        v-model="form.payment_method"
                        class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option :value="null">— None —</option>
                        <option v-for="m in paymentMethodOptions" :key="m.value" :value="m.value">
                            {{ m.label }}
                        </option>
                    </select>
                    <p v-if="form.errors.payment_method" class="text-red-500 text-sm mt-1">
                        {{ form.errors.payment_method }}
                    </p>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Paid At</label>
                <input
                    v-model="form.paid_at"
                    type="datetime-local"
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <p v-if="form.errors.paid_at" class="text-red-500 text-sm mt-1">
                    {{ form.errors.paid_at }}
                </p>
            </div>
        </div>

        <!-- Associations -->
        <div class="space-y-4">
            <h2 class="text-lg font-semibold border-b pb-2">Associations</h2>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Assigned To <span class="text-red-500">*</span>
                    </label>
                    <select
                        v-model="form.assigned_to"
                        class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option :value="null">— None —</option>
                        <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                    </select>
                    <p v-if="form.errors.assigned_to" class="text-red-500 text-sm mt-1">
                        {{ form.errors.assigned_to }}
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">
                        Deal <span class="text-red-500">*</span>
                    </label>
                    <select
                        v-model="form.deal_id"
                        class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option :value="null">— None —</option>
                        <option v-for="d in deals" :key="d.id" :value="d.id">
                            {{ d.title || d.name }}
                        </option>
                    </select>
                    <p v-if="form.errors.deal_id" class="text-red-500 text-sm mt-1">
                        {{ form.errors.deal_id }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Payment References (only show on edit if data exists) -->
        <div v-if="method === 'put'" class="space-y-4">
            <h2 class="text-lg font-semibold border-b pb-2">Payment References</h2>
            <p class="text-sm text-gray-500">
                These are typically system-generated by payment processors.
            </p>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Payment Intent ID</label>
                    <input
                        v-model="form.payment_intent_id"
                        type="text"
                        class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="pi_..."
                    />
                    <p v-if="form.errors.payment_intent_id" class="text-red-500 text-sm mt-1">
                        {{ form.errors.payment_intent_id }}
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Charge ID</label>
                    <input
                        v-model="form.charge_id"
                        type="text"
                        class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="ch_..."
                    />
                    <p v-if="form.errors.charge_id" class="text-red-500 text-sm mt-1">
                        {{ form.errors.charge_id }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Stripe Payment Intent</label>
                    <input
                        v-model="form.stripe_payment_intent"
                        type="text"
                        class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="pi_..."
                    />
                    <p v-if="form.errors.stripe_payment_intent" class="text-red-500 text-sm mt-1">
                        {{ form.errors.stripe_payment_intent }}
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Stripe Invoice ID</label>
                    <input
                        v-model="form.stripe_invoice_id"
                        type="text"
                        class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="in_..."
                    />
                    <p v-if="form.errors.stripe_invoice_id" class="text-red-500 text-sm mt-1">
                        {{ form.errors.stripe_invoice_id }}
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
                {{ form.processing ? 'Saving...' : (submitLabel ?? 'Save Order') }}
            </button>
        </div>
    </form>
</template>