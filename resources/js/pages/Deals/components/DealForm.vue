<script setup lang="ts">
import axios from 'axios'
import { useForm, router } from '@inertiajs/vue3'

import DealDetailsSection from './DealDetailsSection.vue'
import DealAssociationsSection from './DealAssociationsSection.vue'

interface SelectOption {
    id: number
    name: string
}

interface Deal {
    id?: number
    title?: string
    company_id?: number | null
    owner_id?: number | null
    pipeline_id?: number | null
    stage_id?: number | null
    value?: number
    currency?: string
    close_date?: string | null
    status?: 'open' | 'won' | 'lost' | 'archived'
    is_test?: boolean
}

const props = defineProps<{
    deal?: Deal
    companies: SelectOption[]
    owners: SelectOption[]
    pipelines: SelectOption[]
    stages: SelectOption[]
    method?: 'post' | 'put'
    label?: string
    submitLabel?: string
    submitRoute: string
}>()

const form = useForm({
    title: props.deal?.title ?? '',
    company_id: props.deal?.company_id ?? null,
    owner_id: props.deal?.owner_id ?? null,
    pipeline_id: props.deal?.pipeline_id ?? null,
    stage_id: props.deal?.stage_id ?? null,
    value: props.deal?.value ?? 0,
    currency: props.deal?.currency ?? 'USD',
    close_date: props.deal?.close_date ?? '',
    status: props.deal?.status ?? 'open',
    is_test: props.deal?.is_test ?? false,
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

        router.visit(`/deals/${response.data.id}`)
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
        <DealDetailsSection v-model="form" />

        <DealAssociationsSection
            v-model="form"
            :companies="companies"
            :owners="owners"
            :pipelines="pipelines"
            :stages="stages"
        />

        <button
            type="submit"
            class="bg-blue-600 text-white px-5 py-2 rounded disabled:opacity-50"
            :disabled="form.processing"
        >
            {{ form.processing ? 'Saving...' : (label ?? 'Save Deal') }}
        </button>
    </form>
</template>