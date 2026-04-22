<script setup lang="ts">
import axios from 'axios'
import { useForm, router } from '@inertiajs/vue3'

import QuoteDetailsSection from './QuoteDetailsSection.vue'
import QuoteAssociationsSection from './QuoteAssociationsSection.vue'

interface SelectOption {
    id: number
    title?: string
}

interface Quote {
    id?: number
    deal_id?: number | null
    currency?: string
    subtotal?: number
    tax?: number
    total?: number
    sent_at?: string | null
    accepted_at?: string | null
}

const props = defineProps<{
    quote?: Quote
    deals: SelectOption[]
    products: SelectOption[]
    method?: 'post' | 'put'
    label?: string
    submitLabel?: string
    submitRoute: string
}>()

const form = useForm({
    deal_id: props.quote?.deal_id ?? null,
    currency: props.quote?.currency ?? 'USD',
    subtotal: props.quote?.subtotal ?? 0,
    tax: props.quote?.tax ?? 0,
    total: props.quote?.total ?? 0,
    sent_at: props.quote?.sent_at ?? '',
    accepted_at: props.quote?.accepted_at ?? '',
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

        router.visit(`/quotes/${response.data.id}`)
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
        <QuoteAssociationsSection
            v-model="form"
            :deals="deals"
        />

        <QuoteDetailsSection v-model="form" />

        <button
            type="submit"
            class="bg-blue-600 text-white px-5 py-2 rounded disabled:opacity-50"
            :disabled="form.processing"
        >
            {{ form.processing ? 'Saving...' : (label ?? 'Save Quote') }}
        </button>
    </form>
</template>