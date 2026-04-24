<script setup lang="ts">
import axios from 'axios'
import { useForm, router } from '@inertiajs/vue3'
import SupplierIdentitySection from './SupplierIdentitySection.vue'
import SupplierAddressSection from './SupplierAddressSection.vue'
import SupplierContactSection from './SupplierContactSection.vue'
import SupplierBusinessSection from './SupplierBusinessSection.vue'

interface Supplier {
    id?: number
    name?: string
    code?: string
    email?: string
    phone?: string
    website?: string
    currency?: string
    payment_terms?: string
    tax_number?: string
    is_active?: boolean
    notes?: string
    address_line_1?: string
    address_line_2?: string
    city?: string
    county?: string
    postcode?: string
    country?: string
    contact_name?: string
    contact_email?: string
    contact_phone?: string
}

const props = defineProps<{
    supplier?: Supplier
    method?: 'post' | 'put'
    submitLabel?: string
    submitRoute: string
}>()

const form = useForm({
    name: props.supplier?.name ?? '',
    code: props.supplier?.code ?? '',
    email: props.supplier?.email ?? '',
    phone: props.supplier?.phone ?? '',
    website: props.supplier?.website ?? '',
    currency: props.supplier?.currency ?? '',
    payment_terms: props.supplier?.payment_terms ?? '',
    tax_number: props.supplier?.tax_number ?? '',
    is_active: props.supplier?.is_active ?? true,
    notes: props.supplier?.notes ?? '',
    address_line_1: props.supplier?.address_line_1 ?? '',
    address_line_2: props.supplier?.address_line_2 ?? '',
    city: props.supplier?.city ?? '',
    county: props.supplier?.county ?? '',
    postcode: props.supplier?.postcode ?? '',
    country: props.supplier?.country ?? '',
    contact_name: props.supplier?.contact_name ?? '',
    contact_email: props.supplier?.contact_email ?? '',
    contact_phone: props.supplier?.contact_phone ?? '',
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

        router.visit(`/suppliers/${response.data.id}`)
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
        <SupplierIdentitySection :form="form" />
        <SupplierAddressSection :form="form" />
        <SupplierContactSection :form="form" />
        <SupplierBusinessSection :form="form" />

        <div>
            <button
                type="submit"
                class="bg-blue-600 text-white px-5 py-2 rounded disabled:opacity-50"
                :disabled="form.processing"
            >
                {{ form.processing ? 'Saving...' : (submitLabel ?? 'Save Supplier') }}
            </button>
        </div>
    </form>
</template>