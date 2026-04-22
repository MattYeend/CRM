<script setup lang="ts">
import axios from 'axios'
import { useForm, router } from '@inertiajs/vue3'
import { ref, onMounted, watch } from 'vue'
import ActivityTypeUserSection from './ActivityTypeUserSection.vue'
import ActivityDescriptionSection from './ActivityDescriptionSection.vue'
import ActivitySubjectSection from './ActivitySubjectSection.vue'

interface Activity {
    id?: number
    type: string
    description: string
    subject_type?: string
    subject_id?: number
    assigned_to?: number
}

const props = defineProps<{
    activity?: Activity
    method?: 'post' | 'put'
    submitLabel?: string
    subjectTypes: string[]
}>()

function normalizeSubjectType(type?: string): string | undefined {
    if (!type) return undefined
    return type.split('\\').pop()?.toLowerCase()
}

const form = useForm({
    type: props.activity?.type ?? '',
    description: props.activity?.description ?? '',
    subject_type: normalizeSubjectType(props.activity?.subject_type) ?? '',
    subject_id: props.activity?.subject_id ?? null as number | null,
    selected_assigned_to: props.activity?.assigned_to ?? null as number | null,
})

const subjectOptions = ref<{ id: number; name: string }[]>([])
const usersOptions = ref<{ id: number; name: string }[]>([])

const typeApiMap: Record<string, string> = {
    company: 'companies/all',
    deal: 'deals/all',
    task: 'tasks/all',
    user: 'users/all',
}

watch(
    () => form.subject_type,
    async (type, oldType) => {
        // Only reset subject_id if the user actually changed the type
        if (oldType !== undefined) {
            form.subject_id = null
        }
        subjectOptions.value = []

        if (!type) return

        const endpoint = typeApiMap[type.toLowerCase()]
        if (!endpoint) return

        try {
            const response = await axios.get(`/api/${endpoint}`)
            const items = response.data.data ?? response.data ?? []

            subjectOptions.value = items.map((item: any) => ({
                id: item.id,
                name: item.name ?? item.title ?? `#${item.id}`,
            }))

            // Always restore the saved subject_id after loading options
            if (props.activity?.subject_id) {
                const exists = subjectOptions.value.find(i => i.id === props.activity!.subject_id)
                if (exists) form.subject_id = props.activity!.subject_id
            }
        } catch (err) {
            console.error('Failed to load subjects:', err)
            subjectOptions.value = []
        }
    },
    { immediate: true }
)

onMounted(async () => {
    try {
        const response = await axios.get('/api/users')
        const users = response.data.data ?? response.data ?? []

        usersOptions.value = users.map((user: any) => ({
            id: user.id,
            name: user.name ?? user.email ?? `#${user.id}`,
        }))

        if (props.activity?.assigned_to) {
            form.selected_assigned_to = props.activity.assigned_to
        }
    } catch (err) {
        console.error('Failed to load users:', err)
        usersOptions.value = []
    }
})

async function submit() {
    try {
        await axios.get('/sanctum/csrf-cookie', { withCredentials: true })

        const formData = new FormData()
        Object.entries(form.data()).forEach(([key, value]) => {
            if (value !== null && value !== undefined) {
                if (key === 'selected_assigned_to') {
                    formData.append('assigned_to', String(value))
                } else {
                    formData.append(key, String(value))
                }
            }
        })

        const url =
            props.method === 'put' && props.activity?.id
                ? `/api/activities/${props.activity.id}`
                : '/api/activities'

        const response = await axios({
            method: props.method === 'put' ? 'put' : 'post',
            url,
            data: formData,
            withCredentials: true,
            headers: { 'Content-Type': 'multipart/form-data' },
        })

        router.visit(`/activities/${response.data.id}`)
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
    <form @submit.prevent="submit" class="space-y-8 max-w-xl">
        <ActivityTypeUserSection :form="form" :users="usersOptions" />

        <ActivityDescriptionSection :form="form" />

        <ActivitySubjectSection
            :form="form"
            :subject-types="props.subjectTypes"
            :subject-options="subjectOptions"
        />

        <div>
            <button
                class="bg-blue-600 text-white px-5 py-2 rounded"
                :disabled="form.processing"
            >
                {{ form.processing ? 'Saving...' : submitLabel ?? 'Save Activity' }}
            </button>
        </div>
    </form>
</template>