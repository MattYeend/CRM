<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { reactive, computed } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import axios from 'axios'
import AttachmentForm from '@/pages/Attachments/components/AttachmentForm.vue'

interface Attachment {
    id: number
    title: string | null
    description: string | null
    original_filename: string
    mime_type: string | null
    file_size: number | null
    is_public: boolean
    download_url: string
    preview_url: string | null
    attachable_type: string | null
    attachable_id: number | null
}

const props = defineProps<{
    attachment: Attachment
    subjectTypes: string[]
}>()

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Attachments', href: route('attachments.index') },
    {
        title: props.attachment.title ?? props.attachment.original_filename,
        href: route('attachments.show', { attachment: props.attachment.id }),
    },
    { title: 'Edit', href: route('attachments.edit', { attachment: props.attachment.id }) },
]

const isImage = computed(() => props.attachment.mime_type?.startsWith('image/'))

const form = reactive({
    file: null as File | null,
    title: props.attachment.title ?? '',
    description: props.attachment.description ?? '',
    attachable_type: props.attachment.attachable_type ?? '',
    attachable_id: props.attachment.attachable_id ?? null as number | null,
    is_public: props.attachment.is_public ?? false,
    errors: {} as Record<string, string>,
    processing: false,
})

function formatSize(bytes: number | null): string {
    if (!bytes) return '—'
    if (bytes < 1024) return bytes + ' B'
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB'
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB'
}

async function submit() {
    form.errors = {}
    form.processing = true

    try {
        await axios.get('/sanctum/csrf-cookie', { withCredentials: true })

        const formData = new FormData()
        formData.append('_method', 'PUT')
        if (form.file) formData.append('file', form.file)
        formData.append('title', form.title)
        formData.append('description', form.description)
        if (form.attachable_type) formData.append('attachable_type', form.attachable_type)
        if (form.attachable_id !== null) formData.append('attachable_id', String(form.attachable_id))
        formData.append('is_public', form.is_public ? '1' : '0')

        const response = await axios.post(
            `/api/attachments/${props.attachment.id}`,
            formData,
            {
                withCredentials: true,
                headers: { 'Content-Type': 'multipart/form-data' },
            }
        )

        router.visit(route('attachments.show', { attachment: response.data.id }))
    } catch (err: any) {
        console.error(err.response?.data ?? err)
        if (err.response?.status === 422) {
            form.errors = err.response.data.errors ?? {}
        }
    } finally {
        form.processing = false
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="`Edit — ${attachment.title ?? attachment.original_filename}`" />

        <div class="p-6 max-w-2xl space-y-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">Edit Attachment</h1>
                <div class="flex items-center gap-2">
                    <a
                        :href="route('attachments.show', { attachment: attachment.id })"
                        class="px-4 py-2 text-sm border border-gray-300 rounded hover:bg-gray-50"
                    >
                        View
                    </a>
                    <a :href="route('attachments.index')" class="bg-blue-600 text-white px-4 py-2 rounded text-sm">
                        Back
                    </a>
                </div>
            </div>

            <!-- Current file info -->
            <div class="border rounded bg-white p-4">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-3">Current File</p>
                <div class="flex items-center gap-4">
                    <div v-if="isImage" class="shrink-0">
                        <img
                            :src="attachment.preview_url ?? attachment.download_url"
                            :alt="attachment.original_filename"
                            class="h-16 w-16 object-cover rounded border"
                        />
                    </div>
                    <div
                        v-else
                        class="shrink-0 h-16 w-16 flex items-center justify-center rounded border bg-gray-50 text-gray-400"
                    >
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate">{{ attachment.original_filename }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">
                            {{ attachment.mime_type }} — {{ formatSize(attachment.file_size) }}
                        </p>
                        <a
                            :href="attachment.download_url"
                            class="text-xs text-blue-600 hover:underline mt-0.5 inline-block"
                            target="_blank"
                        >
                            Download current file
                        </a>
                    </div>
                </div>
            </div>

            <!-- Edit form -->
            <div class="border rounded bg-white p-6">
                <h2 class="text-base font-semibold mb-2">Update Details</h2>
                <p class="text-sm text-gray-500 mb-6">
                    Update title, description, and other metadata. To replace the file, upload a new one below (optional).
                </p>

                <form @submit.prevent="submit" class="space-y-8 max-w-xl">
                    <AttachmentForm
                        :form="form"
                        :cancel-href="route('attachments.show', { attachment: attachment.id })"
                        :subject-types="subjectTypes"
                        :show-entity-fields="true"
                        submit-label="Update Attachment"
                    />
                </form>
            </div>
        </div>
    </AppLayout>
</template>