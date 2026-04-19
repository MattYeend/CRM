<script setup lang="ts">
import axios from 'axios'
import { useForm, router } from '@inertiajs/vue3'

import IndustryDetailsSection from './IndustryDetailsSection.vue'

interface Industry {
    id?: number
    name?: string
}

const props = defineProps<{
    industry?: Industry
    method?: 'post' | 'put'
    submitLabel?: string
    submitRoute: string
}>()

const form = useForm({
    name: props.industry?.name ?? '',
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

        router.visit(`/industries/${response.data.id}`)
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
    <form @submit.prevent="submit" class="space-y-6 max-w-md">

        <IndustryDetailsSection :form="form" />

        <button
            type="submit"
            class="bg-blue-600 text-white px-5 py-2 rounded disabled:opacity-50"
            :disabled="form.processing"
        >
            {{ form.processing ? 'Saving...' : (submitLabel ?? 'Save Industry') }}
        </button>
    </form>
</template>