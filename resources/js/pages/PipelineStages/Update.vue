<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import PipelineStageForm from './components/PipelineStageForm.vue'
import { route } from 'ziggy-js'

interface SelectOption {
    id: number
    name: string
}

interface DealSelectOption {
    id: number
    title: string
}

const props = defineProps<{
    stage: any
    pipelines: SelectOption[]
    deals: DealSelectOption[]
}>()

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Pipeline Stages', href: route('pipeline-stages.index') },
    { title: `Pipeline Stage #${props.stage.id}`, href: route('pipeline-stages.show', { pipelineStage: props.stage.id }) },
    { title: `Edit Pipeline Stage #${props.stage.id}`, href: route('pipeline-stages.edit', { pipelineStage: props.stage.id }) },
]
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Edit Pipeline Stage" />

        <div class="p-6">
            <h1 class="text-2xl font-bold mb-6">
                Edit Pipeline Stage
            </h1>

            <PipelineStageForm
                :stage="stage"
                :pipelines="pipelines"
                :deals="deals"
                :submit-route="`/api/pipeline-stages/${stage.id}`"
                method="put"
                submitLabel="Update Pipeline Stage"
            />
        </div>
    </AppLayout>
</template>