<script setup lang="ts">
const props = defineProps<{ form: any }>()
const form = props.form
</script>

<template>
    <div>
        <label class="block text-sm font-medium mb-1">
            Name <span class="text-red-500">*</span>
        </label>

        <input
            v-model="form.name"
            type="text"
            class="w-full border rounded px-3 py-2"
            placeholder="e.g. Technology"
        />

        <p v-if="form.errors.name" class="text-red-500 text-sm mt-1">
            {{ form.errors.name }}
        </p>
    </div>
</template>