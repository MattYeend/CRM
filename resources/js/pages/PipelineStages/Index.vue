<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, reactive, onMounted } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { fetchPipelineStages, deletePipelineStages } from '@/services/pipelineStageService'

interface PipelineStage {
    id: number
    pipeline_id: number
    name: string
    position: number
    is_won_stage: boolean
    is_lost_stage: boolean
    is_open: boolean
    deal_count: number
    pipeline?: { id: number; name: string } | null
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

const pipelineStages = ref<PipelineStage[]>([])
const loading = ref(true)
const currentPage = ref(1)
const perPage = 10
const pagination = reactive({
    current_page: 1,
    last_page: 1,
    total: 0,
})

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Pipeline Stages', href: route('pipeline-stages.index') },
]

async function loadPipelineStages(page = 1) {
    loading.value = true
    try {
        const data = await fetchPipelineStages(perPage, page)
        pipelineStages.value = data.data
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
    if (!confirm('Are you sure you want to delete this pipeline stage?')) return
    await deletePipelineStages(id)
    loadPipelineStages(currentPage.value)
}

function goToPage(page: number) {
    if (page >= 1 && page <= pagination.last_page) {
        loadPipelineStages(page)
    }
}

onMounted(() => loadPipelineStages())
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Pipeline Stages" />

        <div class="p-6">
            <div class="flex justify-between mb-6">
                <h1 class="text-2xl font-bold">Pipeline Stages</h1>
                <Link
                    v-if="permissions.create"
                    :href="route('pipeline-stages.create')"
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
                        <th class="p-2 text-left">Position</th>
                        <th class="p-2 text-left">Name</th>
                        <th class="p-2 text-left">Pipeline</th>
                        <th class="p-2 text-left">Type</th>
                        <th class="p-2 text-left">Deal Count</th>
                        <th class="p-2"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="stage in pipelineStages" :key="stage.id" class="border-t">
                        <td class="p-2 font-medium">#{{ stage.position }}</td>
                        <td class="p-2">
                            <Link
                                v-if="stage.permissions.view"
                                :href="route('pipeline-stages.show', { pipelineStage: stage.id })"
                            >
                                {{ stage.name }}
                            </Link>
                            <span v-else>{{ stage.name }}</span>
                        </td>
                        <td class="p-2">
                            <Link
                                v-if="stage.pipeline"
                                :href="route('pipelines.show', { pipeline: stage.pipeline.id })"
                            >
                                {{ stage.pipeline.name }}
                            </Link>
                            <span v-else>—</span>
                        </td>
                        <td class="p-2">
                            <span
                                v-if="stage.is_won_stage"
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800"
                            >
                                Won
                            </span>
                            <span
                                v-else-if="stage.is_lost_stage"
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800"
                            >
                                Lost
                            </span>
                            <span
                                v-else
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800"
                            >
                                Active
                            </span>
                        </td>
                        <td class="p-2">{{ stage.deal_count }}</td>
                        <td class="p-2 space-x-2 whitespace-nowrap">
                            <Link
                                v-if="stage.permissions.view"
                                :href="route('pipeline-stages.show', { pipelineStage: stage.id })"
                            >
                                View
                            </Link>
                            <Link
                                v-if="stage.permissions.update"
                                :href="route('pipeline-stages.edit', { pipelineStage: stage.id })"
                            >
                                Edit
                            </Link>
                            <button
                                v-if="stage.permissions.delete && stage.deal_count === 0"
                                @click="handleDelete(stage.id)"
                                class="text-red-600"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>

                    <tr v-if="pipelineStages.length === 0">
                        <td colspan="6" class="p-4 text-center text-gray-500">
                            No pipeline stages found.
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