<script setup lang="ts">
import { useForm, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { createPartSerialNumber, updatePartSerialNumber } from '@/services/partService'

interface Part {
    id: number
}

interface SerialNumber {
    id?: number
    serial_number?: string
    status?: string
    batch_number?: string
    manufactured_at?: string
    expires_at?: string
}

const props = defineProps<{
    part: Part
    serialNumber?: SerialNumber
    method?: 'post' | 'put'
    submitLabel?: string
}>()

const form = useForm({
    serial_number: props.serialNumber?.serial_number ?? '',
    status: props.serialNumber?.status ?? '',
    batch_number: props.serialNumber?.batch_number ?? '',
    manufactured_at: props.serialNumber?.manufactured_at ?? '',
    expires_at: props.serialNumber?.expires_at ?? '',
})

async function submit() {
    try {
        const payload = form.data()
        let result

        if (props.method === 'put' && props.serialNumber?.id) {
            result = await updatePartSerialNumber(
                props.part.id,
                props.serialNumber.id,
                payload
            )
        } else {
            result = await createPartSerialNumber(
                props.part.id,
                payload
            )
        }

        router.visit(route('parts.serialNumbers.index', { part: result.part_id }))
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
    <form @submit.prevent="submit" class="space-y-6 max-w-lg">
        <div>
            <label class="block text-sm font-medium mb-1">Serial Number *</label>
            <input v-model="form.serial_number" class="w-full border px-3 py-2 rounded font-mono" />
            <p v-if="form.errors.serial_number" class="text-red-600 text-xs">{{ form.errors.serial_number }}</p>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Status</label>
            <input v-model="form.status" class="w-full border px-3 py-2 rounded" />
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Batch Number</label>
            <input v-model="form.batch_number" class="w-full border px-3 py-2 rounded font-mono" />
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Manufactured At</label>
            <input v-model="form.manufactured_at" type="date" class="w-full border px-3 py-2 rounded" />
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Expires At</label>
            <input v-model="form.expires_at" type="date" class="w-full border px-3 py-2 rounded" />
        </div>

        <button
            type="submit"
            :disabled="form.processing"
            class="bg-blue-600 text-white px-4 py-2 rounded"
        >
            {{ form.processing ? 'Saving...' : (submitLabel ?? 'Save') }}
        </button>
    </form>
</template>