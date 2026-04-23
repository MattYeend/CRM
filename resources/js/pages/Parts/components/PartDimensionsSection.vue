<script setup lang="ts">
interface PartForm {
    height: number | null
    width: number | null
    length: number | null
    weight: number | null
    volume: number | null
    errors: Record<string, string>
    [key: string]: any
}

const props = defineProps<{ form: PartForm }>()
const form = props.form
</script>

<template>
    <div class="space-y-4">
        <h2 class="text-lg font-semibold border-b pb-2">Dimensions</h2>

        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Height</label>
                <input
                    v-model="form.height"
                    type="number"
                    step="0.01"
                    class="w-full border rounded px-3 py-2"
                    placeholder="0.00"
                />
                <p v-if="form.errors.height" class="text-red-500 text-sm mt-1">
                    {{ form.errors.height }}
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Width</label>
                <input
                    v-model="form.width"
                    type="number"
                    step="0.01"
                    class="w-full border rounded px-3 py-2"
                    placeholder="0.00"
                />
                <p v-if="form.errors.width" class="text-red-500 text-sm mt-1">
                    {{ form.errors.width }}
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Length</label>
                <input
                    v-model="form.length"
                    type="number"
                    step="0.01"
                    class="w-full border rounded px-3 py-2"
                    placeholder="0.00"
                />
                <p v-if="form.errors.length" class="text-red-500 text-sm mt-1">
                    {{ form.errors.length }}
                </p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Weight</label>
                <input
                    v-model="form.weight"
                    type="number"
                    step="0.001"
                    class="w-full border rounded px-3 py-2"
                    placeholder="0.000"
                />
                <p v-if="form.errors.weight" class="text-red-500 text-sm mt-1">
                    {{ form.errors.weight }}
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Volume</label>
                <input
                    v-model="form.volume"
                    type="number"
                    step="0.001"
                    class="w-full border rounded px-3 py-2"
                    placeholder="0.000"
                />
                <p v-if="form.errors.volume" class="text-red-500 text-sm mt-1">
                    {{ form.errors.volume }}
                </p>
            </div>
        </div>
    </div>
</template>