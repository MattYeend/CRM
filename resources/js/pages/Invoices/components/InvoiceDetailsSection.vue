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
        <h2 class="text-lg font-semibold border-b pb-2">Invoice Details</h2>
        <div>
            <label class="block text-sm font-medium mb-1">
                Invoice Number <span class="text-red-500">*</span>
            </label>
            <input
                v-model="form.number"
                type="text"
                class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="INV-0001"
            />
            <p v-if="form.errors.number" class="text-red-500 text-sm mt-1">{{ form.errors.number }}</p>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Status</label>
                <select
                    v-model="form.status"
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option v-for="s in statusOptions" :key="s.value" :value="s.value">{{ s.label }}</option>
                </select>
                <p v-if="form.errors.status" class="text-red-500 text-sm mt-1">{{ form.errors.status }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Currency</label>
                <select
                    v-model="form.currency"
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option v-for="c in currencyOptions" :key="c" :value="c">{{ c }}</option>
                </select>
                <p v-if="form.errors.currency" class="text-red-500 text-sm mt-1">{{ form.errors.currency }}</p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Issue Date</label>
                <input
                    v-model="form.issue_date"
                    type="date"
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <p v-if="form.errors.issue_date" class="text-red-500 text-sm mt-1">{{ form.errors.issue_date }}</p>
            </div>
 
            <div>
                <label class="block text-sm font-medium mb-1">Due Date</label>
                <input
                    v-model="form.due_date"
                    type="date"
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <p v-if="form.errors.due_date" class="text-red-500 text-sm mt-1">{{ form.errors.due_date }}</p>
            </div>
        </div>
    </div>
</template>