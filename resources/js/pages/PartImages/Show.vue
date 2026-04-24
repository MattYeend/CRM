<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, onMounted } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { fetchPartImage, deletePartImage } from '@/services/partService'

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

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Image preview -->
                    <div class="space-y-3">
                        <h3 class="text-sm font-semibold uppercase tracking-wider">
                            Preview
                        </h3>
                        <img
                            v-if="partImage.image_url"
                            :src="partImage.image_url"
                            :alt="partImage.alt ?? partImage.part?.name"
                            class="w-full max-w-sm rounded border object-contain"
                        />
                        <p v-else class="text-sm text-gray-400">No image available.</p>

                        <div v-if="partImage.thumbnail_url" class="mt-2">
                            <p class="text-xs mb-1">Thumbnail</p>
                            <img
                                :src="partImage.thumbnail_url"
                                :alt="partImage.alt ?? partImage.part?.name"
                                class="h-16 w-16 rounded border object-cover"
                            />
                        </div>
                    </div>

                    <!-- Details -->
                    <div class="space-y-3">
                        <h3 class="text-sm font-semibold uppercase tracking-wider">
                            Details
                        </h3>
                        <dl class="space-y-2 text-sm">
                            <div v-if="partImage.part" class="flex justify-between">
                                <dt>Part</dt>
                                <dd>
                                    <Link
                                        :href="route('parts.show', { part: partImage.part.id })"
                                    >
                                        {{ partImage.part.name }}
                                    </Link>
                                </dd>
                            </div>
                            <div v-if="partImage.alt" class="flex justify-between">
                                <dt>Alt Text</dt>
                                <dd>{{ partImage.alt }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt>Primary</dt>
                                <dd>
                                    <span
                                        class="text-xs px-2 py-0.5 rounded-full"
                                        :class="partImage.is_primary
                                            ? 'bg-green-100 text-green-700'
                                            : 'bg-gray-100 text-gray-600'"
                                    >
                                        {{ partImage.is_primary ? 'Yes' : 'No' }}
                                    </span>
                                </dd>
                            </div>
                            <div v-if="partImage.sort_order != null" class="flex justify-between">
                                <dt>Sort Order</dt>
                                <dd class="tabular-nums">{{ partImage.sort_order }}</dd>
                            </div>
                            <div v-if="partImage.creator" class="text-gray-600 flex justify-between">
                                <dt>Created By</dt>
                                <dd>{{ partImage.creator.name }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>