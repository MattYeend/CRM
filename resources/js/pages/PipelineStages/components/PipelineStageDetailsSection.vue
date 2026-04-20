<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
    name: string
    position: number
    isWonStage: boolean
    isLostStage: boolean
    pipelineName: string
    errors: {
        name?: string
        position?: string
        is_won_stage?: string
        is_lost_stage?: string
    }
}>()

const emit = defineEmits<{
    'update:name': [value: string]
    'update:position': [value: number]
    'update:isWonStage': [value: boolean]
    'update:isLostStage': [value: boolean]
}>()

const nameModel = computed({
    get: () => props.name,
    set: (value: string) => emit('update:name', value)
})

const positionModel = computed({
    get: () => props.position,
    set: (value: number) => emit('update:position', value)
})

const isWonStageModel = computed({
    get: () => props.isWonStage,
    set: (value: boolean) => emit('update:isWonStage', value)
})

const isLostStageModel = computed({
    get: () => props.isLostStage,
    set: (value: boolean) => emit('update:isLostStage', value)
})
</script>

<template>
    <div class="space-y-4">
        <h2 class="text-lg font-semibold border-b pb-2">Stage Details</h2>

        <div>
            <span class="font-medium">Pipeline:</span> {{ pipelineName }}
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">
                Name <span class="text-red-500">*</span>
            </label>
            <input
                v-model="nameModel"
                type="text"
                class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="e.g. Qualification"
            />
            <p v-if="errors.name" class="text-red-500 text-sm mt-1">
                {{ errors.name }}
            </p>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">
                Position <span class="text-red-500">*</span>
            </label>
            <input
                v-model.number="positionModel"
                type="number"
                min="0"
                step="1"
                class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="0"
            />
            <p v-if="errors.position" class="text-red-500 text-sm mt-1">
                {{ errors.position }}
            </p>
            <p class="text-gray-500 text-sm mt-1">
                Order of this stage in the pipeline
            </p>
        </div>

        <div class="space-y-2">
            <label class="block text-sm font-medium">Stage Type</label>
            
            <div class="flex items-center">
                <input
                    id="is-won-stage"
                    v-model="isWonStageModel"
                    type="checkbox"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                />
                <label for="is-won-stage" class="ml-2 text-sm cursor-pointer">
                    Won Stage (deals moved here are marked as won)
                </label>
            </div>

            <div class="flex items-center">
                <input
                    id="is-lost-stage"
                    v-model="isLostStageModel"
                    type="checkbox"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                />
                <label for="is-lost-stage" class="ml-2 text-sm cursor-pointer">
                    Lost Stage (deals moved here are marked as lost)
                </label>
            </div>

            <p v-if="errors.is_won_stage" class="text-red-500 text-sm">
                {{ errors.is_won_stage }}
            </p>
            <p v-if="errors.is_lost_stage" class="text-red-500 text-sm">
                {{ errors.is_lost_stage }}
            </p>
        </div>
    </div>
</template>