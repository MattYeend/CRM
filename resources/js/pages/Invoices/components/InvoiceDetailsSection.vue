<script setup lang="ts">
const props = defineProps<{
    form: any
    errors?: Record<string, string>
}>()

const form = props.form

const statusOptions = [
    { value: 'draft', label: 'Draft' },
    { value: 'sent', label: 'Sent' },
    { value: 'paid', label: 'Paid' },
    { value: 'overdue', label: 'Overdue' },
    { value: 'cancelled', label: 'Cancelled' },
]

const currencyOptions = ['GBP', 'USD', 'EUR', 'CAD', 'AUD']
</script>

<template>
    <div class="space-y-4">
        <input v-model="form.number" />

        <select v-model="form.status">
            <option v-for="s in statusOptions" :key="s.value" :value="s.value">
                {{ s.label }}
            </option>
        </select>

        <select v-model="form.currency">
            <option v-for="c in currencyOptions" :key="c" :value="c">
                {{ c }}
            </option>
        </select>

        <input type="date" v-model="form.issue_date" />
        <input type="date" v-model="form.due_date" />
    </div>
</template>