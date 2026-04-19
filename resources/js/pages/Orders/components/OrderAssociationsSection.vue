<script setup lang="ts">
interface SelectOption {
    id: number
    name?: string
    title?: string
}

const props = defineProps<{
    form: any
    users: SelectOption[]
    deals: SelectOption[]
}>()

const form = props.form
</script>

<template>
    <div class="space-y-4">
        <h2 class="text-lg font-semibold border-b pb-2">Associations</h2>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">
                    Assigned To <span class="text-red-500">*</span>
                </label>
                <select
                    v-model="form.assigned_to"
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option :value="null">- None -</option>
                    <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                </select>
                <p v-if="form.errors.assigned_to" class="text-red-500 text-sm mt-1">
                    {{ form.errors.assigned_to }}
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">
                    Deal <span class="text-red-500">*</span>
                </label>
                <select
                    v-model="form.deal_id"
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option :value="null">- None -</option>
                    <option v-for="d in deals" :key="d.id" :value="d.id">
                        {{ d.title || d.name }}
                    </option>
                </select>
                <p v-if="form.errors.deal_id" class="text-red-500 text-sm mt-1">
                    {{ form.errors.deal_id }}
                </p>
            </div>
        </div>
    </div>
</template>