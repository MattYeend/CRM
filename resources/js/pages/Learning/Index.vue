<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, reactive, onMounted } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { fetchLearnings, deleteLearnings } from '@/services/learningService'

interface Learning {
    id: number
    title: string
    description: string | null
    is_complete: boolean
    score: number | null
    completed_at: string | null
    creator: { id: number; name: string } | null
    permissions: {
        view: boolean
        update: boolean
        delete: boolean
        complete: boolean
        incomplete: boolean
    }
}

interface GlobalPermissions {
    create: boolean
    viewAny: boolean
}

const permissions = ref<GlobalPermissions>({ create: false, viewAny: false })
const learnings = ref<Learning[]>([])
const loading = ref(true)
const currentPage = ref(1)
const perPage = 10
const pagination = reactive({
    current_page: 1,
    last_page: 1,
    total: 0,
})

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Learnings', href: route('learnings.index') },
]

async function loadLearnings(page = 1) {
    loading.value = true
    try {
        const data = await fetchLearnings(perPage, page)
        learnings.value = data.data
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
    if (!confirm('Are you sure you want to delete this learning?')) return
    await deleteLearnings(id)
    loadLearnings(currentPage.value)
}

function goToPage(page: number) {
    if (page >= 1 && page <= pagination.last_page) {
        loadLearnings(page)
    }
}

onMounted(() => loadLearnings())
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Learnings" />

        <div class="p-6">
            <div class="flex justify-between mb-6">
                <h1 class="text-2xl font-bold">Learnings</h1>
                <Link
                    v-if="permissions.create"
                    :href="route('learnings.create')"
                    class="bg-blue-600 text-white px-4 py-2 rounded"
                >
                    Create
                </Link>
            </div>

            <div v-if="loading" class="text-center py-6">Loading...</div>

            <table v-else class="w-full border">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="p-2 text-left">Title</th>
                        <th class="p-2 text-left">Description</th>
                        <th class="p-2 text-left">Created By</th>
                        <th class="p-2 text-left">Status</th>
                        <th class="p-2 text-right">Score</th>
                        <th class="p-2"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="learning in learnings" :key="learning.id" class="border-t">
                        <td class="p-2 font-medium">{{ learning.title }}</td>
                        <td class="p-2 text-gray-500 max-w-xs truncate">
                            {{ learning.description ?? '—' }}
                        </td>
                        <td class="p-2 text-gray-500">{{ learning.creator?.name ?? '—' }}</td>
                        <td class="p-2">
                            <span
                                v-if="learning.is_complete"
                                class="px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700"
                            >
                                Complete
                            </span>
                            <span
                                v-else
                                class="px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700"
                            >
                                Incomplete
                            </span>
                        </td>
                        <td class="p-2 text-right">
                            {{ learning.score !== null ? `${learning.score}%` : '—' }}
                        </td>
                        <td class="p-2 space-x-2 whitespace-nowrap">
                            <Link
                                v-if="learning.permissions.view"
                                :href="route('learnings.show', { learning: learning.id })"
                                class="text-blue-600 underline text-sm"
                            >
                                View
                            </Link>
                            <Link
                                v-if="learning.permissions.complete && !learning.is_complete"
                                :href="route('learnings.complete', { learning: learning.id })"
                                class="text-green-600 underline text-sm"
                            >
                                Take
                            </Link>
                            <Link
                                v-if="learning.permissions.update"
                                :href="route('learnings.edit', { learning: learning.id })"
                                class="text-blue-600 underline text-sm"
                            >
                                Edit
                            </Link>
                            <button
                                v-if="learning.permissions.delete"
                                @click="handleDelete(learning.id)"
                                class="text-red-600 text-sm"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>

                    <tr v-if="learnings.length === 0">
                        <td colspan="6" class="p-4 text-center text-gray-500">
                            No learnings found.
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