<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, onMounted } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { fetchPartCategory, deletePartCategory } from '@/services/partService'
import PartCategoryDetailSection from './components/PartCategoryDetailSection.vue'

interface UserPermissions {
    view: boolean
    update: boolean
    delete: boolean
}

interface PartCategory {
    id: number
    name: string
    slug?: string
    full_path?: string
    description?: string
    parent_id?: number | null
    parent?: { id: number; name: string }
    children?: { id: number; name: string }[]
    parts?: { id: number; name: string; sku: string }[]
    creator?: { name: string }
    permissions: UserPermissions
}

const props = defineProps<{ partCategory: any }>()

const partCategory = ref<PartCategory>({
    ...props.partCategory,
    permissions: props.partCategory.permissions ?? { view: false, update: false, delete: false },
})

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Part Categories', href: route('part-categories.index') },
    { title: props.partCategory.name, href: route('part-categories.show', { partCategory: props.partCategory.id }) },
]

async function loadPartCategory() {
    const data = await fetchPartCategory(partCategory.value.id)
    Object.assign(partCategory.value, data)
}

async function handleDelete() {
    if (!confirm('Are you sure you want to delete this category?')) return
    await deletePartCategory(partCategory.value.id)
    window.location.href = route('part-categories.index')
}

onMounted(() => loadPartCategory())
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="partCategory.name || 'Part Category'" />

        <div class="p-6">
            <div class="mx-auto border p-6 rounded shadow">

                <!-- Header -->
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <h1 class="text-2xl font-bold">{{ partCategory.name }}</h1>

                        <p v-if="partCategory.full_path" class="text-sm text-gray-600 mt-1">
                            {{ partCategory.full_path }}
                        </p>

                        <p v-if="partCategory.description" class="text-gray-600 mt-2">
                            {{ partCategory.description }}
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <Link
                            v-if="partCategory.permissions?.update"
                            :href="route('part-categories.edit', { partCategory: partCategory.id })"
                            class="bg-blue-600 text-white px-4 py-2 rounded"
                        >
                            Edit
                        </Link>

                        <Link
                            :href="route('part-categories.index')"
                            class="bg-gray-200 text-gray-700 px-4 py-2 rounded"
                        >
                            Back
                        </Link>

                        <button
                            v-if="partCategory.permissions?.delete"
                            @click="handleDelete"
                            class="bg-red-600 text-white px-4 py-2 rounded"
                        >
                            Delete
                        </button>
                    </div>
                </div>

                <!-- Extracted Detail Section -->
                <PartCategoryDetailSection :partCategory="partCategory" />

            </div>
        </div>
    </AppLayout>
</template>