<script setup lang="ts">
import type { InertiaForm } from '@inertiajs/vue3'

interface ActivityForm {
    type: string
    selected_user_id: number | null
    description: string
    subject_type: string
    subject_id: number | null
    [key: string]: any
}

const props = defineProps<{
    form: InertiaForm<ActivityForm>
}>()
</script>

<template>
    <div>
        <label class="block text-sm font-medium mb-1">Description</label>
        <textarea
            v-model="props.form.description"
            class="border rounded w-full p-2"
        />
        <p v-if="props.form.errors.description" class="text-red-500 text-sm">
            {{ props.form.errors.description }}
        </p>
    </div>
</template>