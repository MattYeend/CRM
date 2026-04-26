<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, reactive, computed, onMounted } from 'vue'
import { route } from 'ziggy-js'
import { fetchParts } from '@/services/partService'

const parts = ref<any[]>([])
const loading = ref(true)

const currentPage = ref(1)
const perPage = 10

const pagination = reactive({
    current_page: 1,
    last_page: 1,
    total: 0,
})

async function loadParts(page = 1) {
    loading.value = true

    try {
        const data = await fetchParts(perPage, page)

        parts.value = data.data

        pagination.current_page = data.current_page
        pagination.last_page = data.last_page
        pagination.total = data.total

        currentPage.value = data.current_page
        console.log(pagination)

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
        loadParts(page)
    }
}

onMounted(() => loadParts())
</script>

<template>
    <AppLayout>
        <Head title="Parts Stock" />

        <div class="p-6 space-y-6">
            <h1 class="text-2xl font-bold">Stock Overview</h1>

            <div v-if="loading" class="text-center py-6">
                Loading...
            </div>

            <table v-else class="w-full border text-sm">
                <thead>
                    <tr>
                        <th class="p-2 text-left">Name</th>
                        <th class="p-2 text-left">SKU</th>
                        <th class="p-2 text-right">Quantity</th>
                        <th class="p-2 text-right">Reorder Point</th>
                        <th class="p-2"></th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="part in parts" :key="part.id" class="border-t">
                        <td class="p-2">
                            <Link :href="route('parts.stock.show', { part: part.id })">
                                {{ part.name }}
                            </Link>
                        </td>
                        <td class="p-2">{{ part.sku }}</td>
                        <td class="p-2 text-right">{{ part.quantity }}</td>
                        <td class="p-2 text-right">{{ part.reorder_point ?? '—' }}</td>
                        <td class="p-2 whitespace-nowrap">
                            <Link
                                :href="route('parts.stock.show', { part: part.id })"
                            >
                                View
                            </Link>
                        </td>
                    </tr>

                    <tr v-if="parts.length === 0">
                        <td colspan="5" class="p-6 text-center text-gray-500">
                            No parts found.
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="pagination.last_page > 1" class="flex justify-center mt-4 space-x-2">
                <button
                    class="px-3 py-1 border rounded"
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
                    class="px-3 py-1 border rounded"
                    :disabled="currentPage === pagination.last_page"
                    @click="goToPage(currentPage + 1)"
                >
                    Next
                </button>
            </div>
        </div>
    </AppLayout>
</template>