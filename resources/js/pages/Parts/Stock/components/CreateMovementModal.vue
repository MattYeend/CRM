<script setup lang="ts">
import { ref } from 'vue'
import { createPartStockMovement } from '@/services/partService'

const props = defineProps<{ partId: number }>()
const emit = defineEmits(['created'])

const type = ref<'in' | 'out'>('in')
const quantity = ref(0)
const reference = ref('')
const notes = ref('')
const loading = ref(false)

async function submit() {
    loading.value = true

    await createPartStockMovement(props.partId, {
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
    <div class="border p-4 rounded space-y-2">
        <h3 class="font-semibold">Add Movement</h3>

        <div class="flex gap-2">
            <select v-model="type" class="border p-2">
                <option value="in">Stock In</option>
                <option value="out">Stock Out</option>
            </select>

            <input v-model="quantity" type="number" class="border p-2 w-24" />
            <input v-model="reference" placeholder="Ref" class="border p-2" />
            <input v-model="notes" placeholder="Notes" class="border p-2" />

            <button @click="submit" class="bg-blue-600 text-white px-3 py-1">
                {{ loading ? 'Saving...' : 'Save' }}
            </button>
        </div>
    </div>
</template>