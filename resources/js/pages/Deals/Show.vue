<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, onMounted } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { fetchDeal, deleteDeals } from '@/services/dealService'

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

function formatCurrency(value: number, currency: string) {
    return new Intl.NumberFormat('en-GB', { style: 'currency', currency }).format(value)
}

function formatDate(date?: string | null) {
    if (!date) return '—'
    return new Date(date).toLocaleDateString('en-GB')
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

                <!-- Details -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 mb-6">
                    <div>
                        <span class="font-semibold">Value: </span>
                        <span>{{ formatCurrency(deal.value, deal.currency) }}</span>
                    </div>
                    <div>
                        <span class="font-semibold">Close Date: </span>
                        <span>{{ formatDate(deal.close_date) }}</span>
                    </div>
                    <div v-if="deal.company">
                        <span class="font-semibold">Company: </span>
                        <Link
                            :href="route('companies.show', { company: deal.company.id })"
                        >
                            {{ deal.company.name }}
                        </Link>
                    </div>
                    <div v-if="deal.owner">
                        <span class="font-semibold">Owner: </span>
                        <span>{{ deal.owner.name }}</span>
                    </div>
                    <div v-if="deal.pipeline">
                        <span class="font-semibold">Pipeline: </span>
                        <span>{{ deal.pipeline.name }}</span>
                    </div>
                    <div v-if="deal.stage">
                        <span class="font-semibold">Stage: </span>
                        <span>{{ deal.stage.name }}</span>
                    </div>
                    <div v-if="deal.creator">
                        <span class="font-semibold">Created By: </span>
                        <span>{{ deal.creator.name }}</span>
                    </div>
                </div>

                <!-- Products -->
                <div v-if="deal.products && deal.products.length > 0">
                    <h2 class="text-lg font-semibold border-b pb-2 mb-3">Products</h2>
                    <table class="w-full border text-sm">
                        <thead>
                            <tr>
                                <th class="p-2 text-left">Product</th>
                                <th class="p-2 text-right">Qty</th>
                                <th class="p-2 text-right">Price</th>
                                <th class="p-2 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="product in deal.products" :key="product.id" class="border-t">
                                <td class="p-2">{{ product.name }}</td>
                                <td class="p-2 text-right">{{ product.pivot?.quantity ?? 1 }}</td>
                                <td class="p-2 text-right">{{ formatCurrency(product.pivot?.price ?? 0, deal.currency) }}</td>
                                <td class="p-2 text-right">{{ formatCurrency(product.pivot?.total ?? 0, deal.currency) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </AppLayout>
</template>