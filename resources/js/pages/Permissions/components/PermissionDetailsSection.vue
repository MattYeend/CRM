<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
    name: string
    label: string
    errors: {
        name?: string
        label?: string
    }
}>()

const emit = defineEmits<{
    'update:name': [value: string]
    'update:label': [value: string]
}>()

const nameModel = computed({
    get: () => props.name,
    set: (value: string) => emit('update:name', value)
})

const labelModel = computed({
    get: () => props.label,
    set: (value: string) => emit('update:label', value)
})
</script>

<template>
    <div class="space-y-4">
        <h2 class="text-lg font-semibold border-b pb-2">Permission Details</h2>

        <div>
            <label class="block text-sm font-medium mb-1">
                Name <span class="text-red-500">*</span>
            </label>
            <input
                v-model="nameModel"
                type="text"
                class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="e.g. companies.view.any"
            />
            <p v-if="errors.name" class="text-red-500 text-sm mt-1">
                {{ errors.name }}
            </p>
            <p class="text-gray-500 text-sm mt-1">
                Use format: resource.action.scope (e.g., users.update.any, deals.view.own)
            </p>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">
                Label <span class="text-red-500">*</span>
            </label>
            <input
                v-model="labelModel"
                type="text"
                class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="e.g. View Any Companies"
            />
            <p v-if="errors.label" class="text-red-500 text-sm mt-1">
                {{ errors.label }}
            </p>
            <p class="text-gray-500 text-sm mt-1">
                Human-readable description of this permission
            </p>
        </div>
    </div>
</template>