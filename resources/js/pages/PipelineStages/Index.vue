<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, reactive, onMounted, computed } from 'vue'
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
    is_won: boolean
    is_lost: boolean
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

const props = defineProps<{
    pipeline: Pipeline
}>()

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Pipelines', href: route('pipelines.index') },
    { title: `Pipeline ${props.pipeline.name}`, href: route('pipelines.show', { pipeline: props.pipeline.id }) },
    { title: 'Stages', href: route('pipelines.stages.index', { pipeline: props.pipeline.id }) },
]

const permissions = ref<GlobalPermissions>({
    create: false,
    viewAny: false,
})

const items = ref<PipelineStage[]>([])
const loading = ref(true)

const currentPage = ref(1)
const perPage = 10

const pagination = reactive({
    current_page: 1,
    last_page: 1,
    total: 0,
})

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

    for (let i = start; i <= end; i++) {
        pages.push(i)
    }

    if (end < total) {
        if (end < total - 1) pages.push('...')
        pages.push(total)
    }

    return pages
})

function goToPage(page: number) {
    if (page >= 1 && page <= pagination.last_page) {
        loadStages(page)
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

onMounted(() => loadStages())
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="`${pipeline.name} - Stages`" />

        <div class="p-6">
            <div class="flex justify-between mb-6">
                <h1 class="text-2xl font-bold">{{ pipeline.name }} - Stages</h1>

                <Link
                    v-if="permissions.create"
                    :href="route('pipelines.stages.create', { pipeline: pipeline.id })"
                    class="bg-blue-600 text-white px-4 py-2 rounded"
                >
                    Create Stage
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
                        <th class="p-2 text-left">Type</th>
                        <th class="p-2 text-left">Deals</th>
                        <th class="p-2 text-left">Status</th>
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
                                :href="route('pipelines.stages.show', { pipeline: pipeline.id, stage: stage.id })"
                                class="text-blue-600"
                            >
                                {{ stage.name }}
                            </Link>
                        </td>

                        <td class="p-2">
                            <span
                                v-if="stage.is_won_stage"
                                class="px-2 py-1 rounded text-xs bg-green-100 text-green-800"
                            >
                                Won
                            </span>
                            <span
                                v-else-if="stage.is_lost_stage"
                                class="px-2 py-1 rounded text-xs bg-red-100 text-red-800"
                            >
                                Lost
                            </span>
                            <span
                                v-else
                                class="px-2 py-1 rounded text-xs bg-gray-100 text-gray-800"
                            >
                                Active
                            </span>
                        </td>

                        <td class="p-2">
                            {{ stage.deal_count }} deal{{ stage.deal_count !== 1 ? 's' : '' }}
                        </td>

                        <td class="p-2">
                            <span
                                :class="stage.is_open
                                    ? 'bg-blue-100 text-blue-800'
                                    : 'bg-gray-100 text-gray-800'"
                                class="px-2 py-1 rounded text-xs"
                            >
                                {{ stage.is_open ? 'Open' : 'Closed' }}
                            </span>
                        </td>

                        <td class="p-2 space-x-2 text-right">
                            <Link
                                v-if="stage.permissions.view"
                                :href="route('pipelines.stages.show', { pipeline: pipeline.id, stage: stage.id })"
                            >
                                View
                            </Link>

                            <Link
                                v-if="stage.permissions.update"
                                :href="route('pipelines.stages.edit', { pipeline: pipeline.id, stage: stage.id })"
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
                </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="pagination.last_page > 1" class="flex justify-center mt-4 space-x-2">
                <button
                    class="px-3 py-1 border rounded"
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
                    class="px-3 py-1 border rounded"
                    :disabled="currentPage === pagination.last_page"
                    @click="goToPage(currentPage + 1)"
                >
                    Next
                </button>
            </div>
        </div>
    </AppLayout>
</template>