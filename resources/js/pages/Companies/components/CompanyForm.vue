<script setup lang="ts">
import axios from 'axios'
import { useForm, router } from '@inertiajs/vue3'
import CompanyIdentitySection from './CompanyIdentitySection.vue'
import CompanyAddressSection from './CompanyAddressSection.vue'
import CompanyContactSection from './CompanyContactSection.vue'

interface Company {
    id?: number
    name?: string
    industry?: string
    website?: string
    phone?: string
    address?: string
    city?: string
    region?: string
    postal_code?: string
    country?: string
    contact_first_name?: string
    contact_last_name?: string
    contact_email?: string
    contact_phone?: string
    is_test?: boolean
}

const props = defineProps<{
    company?: Company
    method?: 'post' | 'put'
    submitLabel?: string
    submitRoute: string
}>()

const form = useForm({
    name: props.company?.name ?? '',
    industry: props.company?.industry ?? '',
    website: props.company?.website ?? '',
    phone: props.company?.phone ?? '',
    address: props.company?.address ?? '',
    city: props.company?.city ?? '',
    region: props.company?.region ?? '',
    postal_code: props.company?.postal_code ?? '',
    country: props.company?.country ?? '',
    contact_first_name: props.company?.contact_first_name ?? '',
    contact_last_name: props.company?.contact_last_name ?? '',
    contact_email: props.company?.contact_email ?? '',
    contact_phone: props.company?.contact_phone ?? '',
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

        router.visit(`/companies/${response.data.id}`)
    } catch (err: any) {
        console.error(err.response?.data ?? err)
        if (err.response?.status === 422) {
            form.setError(err.response.data.errors)
        }
    }
}
</script>

<template>
    <form @submit.prevent="submit" class="space-y-8 max-w-2xl">
        <CompanyIdentitySection :form="form" />
        <CompanyAddressSection :form="form" />
        <CompanyContactSection :form="form" />

        <div>
            <button
                type="submit"
                class="bg-blue-600 text-white px-5 py-2 rounded disabled:opacity-50"
                :disabled="form.processing"
            >
                {{ form.processing ? 'Saving...' : (submitLabel ?? 'Save Company') }}
            </button>
        </div>
    </form>
</template>
