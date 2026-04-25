<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, onMounted } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { fetchDeal, deleteDeals } from '@/services/dealService'
import DealDetailSection from './components/DealDetailSection.vue'

interface UserPermissions {
    view: boolean
    update: boolean
    delete: boolean
}

interface Deal {
    id: number
    title: string
    status: 'open' | 'won' | 'lost' | 'archived'
    value: number
    currency: string
    close_date?: string | null
    company?: { id: number; name: string } | null
    owner?: { id: number; name: string } | null
    pipeline?: { id: number; name: string } | null
    stage?: { id: number; name: string } | null
    products?: Array<{
        id: number
        name: string
        pivot?: { quantity: number; price: number; total: number }
    }>
    creator?: { name: string }
    permissions: UserPermissions
}

const statusClasses: Record<string, string> = {
    open: 'bg-blue-100 text-blue-700',
    won: 'bg-green-100 text-green-700',
    lost: 'bg-red-100 text-red-700',
    archived: 'bg-gray-100 text-gray-600',
}

const props = defineProps<{ deal: any }>()

const deal = ref<Deal>({
    id: props.deal.id,
    title: props.deal.title,
    status: props.deal.status ?? 'open',
    value: props.deal.value ?? 0,
    currency: props.deal.currency ?? 'USD',
    close_date: props.deal.close_date,
    company: props.deal.company,
    owner: props.deal.owner,
    pipeline: props.deal.pipeline,
    stage: props.deal.stage,
    products: props.deal.products ?? [],
    creator: props.deal.creator,
    permissions: props.deal.permissions ?? { view: false, update: false, delete: false },
})

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Deals', href: route('deals.index') },
    { title: props.deal.title, href: route('deals.show', { deal: deal.value.id }) },
]

async function loadDeal() {
    const data = await fetchDeal(deal.value.id)
    Object.assign(deal.value, data)
}

async function handleDelete() {
    if (!confirm('Are you sure you want to delete this deal?')) return
    await deleteDeals(deal.value.id)
    window.location.href = route('deals.index')
}

onMounted(() => loadDeal())
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="deal.title || 'Deal'" />
        <div class="p-6">
            <div class="mx-auto border p-6 rounded shadow">

                <!-- Header -->
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <h1 class="text-2xl font-bold">{{ deal.title }}</h1>
                        <span
                            class="mt-1 inline-block px-2 py-0.5 rounded-full text-xs font-semibold capitalize"
                            :class="statusClasses[deal.status] ?? 'bg-gray-100 text-gray-600'"
                        >
                            {{ deal.status }}
                        </span>
                    </div>

                    <div class="flex items-center space-x-2">
                        <Link
                            v-if="deal.permissions?.update"
                            :href="route('deals.edit', { deal: deal.id })"
                            class="bg-blue-600 text-white px-4 py-2 rounded"
                        >
                            Edit
                        </Link>
                        <Link
                            :href="route('deals.products.index', { deal: deal.id })"
                            class="bg-gray-200 text-gray-700 px-4 py-2 rounded"
                        >
                            Products
                        </Link>
                        <Link
                            :href="route('deals.index')"
                            class="bg-gray-200 text-gray-700 px-4 py-2 rounded"
                        >
                            Back
                        </Link>
                        <button
                            v-if="deal.permissions?.delete"
                            @click="handleDelete"
                            class="bg-red-600 text-white px-4 py-2 rounded"
                        >
                            Delete
                        </button>
                    </div>
                </div>

                <DealDetailSection :deal="deal" />

            </div>
        </div>
    </AppLayout>
</template>