<script setup lang="ts">
interface SelectOption {
    id: number
    title?: string
}

const form = defineModel<any>({ required: true })

defineProps<{
    deals: SelectOption[]
}>()
</script>

<template>
    <div class="space-y-4">
        <h2 class="text-lg font-semibold border-b pb-2">Associations</h2>

        <div>
            <label class="block text-sm font-medium mb-1">
                Deal <span class="text-red-500">*</span>
            </label>
            <select
                v-model="form.deal_id"
                class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                <option :value="null">— Select Deal —</option>
                <option v-for="deal in deals" :key="deal.id" :value="deal.id">
                    {{ deal.title }}
                </option>
            </select>
            <p v-if="form.errors?.deal_id" class="text-red-500 text-sm mt-1">
                {{ form.errors.deal_id }}
            </p>
        </div>
    </div>
</template>