<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, reactive, onMounted, computed } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { fetchActivities, deleteActivities } from '@/services/activityService'

interface Activity {
    id: number
    username: string
    type: string
    subject_type: string
    description?: string
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

const activities = ref<Activity[]>([])
const loading = ref(true)
const currentPage = ref(1)
const perPage = 10
const pagination = reactive({
    current_page: 1,
    last_page: 1,
    total: 0,
})

function capitalize(str: string | null | undefined) {
    if (!str) return '-'
    return str
        .split(' ')
        .map(s => s.charAt(0).toUpperCase() + s.slice(1))
        .join(' ')
}

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Activities', href: route('activities.index') },
]

const visiblePages = computed(() => {
    const total = pagination.last_page
    const current = currentPage.value
    const delta = 2

    const pages: (number | string)[] = []

    const start = Math.max(1, current - delta)
    const end = Math.min(total, current + delta)

    if (start > 1) {
        pages.push(1)
        if (start > 2) pages.push('...')
    }

    for (let i = start; i <= end; i++) pages.push(i)

    if (end < total) {
        if (end < total - 1) pages.push('...')
        pages.push(total)
    }

    return pages
})


async function loadActivities(page = 1) {
    loading.value = true
    try {
        const data = await fetchActivities(perPage, page)
        activities.value = data.data
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
    if (!confirm('Are you sure?')) return
    await deleteActivities(id)
    loadActivities(currentPage.value)
}

function goToPage(page: number) {
    if (page >= 1 && page <= pagination.last_page) {
        loadActivities(page)
    }
}

onMounted(() => loadActivities())
</script>

<template>
<AppLayout :breadcrumbs="breadcrumbItems">
    <Head title="Activities" />

    <div class="p-6">
        <div class="flex justify-between mb-6">
            <h1 class="text-2xl font-bold">Activities</h1>
            <Link
                v-if="permissions.create"
                href="/activities/create"
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
                    <th class="p-2">User</th>
                    <th class="p-2">Type</th>
                    <th class="p-2">Subject</th>
                    <th class="p-2">Description</th>
                    <th class="p-2"></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="activity in activities" :key="activity.id" class="border-t">
                    <td class="p-2">{{ activity.username }}</td>
                    <td class="p-2">{{ capitalize(activity.type) }}</td>
                    <td class="p-2">{{ capitalize(activity.subject_type) || '-' }}</td>
                    <td class="p-2">{{ activity.description || '—' }}</td>
                    <td class="p-2 space-x-2">
                        <Link
                            v-if="activity.permissions.view"
                            :href="route('activities.show', { activity: activity.id })"
                        >
                            View
                        </Link>

                        <Link
                            v-if="activity.permissions.update"
                            :href="route('activities.edit', { activity: activity.id })"
                        >
                            Edit
                        </Link>

                        <button
                            v-if="activity.permissions.delete"
                            @click="handleDelete(activity.id)"
                            class="text-red-600"
                        >
                            Delete
                        </button>
                    </td>
                </tr>

                <tr v-if="activities.length === 0">
                    <td colspan="5" class="p-4 text-center text-gray-500">
                        No activities found.
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
                    v-for="page in visiblePages"
                    :key="page"
                    class="px-3 py-1 border rounded"
                    :class="{ 'bg-blue-600 text-white': page === currentPage }"
                    :disabled="page === '...'"
                    @click="typeof page === 'number' && goToPage(page)"
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