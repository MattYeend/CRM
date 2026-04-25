<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, onMounted } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { deleteAttachments, fetchAttachment } from '@/services/attachmentService'
import AttachmentDetailSection from './components/AttachmentDetailSection.vue'

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
    permissions: props.attachment.permissions ?? { view: false, update: false, delete: false },
})

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Attachments', href: route('attachments.index') },
    { title: props.attachment.filename, href: route('attachments.show', { attachment: attachment.value.id }) },
]

async function loadAttachment() {
    const data = await fetchAttachment(attachment.value.id)
    Object.assign(attachment.value, data)
}

const showDeleteModal = ref(false)
const deleting = ref(false)

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

function capitalize(str: string | null | undefined) {
    if (!str) return '—'
    return str
        .split(' ')
        .map(s => s.charAt(0).toUpperCase() + s.slice(1))
        .join(' ')
}

onMounted(() => loadAttachment())
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

            <AttachmentDetailSection :attachment="attachment" />

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
            <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
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