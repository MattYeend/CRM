<script setup lang="ts">
import axios from 'axios'
import { useForm, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'

import NoteBodySection from './NoteBodySection.vue'
import NoteNotableSection from './NoteNotableSection.vue'

interface Note {
    id?: number
    body: string
    notable_type?: string
    notable_id?: number
}

const props = defineProps<{
    note?: Note
    method?: 'post' | 'put'
    submitLabel?: string
    notableTypes: string[]
}>()

function normalizeNotableType(type?: string): string {
    if (!type) return ''
    return type.split('\\').pop()?.toLowerCase() ?? ''
}

const form = useForm({
    body: props.note?.body ?? '',
    notable_type: normalizeNotableType(props.note?.notable_type),
    notable_id: props.note?.notable_id ?? null as number | null,
})

const notableOptions = ref<{ id: number; name: string }[]>([])

const typeApiMap: Record<string, string> = {
    company: 'companies',
    deal: 'deals',
    contact: 'contacts',
    user: 'users',
}

watch(
    () => form.notable_type,
    async (type) => {
        form.notable_id = null
        notableOptions.value = []

        if (!type) return

        const endpoint = typeApiMap[type.toLowerCase()]
        if (!endpoint) return

        try {
            const response = await axios.get(`/api/${endpoint}`)
            const items = response.data.data ?? response.data ?? []

            notableOptions.value = items.map((item: any) => ({
                id: item.id,
                name: item.name ?? item.title ?? `#${item.id}`,
            }))
        } catch (err) {
            console.error('Failed to load notables:', err)
            notableOptions.value = []
        }
    },
    { immediate: true }
)

async function submit() {
    try {
        await axios.get('/sanctum/csrf-cookie', { withCredentials: true })

        const formData = new FormData()

        Object.entries(form.data()).forEach(([key, value]) => {
            if (value !== null && value !== undefined) {
                formData.append(key, String(value))
            }
        })

        const url =
            props.method === 'put' && props.note?.id
                ? `/api/notes/${props.note.id}`
                : '/api/notes'

        const response = await axios({
            method: props.method === 'put' ? 'put' : 'post',
            url,
            data: formData,
            withCredentials: true,
        })

        router.visit(`/notes/${response.data.id}`)
    } catch (err: any) {
        console.error(err.response?.data ?? err)

        if (err.response?.status === 422) {
            const raw = err.response.data.errors as Record<string, string[]>

            const flat = Object.fromEntries(
                Object.entries(raw).map(([k, v]) => [k, v[0]])
            ) as Record<string, string>

            form.setError(flat)
        }
    }
}
</script>

<template>
    <form @submit.prevent="submit" class="space-y-8 max-w-xl">

        <NoteBodySection :form="form" />

        <NoteNotableSection
            :form="form"
            :notable-types="props.notableTypes"
            :notable-options="notableOptions"
        />

        <div>
            <button
                class="bg-blue-600 text-white px-5 py-2 rounded"
                :disabled="form.processing"
            >
                {{ form.processing ? 'Saving...' : submitLabel ?? 'Save Note' }}
            </button>
        </div>
    </form>
</template>