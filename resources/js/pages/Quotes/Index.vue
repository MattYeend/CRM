<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, reactive, onMounted, computed } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { fetchQuotes, deleteQuotes } from '@/services/quoteService'

interface Quote {
    id: number
    deal?: { id: number; title: string } | null
    currency: string
    subtotal: number
    formatted_subtotal: string
    tax: number
    formatted_tax: string
    total: number
    formatted_total: string
    sent_at?: string | null
    accepted_at?: string | null
    is_sent: boolean
    is_accepted: boolean
    creator?: { name: string } | null
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

const permissions = ref<GlobalPermissions>({
    create: false,
    viewAny: false,
})

const quotes = ref<Quote[]>([])
const loading = ref(true)
const currentPage = ref(1)
const perPage = 10
const pagination = reactive({
    current_page: 1,
    last_page: 1,
    total: 0,
})

const statusClasses = {
    sent: 'bg-blue-100 text-blue-700',
    accepted: 'bg-green-100 text-green-700',
    draft: 'bg-gray-100 text-gray-600',
}

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Quotes', href: route('quotes.index') },
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

function getStatusLabel(quote: Quote): string {
    if (quote.is_accepted) return 'Accepted'
    if (quote.is_sent) return 'Sent'
    return 'Draft'
}

function getStatusClass(quote: Quote): string {
    if (quote.is_accepted) return statusClasses.accepted
    if (quote.is_sent) return statusClasses.sent
    return statusClasses.draft
}

function formatDate(date?: string | null) {
    if (!date) return '—'
    return new Date(date).toLocaleDateString('en-GB')
}

async function loadQuotes(page = 1) {
    loading.value = true
    try {
        const data = await fetchQuotes(perPage, page)
        quotes.value = data.data
        permissions.value = data.permissions
        pagination.current_page = data.current_page
        pagination.last_page = data.last_page
        pagination.total = data.total
        currentPage.value = data.current_page
    } finally {
        loading.value = false
    }
}

async function handleDelete(id: number) {
    if (!confirm('Are you sure you want to delete this quote?')) return
    await deleteQuotes(id)
    loadQuotes(currentPage.value)
}

function goToPage(page: number) {
    if (page >= 1 && page <= pagination.last_page) {
        loadQuotes(page)
    }
}

onMounted(() => loadQuotes())
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Quotes" />

        <div class="p-6">
            <div class="flex justify-between mb-6">
                <h1 class="text-2xl font-bold">Quotes</h1>
                <Link
                    v-if="permissions.create"
                    :href="route('quotes.create')"
                    class="bg-blue-600 text-white px-4 py-2 rounded"
                >
                    Create
                </Link>
            </div>

            <div v-if="loading" class="text-center py-6">
                Loading...
            </div>

            <table v-else class="w-full border">
                <thead>
                    <tr>
                        <th class="p-2 text-left">Deal</th>
                        <th class="p-2 text-left">Subtotal</th>
                        <th class="p-2 text-left">Tax</th>
                        <th class="p-2 text-left">Total</th>
                        <th class="p-2 text-left">Status</th>
                        <th class="p-2 text-left">Sent At</th>
                        <th class="p-2 text-left">Accepted At</th>
                        <th class="p-2"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="quote in quotes" :key="quote.id" class="border-t">
                        <td class="p-2">
                            <Link
                                v-if="quote.deal"
                                :href="route('deals.show', { deal: quote.deal.id })"
                            >
                                {{ quote.deal.title }}
                            </Link>
                            <span v-else>—</span>
                        </td>
                        <td class="p-2">{{ quote.formatted_subtotal }}</td>
                        <td class="p-2">{{ quote.formatted_tax }}</td>
                        <td class="p-2 font-medium">{{ quote.formatted_total }}</td>
                        <td class="p-2">
                            <span
                                class="px-2 py-0.5 rounded-full text-xs font-semibold"
                                :class="getStatusClass(quote)"
                            >
                                {{ getStatusLabel(quote) }}
                            </span>
                        </td>
                        <td class="p-2">{{ formatDate(quote.sent_at) }}</td>
                        <td class="p-2">{{ formatDate(quote.accepted_at) }}</td>
                        <td class="p-2 space-x-2 whitespace-nowrap">
                            <Link
                                v-if="quote.permissions.view"
                                :href="route('quotes.show', { quote: quote.id })"
                            >
                                View
                            </Link>
                            <Link
                                v-if="quote.permissions.update"
                                :href="route('quotes.edit', { quote: quote.id })"
                            >
                                Edit
                            </Link>
                            <button
                                v-if="quote.permissions.delete"
                                @click="handleDelete(quote.id)"
                                class="text-red-600"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>

                    <tr v-if="quotes.length === 0">
                        <td colspan="9" class="p-4 text-center text-gray-500">
                            No quotes found.
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
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