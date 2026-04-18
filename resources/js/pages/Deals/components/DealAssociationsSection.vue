<script setup lang="ts">
interface SelectOption {
    id: number
    name: string
}

const form = defineModel<any>({ required: true })

defineProps<{
    companies: SelectOption[]
    owners: SelectOption[]
    pipelines: SelectOption[]
    stages: SelectOption[]
}>()
</script>

<template>
    <div class="space-y-4">
        <h2 class="text-lg font-semibold border-b pb-2">Associations</h2>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Company</label>
                <select
                    v-model="form.company_id"
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option :value="null">— None —</option>
                    <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
                <p v-if="form.errors.company_id" class="text-red-500 text-sm mt-1">{{ form.errors.company_id }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Owner</label>
                <select
                    v-model="form.owner_id"
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option :value="null">— None —</option>
                    <option v-for="o in owners" :key="o.id" :value="o.id">{{ o.name }}</option>
                </select>
                <p v-if="form.errors.owner_id" class="text-red-500 text-sm mt-1">{{ form.errors.owner_id }}</p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Pipeline</label>
                <select
                    v-model="form.pipeline_id"
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option :value="null">— None —</option>
                    <option v-for="p in pipelines" :key="p.id" :value="p.id">{{ p.name }}</option>
                </select>
                <p v-if="form.errors.pipeline_id" class="text-red-500 text-sm mt-1">{{ form.errors.pipeline_id }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Stage</label>
                <select
                    v-model="form.stage_id"
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option :value="null">— None —</option>
                    <option v-for="s in stages" :key="s.id" :value="s.id">{{ s.name }}</option>
                </select>
                <p v-if="form.errors.stage_id" class="text-red-500 text-sm mt-1">{{ form.errors.stage_id }}</p>
            </div>
        </div>
    </div>
</template>