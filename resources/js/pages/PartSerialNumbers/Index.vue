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

interface SerialNumber {
    id: number
    part_id: number
    part?: Part
    serial_number: string
    status?: string
    batch_number?: string
    manufactured_at: string | null 
    expires_at: string | null 
    is_expired: boolean
    is_expiring_soon: boolean
    permissions: UserPermissions
}

interface GlobalPermissions {
    create: boolean
    viewAny: boolean
}

interface PaginatedSerialNumbers {
    data: SerialNumber[]
    current_page: number
    last_page: number
    total: number
    permissions: GlobalPermissions
}

const props = defineProps<{
    part: Part
    serialNumbers: PaginatedSerialNumbers
}>()

const currentPage = ref(props.serialNumbers.current_page)
const pagination = reactive({
    current_page: props.serialNumbers.current_page,
    last_page: props.serialNumbers.last_page,
    total: props.serialNumbers.total,
})

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Parts', href: route('parts.index') },
    { title: props.part.name, href: route('parts.show', { part: props.part.id }) },
    { title: 'Serial Numbers', href: route('parts.serialNumbers.index', { part: props.part.id }) },
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

function statusClass(serialNumber: SerialNumber): string {
    if (serialNumber.is_expired) return 'bg-red-100 text-red-700'
    if (serialNumber.is_expiring_soon) return 'bg-yellow-100 text-yellow-700'
    return 'bg-green-100 text-green-700'
}

function formatStatus(value: string): string {
    return value
        .replace(/_/g, ' ')
        .replace(/\b\w/g, char => char.toUpperCase())
}

function statusLabel(serialNumber: SerialNumber): string {
    if (serialNumber.is_expired) return 'Expired'
    if (serialNumber.is_expiring_soon) return 'Expiring Soon'

    const status = serialNumber.status ?? 'active'
    return formatStatus(status)
}

function goToPage(page: number) {
    if (page >= 1 && page <= pagination.last_page) {
        window.location.href = route('parts.serialNumbers.index', {
            part: props.part.id,
            page,
        })
    }
}

function formatDate(dateStr: string | null): string {
    if (!dateStr) return '—'
    return new Date(dateStr).toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    })
}

</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="`${part.name} — Serial Numbers`" />

        <div class="p-6">
            <div class="flex items-start justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold">Serial Numbers</h1>
                    <p class="text-sm mt-1">
                        {{ part.name }} · {{ part.sku }}
                    </p>
                </div>

                <Link
                    v-if="serialNumbers?.permissions?.create === true"
                    :href="route('parts.serialNumbers.create', { part: part.id })"
                    class="bg-blue-600 text-white px-4 py-2 rounded self-start"
                >
                    Create
                </Link>
            </div>

            <table class="w-full border text-sm">
                <thead>
                    <tr>
                        <th class="p-2 text-left">Serial Number</th>
                        <th class="p-2 text-left">Batch</th>
                        <th class="p-2 text-left">Status</th>
                        <th class="p-2 text-left">Manufactured</th>
                        <th class="p-2 text-left">Expires</th>
                        <th class="p-2"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="sn in serialNumbers.data"
                        :key="sn.id"
                        class="border-t"
                    >
                        <td class="p-2">{{ sn.serial_number }}</td>
                        <td class="p-2">{{ sn.batch_number ?? '—' }}</td>
                        <td class="p-2">
                            <span
                                class="text-xs px-2 py-0.5 rounded-full"
                                :class="statusClass(sn)"
                            >
                                {{ statusLabel(sn) }}
                            </span>
                        </td>
                        <td class="p-2">{{ formatDate(sn.manufactured_at) }}</td>
                        <td class="p-2">{{ formatDate(sn.expires_at) }}</td>
                        <td class="p-2 space-x-2 whitespace-nowrap">
                            <Link
                                v-if="sn.permissions.update"
                                :href="route('parts.serialNumbers.edit', { part: part.id, serialNumber: sn.id })"
                            >
                                Edit
                            </Link>
                        </td>
                    </tr>

                    <tr v-if="serialNumbers.data.length === 0">
                        <td colspan="6" class="p-6 text-center text-gray-500">
                            No serial numbers found.
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