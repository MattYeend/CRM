<script setup lang="ts">
const form = defineModel<any>({ required: true })

const statusOptions = [
    { value: 'active', label: 'Active' },
    { value: 'pending', label: 'Pending' },
    { value: 'out_of_stock', label: 'Out of Stock' },
    { value: 'discontinued', label: 'Discontinued' },
]

const currencyOptions = ['USD', 'GBP', 'EUR', 'CAD', 'AUD']
</script>

<template>
    <div class="space-y-4">
        <h2 class="text-lg font-semibold border-b pb-2">Product Details</h2>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">
                    SKU <span class="text-red-500">*</span>
                </label>

                <input
                    v-model="form.sku"
                    type="text"
                    placeholder="Product SKU"
                    class="w-full border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                />

                <p v-if="form.errors?.sku" class="text-red-500 text-sm mt-1">
                    {{ form.errors.sku }}
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">
                    Name <span class="text-red-500">*</span>
                </label>

                <input
                    v-model="form.name"
                    type="text"
                    placeholder="Product name"
                    class="w-full border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                />

                <p v-if="form.errors?.name" class="text-red-500 text-sm mt-1">
                    {{ form.errors.name }}
                </p>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Description</label>

            <textarea
                v-model="form.description"
                rows="3"
                placeholder="Product description"
                class="w-full border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
            />

            <p v-if="form.errors?.description" class="text-red-500 text-sm mt-1">
                {{ form.errors.description }}
            </p>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">
                    Price <span class="text-red-500">*</span>
                </label>

                <input
                    v-model="form.price"
                    type="number"
                    min="0"
                    step="0.01"
                    class="w-full border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                />

                <p v-if="form.errors?.price" class="text-red-500 text-sm mt-1">
                    {{ form.errors.price }}
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
    </div>
</template>