<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, onMounted } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { fetchTask, deleteTasks } from '@/services/taskService'
import TaskDetailSection from './components/TaskDetailSection.vue'

interface UserPermissions {
    view: boolean
    update: boolean
    delete: boolean
}

interface Taskable {
    id: number
    name?: string
    title?: string
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
    taskable_type: string
    taskable_id: number | null
    taskable_name: string | null
    taskable: Taskable
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

const props = defineProps<{ task: Task }>()

const deleting = ref(false)

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Tasks', href: route('tasks.index') },
    { title: props.task.title, href: route('tasks.show', { task: props.task.id }) },
]

async function loadTask() {
    const data = await fetchTask(props.task.id)
    Object.assign(props.task, data)
}

async function handleDelete() {
    if (!confirm('Are you sure?')) return
    deleting.value = true
    try {
        await deleteTasks(props.task.id)
        window.location.href = route('tasks.index')
    } catch {
        deleting.value = false
    }
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

                <TaskDetailSection :task="task" />
            </div>
        </div>
    </AppLayout>
</template>