<script setup lang="ts">
import { ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { route } from 'ziggy-js'
import { type BreadcrumbItem } from '@/types'
import { deletePipelines } from '@/services/pipelineService'

interface Stage {
    id: number
    name: string
    order: number
}

interface User {
    id: number
    name: string
}

interface Pipeline {
    id: number
    name: string
    description: string
    is_default: boolean
    stage_count: number
    deal_count: number
    stages: Stage[]
    creator: User | null
    permissions: {
        view: boolean
        update: boolean
        delete: boolean
    }
}

const props = defineProps<{
    pipeline: Pipeline
}>()

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Pipelines', href: route('pipelines.index') },
    { title: `Pipeline ${props.pipeline.name}`, href: route('pipelines.show', { pipeline: props.pipeline.id }) },
]

const deleting = ref(false)

async function handleDelete() {
    if (!confirm('Are you sure?')) return

    deleting.value = true
    try {
        await deletePipelines(props.pipeline.id)
        window.location.href = route('pipelines.index')
    } catch {
        deleting.value = false
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="pipeline.name" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <div class="flex items-start justify-between mb-6">
                    <div class="flex items-center space-x-4">
                        <h1 class="text-2xl font-bold">
                            {{ pipeline.name }}
                        </h1>
                        <p v-if="pipeline.creator" class="text-gray-600">
                            {{ pipeline.creator.name }}
                        </p>
                    </div>

                    <div class="flex items-center space-x-2">
                        <Link
                            v-if="pipeline.permissions.update"
                            :href="route('pipelines.edit', { pipeline: pipeline.id })"
                            class="bg-blue-600 text-white px-4 py-2 rounded"
                        >
                            Edit
                        </Link>

                        <Link
                            :href="route('pipelines.index')"
                            class="bg-gray-200 text-gray-700 px-4 py-2 rounded"
                        >
                            Back
                        </Link>

                        <button
                            v-if="pipeline.permissions.delete && pipeline.stage_count === 0"
                            :disabled="deleting"
                            class="bg-red-600 text-white px-4 py-2 rounded disabled:opacity-50"
                            @click="handleDelete"
                        >
                            {{ deleting ? 'Deleting…' : 'Delete' }}
                        </button>
                    </div>
                </div>
                <!-- Pipeline Details -->
                <div class="overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 space-y-6">
                        <div>
                            <h3 class="text-lg font-semibold border-b pb-2 mb-4">
                                Pipeline Information
                            </h3>
                            
                            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <dt class="text-sm font-medium">Name</dt>
                                    <dd class="mt-1 text-sm font-medium">
                                        {{ pipeline.name }}
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-sm font-medium">Status</dt>
                                    <dd class="mt-1">
                                        <span
                                            v-if="pipeline.is_default"
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800"
                                        >
                                            Default
                                        </span>
                                        <span
                                            v-else
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800"
                                        >
                                            Active
                                        </span>
                                    </dd>
                                </div>

                                <div class="md:col-span-2">
                                    <dt class="text-sm font-medium">Description</dt>
                                    <dd class="mt-1 text-sm">
                                        {{ pipeline.description || 'No description provided' }}
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-sm font-medium">Stage Count</dt>
                                    <dd class="mt-1 text-sm">
                                        {{ pipeline.stage_count }} stage{{ pipeline.stage_count !== 1 ? 's' : '' }}
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-sm font-medium">Deal Count</dt>
                                    <dd class="mt-1 text-sm">
                                        {{ pipeline.deal_count }} deal{{ pipeline.deal_count !== 1 ? 's' : '' }}
                                    </dd>
                                </div>

                                <div v-if="pipeline.creator">
                                    <dt class="text-sm font-medium">Created By</dt>
                                    <dd class="mt-1 text-sm">
                                        {{ pipeline.creator.name }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>

                <!-- Pipeline Stages -->
                <div class="overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold border-b pb-2 mb-4">
                            Pipeline Stages ({{ pipeline.stages.length }})
                        </h3>

                        <div v-if="pipeline.stages.length > 0" class="space-y-2">
                            <div
                                v-for="stage in pipeline.stages"
                                :key="stage.id"
                                class="flex items-center justify-between p-3 rounded"
                            >
                                <div class="flex items-center space-x-3">
                                    <span class="text-sm font-medium">
                                        #{{ stage.id }}
                                    </span>
                                    <span class="text-sm font-medium">
                                        {{ stage.name }}
                                    </span>
                                </div>
                                <Link
                                    :href="route('pipelines.stages.show', { pipeline: pipeline.id, stage: stage.id })"
                                    class="text-blue-600 hover:text-blue-900 text-sm"
                                >
                                    View Stage
                                </Link>
                            </div>
                        </div>

                        <div v-else class="text-sm">
                            This pipeline has no stages yet.
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>