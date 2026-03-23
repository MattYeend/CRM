<script setup lang="ts">
interface ActivityForm {
    description: string
    errors: Record<string, string>
    [key: string]: any
}

const props = defineProps<{
    form: ActivityForm
}>()

const form = props.form
</script>

<template>
    <div>
        <label class="block text-sm font-medium mb-1">Description</label>
        <textarea
            v-model="form.description"
            class="border rounded w-full p-2"
        />
        <p v-if="form.errors.description" class="text-red-500 text-sm">
            {{ form.errors.description }}
        </p>
    </div>
</template>