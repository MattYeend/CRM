<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, reactive, computed } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'

interface Part {
    id: number
    name: string
    sku: string
}

interface UserPermissions {
    view: boolean
    update: boolean
    delete: boolean
}

interface StockMovement {
    id: number
    part_id: number
    type: string
    reference?: string
    notes?: string
    quantity: number
    quantity_before: number
    quantity_after: number
    is_inbound: boolean
    is_outbound: boolean
    created_by?: { name: string }
    permissions: UserPermissions
}

interface GlobalPermissions {
    create: boolean
    viewAny: boolean
}

interface PaginatedMovements {
    data: StockMovement[]
    current_page: number
    last_page: number
    total: number
    permissions: GlobalPermissions
}

const props = defineProps<{
    part: Part
    stockMovements: PaginatedMovements
}>()

const currentPage = ref(props.stockMovements.current_page)
const pagination = reactive({
    current_page: props.stockMovements.current_page,
    last_page: props.stockMovements.last_page,
    total: props.stockMovements.total,
})

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Parts', href: route('parts.index') },
    { title: props.part.name, href: route('parts.show', { part: props.part.id }) },
    { title: 'Stock Movements', href: route('parts.stockMovements.index', { part: props.part.id }) },
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
    for (let i = start; i <= end; i++) pages.push(i)
    if (end < total) {
        if (end < total - 1) pages.push('...')
        pages.push(total)
    }

    return pages
})

function goToPage(page: number) {
    if (page >= 1 && page <= pagination.last_page) {
        window.location.href = route('parts.stockMovements.index', { part: props.part.id, page })
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="`Stock Movements — ${part.name}`" />

        <div class="p-6">
            <div class="flex justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold">Stock Movements</h1>
                    <p class="text-sm text-gray-500 mt-1 font-mono">{{ part.name }} · {{ part.sku }}</p>
                </div>
                <div class="flex gap-2">
                    <Link
                        v-if="stockMovements.permissions?.create"
                        :href="route('parts.stockMovements.create', { part: part.id })"
                        class="bg-blue-600 text-white px-4 py-2 rounded"
                    >
                        Create
                    </Link>
                </div>
            </div>

            <table class="w-full border text-sm">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="p-2 text-left">Type</th>
                        <th class="p-2 text-right">Qty</th>
                        <th class="p-2 text-right">Before</th>
                        <th class="p-2 text-right">After</th>
                        <th class="p-2 text-left">Reference</th>
                        <th class="p-2 text-left">Notes</th>
                        <th class="p-2 text-left">By</th>
                        <th class="p-2"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="movement in stockMovements.data"
                        :key="movement.id"
                        class="border-t"
                    >
                        <td class="p-2">
                            <span
                                class="text-xs px-2 py-0.5 rounded-full font-medium"
                                :class="movement.is_inbound
                                    ? 'bg-green-100 text-green-700'
                                    : 'bg-red-100 text-red-700'"
                            >
                                {{ movement.type }}
                            </span>
                        </td>
                        <td class="p-2 text-right tabular-nums">
                            <span :class="movement.is_inbound ? 'text-green-600' : 'text-red-600'">
                                {{ movement.is_inbound ? '+' : '-' }}{{ movement.quantity }}
                            </span>
                        </td>
                        <td class="p-2 text-right tabular-nums text-gray-500">{{ movement.quantity_before }}</td>
                        <td class="p-2 text-right tabular-nums text-gray-500">{{ movement.quantity_after }}</td>
                        <td class="p-2 font-mono text-xs text-gray-500">{{ movement.reference ?? '—' }}</td>
                        <td class="p-2 text-gray-500 max-w-xs truncate">{{ movement.notes ?? '—' }}</td>
                        <td class="p-2 text-gray-500">{{ movement.created_by?.name ?? '—' }}</td>
                        <td class="p-2 whitespace-nowrap">
                            <Link
                                v-if="movement.permissions.view"
                                :href="route('parts.stockMovements.show', { part: part.id, stockMovement: movement.id })"
                                class="text-blue-600 hover:underline"
                            >
                                View
                            </Link>
                        </td>
                    </tr>

                    <tr v-if="stockMovements.data.length === 0">
                        <td colspan="8" class="p-6 text-center text-gray-500">
                            No stock movements found.
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