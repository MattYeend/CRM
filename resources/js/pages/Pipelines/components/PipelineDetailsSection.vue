<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
    name: string
    description: string
    errors: {
        name?: string
        description?: string
    }
}>()

const emit = defineEmits<{
    'update:name': [value: string]
    'update:description': [value: string]
}>()

const nameModel = computed({
    get: () => props.name,
    set: (value: string) => emit('update:name', value)
})

const descriptionModel = computed({
    get: () => props.description,
    set: (value: string) => emit('update:description', value)
})
</script>

<template>
    <div class="space-y-4">
        <h2 class="text-lg font-semibold border-b pb-2">Pipeline Details</h2>

        <div>
            <label class="block text-sm font-medium mb-1">
                Name <span class="text-red-500">*</span>
            </label>
            <input
                v-model="nameModel"
                type="text"
                class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="e.g. Sales Pipeline"
            />
            <p v-if="errors.name" class="text-red-500 text-sm mt-1">
                {{ errors.name }}
            </p>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">
                Description
            </label>
            <textarea
                v-model="descriptionModel"
                rows="3"
                class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Describe the purpose of this pipeline..."
            />
            <p v-if="errors.description" class="text-red-500 text-sm mt-1">
                {{ errors.description }}
            </p>
        </div>
    </div>
</template>