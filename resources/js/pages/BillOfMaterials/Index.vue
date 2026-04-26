<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, onMounted, reactive, computed } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { fetchBillOfMaterials, deleteBillOfMaterial } from '@/services/partService'

interface Part {
    id: number
    name: string
    sku: string
}

interface BillOfMaterial {
    id: number
    parent_part_id: number
    child_part_id: number
    quantity: number
    unit_of_measure?: string
    scrap_percentage?: number
    notes?: string
    child_part?: Part
    permissions: UserPermissions
}

interface GlobalPermissions {
    create: boolean
    viewAny: boolean
}

interface UserPermissions {
    view: boolean
    update: boolean
    delete: boolean
}

const props = defineProps<{ part: Part }>()

const permissions = ref<GlobalPermissions>({
    create: false,
    viewAny: false,
})

const billOfMaterials = ref<BillOfMaterial[]>([])
const loading = ref(true)
const currentPage = ref(1)
const perPage = 10
const pagination = reactive({
    current_page: 1,
    last_page: 1,
    total: 0,
})

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Parts', href: route('parts.index') },
    { title: props.part.name, href: route('parts.show', { part: props.part.id }) },
    { title: 'Bill of Materials', href: route('parts.billOfMaterials.index', { part: props.part.id }) },
]

const visiblePages = computed(() => {
    const total = pagination.last_page
    const current = currentPage.value
    const delta = 2

    const pages: (number | string)[] = []

    const start = Math.max(1, current - delta)
    const end = Math.min(total, current + delta)

    if (start > 1) {
        pages.push(1)
        if (start > 2) pages.push('...')
    }

    for (let i = start; i <= end; i++) {
        pages.push(i)
    }

    if (end < total) {
        if (end < total - 1) pages.push('...')
        pages.push(total)
    }

    return pages
})

async function loadBOMs(page = 1) {
    loading.value = true
    try {
        const data = await fetchBillOfMaterials(props.part.id, perPage, page)
        billOfMaterials.value = data.data
        permissions.value = data.permissions
        pagination.current_page = data.current_page
        pagination.last_page = data.last_page
        pagination.total = data.total
        currentPage.value = data.current_page
    } finally {
        loading.value = false
    }
}

function goToPage(page: number) {
    if (page >= 1 && page <= pagination.last_page) {
        loadBOMs(page)
    }
}

async function handleDelete(id: number) {
    if (!confirm('Are you sure you want to remove this BOM entry?')) return
    await deleteBillOfMaterial(props.part.id, id)
    loadBOMs(currentPage.value)
}

onMounted(() => loadBOMs())
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Bill of Materials" />

        <div class="p-6">
            <div class="flex items-start justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold">Bill of Materials</h1>
                    <p class="text-sm mt-1">{{ part.name }} · {{ part.sku }}</p>
                </div>
                <div class="flex gap-2">
                    <Link
                        :href="route('parts.show', { part: part.id })"
                        class="bg-gray-200 text-gray-700 px-4 py-2 rounded"
                    >
                        Back to Part
                    </Link>
                    <Link
                        v-if="permissions.create"
                        :href="route('parts.billOfMaterials.create', { part: part.id })"
                        class="bg-blue-600 text-white px-4 py-2 rounded"
                    >
                        Add Component
                    </Link>
                </div>
            </div>

            <div v-if="loading" class="text-center py-6 text-gray-500">
                Loading...
            </div>

            <table v-else class="w-full border text-sm">
                <thead>
                    <tr>
                        <th class="p-2 text-left">SKU</th>
                        <th class="p-2 text-left">Name</th>
                        <th class="p-2 text-right">Qty</th>
                        <th class="p-2 text-left">UoM</th>
                        <th class="p-2 text-right">Scrap %</th>
                        <th class="p-2 text-left">Notes</th>
                        <th class="p-2"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="bom in billOfMaterials"
                        :key="bom.id"
                        class="border-t"
                    >
                        <td class="p-2 text-xs">
                            {{ bom.child_part?.sku || '—' }}
                        </td>
                        <td class="p-2">{{ bom.child_part?.name || '—' }}</td>
                        <td class="p-2 text-right tabular-nums">{{ bom.quantity }}</td>
                        <td class="p-2">{{ bom.unit_of_measure || '—' }}</td>
                        <td class="p-2 text-right tabular-nums">
                            {{ bom.scrap_percentage != null ? `${bom.scrap_percentage}%` : '—' }}
                        </td>
                        <td class="p-2 text-gray-500 truncate max-w-xs">
                            {{ bom.notes || '—' }}
                        </td>
                        <td class="p-2 space-x-2 whitespace-nowrap">
                            <Link
                                v-if="bom.permissions.view"
                                :href="route('parts.billOfMaterials.show', { part: part.id, billOfMaterial: bom.id })"
                            >
                                View
                            </Link>
                            <Link
                                v-if="bom.permissions.update"
                                :href="route('parts.billOfMaterials.edit', { part: part.id, billOfMaterial: bom.id })"
                            >
                                Edit
                            </Link>
                            <button
                                v-if="bom.permissions.delete"
                                @click="handleDelete(bom.id)"
                                class="text-red-600"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>

                    <tr v-if="billOfMaterials.length === 0">
                        <td colspan="7" class="p-6 text-center text-gray-500">
                            No components found. Add one to get started.
                        </td>
                    </tr>
                </tbody>
            </table>

            <div v-if="pagination.last_page > 1" class="flex justify-center mt-4 space-x-2">
                <button
                    class="px-3 py-1 border rounded disabled:opacity-50"
                    :disabled="currentPage === 1"
                    @click="goToPage(currentPage - 1)"
                >
                    Previous
                </button>

                <button
                    v-for="page in visiblePages"
                    :key="page"
                    class="px-3 py-1 border rounded"
                    :class="{ 'bg-blue-600 text-white': page === currentPage }"
                    :disabled="page === '...'"
                    @click="typeof page === 'number' && goToPage(page)"
                >
                    {{ page }}
                </button>

                <button
                    class="px-3 py-1 border rounded disabled:opacity-50"
                    :disabled="currentPage === pagination.last_page"
                    @click="goToPage(currentPage + 1)"
                >
                    Next
                </button>
            </div>
        </div>
    </AppLayout>
</template>