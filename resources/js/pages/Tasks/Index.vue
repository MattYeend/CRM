<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, reactive, onMounted } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { fetchTasks, deleteTasks } from '@/services/taskService'

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
    is_overdue: boolean
    is_pending: boolean
    is_completed: boolean
    is_cancelled: boolean
    creator?: { name: string } | null
    permissions: UserPermissions
}

interface GlobalPermissions {
    create: boolean
    viewAny: boolean
}

interface UserPermissions {
    view: boolean
    update: boolean
    delete: boolean
}

const permissions = ref<GlobalPermissions>({
    create: false,
    viewAny: false,
})

const tasks = ref<Task[]>([])
const loading = ref(true)
const currentPage = ref(1)
const perPage = 10
const pagination = reactive({
    current_page: 1,
    last_page: 1,
    total: 0,
})

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
    return new Date(date).toLocaleDateString('en-GB')
}

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Tasks', href: route('tasks.index') },
]

async function loadTasks(page = 1) {
    loading.value = true
    try {
        const data = await fetchTasks(perPage, page)
        tasks.value = data.data
        permissions.value = data.permissions
        pagination.current_page = data.current_page
        pagination.last_page = data.last_page
        pagination.total = data.total
        currentPage.value = data.current_page
    } finally {
        loading.value = false
    }
}

async function handleDelete(id: number) {
    if (!confirm('Are you sure you want to delete this task?')) return
    await deleteTasks(id)
    loadTasks(currentPage.value)
}

function goToPage(page: number) {
    if (page >= 1 && page <= pagination.last_page) {
        loadTasks(page)
    }
}

onMounted(() => loadTasks())
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Tasks" />

        <div class="p-6">
            <div class="flex justify-between mb-6">
                <h1 class="text-2xl font-bold">Tasks</h1>
                <Link
                    v-if="permissions.create"
                    :href="route('tasks.create')"
                    class="bg-blue-600 text-white px-4 py-2 rounded"
                >
                    Create
                </Link>
            </div>

            <div v-if="loading" class="text-center py-6">
                Loading...
            </div>

            <table v-else class="w-full border">
                <thead>
                    <tr>
                        <th class="p-2 text-left">Title</th>
                        <th class="p-2 text-left">Assignee</th>
                        <th class="p-2 text-left">Priority</th>
                        <th class="p-2 text-left">Status</th>
                        <th class="p-2 text-left">Due Date</th>
                        <th class="p-2 text-left">Related To</th>
                        <th class="p-2"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="task in tasks" :key="task.id" class="border-t">
                        <td class="p-2">
                            <div>{{ task.title }}</div>
                            <div v-if="task.is_overdue" class="text-xs text-red-600">
                                Overdue
                            </div>
                        </td>
                        <td class="p-2">{{ task.assignee?.name ?? '—' }}</td>
                        <td class="p-2">
                            <span
                                class="px-2 py-0.5 rounded-full text-xs font-semibold capitalize"
                                :class="priorityClasses[task.priority] ?? 'bg-gray-100 text-gray-600'"
                            >
                                {{ task.priority }}
                            </span>
                        </td>
                        <td class="p-2">
                            <span
                                class="px-2 py-0.5 rounded-full text-xs font-semibold capitalize"
                                :class="statusClasses[task.status] ?? 'bg-gray-100 text-gray-600'"
                            >
                                {{ task.status.replace('_', ' ') }}
                            </span>
                        </td>
                        <td class="p-2">{{ formatDate(task.due_at) }}</td>
                        <td class="p-2">
                            <span v-if="task.taskable_name" class="text-sm">
                                {{ task.taskable_name }}
                            </span>
                            <span v-else>—</span>
                        </td>
                        <td class="p-2 space-x-2 whitespace-nowrap">
                            <Link
                                v-if="task.permissions.view"
                                :href="route('tasks.show', { task: task.id })"
                            >
                                View
                            </Link>
                            <Link
                                v-if="task.permissions.update"
                                :href="route('tasks.edit', { task: task.id })"
                            >
                                Edit
                            </Link>
                            <button
                                v-if="task.permissions.delete"
                                @click="handleDelete(task.id)"
                                class="text-red-600"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>

                    <tr v-if="tasks.length === 0">
                        <td colspan="8" class="p-4 text-center text-gray-500">
                            No tasks found.
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="pagination.last_page > 1" class="flex justify-center mt-4 space-x-2">
                <button
                    class="px-3 py-1 border rounded disabled:opacity-50"
                    :disabled="currentPage === 1"
                    @click="goToPage(currentPage - 1)"
                >
                    Previous
                </button>

                <button
                    v-for="page in pagination.last_page"
                    :key="page"
                    class="px-3 py-1 border rounded"
                    :class="{ 'bg-blue-600 text-white': page === currentPage }"
                    @click="goToPage(page)"
                >
                    {{ page }}
                </button>

                <button
                    class="px-3 py-1 border rounded disabled:opacity-50"
                    :disabled="currentPage === pagination.last_page"
                    @click="goToPage(currentPage + 1)"
                >
                    Next
                </button>
            </div>
        </div>
    </AppLayout>
</template>