<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import PipelineStageForm from './components/PipelineStageForm.vue'
import { route } from 'ziggy-js'

interface Pipeline {
    id: number
    name: string
}

interface Deal {
    id: number
    title: string
}

interface Note {
    id: number
}

interface Task {
    id: number
}

interface Attachment {
    id: number
}

interface PipelineStage {
    id: number
    pipeline_id: number
    name: string
    position: number
    is_won_stage: boolean
    is_lost_stage: boolean
    is_test: boolean
    meta: Record<string, any> | null
    pipeline: Pipeline
    deals: Deal[]
    notes: Note[]
    tasks: Task[]
    attachments: Attachment[]
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
    { title: `Edit Stage ${props.stage.name}`, href: route('pipelines.stages.edit', { pipeline: props.pipeline.id, stage: props.stage.id }) },
]
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="`Edit ${stage.name} - ${pipeline.name}`" />

        <div class="p-6">
            <h1 class="text-2xl font-bold mb-6">Edit Stage {{ stage.name }}</h1>
            <PipelineStageForm
                :pipeline="pipeline"
                :stage="stage"
                method="put"
                :submit-route="`/api/pipelineStages/${stage.id}`"
                submit-label="Update Stage"
            />
        </div>
    </AppLayout>
</template>