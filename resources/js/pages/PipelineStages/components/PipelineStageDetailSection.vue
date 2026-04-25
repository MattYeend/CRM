<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

interface PipelineStage {
    pipeline?: { id: number; name: string } | null
    position: number
    is_open: boolean
    deal_count: number
    creator: { name: string } | null
    updater: { name: string } | null
    deleter: { name: string } | null
    created_at: string | null
    updated_at: string | null
}

defineProps<{ pipelineStage: PipelineStage }>()

function formatDate(dateStr: string | null): string {
    if (!dateStr) return '—'
    return new Date(dateStr).toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    })
}
</script>

<template>
    <div class="space-y-6">
        <!-- Stage Details -->
        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 text-sm">
            <div v-if="pipelineStage.pipeline">
                <dt class="font-semibold">Pipeline</dt>
                <dd>
                    <Link
                        :href="route('pipelines.show', { pipeline: pipelineStage.pipeline.id })"
                        class="text-blue-600 hover:underline"
                    >
                        {{ pipelineStage.pipeline.name }}
                    </Link>
                </dd>
            </div>

            <div>
                <dt class="font-semibold">Position</dt>
                <dd>#{{ pipelineStage.position }}</dd>
            </div>

            <div>
                <dt class="font-semibold">Status</dt>
                <dd>
                    <span
                        :class="pipelineStage.is_open
                            ? 'bg-blue-100 text-blue-800'
                            : 'bg-gray-100 text-gray-800'"
                        class="px-2 inline-flex text-xs font-semibold rounded-full"
                    >
                        {{ pipelineStage.is_open ? 'Open' : 'Closed' }}
                    </span>
                </dd>
            </div>

            <div>
                <dt class="font-semibold">Deal Count</dt>
                <dd>
                    {{ pipelineStage.deal_count }} deal{{ pipelineStage.deal_count !== 1 ? 's' : '' }}
                </dd>
            </div>
        </dl>

        <!-- Audit Information -->
        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 text-sm text-gray-600 pt-2 border-t border-gray-200">
            <div v-if="pipelineStage.creator">
                <dt class="font-semibold inline">Created By: </dt>
                <dd class="inline">{{ pipelineStage.creator.name }}</dd>
            </div>
            <div v-if="pipelineStage.created_at">
                <dt class="font-semibold inline">Created: </dt>
                <dd class="inline">
                    <time :datetime="pipelineStage.created_at">
                        {{ formatDate(pipelineStage.created_at) }}
                    </time>
                </dd>
            </div>
            <div v-if="pipelineStage.updater">
                <dt class="font-semibold inline">Last Updated By: </dt>
                <dd class="inline">{{ pipelineStage.updater.name }}</dd>
            </div>
            <div v-if="pipelineStage.updated_at">
                <dt class="font-semibold inline">Last Updated: </dt>
                <dd class="inline">
                    <time :datetime="pipelineStage.updated_at">
                        {{ formatDate(pipelineStage.updated_at) }}
                    </time>
                </dd>
            </div>
            <div v-if="pipelineStage.deleter" class="md:col-span-2">
                <dt class="font-semibold inline">Deleted By: </dt>
                <dd class="inline text-red-600">{{ pipelineStage.deleter.name }}</dd>
            </div>
        </dl>
    </div>
</template>