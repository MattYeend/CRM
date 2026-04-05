<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, computed, onMounted } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { deleteAttachments, fetchAttachment } from '@/services/attachmentService'

interface UserPermissions {
    view: boolean
    update: boolean
    delete: boolean
}

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
    permissions: UserPermissions
}

const props = defineProps<{ attachment: any }>()

const attachment = ref<Attachment>({
    id: props.attachment.id,
    filename: props.attachment.filename,
    mime: props.attachment.mime,
    size: props.attachment.size,
    attachable_type: props.attachment.attachable_type,
    uploaded_by: props.attachment.uploaded_by,
    uploader: props.attachment.uploader,
    created_at: props.attachment.created_at,
    download_url: props.attachment.download_url,
    preview_url: props.attachment.preview_url,
    attachable_type_label: props.attachment.attachable_type_label,
    attachable_url: props.attachment.attachable_url,
    attachable: props.attachment.attachable,
    updated_at: props.attachment.updated_at,
    is_test: props.attachment.is_test ?? false,
    permissions: { view: false, update: false, delete: false },
})

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Attachments', href: route('attachments.index') },
    { title: 'View Attachment', href: route('attachments.show', { attachment: attachment.value.id }) },
]

function capitalize(str: string | null | undefined) {
    if (!str) return '-'
    return str
        .split(' ')
        .map(s => s.charAt(0).toUpperCase() + s.slice(1))
        .join(' ')
}

async function loadAttachment() {
    const data = await fetchAttachment(attachment.value.id)

    Object.assign(attachment.value, data)
}
const showDeleteModal = ref(false)
const deleting = ref(false)

const isImage = computed(() => props.attachment.mime?.startsWith('image/'))
const isPdf = computed(() => props.attachment.mime === 'application/pdf')

async function handleDelete() {
    deleting.value = true
    try {
        await deleteAttachments(props.attachment.id)
        router.visit(route('attachments.index'))
    } catch (err) {
        console.error('Failed to delete:', err)
    } finally {
        deleting.value = false
        showDeleteModal.value = false
    }
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

onMounted(() => {
    loadAttachment()
})
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="capitalize(attachment.filename) || 'Attachment'" />

        <div class="p-6 max-w-4xl space-y-6">

            <!-- Header actions -->
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">
                    {{ attachment.filename }}
                </h1>
                <div class="flex items-center gap-2">
                    <a
                        :href="attachment.download_url"
                        class="px-4 py-2 text-sm border border-gray-300 rounded"
                        :class="attachment.is_test ? 'opacity-50 pointer-events-none cursor-not-allowed' : 'hover:bg-gray-50'"
                        target="_blank"
                        :aria-disabled="attachment.is_test"
                    >
                        Download
                    </a>
                    <Link
                        v-if="attachment.permissions.update"
                        :href="route('attachments.edit', { attachment: attachment.id })"
                        class="bg-yellow-500 text-white px-4 py-2 rounded text-sm"
                    >
                        Edit
                    </Link>
                    <Link
                        :href="route('attachments.index')"
                        class="bg-blue-600 text-white px-4 py-2 rounded text-sm"
                    >
                        Back
                    </Link>
                </div>
            </div>

            <!-- File Preview -->
            <div class="border rounded p-6">
                <h2 class="text-sm font-medium text-gray-500 mb-4">Preview</h2>

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
                        class="px-4 py-2 text-sm border border-gray-300 rounded"
                        :class="attachment.is_test ? 'opacity-50 pointer-events-none cursor-not-allowed' : 'hover:bg-gray-50'"
                        target="_blank"
                        :aria-disabled="attachment.is_test"
                    >
                        Download to view
                    </a>
                </div>
            </div>

            <!-- Details -->
            <div class="border rounded p-6">
                <h2 class="text-sm font-medium text-gray-500 mb-4">Details</h2>

                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Filename</dt>
                        <dd class="mt-1 text-sm">{{ attachment.filename }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">File Size</dt>
                        <dd class="mt-1 text-sm">{{ formatSize(attachment.size) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">File Type</dt>
                        <dd class="mt-1 text-sm">{{ attachment.mime || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Attached To</dt>
                        <dd class="mt-1 text-sm">{{ capitalize(attachment.attachable_type) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Uploaded By</dt>
                        <dd class="mt-1 text-sm">{{ attachment.uploader?.name || '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Uploaded At</dt>
                        <dd class="mt-1 text-sm">{{ formatDate(attachment.created_at) }}</dd>
                    </div>
                    <div v-if="attachment.updated_at !== attachment.created_at">
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Last Updated</dt>
                        <dd class="mt-1 text-sm">{{ formatDate(attachment.updated_at) }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Danger Zone -->
            <div v-if="attachment.permissions.delete" class="border border-red-200 rounded p-6">
                <h2 class="text-sm font-medium text-red-700 mb-1">Danger Zone</h2>
                <p class="text-sm text-gray-600 mb-4">Permanently delete this attachment. This action cannot be undone.</p>
                <button
                    class="bg-red-600 text-white px-4 py-2 rounded text-sm hover:bg-red-700"
                    @click="showDeleteModal = true"
                >
                    Delete Attachment
                </button>
            </div>
        </div>

        <!-- Delete Modal -->
        <div
            v-if="showDeleteModal"
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
            @click.self="showDeleteModal = false"
        >
            <div class="rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
                <h2 class="text-lg font-medium mb-2">Delete attachment?</h2>
                <p class="text-sm text-gray-600 mb-6">
                    This will permanently remove
                    <strong>{{ attachment.filename }}</strong>.
                    This cannot be undone.
                </p>
                <div class="flex justify-end gap-3">
                    <button
                        class="px-4 py-2 text-sm border border-gray-300 rounded hover:bg-gray-50"
                        @click="showDeleteModal = false"
                    >
                        Cancel
                    </button>
                    <button
                        class="px-4 py-2 text-sm bg-red-600 text-white rounded hover:bg-red-700 disabled:opacity-50"
                        :disabled="deleting"
                        @click="handleDelete"
                    >
                        {{ deleting ? 'Deleting...' : 'Delete Attachment' }}
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>