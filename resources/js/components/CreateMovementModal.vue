<script setup lang="ts">
import { ref } from 'vue'

type MovementType = 'in' | 'out'

type MovementPayload = {
    type: MovementType
    quantity: number
    reference: string
    notes: string
}

const props = defineProps<{
    entityId: number
    createMovement: (id: number, payload: MovementPayload) => Promise<any>
}>()

const emit = defineEmits(['created'])

const type = ref<MovementType>('in')
const quantity = ref(0)
const reference = ref('')
const notes = ref('')
const loading = ref(false)

async function submit() {
    if (!quantity.value) return

    loading.value = true

    await props.createMovement(props.entityId, {
        type: type.value,
        quantity: quantity.value,
        reference: reference.value,
        notes: notes.value,
    })

    emit('created')

    quantity.value = 0
    reference.value = ''
    notes.value = ''

    loading.value = false
}
</script>

<template>
    <div class="border p-4 rounded space-y-3 mt-6">
        <h3 class="font-semibold text-lg">Add Movement</h3>

        <div class="flex gap-3">
            <select v-model="type" class="border rounded px-3 py-2">
                <option value="in">Stock In</option>
                <option value="out">Stock Out</option>
            </select>

            <input 
                v-model="quantity" 
                type="number" 
                placeholder="Quantity"
                class="border rounded px-3 py-2 w-32" 
            />

            <input 
                v-model="reference" 
                placeholder="Reference" 
                class="border rounded px-3 py-2 flex-1" 
            />

            <input 
                v-model="notes" 
                placeholder="Notes (optional)" 
                class="border rounded px-3 py-2 flex-1" 
            />

            <button 
                @click="submit" 
                :disabled="loading || !quantity"
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                {{ loading ? 'Saving...' : 'Save' }}
            </button>
        </div>
    </div>
</template>