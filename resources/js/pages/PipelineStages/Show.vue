<script setup lang="ts">
import { ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { route } from 'ziggy-js'
import { type BreadcrumbItem } from '@/types'
import { deletePipelineStages } from '@/services/pipelineStageService'

interface Pipeline {
    id: number
    name: string
}

interface User {
    id: number
    name: string
}

interface PipelineStage {
    id: number
    pipeline_id: number
    pipeline: Pipeline
    name: string
    position: number
    is_won_stage: boolean
    is_lost_stage: boolean
    is_open: boolean
    is_won: boolean
    is_lost: boolean
    deal_count: number
    creator: User | null
    permissions: {
        view: boolean
        update: boolean
        delete: boolean
    }
}

const props = defineProps<{
    pipeline: Pipeline
    stage: PipelineStage
}>()

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Pipelines', href: route('pipelines.index') },
    { title: `Pipeline ${props.pipeline.name}`, href: route('pipelines.show', { pipeline: props.pipeline.id }) },
    { title: 'Stages', href: route('pipelines.stages.index', { pipeline: props.pipeline.id }) },
    { title: `Stage ${props.stage.name}`, href: route('pipelines.stages.show', { pipeline: props.pipeline.id, stage: props.stage.id }) },
]

const deleting = ref(false)

async function handleDelete() {
    if (!confirm('Are you sure?')) return

    deleting.value = true
    try {
        await deletePipelineStages(props.stage.id)
        window.location.href = route('pipelines.stages.index', { pipeline: props.pipeline.id })
    } catch {
        deleting.value = false
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="`${pipeline.name} - ${stage.name}`" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <div class="flex items-start justify-between mb-6">
                    <div class="flex items-center space-x-4">
                        <h1 class="text-2xl font-bold">
                            {{ stage.name }}
                        </h1>
                        <p v-if="stage.creator" class="text-gray-600">
                            {{ stage.creator.name }}
                        </p>
                    </div>

                    <div class="flex items-center space-x-2">
                        <Link
                            v-if="stage.permissions.update"
                            :href="route('pipelines.stages.edit', { pipeline: pipeline.id, stage: stage.id })"
                            class="bg-blue-600 text-white px-4 py-2 rounded"
                        >
                            Edit
                        </Link>

                        <Link
                            :href="route('pipelines.stages.index', { pipeline: pipeline.id })"
                            class="bg-gray-200 text-gray-700 px-4 py-2 rounded"
                        >
                            Back
                        </Link>

                        <button
                            v-if="stage.permissions.delete && stage.deal_count === 0"
                            :disabled="deleting"
                            class="bg-red-600 text-white px-4 py-2 rounded disabled:opacity-50"
                            @click="handleDelete"
                        >
                            {{ deleting ? 'Deleting…' : 'Delete' }}
                        </button>
                    </div>
                </div>

                <!-- Stage Details -->
                <div class="overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 space-y-6">
                        <div>
                            <h3 class="text-lg font-semibold border-b pb-2 mb-4">
                                Stage Information
                            </h3>
                            
                            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <dt class="text-sm font-medium">Pipeline</dt>
                                    <dd class="mt-1 text-sm font-medium">
                                        <Link
                                            :href="route('pipelines.show', { pipeline: pipeline.id })"
                                            class="text-blue-600 hover:text-blue-900"
                                        >
                                            {{ pipeline.name }}
                                        </Link>
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-sm font-medium">Name</dt>
                                    <dd class="mt-1 text-sm font-medium">
                                        {{ stage.name }}
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-sm font-medium">Position</dt>
                                    <dd class="mt-1 text-sm">
                                        #{{ stage.position }}
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-sm font-medium">Type</dt>
                                    <dd class="mt-1">
                                        <span
                                            v-if="stage.is_won_stage"
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800"
                                        >
                                            Won Stage
                                        </span>
                                        <span
                                            v-else-if="stage.is_lost_stage"
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800"
                                        >
                                            Lost Stage
                                        </span>
                                        <span
                                            v-else
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800"
                                        >
                                            Active Stage
                                        </span>
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-sm font-medium">Status</dt>
                                    <dd class="mt-1">
                                        <span
                                            :class="stage.is_open
                                                ? 'bg-blue-100 text-blue-800'
                                                : 'bg-gray-100 text-gray-800'"
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                        >
                                            {{ stage.is_open ? 'Open' : 'Closed' }}
                                        </span>
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-sm font-medium">Deal Count</dt>
                                    <dd class="mt-1 text-sm">
                                        {{ stage.deal_count }} deal{{ stage.deal_count !== 1 ? 's' : '' }}
                                    </dd>
                                </div>

                                <div v-if="stage.creator">
                                    <dt class="text-sm font-medium">Created By</dt>
                                    <dd class="mt-1 text-sm">
                                        {{ stage.creator.name }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>