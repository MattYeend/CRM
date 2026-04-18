<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import axios from 'axios'
import AttachmentForm from '@/pages/Attachments/components/AttachmentForm.vue'

const props = defineProps<{
    attachableTypes: string[]
    attachableType?: string
    attachableId?: string | number
}>()

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Attachments', href: route('attachments.index') },
    { title: 'Add Attachment', href: route('attachments.create') },
]

const form = ref({
    file: null as File | null,
    attachable_type: props.attachableType ?? '',
    attachable_id: props.attachableId ? Number(props.attachableId) : null as number | null,
    errors: {} as Record<string, string>,
    processing: false,
})

async function submit() {
    form.value.errors = {}
    form.value.processing = true

    try {
        await axios.get('/sanctum/csrf-cookie', { withCredentials: true })

        const formData = new FormData()
        if (form.value.file) formData.append('file', form.value.file)
        if (form.value.attachable_type) formData.append('attachable_type', form.value.attachable_type)
        if (form.value.attachable_id !== null) {
            formData.append('attachable_id', String(form.value.attachable_id))
        }

        const response = await axios.post('/api/attachments', formData, {
            withCredentials: true,
            headers: { 'Content-Type': 'multipart/form-data' },
        })

        router.visit(route('attachments.show', { attachment: response.data.id }))
    } catch (err: any) {
        console.error(err.response?.data ?? err)
        if (err.response?.status === 422) {
            form.value.errors = err.response.data.errors ?? {}
        }
    } finally {
        form.value.processing = false
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Add Attachment" />

        <div class="p-6 max-w-2xl">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold">Add Attachment</h1>
                <a :href="route('attachments.index')" class="bg-blue-600 text-white px-4 py-2 rounded text-sm">
                    Back to Attachments
                </a>
            </div>

            <div class="border rounded p-6">
                <h2 class="text-base font-semibold mb-6">Upload a new file</h2>

                <form @submit.prevent="submit" class="space-y-8 max-w-xl">
                    <AttachmentForm
                        v-model="form"
                        :cancel-href="route('attachments.index')"
                        :attachable-type="attachableTypes"
                        :show-entity-fields="true"
                        submit-label="Upload Attachment"
                        @update:file="form.file = $event"
                        @update:attachable_type="form.attachable_type = $event"
                        @update:attachable_id="form.attachable_id = $event"
                    />
                </form>
            </div>
        </div>
    </AppLayout>
</template>