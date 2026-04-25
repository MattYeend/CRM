<script setup lang="ts">
import { ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { route } from 'ziggy-js'
import { type BreadcrumbItem } from '@/types'
import { deletePipelines } from '@/services/pipelineService'
import PipelineDetailSection from './components/PipelineDetailSection.vue'

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

        <div class="p-6">
            <div class="mx-auto border p-6 rounded shadow">

                <!-- Header -->
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <h1 class="text-2xl font-bold">
                            {{ pipeline.name }}
                        </h1>

                        <p v-if="pipeline.creator" class="text-gray-600 text-sm mt-1">
                            {{ pipeline.creator.name }}
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <Link
                            v-if="pipeline.permissions.update"
                            :href="route('pipelines.edit', { pipeline: pipeline.id })"
                            class="bg-blue-600 text-white px-4 py-2 rounded"
                        >
                            Edit
                        </Link>

                        <Link
                            v-if="pipeline.permissions.update"
                            :href="route('pipeline-stages.create', { pipeline: pipeline.id })"
                            class="bg-green-600 text-white px-4 py-2 rounded"
                        >
                            Add Stage
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

                <PipelineDetailSection :pipeline="pipeline" />
            </div>
        </div>
    </AppLayout>
</template>