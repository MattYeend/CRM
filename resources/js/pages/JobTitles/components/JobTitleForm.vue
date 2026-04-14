<script setup lang="ts">
import axios from 'axios'
import { useForm, router } from '@inertiajs/vue3'

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

const groupOptions = [
    { value: 'c_suite', label: 'C-Suite' },
    { value: 'executive', label: 'Executive' },
    { value: 'director', label: 'Director' },
]

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

        <div class="space-y-4">
            <h2 class="text-lg font-semibold border-b pb-2">Job Title Details</h2>

            <div>
                <label class="block text-sm font-medium mb-1">
                    Title <span class="text-red-500">*</span>
                </label>
                <input
                    v-model="form.title"
                    type="text"
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="e.g. Chief Executive Officer"
                />
                <p v-if="form.errors.title" class="text-red-500 text-sm mt-1">{{ form.errors.title }}</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Short Code</label>
                    <input
                        v-model="form.short_code"
                        type="text"
                        class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="e.g. CEO"
                    />
                    <p v-if="form.errors.short_code" class="text-red-500 text-sm mt-1">{{ form.errors.short_code }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Group</label>
                    <select
                        v-model="form.group"
                        class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option :value="null">— None —</option>
                        <option v-for="g in groupOptions" :key="g.value" :value="g.value">{{ g.label }}</option>
                    </select>
                    <p v-if="form.errors.group" class="text-red-500 text-sm mt-1">{{ form.errors.group }}</p>
                </div>
            </div>
        </div>

        <div>
            <button
                type="submit"
                class="bg-blue-600 text-white px-5 py-2 rounded disabled:opacity-50"
                :disabled="form.processing"
            >
                {{ form.processing ? 'Saving...' : (submitLabel ?? 'Save Job Title') }}
            </button>
        </div>
    </form>
</template>