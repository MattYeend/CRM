<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

interface PipelineStage {
    pipeline?: { id: number; name: string } | null
    position: number
    is_open: boolean
    deal_count: number
    creator?: { name: string } | null
}

defineProps<{ pipelineStage: PipelineStage }>()
</script>

<template>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 text-sm">

        <div v-if="pipelineStage.pipeline">
            <span class="font-semibold">Pipeline</span>
            <div>
                <Link
                    :href="route('pipelines.show', { pipeline: pipelineStage.pipeline.id })"
                    class="text-blue-600"
                >
                    {{ pipelineStage.pipeline.name }}
                </Link>
            </div>
        </div>

        <div>
            <span class="font-semibold">Position</span>
            <div>#{{ pipelineStage.position }}</div>
        </div>

        <div>
            <span class="font-semibold">Status</span>
            <div>
                <span
                    :class="pipelineStage.is_open
                        ? 'bg-blue-100 text-blue-800'
                        : 'bg-gray-100 text-gray-800'"
                    class="px-2 inline-flex text-xs font-semibold rounded-full"
                >
                    {{ pipelineStage.is_open ? 'Open' : 'Closed' }}
                </span>
            </div>
        </div>

        <div>
            <span class="font-semibold">Deal Count</span>
            <div>
                {{ pipelineStage.deal_count }} deal{{ pipelineStage.deal_count !== 1 ? 's' : '' }}
            </div>
        </div>

        <div v-if="pipelineStage.creator">
            <span class="font-semibold">Created By</span>
            <div>{{ pipelineStage.creator.name }}</div>
        </div>

    </div>
</template>