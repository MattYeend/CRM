<script setup lang="ts">
import { useForm, router } from '@inertiajs/vue3'
import { createBillOfMaterial, updateBillOfMaterial } from '@/services/partService'

interface Part {
    id: number
    name: string
    sku: string
}

interface BOM {
    id?: number
    child_part_id?: number | null
    quantity?: number
    unit_of_measure?: string
    scrap_percentage?: number | null
    notes?: string
}

const props = defineProps<{
    parentPart: Part
    bom?: BOM
    parts: Part[]
    method?: 'post' | 'put'
    submitLabel?: string
}>()

const form = useForm({
    child_part_id: props.bom?.child_part_id ?? null,
    quantity: props.bom?.quantity ?? 1,
    unit_of_measure: props.bom?.unit_of_measure ?? '',
    scrap_percentage: props.bom?.scrap_percentage ?? null,
    notes: props.bom?.notes ?? '',
})

async function submit() {
    try {
        const payload = form.data()
        let result

        if (props.method === 'put' && props.bom?.id) {
            result = await updateBillOfMaterial(props.parentPart.id, props.bom.id, payload)
        } else {
            result = await createBillOfMaterial(props.parentPart.id, payload)
        }

        router.visit(`/parts/${props.parentPart.id}/bill-of-materials/${result.id}`)
    } catch (err: any) {
        console.error(err.response?.data ?? err)

        if (err.response?.status === 422) {
            const raw = err.response.data.errors as Record<string, string[]>
            const flat = Object.fromEntries(
                Object.entries(raw).map(([key, messages]) => [key, messages[0]])
            ) as Record<string, string>
            form.setError(flat)
        }
    }
}
</script>

<template>
    <form @submit.prevent="submit" class="space-y-6 max-w-2xl">
        <div class="space-y-4">
            <h2 class="text-lg font-semibold border-b pb-2">Component Details</h2>

            <div>
                <label class="block text-sm font-medium mb-1">
                    Component Part <span class="text-red-500">*</span>
                </label>
                <select
                    v-model="form.child_part_id"
                    class="w-full border rounded px-3 py-2"
                >
                    <option :value="null">-- Select a part --</option>
                    <option
                        v-for="part in parts"
                        :key="part.id"
                        :value="part.id"
                        :disabled="part.id === parentPart.id"
                    >
                        {{ part.sku }} — {{ part.name }}
                    </option>
                </select>
                <p v-if="form.errors.child_part_id" class="text-red-500 text-sm mt-1">
                    {{ form.errors.child_part_id }}
                </p>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Quantity <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.quantity"
                        type="number"
                        step="0.001"
                        min="0"
                        class="w-full border rounded px-3 py-2"
                        placeholder="1"
                    />
                    <p v-if="form.errors.quantity" class="text-red-500 text-sm mt-1">
                        {{ form.errors.quantity }}
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Unit of Measure</label>
                    <input
                        v-model="form.unit_of_measure"
                        type="text"
                        class="w-full border rounded px-3 py-2"
                        placeholder="each"
                    />
                    <p v-if="form.errors.unit_of_measure" class="text-red-500 text-sm mt-1">
                        {{ form.errors.unit_of_measure }}
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Scrap %</label>
                    <input
                        v-model="form.scrap_percentage"
                        type="number"
                        step="0.01"
                        min="0"
                        max="100"
                        class="w-full border rounded px-3 py-2"
                        placeholder="0.00"
                    />
                    <p v-if="form.errors.scrap_percentage" class="text-red-500 text-sm mt-1">
                        {{ form.errors.scrap_percentage }}
                    </p>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Notes</label>
                <textarea
                    v-model="form.notes"
                    class="w-full border rounded px-3 py-2"
                    rows="3"
                    placeholder="Any additional notes about this component..."
                />
                <p v-if="form.errors.notes" class="text-red-500 text-sm mt-1">
                    {{ form.errors.notes }}
                </p>
            </div>
        </div>

        <div>
            <button
                type="submit"
                class="bg-blue-600 text-white px-5 py-2 rounded disabled:opacity-50"
                :disabled="form.processing"
            >
                {{ form.processing ? 'Saving...' : (submitLabel ?? 'Save') }}
            </button>
        </div>
    </form>
</template>