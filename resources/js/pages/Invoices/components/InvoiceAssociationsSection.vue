<script setup lang="ts">
interface Company {
    id: number
    name: string
}

const props = defineProps<{
    form: any
    companies: Company[]
}>()

const form = props.form
</script>

<template>
    <div class="space-y-4">
        <h2 class="text-lg font-semibold border-b pb-2">Associations</h2>
 
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
    </div>
</template>