<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, onMounted } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { fetchPipelineStage, deletePipelineStages } from '@/services/pipelineStageService'
import PipelineStageDetailSection from './components/PipelineStageDetailSection.vue'

interface UserPermissions {
    view: boolean
    update: boolean
    delete: boolean
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
    pipeline?: { id: number; name: string } | null
    creator?: { name: string } | null
    permissions: UserPermissions
}

const props = defineProps<{ pipelineStage: any }>()

const pipelineStage = ref<PipelineStage>({
    id: props.pipelineStage.id,
    pipeline_id: props.pipelineStage.pipeline_id,
    name: props.pipelineStage.name ?? '',
    position: props.pipelineStage.position ?? 0,
    is_won_stage: props.pipelineStage.is_won_stage ?? false,
    is_lost_stage: props.pipelineStage.is_lost_stage ?? false,
    is_open: props.pipelineStage.is_open ?? true,
    deal_count: props.pipelineStage.deal_count ?? 0,
    pipeline: props.pipelineStage.pipeline ?? null,
    creator: props.pipelineStage.creator ?? null,
    permissions: props.pipelineStage.permissions ?? { view: false, update: false, delete: false },
})

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Pipeline Stages', href: route('pipeline-stages.index') },
    { title: `Pipeline Stage #${props.pipelineStage.id}`, href: route('pipeline-stages.show', { pipelineStage: pipelineStage.value.id }) },
]

async function loadPipelineStage() {
    const data = await fetchPipelineStage(pipelineStage.value.id)
    Object.assign(pipelineStage.value, data)
}

async function handleDelete() {
    if (!confirm('Are you sure you want to delete this pipeline stage?')) return
    await deletePipelineStages(pipelineStage.value.id)
    window.location.href = route('pipeline-stages.index')
}

onMounted(() => loadPipelineStage())
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="View Pipeline Stage" />

        <div class="p-6">
            <div class="mx-auto border p-6 rounded shadow">

                <!-- Header -->
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <h1 class="text-2xl font-bold">{{ pipelineStage.name }}</h1>

                        <span
                            v-if="pipelineStage.is_won_stage"
                            class="mt-1 inline-block px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700"
                        >
                            Won Stage
                        </span>

                        <span
                            v-else-if="pipelineStage.is_lost_stage"
                            class="mt-1 inline-block px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700"
                        >
                            Lost Stage
                        </span>

                        <span
                            v-else
                            class="mt-1 inline-block px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600"
                        >
                            Active Stage
                        </span>
                    </div>

                    <div class="flex items-center gap-2">
                        <Link
                            v-if="pipelineStage.permissions?.update"
                            :href="route('pipeline-stages.edit', { pipelineStage: pipelineStage.id })"
                            class="bg-blue-600 text-white px-4 py-2 rounded"
                        >
                            Edit
                        </Link>

                        <Link
                            :href="route('pipeline-stages.index')"
                            class="bg-gray-200 text-gray-700 px-4 py-2 rounded"
                        >
                            Back
                        </Link>

                        <button
                            v-if="pipelineStage.permissions?.delete && pipelineStage.deal_count === 0"
                            @click="handleDelete"
                            class="bg-red-600 text-white px-4 py-2 rounded"
                        >
                            Delete
                        </button>
                    </div>
                </div>

                <PipelineStageDetailSection :pipelineStage="pipelineStage" />
            </div>
        </div>
    </AppLayout>
</template>