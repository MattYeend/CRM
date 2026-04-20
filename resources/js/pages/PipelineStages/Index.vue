<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, reactive, onMounted } from 'vue'
import { route } from 'ziggy-js'
import { type BreadcrumbItem } from '@/types'
import { fetchPipelineStages, deletePipelineStages } from '@/services/pipelineStageService'

interface Pipeline {
    id: number
    name: string
}

interface PipelineStage {
    id: number
    pipeline_id: number
    name: string
    position: number
    is_won_stage: boolean
    is_lost_stage: boolean
    is_open: boolean
    deal_count: number
    permissions: {
        view: boolean
        update: boolean
        delete: boolean
    }
}

interface GlobalPermissions {
    create: boolean
    viewAny: boolean
}

defineProps<{
    pipeline: Pipeline
}>()

const items = ref<PipelineStage[]>([])
const loading = ref(true)

const permissions = ref<GlobalPermissions>({
    create: false,
    viewAny: false,
})

const currentPage = ref(1)
const perPage = 10

const pagination = reactive({
    current_page: 1,
    last_page: 1,
    total: 0,
})

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Invoice Items', href: route('invoice-items.index') },
]

const deletingId = ref<number | null>(null)

async function loadStages(page = 1) {
    loading.value = true

    try {
        const data = await fetchPipelineStages(perPage, page)

        items.value = data.data
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

    deletingId.value = id

    try {
        await deletePipelineStages(id)
        loadStages(currentPage.value)
    } finally {
        deletingId.value = null
    }
}

function goToPage(page: number) {
    if (page >= 1 && page <= pagination.last_page) {
        loadStages(page)
    }
}

onMounted(() => loadStages())
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head  title="Pipeline Stages"/>
        <div class="p-6">
            <div class="flex justify-between mb-6">
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
                        <th class="p-2 text-left">Pipeline</th>
                        <th class="p-2 text-left">Deal Count</th>
                        <th class="p-2"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="stage in items" :key="stage.id" class="border-t">
                        <td class="p-2 font-medium">
                            #{{ stage.position }}
                        </td>

                        <td class="p-2">
                            <Link
                                :href="route('pipelines.stages.show', {
                                    pipeline: pipeline.id,
                                    stage: stage.id
                                })"
                                class="text-blue-600"
                            >
                                {{ stage.name }}
                            </Link>
                        </td>

                        <td class="p-2">
                            {{ stage.deal_count }} deals
                        </td>

                        <td class="p-2 text-right space-x-2">
                            <Link
                                v-if="stage.permissions.update"
                                :href="route('pipelines.stages.edit', {
                                    pipeline: pipeline.id,
                                    stage: stage.id
                                })"
                            >
                                Edit
                            </Link>

                            <button
                                v-if="stage.permissions.delete && stage.deal_count === 0"
                                class="text-red-600"
                                :disabled="deletingId === stage.id"
                                @click="handleDelete(stage.id)"
                            >
                                {{ deletingId === stage.id ? 'Deleting…' : 'Delete' }}
                            </button>
                        </td>
                    </tr>
                    <tr v-if="items.length === 0">
                        <td colspan="4" class="p-4 text-center text-gray-500">
                            No invoice items found.
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