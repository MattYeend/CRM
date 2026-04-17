<script setup lang="ts">
interface NoteForm {
    notable_type: string
    notable_id: number | null
    errors: Record<string, string>
    [key: string]: any
}

const props = defineProps<{
    form: NoteForm
    notableTypes: string[]
    notableOptions: { id: number; name: string }[]
}>()

const form = props.form
</script>

<template>
    <div class="space-y-4">

        <!-- TYPE -->
        <div>
            <label class="block text-sm font-medium mb-1">Related To</label>

            <select v-model="form.notable_type" class="border rounded w-full p-2">
                <option value="">Select type</option>

                <option
                    v-for="type in props.notableTypes"
                    :key="type"
                    :value="type"
                >
                    {{ type }}
                </option>
            </select>

            <p v-if="form.errors.notable_type" class="text-red-500 text-sm">
                {{ form.errors.notable_type }}
            </p>
        </div>

        <!-- RECORD -->
        <div v-if="props.notableOptions.length">
            <label class="block text-sm font-medium mb-1">Record</label>

            <select v-model="form.notable_id" class="border rounded w-full p-2">
                <option :value="null">Select record</option>

                <option
                    v-for="item in props.notableOptions"
                    :key="item.id"
                    :value="item.id"
                >
                    {{ item.name }}
                </option>
            </select>

            <p v-if="form.errors.notable_id" class="text-red-500 text-sm">
                {{ form.errors.notable_id }}
            </p>
        </div>

    </div>
</template>