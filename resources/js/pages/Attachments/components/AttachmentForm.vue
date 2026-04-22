<script setup lang="ts">
import { ref, watch, computed } from 'vue'
import axios from 'axios'
import { router } from '@inertiajs/vue3'
import AttachmentFileSection from './AttachmentFileSection.vue'
import AttachmentEntitySection from './AttachmentEntitySection.vue'

interface FormData {
    file: File | null
    attachable_type: string
    attachable_id: number | null
    errors: Record<string, string>
    processing?: boolean
}

const props = defineProps<{
    modelValue: FormData
    cancelHref: string
    submitRoute: string
    submitLabel?: string
    method?: 'post' | 'put'
    attachableType?: string[]
    showEntityFields?: boolean
    currentFilename?: string
}>()

const emit = defineEmits<{
    (e: 'update:modelValue', value: FormData): void
}>()

const form = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val),
})

const errors = ref<Record<string, string>>({})
const processing = ref(false)

const types = (props.attachableType ?? []).map(t => {
    const short = t.split('\\').pop()?.toLowerCase() ?? t
    return {
        value: t,
        label: short.charAt(0).toUpperCase() + short.slice(1),
    }
})

const entityOptions = ref<any[]>([])
const loadingEntities = ref(false)

const typeApiMap: Record<string, string> = {
    company: 'companies/all',
    deal: 'deals/all',
    task: 'tasks/all',
    user: 'users/all',
}

function getKey(type: string) {
    return type.split('\\').pop()?.toLowerCase() ?? ''
}

watch(
    () => form.value.attachable_type,
    async (type) => {
        entityOptions.value = []

        if (!type) return

        const endpoint = typeApiMap[getKey(type)]
        if (!endpoint) return

        loadingEntities.value = true

        try {
            const res = await axios.get(`/api/${endpoint}`)
            const items = res.data.data ?? res.data ?? []

            entityOptions.value = items.map((i: any) => ({
                id: i.id,
                name: i.name ?? i.title ?? `#${i.id}`,
            }))
        } finally {
            loadingEntities.value = false
        }
    },
    { immediate: true }
)

async function submit() {
    processing.value = true
    errors.value = {}

    try {
        await axios.get('/sanctum/csrf-cookie', { withCredentials: true })

        const formData = new FormData()

        if (form.value.file) {
            formData.append('file', form.value.file)
        }

        if (form.value.attachable_type) {
            // Send the full class name, not the short key
            formData.append(
                'attachable_type',
                form.value.attachable_type  // Remove getKey() here
            )
        }

        if (form.value.attachable_id !== null) {
            formData.append(
                'attachable_id',
                String(form.value.attachable_id)
            )
        }

        if (props.method === 'put') {
            formData.append('_method', 'PUT')
        }

        const response = await axios.post(props.submitRoute, formData, {
            withCredentials: true,
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        })

        router.visit(`/attachments/${response.data.id}`)
    } catch (err: any) {
        if (err.response?.status === 422) {
            const raw = err.response.data.errors
            errors.value = Object.fromEntries(
                Object.entries(raw).map(([k, v]: any) => [k, v[0]])
            )
        }
    } finally {
        processing.value = false
    }
}
</script>

<template>
    <form @submit.prevent="submit" class="space-y-8 max-w-2xl">

        <AttachmentFileSection
            v-model="form"
            :errors="errors"
            :current-filename="currentFilename"
        />

        <AttachmentEntitySection
            v-if="showEntityFields !== false"
            v-model="form"
            :types="types"
            :options="entityOptions"
            :loading="loadingEntities"
        />

        <div class="flex gap-3">
            <button
                type="submit"
                class="bg-blue-600 text-white px-5 py-2 rounded"
                :disabled="processing"
            >
                {{ processing ? 'Saving...' : (submitLabel ?? 'Save') }}
            </button>

            <a :href="cancelHref" class="border px-5 py-2 rounded">
                Cancel
            </a>
        </div>

    </form>
</template>