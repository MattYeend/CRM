<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

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
}

defineProps<{ pipeline: Pipeline }>()
</script>

<template>
    <div class="space-y-6">

        <!-- Pipeline Details -->
        <div class="overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6 space-y-6">

                <div>
                    <h3 class="text-sm font-semibold uppercase tracking-wider mb-4">
                        Pipeline Information
                    </h3>

                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">

                        <div>
                            <dt class="font-medium">Name</dt>
                            <dd class="mt-1">{{ pipeline.name }}</dd>
                        </div>

                        <div>
                            <dt class="font-medium">Status</dt>
                            <dd class="mt-1">
                                <span
                                    v-if="pipeline.is_default"
                                    class="px-2 inline-flex text-xs font-semibold rounded-full bg-blue-100 text-blue-800"
                                >
                                    Default
                                </span>
                                <span
                                    v-else
                                    class="px-2 inline-flex text-xs font-semibold rounded-full bg-gray-100 text-gray-800"
                                >
                                    Active
                                </span>
                            </dd>
                        </div>

                        <div class="md:col-span-2">
                            <dt class="font-medium">Description</dt>
                            <dd class="mt-1">
                                {{ pipeline.description || 'No description provided' }}
                            </dd>
                        </div>

                        <div>
                            <dt class="font-medium">Stage Count</dt>
                            <dd class="mt-1">
                                {{ pipeline.stage_count }} stage{{ pipeline.stage_count !== 1 ? 's' : '' }}
                            </dd>
                        </div>

                        <div>
                            <dt class="font-medium">Deal Count</dt>
                            <dd class="mt-1">
                                {{ pipeline.deal_count }} deal{{ pipeline.deal_count !== 1 ? 's' : '' }}
                            </dd>
                        </div>

                        <div v-if="pipeline.creator">
                            <dt class="font-medium">Created By</dt>
                            <dd class="mt-1">
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
                <h3 class="text-sm font-semibold uppercase tracking-wider mb-4">
                    Pipeline Stages ({{ pipeline.stages.length }})
                </h3>

                <div v-if="pipeline.stages.length > 0" class="space-y-2">
                    <div
                        v-for="stage in pipeline.stages"
                        :key="stage.id"
                        class="flex items-center justify-between p-3 rounded"
                    >
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-medium">
                                #{{ stage.id }}
                            </span>
                            <span class="text-sm">
                                {{ stage.name }}
                            </span>
                        </div>

                        <Link
                            :href="route('pipeline-stages.show', { pipeline: pipeline.id, pipelineStage: stage.id })"
                            class="text-sm"
                        >
                            View Stage
                        </Link>
                    </div>
                </div>

                <div v-else class="text-sm text-gray-400">
                    This pipeline has no stages yet.
                </div>
            </div>
        </div>

    </div>
</template>