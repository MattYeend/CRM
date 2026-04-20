<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { route } from 'ziggy-js'
import PipelineStageForm from './components/PipelineStageForm.vue'

interface Pipeline {
    id: number
    name: string
}

const props = defineProps<{
    pipeline: Pipeline
}>()

const breadcrumbItems = [
    { title: 'Pipelines', href: route('pipelines.index') },
    { title: props.pipeline.name, href: route('pipelines.show', { pipeline: props.pipeline.id }) },
    { title: 'Stages', href: route('pipelines.stages.index', { pipeline: props.pipeline.id }) },
    { title: 'Create Stage', href: route('pipelines.stages.create', { pipeline: props.pipeline.id }) },
]
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="`Create Stage - ${pipeline.name}`" />

        <div class="p-6">
            <h1 class="text-2xl font-bold mb-6">
                Create Stage for {{ pipeline.name }}
            </h1>

            <PipelineStageForm
                :pipeline="pipeline"
                method="post"
                submit-route="/api/pipelineStages"
                submit-label="Create Stage"
            />
        </div>
    </AppLayout>
</template>