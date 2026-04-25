<script setup lang="ts">
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
    owner: { id: number; name: string } | null
    assigned_to: { id: number; name: string } | null
    creator: { name: string } | null
    updater: { name: string } | null
    deleter: { name: string } | null
    created_at: string | null
    updated_at: string | null
}

defineProps<{ lead: Lead }>()

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
    <div class="text-sm">
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
                        class="text-blue-600"
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
</template>