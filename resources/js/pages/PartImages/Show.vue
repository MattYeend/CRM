<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, onMounted } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { fetchPartImage, deletePartImage } from '@/services/partService'
import PartImageDetailSection from './components/PartImageDetailSection.vue'

interface UserPermissions {
    view: boolean
    update: boolean
    delete: boolean
}

interface PartImage {
    id: number
    part_id: number
    part?: { id: number; name: string; sku: string }
    image?: string
    image_url?: string
    thumbnail_url?: string
    thumbnail_or_image_url?: string
    alt?: string
    is_primary: boolean
    sort_order?: number
    creator?: { name: string }
    permissions: UserPermissions
}

const props = defineProps<{ partImage: any }>()

const partImage = ref<PartImage>({
    ...props.partImage,
    permissions: props.partImage.permissions ?? { view: false, update: false, delete: false },
})

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Part Images', href: route('part-images.index') },
    { title: props.partImage.part?.name ?? `Image #${props.partImage.id}`, href: route('part-images.show', { partImage: props.partImage.id }) },
]

async function loadPartImage() {
    const data = await fetchPartImage(partImage.value.id)
    Object.assign(partImage.value, data)
}

async function handleDelete() {
    if (!confirm('Are you sure you want to delete this image?')) return
    await deletePartImage(partImage.value.id)
    window.location.href = route('part-images.index')
}

onMounted(() => loadPartImage())
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="partImage.part?.name ?? 'Part Image'" />

        <div class="p-6">
            <div class="mx-auto border p-6 rounded shadow">

                <!-- Header -->
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <h1 class="text-2xl font-bold">
                            {{ partImage.part?.name ?? `Image #${partImage.id}` }}
                        </h1>

                        <p v-if="partImage.part?.sku" class="text-gray-600 text-sm mt-1">
                            {{ partImage.part.sku }}
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <Link
                            v-if="partImage.permissions?.update"
                            :href="route('part-images.edit', { partImage: partImage.id })"
                            class="bg-blue-600 text-white px-4 py-2 rounded"
                        >
                            Edit
                        </Link>

                        <Link
                            :href="route('part-images.index')"
                            class="bg-gray-200 text-gray-700 px-4 py-2 rounded"
                        >
                            Back
                        </Link>

                        <button
                            v-if="partImage.permissions?.delete"
                            @click="handleDelete"
                            class="bg-red-600 text-white px-4 py-2 rounded"
                        >
                            Delete
                        </button>
                    </div>
                </div>

                <PartImageDetailSection :partImage="partImage" />
            </div>
        </div>
    </AppLayout>
</template>