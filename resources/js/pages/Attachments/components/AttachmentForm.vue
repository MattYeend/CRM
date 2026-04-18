<script setup lang="ts">
import { ref } from 'vue'
import axios from 'axios'
import { router } from '@inertiajs/vue3'

import AttachmentFileSection from './AttachmentFileSection.vue'
import AttachmentEntitySection from './AttachmentEntitySection.vue'

interface FormData {
    file: File | null
    attachable_type: string
    attachable_id: number | null
}

const props = defineProps<{
    attachment?: any
    cancelHref: string
    submitLabel?: string
    submitRoute: string
    method?: 'post' | 'put'
    showEntityFields?: boolean
    attachableType?: string[]
    currentFilename?: string
}>()

const form = ref<FormData>({
    file: null,
    attachable_type: props.attachment?.attachable_type ?? '',
    attachable_id: props.attachment?.attachable_id ?? null,
})

const errors = ref<Record<string, string>>({})
const processing = ref(false)

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
            formData.append('attachable_type', form.value.attachable_type)
        }
        
        if (form.value.attachable_id) {
            formData.append('attachable_id', form.value.attachable_id.toString())
        }

        if (props.method === 'put') {
            formData.append('_method', 'PUT')
        }

        await axios({
            method: 'post',
            url: props.submitRoute,
            data: formData,
            withCredentials: true,
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        })

        // Redirect based on attachable type
        if (form.value.attachable_type && form.value.attachable_id) {
            router.visit(`/${form.value.attachable_type}s/${form.value.attachable_id}`)
        } else {
            router.visit(props.cancelHref)
        }
    } catch (err: any) {
        console.error(err.response?.data ?? err)

        if (err.response?.status === 422) {
            const raw = err.response.data.errors as Record<string, string[]>
            errors.value = Object.fromEntries(
                Object.entries(raw).map(([key, messages]) => [key, messages[0]])
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
            :errors="errors"
            :attachable-type="attachableType"
        />

        <div class="flex items-center gap-3">
            <button
                type="submit"
                class="bg-blue-600 text-white px-5 py-2 rounded text-sm font-medium hover:bg-blue-700 disabled:opacity-50"
                :disabled="processing"
            >
                {{ processing ? 'Saving...' : (submitLabel ?? 'Save') }}
            </button>
            <a
                :href="cancelHref"
                class="px-5 py-2 rounded text-sm font-medium border border-gray-300 text-gray-700 hover:bg-gray-50"
            >
                Cancel
            </a>
        </div>
    </form>
</template>