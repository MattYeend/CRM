<script setup lang="ts">
const props = defineProps<{
    form: any
    statusOptions: { value: string; label: string }[]
    currencyOptions: string[]
    paymentMethodOptions: { value: string; label: string }[]
}>()

const form = props.form
</script>

<template>
    <div class="space-y-4">
        <h2 class="text-lg font-semibold border-b pb-2">Order Details</h2>

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
                    Amount <span class="text-red-500">*</span>
                </label>
                <input
                    v-model="form.amount"
                    type="number"
                    min="0"
                    step="0.01"
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <p v-if="form.errors.amount" class="text-red-500 text-sm mt-1">
                    {{ form.errors.amount }}
                </p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Currency</label>
                <select
                    v-model="form.currency"
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option v-for="c in currencyOptions" :key="c" :value="c">{{ c }}</option>
                </select>
                <p v-if="form.errors.currency" class="text-red-500 text-sm mt-1">
                    {{ form.errors.currency }}
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Payment Method</label>
                <select
                    v-model="form.payment_method"
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option :value="null">- None -</option>
                    <option v-for="m in paymentMethodOptions" :key="m.value" :value="m.value">
                        {{ m.label }}
                    </option>
                </select>
                <p v-if="form.errors.payment_method" class="text-red-500 text-sm mt-1">
                    {{ form.errors.payment_method }}
                </p>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Paid At</label>
            <input
                v-model="form.paid_at"
                type="datetime-local"
                class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
            <p v-if="form.errors.paid_at" class="text-red-500 text-sm mt-1">
                {{ form.errors.paid_at }}
            </p>
        </div>
    </div>
</template>