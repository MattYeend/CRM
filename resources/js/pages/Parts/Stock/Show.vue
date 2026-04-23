<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, reactive, computed, onMounted } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { fetchPartStockMovement } from '@/services/partService'

interface Part {
    id: number
    name: string
    sku: string
    quantity: number
    reorder_point?: number
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

const permissions = ref<GlobalPermissions>({
    create: false,
    viewAny: false,
})

const props = defineProps<{
    part: Part
    movements: PaginatedMovements
}>()

const stock = ref<StockMovement[]>([])
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
    { title: 'Stock', href: route('parts.stock.show', { part: props.part.id }) },
]

const isLowStock = computed(() =>
    props.part.reorder_point != null && props.part.quantity <= props.part.reorder_point
)

async function loadStock(page = 1){
    loading.value = true
    try {
        const data = await fetchPartStockMovement(perPage, page)
        stock.value = data.data
        permissions.value = data.permissions
        pagination.current_page = data.current_page
        pagination.last_page = data.last_page
        pagination.total = data.total
        currentPage.value = data.current_page
    } finally {
        loading.value = false
    }
}

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
        window.location.href = route('parts.stock.show', { part: props.part.id, page })
    }
}
onMounted(() => loadStock())
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="`Stock — ${part.name}`" />

        <div class="p-6 space-y-6">
            <!-- Header -->
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-2xl font-bold">Stock Overview</h1>
                    <p class="text-sm mt-1">{{ part.name }} · {{ part.sku }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <!-- <Link
                        v-if="movements.permissions?.create"
                        :href="route('parts.stockMovements.create', { part: part.id })"
                        class="bg-blue-600 text-white px-4 py-2 rounded"
                    >
                        Add Movement
                    </Link> -->
                    <Link
                        :href="route('parts.show', { part: part.id })"
                        class="bg-gray-200 text-gray-700 px-4 py-2 rounded"
                    >
                        Back
                    </Link>
                </div>
            </div>

            <!-- Stock summary cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="border rounded p-4">
                    <p class="text-xs uppercase tracking-wide mb-1">Current Stock</p>
                    <p
                        class="text-3xl font-bold tabular-nums"
                        :class="isLowStock ? 'text-red-600' : 'text-gray-200'"
                    >
                        {{ part.quantity }}
                    </p>
                    <p v-if="isLowStock" class="text-xs text-red-500 mt-1">Below reorder point</p>
                </div>
                <div v-if="part.reorder_point != null" class="border rounded p-4">
                    <p class="text-xs uppercase tracking-wide mb-1">Reorder Point</p>
                    <p class="text-3xl font-bold tabular-nums">{{ part.reorder_point }}</p>
                </div>
                <div class="border rounded p-4">
                    <p class="text-xs uppercase tracking-wide mb-1">Total Movements</p>
                    <p class="text-3xl font-bold tabular-nums">{{ pagination.total }}</p>
                </div>
            </div>

            <!-- Movements table -->
            <div>
                <h2 class="text-lg font-semibold mb-3">Recent Movements</h2>

                <table class="w-full border text-sm">
                    <thead>
                        <tr>
                            <th class="p-2 text-left">Type</th>
                            <th class="p-2 text-right">Qty</th>
                            <th class="p-2 text-right">Before</th>
                            <th class="p-2 text-right">After</th>
                            <th class="p-2 text-left">Reference</th>
                            <th class="p-2 text-left">By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="movement in movements.data"
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
                            <td class="p-2 text-right tabular-nums">{{ movement.quantity_before }}</td>
                            <td class="p-2 text-right tabular-nums">{{ movement.quantity_after }}</td>
                            <td class="p-2 text-xs">{{ movement.reference ?? '—' }}</td>
                            <td class="p-2">{{ movement.created_by?.name ?? '—' }}</td>
                        </tr>

                        <tr v-if="movements.data.length === 0">
                            <td colspan="7" class="p-6 text-center text-gray-500">
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
        </div>
    </AppLayout>
</template>