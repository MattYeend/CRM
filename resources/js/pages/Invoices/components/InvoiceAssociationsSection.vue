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
    <select v-model="form.company_id">
        <option :value="null">— None —</option>
        <option v-for="c in companies" :key="c.id" :value="c.id">
            {{ c.name }}
        </option>
    </select>
</template>