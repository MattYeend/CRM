<script setup lang="ts">
import { computed } from 'vue';
import { InertiaForm } from '@inertiajs/vue3';

export interface NoteFormData {
    body: string;
    notable_type: string;
    notable_id: number | null;
}

const props = defineProps<{
    form: InertiaForm<NoteFormData>;
    notableTypes: string[];
    submitLabel?: string;
}>();

const emit = defineEmits<{
    (e: 'submit'): void;
}>();

const notableTypeOptions = computed(() =>
    props.notableTypes.map((type) => ({
        value: type,
        label: type.split('\\').pop() ?? type,
    }))
);
</script>

<template>
    <form class="space-y-6" @submit.prevent="emit('submit')">
        <!-- Body -->
        <div>
            <label for="body" class="block text-sm font-medium text-gray-700">
                Note Body <span class="text-red-500">*</span>
            </label>
            <div class="mt-1">
                <textarea
                    id="body"
                    v-model="form.body"
                    rows="6"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    :class="{ 'border-red-300': form.errors.body }"
                    placeholder="Enter note content…"
                />
            </div>
            <p v-if="form.errors.body" class="mt-1 text-xs text-red-600">{{ form.errors.body }}</p>
        </div>

        <!-- Notable Type -->
        <div>
            <label for="notable_type" class="block text-sm font-medium text-gray-700">
                Related To (Type)
            </label>
            <div class="mt-1">
                <select
                    id="notable_type"
                    v-model="form.notable_type"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    :class="{ 'border-red-300': form.errors.notable_type }"
                >
                    <option value="">— None —</option>
                    <option
                        v-for="option in notableTypeOptions"
                        :key="option.value"
                        :value="option.value"
                    >
                        {{ option.label }}
                    </option>
                </select>
            </div>
            <p v-if="form.errors.notable_type" class="mt-1 text-xs text-red-600">{{ form.errors.notable_type }}</p>
        </div>

        <!-- Notable ID -->
        <div v-if="form.notable_type">
            <label for="notable_id" class="block text-sm font-medium text-gray-700">
                Related Record ID
            </label>
            <div class="mt-1">
                <input
                    id="notable_id"
                    v-model.number="form.notable_id"
                    type="number"
                    min="1"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    :class="{ 'border-red-300': form.errors.notable_id }"
                    placeholder="Enter the record ID"
                />
            </div>
            <p v-if="form.errors.notable_id" class="mt-1 text-xs text-red-600">{{ form.errors.notable_id }}</p>
            <p class="mt-1 text-xs text-gray-400">Enter the ID of the related {{ form.notable_type.split('\\').pop() }}.</p>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <slot name="actions-left" />
            <button
                type="submit"
                :disabled="form.processing"
                class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:opacity-50 transition-colors"
            >
                <svg
                    v-if="form.processing"
                    class="h-4 w-4 animate-spin"
                    fill="none"
                    viewBox="0 0 24 24"
                >
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
                {{ submitLabel ?? 'Save' }}
            </button>
        </div>
    </form>
</template>