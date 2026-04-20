<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
    name: string
    position: number
    isWonStage: boolean
    isLostStage: boolean
    isOpen: boolean
    pipelineName: string
    errors: {
        name?: string
        position?: string
        is_won_stage?: string
        is_lost_stage?: string
        is_open?: string
    }
}>()

const emit = defineEmits<{
    'update:name': [string]
    'update:position': [number]
    'update:isWonStage': [boolean]
    'update:isLostStage': [boolean]
    'update:isOpen': [boolean]
}>()

/**
 * Basic fields
 */
const nameModel = computed({
    get: () => props.name,
    set: v => emit('update:name', v)
})

const positionModel = computed({
    get: () => props.position,
    set: v => emit('update:position', v)
})

/**
 * ✅ Single source of truth for stage outcome
 */
const stageOutcome = computed({
    get: () => {
        if (props.isWonStage) return 'won'
        if (props.isLostStage) return 'lost'
        return 'open'
    },
    set: (value: 'won' | 'lost' | 'open') => {
        emit('update:isWonStage', value === 'won')
        emit('update:isLostStage', value === 'lost')
        emit('update:isOpen', value === 'open')
    }
})
</script>

<template>
    <div class="space-y-4">

        <h2 class="text-lg font-semibold border-b pb-2">
            Stage Details
        </h2>

        <!-- Pipeline name -->
        <div class="text-sm">
            <span class="font-medium">Pipeline:</span>
            {{ pipelineName || 'N/A' }}
        </div>

        <!-- Name -->
        <div>
            <label class="block text-sm font-medium mb-1">Name</label>
            <input
                v-model="nameModel"
                class="w-full border rounded px-3 py-2"
            />
            <p v-if="errors.name" class="text-red-500 text-sm">
                {{ errors.name }}
            </p>
        </div>

        <!-- Position -->
        <div>
            <label class="block text-sm font-medium mb-1">Position</label>
            <input
                v-model.number="positionModel"
                type="number"
                class="w-full border rounded px-3 py-2"
            />
            <p v-if="errors.position" class="text-red-500 text-sm">
                {{ errors.position }}
            </p>
        </div>

        <!-- ✅ Stage Outcome (fixed) -->
        <div class="space-y-2">
            <label class="block text-sm font-medium">Stage Outcome</label>

            <div class="flex items-center gap-2">
                <input
                    type="radio"
                    name="stage-type"
                    value="open"
                    v-model="stageOutcome"
                />
                <span>Open</span>
            </div>

            <div class="flex items-center gap-2">
                <input
                    type="radio"
                    name="stage-type"
                    value="won"
                    v-model="stageOutcome"
                />
                <span>Won</span>
            </div>

            <div class="flex items-center gap-2">
                <input
                    type="radio"
                    name="stage-type"
                    value="lost"
                    v-model="stageOutcome"
                />
                <span>Lost</span>
            </div>
        </div>

        <!-- Optional error display -->
        <p v-if="errors.is_won_stage || errors.is_lost_stage" class="text-red-500 text-sm">
            {{ errors.is_won_stage || errors.is_lost_stage }}
        </p>

    </div>
</template>