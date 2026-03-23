<script setup lang="ts">
import { useForm, router } from '@inertiajs/vue3'
import axios from 'axios'
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
    user_id?: number
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
    selected_user_id: props.activity?.user_id ?? null as number | null,
})

const subjectOptions = ref<{ id: number; name: string }[]>([])
const usersOptions = ref<{ id: number; name: string }[]>([])

// Map of subject_type to API endpoint
const typeApiMap: Record<string, string> = {
    company: 'companies',
    contact: 'contacts',
    deal: 'deals',
    task: 'tasks',
    user: 'users',
}

// Watch subject type
watch(
    () => form.subject_type,
    async (type) => {
        form.subject_id = null
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

            // Preselect subject_id if editing
            if (props.activity) {
                const activityType = normalizeSubjectType(props.activity.subject_type)
                const activityId = props.activity.subject_id
                if (activityType === type && activityId) {
                    const exists = subjectOptions.value.find(i => i.id === activityId)
                    if (exists) form.subject_id = activityId
                }
            }
        } catch (err) {
            console.error('Failed to load subjects:', err)
            subjectOptions.value = []
        }
    },
    { immediate: true }
)

// Load users
onMounted(async () => {
    try {
        const response = await axios.get('/api/users')
        const users = response.data.data ?? response.data ?? []

        usersOptions.value = users.map((user: any) => ({
            id: user.id,
            name: user.name ?? user.email ?? `#${user.id}`,
        }))

        // Preselect user on edit
        if (props.activity?.user_id) {
            form.selected_user_id = props.activity.user_id
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
                // Map selected_user_id to user_id for backend
                if (key === 'selected_user_id') {
                    formData.append('user_id', String(value))
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
        console.error(err.response?.data ?? err)
        if (err.response?.status === 422) {
            form.setError(err.response.data.errors)
        }
    }
}
</script>

<template>
    <form @submit.prevent="submit" class="space-y-4 max-w-xl">
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