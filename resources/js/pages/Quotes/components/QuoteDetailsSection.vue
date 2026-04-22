<script setup lang="ts">
import { computed } from 'vue'

const form = defineModel<any>({ required: true })

const currencyOptions = ['USD', 'GBP', 'EUR', 'CAD', 'AUD']

const sentAtFormatted = computed({
    get() {
        if (!form.value.sent_at) return ''

        const datePart = form.value.sent_at.split('T')[0]
        const parts = datePart.split('-')
        if (parts.length !== 3) return ''

        const [year, month, day] = parts
        return `${day}/${month}/${year}`
    },
    set(value: string) {
        if (!value) {
            form.value.sent_at = ''
            return
        }

        const parts = value.split('/')
        if (parts.length !== 3) return

        const [dayRaw, monthRaw, year] = parts
        const day = dayRaw.padStart(2, '0')
        const month = monthRaw.padStart(2, '0')

        form.value.sent_at = `${year}-${month}-${day}`
    }
})

const acceptedAtFormatted = computed({
    get() {
        if (!form.value.accepted_at) return ''

        const datePart = form.value.accepted_at.split('T')[0]
        const parts = datePart.split('-')
        if (parts.length !== 3) return ''

        const [year, month, day] = parts
        return `${day}/${month}/${year}`
    },
    set(value: string) {
        if (!value) {
            form.value.accepted_at = ''
            return
        }

        const parts = value.split('/')
        if (parts.length !== 3) return

        const [dayRaw, monthRaw, year] = parts
        const day = dayRaw.padStart(2, '0')
        const month = monthRaw.padStart(2, '0')

        form.value.accepted_at = `${year}-${month}-${day}`
    }
})
</script>

<template>
    <div class="space-y-4">
        <h2 class="text-lg font-semibold border-b pb-2">Quote Details</h2>

        <div class="grid grid-cols-3 gap-4">
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

            <div>
                <label class="block text-sm font-medium mb-1">Subtotal</label>
                <input
                    v-model="form.subtotal"
                    type="number"
                    min="0"
                    step="0.01"
                    class="w-full border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <p v-if="form.errors?.subtotal" class="text-red-500 text-sm mt-1">
                    {{ form.errors.subtotal }}
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Tax</label>
                <input
                    v-model="form.tax"
                    type="number"
                    min="0"
                    step="0.01"
                    class="w-full border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <p v-if="form.errors?.tax" class="text-red-500 text-sm mt-1">
                    {{ form.errors.tax }}
                </p>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Total</label>
            <input
                v-model="form.total"
                type="number"
                min="0"
                step="0.01"
                class="w-full border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
            <p v-if="form.errors?.total" class="text-red-500 text-sm mt-1">
                {{ form.errors.total }}
            </p>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Sent At</label>
                <input
                    v-model="sentAtFormatted"
                    type="text"
                    placeholder="DD/MM/YYYY"
                    class="w-full border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <p v-if="form.errors?.sent_at" class="text-red-500 text-sm mt-1">
                    {{ form.errors.sent_at }}
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Accepted At</label>
                <input
                    v-model="acceptedAtFormatted"
                    type="text"
                    placeholder="DD/MM/YYYY"
                    class="w-full border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <p v-if="form.errors?.accepted_at" class="text-red-500 text-sm mt-1">
                    {{ form.errors.accepted_at }}
                </p>
            </div>
        </div>
    </div>
</template>