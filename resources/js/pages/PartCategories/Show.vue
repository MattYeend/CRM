<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, onMounted } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { fetchPartCategory, deletePartCategory } from '@/services/partService'

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
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <h1 class="text-2xl font-bold">{{ partCategory.name }}</h1>
                        <p v-if="partCategory.full_path" class="text-sm mt-1">
                            {{ partCategory.full_path }}
                        </p>
                        <p v-if="partCategory.description" class="mt-2">
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

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 text-sm">
                    <!-- Details -->
                    <div class="space-y-3">
                        <h3 class="text-sm font-semibold uppercase tracking-wider">
                            Details
                        </h3>
                        <dl class="space-y-2">
                            <div v-if="partCategory.slug" class="flex justify-between">
                                <dt class="font-medium">Slug</dt>
                                <dd class="font-mono text-xs">{{ partCategory.slug }}</dd>
                            </div>
                            <div v-if="partCategory.parent" class="flex justify-between">
                                <dt class="font-medium">Parent</dt>
                                <dd>
                                    <Link
                                        :href="route('part-categories.show', { partCategory: partCategory.parent.id })"
                                    >
                                        {{ partCategory.parent.name }}
                                    </Link>
                                </dd>
                            </div>
                            <div v-if="partCategory.creator" class="flex justify-between">
                                <dt class="font-medium">Created By</dt>
                                <dd>{{ partCategory.creator.name }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Sub-categories -->
                    <div class="space-y-3">
                        <h3 class="text-sm font-semibold uppercase tracking-wider">
                            Sub-categories ({{ partCategory.children?.length ?? 0 }})
                        </h3>
                        <ul v-if="partCategory.children?.length" class="space-y-1">
                            <li
                                v-for="child in partCategory.children"
                                :key="child.id"
                            >
                                <Link
                                    :href="route('part-categories.show', { partCategory: child.id })"
                                >
                                    {{ child.name }}
                                </Link>
                            </li>
                        </ul>
                        <p v-else class="text-gray-400">None</p>
                    </div>

                    <!-- Parts -->
                    <div class="space-y-3 md:col-span-2">
                        <h3 class="text-sm font-semibold uppercase tracking-wider">
                            Parts ({{ partCategory.parts?.length ?? 0 }})
                        </h3>
                        <table v-if="partCategory.parts?.length" class="w-full border text-sm">
                            <thead>
                                <tr>
                                    <th class="p-2 text-left">SKU</th>
                                    <th class="p-2 text-left">Name</th>
                                    <th class="p-2"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="part in partCategory.parts"
                                    :key="part.id"
                                    class="border-t"
                                >
                                    <td class="p-2 font-mono text-xs">
                                        {{ part.sku }}
                                    </td>
                                    <td class="p-2">{{ part.name }}</td>
                                    <td class="p-2">
                                        <Link
                                            :href="route('parts.show', { part: part.id })"
                                        >
                                            View
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <p v-else class="text-gray-400 text-sm">No parts in this category.</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>