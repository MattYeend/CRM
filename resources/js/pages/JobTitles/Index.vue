<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, reactive, onMounted } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { fetchJobTitles, deleteJobTitles } from '@/services/jobTitleService'

interface JobTitle {
    id: number
    title: string
    short_code: string | null
    group: string | null
    is_csuite: boolean
    is_executive: boolean
    is_director: boolean
    user_count: number
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

const jobTitles = ref<JobTitle[]>([])
const loading = ref(true)
const currentPage = ref(1)
const perPage = 10
const pagination = reactive({
    current_page: 1,
    last_page: 1,
    total: 0,
})

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Job Titles', href: route('job-titles.index') },
]

const groupLabels: Record<string, string> = {
    c_suite: 'C-Suite',
    executive: 'Executive',
    director: 'Director',
}

const groupClasses: Record<string, string> = {
    c_suite: 'bg-purple-100 text-purple-700',
    executive: 'bg-blue-100 text-blue-700',
    director: 'bg-green-100 text-green-700',
}

async function loadJobTitles(page = 1) {
    loading.value = true
    try {
        const data = await fetchJobTitles(perPage, page)
        jobTitles.value = data.data
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
    if (!confirm('Are you sure you want to delete this job title?')) return
    await deleteJobTitles(id)
    loadJobTitles(currentPage.value)
}

function goToPage(page: number) {
    if (page >= 1 && page <= pagination.last_page) {
        loadJobTitles(page)
    }
}

onMounted(() => loadJobTitles())
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Job Titles" />

        <div class="p-6">
            <div class="flex justify-between mb-6">
                <h1 class="text-2xl font-bold">Job Titles</h1>
                <Link
                    v-if="permissions.create"
                    :href="route('job-titles.create')"
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
                        <th class="p-2 text-left">Short Code</th>
                        <th class="p-2 text-left">Group</th>
                        <th class="p-2 text-left">Level</th>
                        <th class="p-2 text-right">Users</th>
                        <th class="p-2"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="jobTitle in jobTitles" :key="jobTitle.id" class="border-t">
                        <td class="p-2 font-medium">{{ jobTitle.title }}</td>
                        <td class="p-2 text-gray-500">{{ jobTitle.short_code ?? '—' }}</td>
                        <td class="p-2">
                            <span
                                v-if="jobTitle.group"
                                class="px-2 py-0.5 rounded-full text-xs font-semibold"
                                :class="groupClasses[jobTitle.group] ?? 'bg-gray-100 text-gray-600'"
                            >
                                {{ groupLabels[jobTitle.group] ?? jobTitle.group }}
                            </span>
                            <span v-else class="text-gray-400">—</span>
                        </td>
                        <td class="p-2">
                            <span
                                v-if="jobTitle.is_csuite"
                                class="px-2 py-0.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-700"
                            >
                                C-Suite
                            </span>
                            <span
                                v-else-if="jobTitle.is_director" 
                                class="px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700"
                            >
                                Director
                            </span>
                            <span
                                v-else-if="jobTitle.is_executive" 
                                class="px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700"
                            >
                                Executive
                            </span>
                            <span
                                v-else 
                                class="px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700"
                            >
                                Staff
                            </span>
                        </td>
                        <td class="p-2 text-right">{{ jobTitle.user_count }}</td>
                        <td class="p-2 space-x-2 whitespace-nowrap">
                            <Link
                                v-if="jobTitle.permissions.view"
                                :href="route('job-titles.show', { jobTitle: jobTitle.id })"
                            >
                                View
                            </Link>
                            <Link
                                v-if="jobTitle.permissions.update && jobTitle.user_count === 0"
                                :href="route('job-titles.edit', { jobTitle: jobTitle.id })"
                            >
                                Edit
                            </Link>
                            <button
                                v-if="jobTitle.permissions.delete && jobTitle.user_count === 0"
                                @click="handleDelete(jobTitle.id)"
                                class="text-red-600"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>

                    <tr v-if="jobTitles.length === 0">
                        <td colspan="6" class="p-4 text-center text-gray-500">
                            No job titles found.
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