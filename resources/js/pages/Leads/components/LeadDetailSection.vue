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
    <div class="space-y-6 text-sm">
        <!-- Contact Details -->
        <div>
            <h2 class="text-lg font-semibold border-b pb-2 mb-3">Contact Details</h2>
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3">
                <div>
                    <dt class="font-semibold inline">First Name: </dt>
                    <dd class="inline">{{ lead.first_name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="font-semibold inline">Last Name: </dt>
                    <dd class="inline">{{ lead.last_name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="font-semibold inline">Email: </dt>
                    <dd class="inline">
                        <a 
                            v-if="lead.email"
                            :href="`mailto:${lead.email}`"
                            class="text-blue-600 hover:underline"
                        >
                            {{ lead.email }}
                        </a>
                        <span v-else>—</span>
                    </dd>
                </div>
                <div>
                    <dt class="font-semibold inline">Phone: </dt>
                    <dd class="inline">
                        <a
                            v-if="lead.phone"
                            :href="`tel:${lead.phone}`"
                            class="text-blue-600 hover:underline"
                        >
                            {{ lead.phone }}
                        </a>
                        <span v-else>—</span>
                    </dd>
                </div>
                <div>
                    <dt class="font-semibold inline">Source: </dt>
                    <dd class="inline">{{ lead.source ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="font-semibold inline">Age: </dt>
                    <dd class="inline">{{ lead.age_in_days }} days</dd>
                </div>
            </dl>
        </div>

        <!-- Assignment -->
        <div>
            <h2 class="text-lg font-semibold border-b pb-2 mb-3">Assignment</h2>
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3">
                <div>
                    <dt class="font-semibold inline">Owner: </dt>
                    <dd class="inline">{{ lead.owner?.name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="font-semibold inline">Assigned To: </dt>
                    <dd class="inline">{{ lead.assigned_to?.name ?? '—' }}</dd>
                </div>
                <div v-if="lead.assigned_at">
                    <dt class="font-semibold inline">Assigned At: </dt>
                    <dd class="inline">
                        <time :datetime="lead.assigned_at">
                            {{ formatDate(lead.assigned_at) }}
                        </time>
                    </dd>
                </div>
            </dl>
        </div>

        <!-- Audit Information -->
        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 text-gray-600 pt-2 border-t border-gray-200">
            <div v-if="lead.creator">
                <dt class="font-semibold inline">Created By: </dt>
                <dd class="inline">{{ lead.creator.name }}</dd>
            </div>
            <div v-if="lead.created_at">
                <dt class="font-semibold inline">Created: </dt>
                <dd class="inline">
                    <time :datetime="lead.created_at">
                        {{ formatDate(lead.created_at) }}
                    </time>
                </dd>
            </div>
            <div v-if="lead.updater">
                <dt class="font-semibold inline">Last Updated By: </dt>
                <dd class="inline">{{ lead.updater.name }}</dd>
            </div>
            <div v-if="lead.updated_at">
                <dt class="font-semibold inline">Last Updated: </dt>
                <dd class="inline">
                    <time :datetime="lead.updated_at">
                        {{ formatDate(lead.updated_at) }}
                    </time>
                </dd>
            </div>
            <div v-if="lead.deleter" class="md:col-span-2">
                <dt class="font-semibold inline">Deleted By: </dt>
                <dd class="inline text-red-600">{{ lead.deleter.name }}</dd>
            </div>
        </dl>
    </div>
</template>