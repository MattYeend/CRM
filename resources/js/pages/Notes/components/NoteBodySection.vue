<script setup lang="ts">
interface NoteForm {
    body: string
    errors: Record<string, string>
}

const props = defineProps<{ form: NoteForm }>()
const form = props.form
</script>

<template>
    <div>
        <label class="block text-sm font-medium mb-1">
            Body <span class="text-red-500">*</span>
        </label>

        <textarea
            v-model="form.body"
            rows="6"
            class="border rounded w-full p-2"
        />

        <p v-if="form.errors.body" class="text-red-500 text-sm">
            {{ form.errors.body }}
        </p>
    </div>
</template>