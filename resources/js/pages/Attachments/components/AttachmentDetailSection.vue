<script setup lang="ts">
import { computed } from 'vue'

interface Attachment {
    id: number
    filename: string
    mime: string | null
    size: number
    attachable_type: string | null
    uploaded_by: { id: number; name: string } | null
    uploader: { id: number; name: string } | null
    created_at: string
    download_url: string
    preview_url: string | null
    attachable_type_label: string | null
    attachable_url: string | null
    attachable: { id: number; name?: string; title?: string } | null
    updated_at: string
    is_test: boolean
}

const props = defineProps<{ attachment: Attachment }>()

const isImage = computed(() => props.attachment.mime?.startsWith('image/'))
const isPdf = computed(() => props.attachment.mime === 'application/pdf')

function capitalize(str: string | null | undefined) {
    if (!str) return '—'
    return str
        .split(' ')
        .map(s => s.charAt(0).toUpperCase() + s.slice(1))
        .join(' ')
}

function formatSize(bytes: number | null): string {
    if (!bytes) return '—'
    if (bytes < 1024) return bytes + ' B'
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB'
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB'
}

function formatDate(dateString: string | null): string {
    if (!dateString) return '—'
    return new Date(dateString).toLocaleDateString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    })
}
</script>

<template>
    <div class="space-y-6">
        <!-- File Preview -->
        <div class="border rounded p-6">
            <h2 class="text-sm font-medium mb-4">Preview</h2>

            <img
                v-if="isImage"
                :src="attachment.preview_url ?? attachment.download_url"
                :alt="attachment.filename"
                class="max-w-full max-h-96 rounded border object-contain"
            />

            <iframe
                v-else-if="isPdf"
                :src="attachment.preview_url ?? attachment.download_url"
                class="w-full h-96 border rounded"
            ></iframe>

            <div
                v-else
                class="flex flex-col items-center justify-center py-12 rounded border"
            >
                <svg class="w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="mt-2 text-sm text-gray-500">No preview available</p>
                <a
                    :href="attachment.download_url"
                    class="mt-4 px-4 py-2 text-sm border border-gray-300 rounded hover:bg-gray-50"
                    :class="attachment.is_test ? 'opacity-50 pointer-events-none cursor-not-allowed' : ''"
                    target="_blank"
                    :aria-disabled="attachment.is_test"
                >
                    Download to view
                </a>
            </div>
        </div>

        <!-- Details -->
        <div class="border rounded p-6">
            <h2 class="text-sm font-medium mb-4">Details</h2>

            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide">Filename</dt>
                    <dd class="mt-1 text-sm">{{ attachment.filename }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide">File Size</dt>
                    <dd class="mt-1 text-sm">{{ formatSize(attachment.size) }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide">File Type</dt>
                    <dd class="mt-1 text-sm">{{ attachment.mime || '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide">Attached To</dt>
                    <dd class="mt-1 text-sm">{{ capitalize(attachment.attachable_type) }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide">Uploaded By</dt>
                    <dd class="mt-1 text-sm">{{ attachment.uploader?.name || '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide">Uploaded At</dt>
                    <dd class="mt-1 text-sm">{{ formatDate(attachment.created_at) }}</dd>
                </div>
                <div v-if="attachment.updated_at !== attachment.created_at">
                    <dt class="text-xs font-medium uppercase tracking-wide">Last Updated</dt>
                    <dd class="mt-1 text-sm">{{ formatDate(attachment.updated_at) }}</dd>
                </div>
            </dl>
        </div>
    </div>
</template>