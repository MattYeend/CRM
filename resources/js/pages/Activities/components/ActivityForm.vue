<script setup lang="ts">
import { useForm, router } from '@inertiajs/vue3'
import axios from 'axios'
import { ref, onMounted, watch } from 'vue'

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

// --- Form ---
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

// --- Watch subject type ---
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

// --- Load users ---
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

// --- Submit ---
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
        <!-- Type -->
        <div>
            <label class="block text-sm font-medium mb-1">Type</label>
            <input
                v-model="form.type"
                type="text"
                class="border rounded w-full p-2"
            />
            <p v-if="form.errors.type" class="text-red-500 text-sm">
                {{ form.errors.type }}
            </p>
        </div>

        <!-- Users Dropdown -->
        <div v-if="usersOptions.length">
            <label class="block text-sm font-medium mb-1">Assign User</label>
            <select v-model="form.selected_user_id" class="border rounded w-full p-2">
                <option value="">Select user</option>
                <option v-for="user in usersOptions" :key="user.id" :value="user.id">
                    {{ user.name }}
                </option>
            </select>
            <p v-if="form.errors.selected_user_id" class="text-red-500 text-sm">
                {{ form.errors.selected_user_id }}
            </p>
        </div>

        <!-- Description -->
        <div>
            <label class="block text-sm font-medium mb-1">Description</label>
            <textarea
                v-model="form.description"
                class="border rounded w-full p-2"
            />
            <p v-if="form.errors.description" class="text-red-500 text-sm">
                {{ form.errors.description }}
            </p>
        </div>

        <!-- Subject Type -->
        <div>
            <label class="block text-sm font-medium mb-1">Subject Type</label>
            <select v-model="form.subject_type" class="border rounded w-full p-2">
                <option value="">Select type</option>
                <option v-for="type in props.subjectTypes" :key="type" :value="type">
                    {{ type }}
                </option>
            </select>
            <p v-if="form.errors.subject_type" class="text-red-500 text-sm">
                {{ form.errors.subject_type }}
            </p>
        </div>

        <!-- Subject ID -->
        <div v-if="subjectOptions.length">
            <label class="block text-sm font-medium mb-1">Subject</label>
            <select v-model="form.subject_id" class="border rounded w-full p-2">
                <option value="">Select subject</option>
                <option v-for="item in subjectOptions" :key="item.id" :value="item.id">
                    {{ item.name }}
                </option>
            </select>
            <p v-if="form.errors.subject_id" class="text-red-500 text-sm">
                {{ form.errors.subject_id }}
            </p>
        </div>

        <!-- Submit -->
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