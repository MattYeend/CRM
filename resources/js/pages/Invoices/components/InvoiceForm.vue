<script setup lang="ts">
import axios from 'axios'
import { useForm, router } from '@inertiajs/vue3'

import InvoiceDetailsSection from './InvoiceDetailsSection.vue'
import InvoiceFinancialsSection from './InvoiceFinancialsSection.vue'
import InvoiceAssociationsSection from './InvoiceAssociationsSection.vue'

interface SelectOption {
    id: number
    name: string
}

interface Invoice {
    id?: number
    number?: string
    company_id?: number | null
    status?: 'draft' | 'sent' | 'paid' | 'overdue' | 'cancelled'
    currency?: string
    subtotal?: number
    tax?: number
    total?: number
    issue_date?: string | null
    due_date?: string | null
    is_test?: boolean
}

const props = defineProps<{
    invoice?: Invoice
    companies: SelectOption[]
    method?: 'post' | 'put'
    submitLabel?: string
    submitRoute: string
}>()

const form = useForm({
    number: props.invoice?.number ?? '',
    company_id: props.invoice?.company_id ?? null,
    status: props.invoice?.status ?? 'draft',
    currency: props.invoice?.currency ?? 'GBP',
    subtotal: props.invoice?.subtotal ?? 0,
    tax: props.invoice?.tax ?? 0,
    total: props.invoice?.total ?? 0,
    issue_date: props.invoice?.issue_date ?? '',
    due_date: props.invoice?.due_date ?? '',
    is_test: props.invoice?.is_test ?? false,
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

        router.visit(`/invoices/${response.data.id}`)
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

        <InvoiceDetailsSection
            :form="form"
        />

        <InvoiceFinancialsSection
            :form="form"
        />

        <InvoiceAssociationsSection
            :form="form"
            :companies="companies"
        />

        <button
            class="bg-blue-600 text-white px-5 py-2 rounded"
            :disabled="form.processing"
        >
            {{ form.processing ? 'Saving...' : (submitLabel ?? 'Save Invoice') }}
        </button>

    </form>
</template>