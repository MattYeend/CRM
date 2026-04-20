<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { route } from 'ziggy-js'
import PipelineStageForm from './components/PipelineStageForm.vue'

interface Pipeline {
    id: number
    name: string
}

interface PipelineStage {
    id: number
    name: string
}

const props = defineProps<{
    pipeline: Pipeline
    stage: PipelineStage
}>()

const breadcrumbItems = [
    { title: 'Pipelines', href: route('pipelines.index') },
    { title: props.pipeline.name, href: route('pipelines.show', { pipeline: props.pipeline.id }) },
    { title: 'Stages', href: route('pipelines.stages.index', { pipeline: props.pipeline.id }) },
    {
        title: props.stage.name,
        href: route('pipelines.stages.show', {
            pipeline: props.pipeline.id,
            stage: props.stage.id
        })
    },
    {
        title: 'Edit Stage',
        href: route('pipelines.stages.edit', {
            pipeline: props.pipeline.id,
            stage: props.stage.id
        })
    },
]
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="`Edit ${stage.name} - ${pipeline.name}`" />

        <div class="p-6">
            <h1 class="text-2xl font-bold mb-6">
                Edit Stage {{ stage.name }}
            </h1>

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