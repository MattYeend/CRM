<script setup lang="ts">
const props = defineProps<{
    form: any
    statusOptions: { value: string; label: string }[]
    priorityOptions: { value: string; label: string }[]
}>()

const form = props.form
</script>

<template>
    <div class="space-y-4">
        <h2 class="text-lg font-semibold border-b pb-2">Task Details</h2>

        <div>
            <label class="block text-sm font-medium mb-1">
                Title <span class="text-red-500">*</span>
            </label>
            <input
                v-model="form.title"
                type="text"
                class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
            <p v-if="form.errors.title" class="text-red-500 text-sm mt-1">
                {{ form.errors.title }}
            </p>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Description</label>
            <textarea
                v-model="form.description"
                rows="4"
                class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            ></textarea>
            <p v-if="form.errors.description" class="text-red-500 text-sm mt-1">
                {{ form.errors.description }}
            </p>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">
                    Status <span class="text-red-500">*</span>
                </label>
                <select
                    v-model="form.status"
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option v-for="s in statusOptions" :key="s.value" :value="s.value">
                        {{ s.label }}
                    </option>
                </select>
                <p v-if="form.errors.status" class="text-red-500 text-sm mt-1">
                    {{ form.errors.status }}
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">
                    Priority <span class="text-red-500">*</span>
                </label>
                <select
                    v-model="form.priority"
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option v-for="p in priorityOptions" :key="p.value" :value="p.value">
                        {{ p.label }}
                    </option>
                </select>
                <p v-if="form.errors.priority" class="text-red-500 text-sm mt-1">
                    {{ form.errors.priority }}
                </p>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Due Date</label>
            <input
                v-model="form.due_at"
                type="datetime-local"
                class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
            <p v-if="form.errors.due_at" class="text-red-500 text-sm mt-1">
                {{ form.errors.due_at }}
            </p>
        </div>
    </div>
</template>