<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, computed, onMounted } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import { fetchLead, deleteLeads } from '@/services/leadService'

interface UserPermissions {
    view: boolean
    update: boolean
    delete: boolean
}

interface Lead {
    id: number
    title: string
    first_name: string | null
    last_name: string | null
    full_name: string
    display_name: string
    email: string | null
    phone: string | null
    contact_info: string
    source: string | null
    age_in_days: number
    assigned_at: string | null
    meta: Record<string, any> | null
    is_stale: boolean
    is_hot: boolean
    is_high_priority: boolean
    is_low_priority: boolean
    is_eligible_for_conversion: boolean
    owner: { id: number; name: string } | null
    assigned_to: { id: number; name: string } | null
    creator: { name: string } | null
    updater: { name: string } | null
    deleter: { name: string } | null
    created_at: string | null
    updated_at: string | null
    permissions: UserPermissions
}

const props = defineProps<{ lead: any }>()

const lead = ref<Lead>({
    id: props.lead.id,
    title: props.lead.title ?? '',
    first_name: props.lead.first_name ?? null,
    last_name: props.lead.last_name ?? null,
    full_name: props.lead.full_name ?? '',
    display_name: props.lead.display_name ?? '',
    email: props.lead.email ?? null,
    phone: props.lead.phone ?? null,
    contact_info: props.lead.contact_info ?? '',
    source: props.lead.source ?? null,
    age_in_days: props.lead.age_in_days ?? 0,
    assigned_at: props.lead.assigned_at ?? null,
    meta: props.lead.meta ?? null,
    is_stale: props.lead.is_stale ?? false,
    is_hot: props.lead.is_hot ?? false,
    is_high_priority: props.lead.is_high_priority ?? false,
    is_low_priority: props.lead.is_low_priority ?? false,
    is_eligible_for_conversion: props.lead.is_eligible_for_conversion ?? false,
    owner: props.lead.owner ?? null,
    assigned_to: props.lead.assigned_to ?? null,
    creator: props.lead.creator ?? null,
    updater: props.lead.updater ?? null,
    deleter: props.lead.deleter ?? null,
    created_at: props.lead.created_at ?? null,
    updated_at: props.lead.updated_at ?? null,
    permissions: props.lead.permissions ?? { view: false, update: false, delete: false },
})

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Leads', href: route('leads.index') },
    { title: lead.value.display_name || 'View Lead', href: route('leads.show', { lead: lead.value.id }) },
]

const statusLabel = computed(() => {
    if (lead.value.is_hot) return { label: 'Hot', classes: 'bg-red-100 text-red-700' }
    if (lead.value.is_high_priority) return { label: 'High Priority', classes: 'bg-orange-100 text-orange-700' }
    if (lead.value.is_eligible_for_conversion) return { label: 'Eligible for Conversion', classes: 'bg-purple-100 text-purple-700' }
    if (lead.value.is_stale) return { label: 'Stale', classes: 'bg-gray-100 text-gray-600' }
    if (lead.value.is_low_priority) return { label: 'Low Priority', classes: 'bg-blue-100 text-blue-600' }
    return { label: 'Active', classes: 'bg-green-100 text-green-700' }
})

async function loadLead() {
    const data = await fetchLead(lead.value.id)
    Object.assign(lead.value, data)
}

async function handleDelete() {
    if (!confirm('Are you sure you want to delete this lead?')) return
    await deleteLeads(lead.value.id)
    window.location.href = route('leads.index')
}

function formatDate(dateStr: string | null): string {
    if (!dateStr) return '—'
    return new Date(dateStr).toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    })
}

onMounted(() => loadLead())
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="lead.display_name || 'Lead'" />

        <div class="p-6">
            <div class="mx-auto border p-6 rounded shadow">

                <!-- Header -->
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <h1 class="text-2xl font-bold">{{ lead.display_name }}</h1>
                        <div class="flex items-center gap-2 mt-1 flex-wrap">
                            <span
                                v-if="lead.title"
                                class="text-sm text-gray-500 italic"
                            >
                                {{ lead.title }}
                            </span>
                            <span
                                class="px-2 py-0.5 rounded-full text-xs font-semibold"
                                :class="statusLabel.classes"
                            >
                                {{ statusLabel.label }}
                            </span>
                            <span
                                v-if="lead.is_eligible_for_conversion && !lead.is_hot"
                                class="px-2 py-0.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-700"
                            >
                                Eligible for Conversion
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center space-x-2">
                        <Link
                            v-if="lead.permissions.update"
                            :href="route('leads.edit', { lead: lead.id })"
                            class="bg-blue-600 text-white px-4 py-2 rounded"
                        >
                            Edit
                        </Link>
                        <Link
                            :href="route('leads.index')"
                            class="bg-gray-200 text-gray-700 px-4 py-2 rounded"
                        >
                            Back
                        </Link>
                        <button
                            v-if="lead.permissions.delete"
                            @click="handleDelete"
                            class="bg-red-600 text-white px-4 py-2 rounded"
                        >
                            Delete
                        </button>
                    </div>
                </div>

                <!-- Contact Details -->
                <div class="mb-6">
                    <h2 class="text-lg font-semibold border-b pb-2 mb-3">Contact Details</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3">
                        <div>
                            <span class="font-semibold">First Name: </span>
                            <span>{{ lead.first_name ?? '—' }}</span>
                        </div>
                        <div>
                            <span class="font-semibold">Last Name: </span>
                            <span>{{ lead.last_name ?? '—' }}</span>
                        </div>
                        <div>
                            <span class="font-semibold">Email: </span>
                            <a
                                v-if="lead.email"
                                :href="`mailto:${lead.email}`"
                                class="text-blue-600 underline"
                            >
                                {{ lead.email }}
                            </a>
                            <span v-else>—</span>
                        </div>
                        <div>
                            <span class="font-semibold">Phone: </span>
                            <a
                                v-if="lead.phone"
                                :href="`tel:${lead.phone}`"
                                class="text-blue-600 underline"
                            >
                                {{ lead.phone }}
                            </a>
                            <span v-else>—</span>
                        </div>
                        <div>
                            <span class="font-semibold">Source: </span>
                            <span>{{ lead.source ?? '—' }}</span>
                        </div>
                        <div>
                            <span class="font-semibold">Age: </span>
                            <span>{{ lead.age_in_days }} days</span>
                        </div>
                    </div>
                </div>

                <!-- Assignment -->
                <div class="mb-6">
                    <h2 class="text-lg font-semibold border-b pb-2 mb-3">Assignment</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3">
                        <div>
                            <span class="font-semibold">Owner: </span>
                            <span>{{ lead.owner?.name ?? '—' }}</span>
                        </div>
                        <div>
                            <span class="font-semibold">Assigned To: </span>
                            <span>{{ lead.assigned_to?.name ?? '—' }}</span>
                        </div>
                        <div v-if="lead.assigned_at">
                            <span class="font-semibold">Assigned At: </span>
                            <span>{{ formatDate(lead.assigned_at) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Audit -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 text-sm text-gray-600">
                    <div v-if="lead.creator">
                        <span class="font-semibold">Created By: </span>
                        <span>{{ lead.creator.name }}</span>
                    </div>
                    <div v-if="lead.created_at">
                        <span class="font-semibold">Created: </span>
                        <span>{{ formatDate(lead.created_at) }}</span>
                    </div>
                    <div v-if="lead.updater">
                        <span class="font-semibold">Last Updated By: </span>
                        <span>{{ lead.updater.name }}</span>
                    </div>
                    <div v-if="lead.updated_at">
                        <span class="font-semibold">Last Updated: </span>
                        <span>{{ formatDate(lead.updated_at) }}</span>
                    </div>
                    <div v-if="lead.deleter">
                        <span class="font-semibold">Deleted By: </span>
                        <span class="text-red-600">{{ lead.deleter.name }}</span>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>