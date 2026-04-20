<script setup lang="ts">
import { useForm, router } from '@inertiajs/vue3'
import { createPipelineStages, updatePipelineStages } from '@/services/pipelineStageService'
import PipelineStageDetailsSection from './PipelineStageDetailsSection.vue'

interface Pipeline {
    id: number
    name: string
}

interface PipelineStage {
    id?: number
    pipeline_id?: number
    name?: string
    position?: number
    is_won_stage?: boolean
    is_lost_stage?: boolean
    is_test?: boolean
    meta?: Record<string, any> | null
}

interface PipelineStageFormData {
    pipeline_id: number
    name: string
    position: number
    is_won_stage: boolean
    is_lost_stage: boolean
    is_test: boolean
}

const props = defineProps<{
    stage?: PipelineStage
    pipeline: Pipeline
    method?: 'post' | 'put'
    submitLabel?: string
    submitRoute: string
}>()

const form = useForm<PipelineStageFormData>({
    pipeline_id: props.stage?.pipeline_id ?? props.pipeline.id,
    name: props.stage?.name ?? '',
    position: props.stage?.position ?? 0,
    is_won_stage: props.stage?.is_won_stage ?? false,
    is_lost_stage: props.stage?.is_lost_stage ?? false,
    is_test: props.stage?.is_test ?? false,
})

async function submit() {
    form.clearErrors()
    
    try {
        const payload = { ...form.data() }

        const response = props.method === 'put' && props.stage?.id
            ? await updatePipelineStages(props.stage.id, payload)
            : await createPipelineStages(payload)

        router.visit(`/pipelines/${props.pipeline.id}/stages/${response.id}`)
    } catch (err: any) {
        console.error(err.response?.data ?? err);

        if (err.response?.status === 422 && err.response.data.errors) {
            const errors = err.response.data.errors as Record<string, string[]>
            Object.keys(errors).forEach(key => {
                form.setError(key as keyof PipelineStageFormData, errors[key][0])
            })
        }
    }
}
</script>

<template>
    <form @submit.prevent="submit" class="space-y-8 max-w-2xl">

        <PipelineStageDetailsSection
            :name="form.name"
            :position="form.position"
            :is-won-stage="form.is_won_stage"
            :is-lost-stage="form.is_lost_stage"
            :pipeline-name="pipeline.name"
            :errors="form.errors"
            @update:name="form.name = $event"
            @update:position="form.position = $event"
            @update:is-won-stage="form.is_won_stage = $event"
            @update:is-lost-stage="form.is_lost_stage = $event"
        />

        <button
            type="submit"
            class="bg-blue-600 text-white px-5 py-2 rounded disabled:opacity-50"
            :disabled="form.processing"
        >
            {{ form.processing ? 'Saving...' : (submitLabel ?? 'Save Stage') }}
        </button>

    </form>
</template>