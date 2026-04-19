<script setup lang="ts">
interface User {
    id: number
    name: string
}

const props = defineProps<{
    form: any
    users?: User[]
}>()

const form = props.form
</script>

<template>
    <div class="space-y-4">
        <h2 class="text-lg font-semibold border-b pb-2">Learning Details</h2>

        <div>
            <label class="block text-sm font-medium mb-1">
                Title <span class="text-red-500">*</span>
            </label>
            <input
                v-model="form.title"
                type="text"
                class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="e.g. Health & Safety Induction"
            />
            <p v-if="form.errors.title" class="text-red-500 text-sm mt-1">{{ form.errors.title }}</p>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Description</label>
            <textarea
                v-model="form.description"
                rows="3"
                class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Brief description of this learning module..."
            />
            <p v-if="form.errors.description" class="text-red-500 text-sm mt-1">{{ form.errors.description }}</p>
        </div>

        <div class="space-y-2">
            <h2 class="text-lg font-semibold border-b pb-2">Assign Users</h2>

            <div
                v-for="user in props.users"
                :key="user.id"
                class="flex items-center gap-2"
            >
                <input
                    type="checkbox"
                    :value="user.id"
                    v-model="form.users"
                />
                <span>{{ user.name }}</span>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">
                Pass Score (%)
            </label>

            <input
                v-model.number="form.pass_score"
                type="number"
                min="0"
                max="100"
                class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="e.g. 70"
            />

            <p v-if="form.errors.pass_score" class="text-red-500 text-sm mt-1">
                {{ form.errors.pass_score }}
            </p>
        </div>
    </div>
</template>