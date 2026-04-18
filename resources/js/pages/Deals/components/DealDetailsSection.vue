<script setup lang="ts">
import { computed } from 'vue'

const form = defineModel<any>({ required: true })

const statusOptions = [
    { value: 'open', label: 'Open' },
    { value: 'won', label: 'Won' },
    { value: 'lost', label: 'Lost' },
    { value: 'archived', label: 'Archived' },
]

const currencyOptions = ['USD', 'GBP', 'EUR', 'CAD', 'AUD']

const closeDateFormatted = computed({
    get() {
        if (!form.value.close_date) return ''

        // handle both "YYYY-MM-DD" and ISO datetime
        const datePart = form.value.close_date.split('T')[0]

        const parts = datePart.split('-')
        if (parts.length !== 3) return ''

        const [year, month, day] = parts
        return `${day}/${month}/${year}`
    },
    set(value: string) {
        if (!value) {
            form.value.close_date = ''
            return
        }

        const parts = value.split('/')
        if (parts.length !== 3) return

        const [dayRaw, monthRaw, year] = parts

        const day = dayRaw.padStart(2, '0')
        const month = monthRaw.padStart(2, '0')

        form.value.close_date = `${year}-${month}-${day}`
    }
})
</script>

<template>
    <div class="space-y-4">
        <h2 class="text-lg font-semibold border-b pb-2">Deal Details</h2>

        <div>
            <label class="block text-sm font-medium mb-1">
                Title <span class="text-red-500">*</span>
            </label>

            <input
                v-model="form.title"
                type="text"
                placeholder="Deal title"
                class="w-full border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
            />

            <p v-if="form.errors?.title" class="text-red-500 text-sm mt-1">
                {{ form.errors.title }}
            </p>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Value</label>

                <input
                    v-model="form.value"
                    type="number"
                    min="0"
                    step="0.01"
                    class="w-full border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                />

                <p v-if="form.errors?.value" class="text-red-500 text-sm mt-1">
                    {{ form.errors.value }}
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Currency</label>

                <select
                    v-model="form.currency"
                    class="w-full border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option v-for="c in currencyOptions" :key="c" :value="c">
                        {{ c }}
                    </option>
                </select>

                <p v-if="form.errors?.currency" class="text-red-500 text-sm mt-1">
                    {{ form.errors.currency }}
                </p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Status</label>

                <select
                    v-model="form.status"
                    class="w-full border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option
                        v-for="s in statusOptions"
                        :key="s.value"
                        :value="s.value"
                    >
                        {{ s.label }}
                    </option>
                </select>

                <p v-if="form.errors?.status" class="text-red-500 text-sm mt-1">
                    {{ form.errors.status }}
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">
                    Close Date
                </label>

                <input
                    v-model="closeDateFormatted"
                    type="text"
                    placeholder="DD/MM/YYYY"
                    class="w-full border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                />

                <p v-if="form.errors?.close_date" class="text-red-500 text-sm mt-1">
                    {{ form.errors.close_date }}
                </p>
            </div>
        </div>
    </div>
</template>