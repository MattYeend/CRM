<script setup lang="ts">
import axios from 'axios'
import { useForm, router } from '@inertiajs/vue3'
import TaskDetailsSection from './TaskDetailsSection.vue'
import TaskAssociationSection from './TaskAssociationSection.vue'

interface SelectOption {
    id: number
    name: string
}

interface Task {
    id?: number
    title?: string
    description?: string | null
    status?: string
    priority?: string
    due_at?: string | null
    assigned_to?: number | null
    taskable_type?: string | null
    taskable_id?: number | null
}

const props = defineProps<{
    task?: Task
    users: SelectOption[]
    taskableTypes: string[]
    method?: 'post' | 'put'
    submitLabel?: string
    submitRoute: string
}>()

const statusOptions = [
    { value: 'pending', label: 'Pending' },
    { value: 'in_progress', label: 'In Progress' },
    { value: 'completed', label: 'Completed' },
    { value: 'cancelled', label: 'Cancelled' },
]

const priorityOptions = [
    { value: 'low', label: 'Low' },
    { value: 'medium', label: 'Medium' },
    { value: 'high', label: 'High' },
    { value: 'urgent', label: 'Urgent' },
]

const form = useForm({
    title: props.task?.title ?? '',
    description: props.task?.description ?? '',
    status: props.task?.status ?? 'pending',
    priority: props.task?.priority ?? 'medium',
    due_at: props.task?.due_at?.slice(0, 16) ?? '',
    assigned_to: props.task?.assigned_to ?? null,
    taskable_type: props.task?.taskable_type ?? null,
    taskable_id: props.task?.taskable_id ?? null,
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

        router.visit(`/tasks/${response.data.id}`)
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

        <TaskDetailsSection
            :form="form"
            :statusOptions="statusOptions"
            :priorityOptions="priorityOptions"
        />

        <TaskAssociationSection
            :form="form"
            :users="users"
            :taskableTypes="taskableTypes"
        />

        <button
            type="submit"
            class="bg-blue-600 text-white px-5 py-2 rounded disabled:opacity-50"
            :disabled="form.processing"
        >
            {{ form.processing ? 'Saving...' : (submitLabel ?? 'Save Task') }}
        </button>
    </form>
</template>