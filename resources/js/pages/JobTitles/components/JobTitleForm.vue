<script setup lang="ts">
import axios from 'axios'
import { useForm, router } from '@inertiajs/vue3'

import JobTitleDetailsSection from './JobTitleDetailsSection.vue'

interface JobTitle {
    id?: number
    title?: string
    short_code?: string
    group?: string | null
    is_test?: boolean
    meta?: Record<string, any> | null
}

const props = defineProps<{
    jobTitle?: JobTitle
    method?: 'post' | 'put'
    submitLabel?: string
    submitRoute: string
}>()

const form = useForm({
    title: props.jobTitle?.title ?? '',
    short_code: props.jobTitle?.short_code ?? '',
    group: props.jobTitle?.group ?? null,
    is_test: props.jobTitle?.is_test ?? false,
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

        router.visit(`/job-titles/${response.data.id}`)
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

        <JobTitleDetailsSection
            :title="form.title"
            :short-code="form.short_code"
            :group="form.group"
            :errors="form.errors"
            @update:title="form.title = $event"
            @update:short-code="form.short_code = $event"
            @update:group="form.group = $event"
        />

        <button
            type="submit"
            class="bg-blue-600 text-white px-5 py-2 rounded disabled:opacity-50"
            :disabled="form.processing"
        >
            {{ form.processing ? 'Saving...' : (submitLabel ?? 'Save Job Title') }}
        </button>

    </form>
</template>