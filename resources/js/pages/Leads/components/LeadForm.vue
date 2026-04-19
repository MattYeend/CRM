<script setup lang="ts">
import axios from 'axios'
import { useForm, router } from '@inertiajs/vue3'

import LeadPersonalSection from './LeadPersonalSection.vue'
import LeadContactSection from './LeadContactSection.vue'
import LeadAssignmentSection from './LeadAssignmentSection.vue'

interface User {
    id: number
    name: string
}

interface Lead {
    id?: number
    title?: string
    first_name?: string
    last_name?: string
    email?: string
    phone?: string
    source?: string | null
    owner_id?: number | null
    assigned_to?: { id: number } | number | null
}

const props = defineProps<{
    lead?: Lead
    users: User[]
    method?: 'post' | 'put'
    submitLabel?: string
    submitRoute: string
}>()

const form = useForm({
    title: props.lead?.title ?? '',
    first_name: props.lead?.first_name ?? '',
    last_name: props.lead?.last_name ?? '',
    email: props.lead?.email ?? '',
    phone: props.lead?.phone ?? '',
    source: props.lead?.source ?? null,
    owner_id: props.lead?.owner_id ?? null,
    assigned_to: props.lead?.assigned_to
        ? typeof props.lead.assigned_to === 'object'
            ? props.lead.assigned_to.id
            : props.lead.assigned_to
        : null,
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

        router.visit(`/leads/${response.data.id}`)
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

        <LeadPersonalSection :form="form" />
        <LeadContactSection :form="form" />
        <LeadAssignmentSection :form="form" :users="users" />

        <button
            type="submit"
            class="bg-blue-600 text-white px-5 py-2 rounded"
            :disabled="form.processing"
        >
            {{ form.processing ? 'Saving...' : (submitLabel ?? 'Save Lead') }}
        </button>
    </form>
</template>