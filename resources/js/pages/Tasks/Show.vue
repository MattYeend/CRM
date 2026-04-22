<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, onMounted } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { fetchTask, deleteTasks } from '@/services/taskService'

interface UserPermissions {
    view: boolean
    update: boolean
    delete: boolean
}

interface Task {
    id: number
    title: string
    description?: string | null
    priority: string
    status: string
    due_at?: string | null
    assigned_to?: number | null
    assignee?: { id: number; name: string } | null
    taskable_type?: string | null
    taskable_id?: number | null
    taskable_name?: string | null
    taskable?: any
    is_overdue: boolean
    is_pending: boolean
    is_completed: boolean
    is_cancelled: boolean
    creator?: { id: number; name: string } | null
    permissions: UserPermissions
}

const statusClasses: Record<string, string> = {
    pending: 'bg-yellow-100 text-yellow-700',
    in_progress: 'bg-blue-100 text-blue-700',
    completed: 'bg-green-100 text-green-700',
    cancelled: 'bg-gray-100 text-gray-600',
}

const priorityClasses: Record<string, string> = {
    low: 'bg-green-100 text-green-700',
    medium: 'bg-yellow-100 text-yellow-700',
    high: 'bg-orange-100 text-orange-700',
    urgent: 'bg-red-100 text-red-700',
}

function formatDate(date?: string | null) {
    if (!date) return '—'
    return new Date(date).toLocaleDateString('en-GB', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    })
}

const props = defineProps<{ task: any }>()

const task = ref<Task>({
    id: props.task.id,
    title: props.task.title,
    description: props.task.description,
    priority: props.task.priority ?? 'medium',
    status: props.task.status ?? 'pending',
    due_at: props.task.due_at,
    assigned_to: props.task.assigned_to,
    assignee: props.task.assignee,
    taskable_type: props.task.taskable_type,
    taskable_id: props.task.taskable_id,
    taskable_name: props.task.taskable_name,
    taskable: props.task.taskable,
    is_overdue: props.task.is_overdue ?? false,
    is_pending: props.task.is_pending ?? false,
    is_completed: props.task.is_completed ?? false,
    is_cancelled: props.task.is_cancelled ?? false,
    creator: props.task.creator,
    permissions: props.task.permissions ?? { view: false, update: false, delete: false },
})

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Tasks', href: route('tasks.index') },
    { title: task.value.title, href: route('tasks.show', { task: task.value.id }) },
]

async function loadTask() {
    const data = await fetchTask(task.value.id)
    Object.assign(task.value, data)
}

async function handleDelete() {
    if (!confirm('Are you sure you want to delete this task?')) return
    await deleteTasks(task.value.id)
    window.location.href = route('tasks.index')
}

onMounted(() => loadTask())
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="`Task: ${task.title}`" />
        <div class="p-6">
            <div class="mx-auto border p-6 rounded shadow">

                <!-- Header -->
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <h1 class="text-2xl font-bold">{{ task.title }}</h1>
                        <div class="mt-2 flex items-center gap-2">
                            <span
                                class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold capitalize"
                                :class="statusClasses[task.status] ?? 'bg-gray-100 text-gray-600'"
                            >
                                {{ task.status.replace('_', ' ') }}
                            </span>
                            <span
                                class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold capitalize"
                                :class="priorityClasses[task.priority] ?? 'bg-gray-100 text-gray-600'"
                            >
                                {{ task.priority }} Priority
                            </span>
                            <span
                                v-if="task.is_overdue"
                                class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700"
                            >
                                Overdue
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center space-x-2">
                        <Link
                            v-if="task.permissions?.update"
                            :href="route('tasks.edit', { task: task.id })"
                            class="bg-blue-600 text-white px-4 py-2 rounded"
                        >
                            Edit
                        </Link>
                        <Link
                            :href="route('tasks.index')"
                            class="bg-gray-200 text-gray-700 px-4 py-2 rounded"
                        >
                            Back
                        </Link>
                        <button
                            v-if="task.permissions?.delete"
                            @click="handleDelete"
                            class="bg-red-600 text-white px-4 py-2 rounded"
                        >
                            Delete
                        </button>
                    </div>
                </div>

                <!-- Description -->
                <div v-if="task.description" class="mb-6">
                    <h2 class="text-lg font-semibold border-b pb-2 mb-3">Description</h2>
                    <p class="text-gray-700 whitespace-pre-wrap">{{ task.description }}</p>
                </div>

                <!-- Details -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 mb-6">
                    <div v-if="task.assignee">
                        <span class="font-semibold">Assigned To: </span>
                        <span>{{ task.assignee.name }}</span>
                    </div>
                    <div v-if="task.due_at">
                        <span class="font-semibold">Due Date: </span>
                        <span>{{ formatDate(task.due_at) }}</span>
                    </div>
                    <div v-if="task.taskable_name">
                        <span class="font-semibold">Related To: </span>
                        <span>{{ task.taskable_name }}</span>
                    </div>
                    <div v-if="task.creator">
                        <span class="font-semibold">Created By: </span>
                        <span>{{ task.creator.name }}</span>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>