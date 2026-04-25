<script setup lang="ts">
interface Activity {
    type: string
    description: string
    username: string
    subject_type: string
    subject_id?: number
    subject_name?: string
    creator: { name: string } | null
    updater: { name: string } | null
    deleter: { name: string } | null
    created_at: string | null
    updated_at: string | null
}

defineProps<{ activity: Activity }>()

function capitalize(str: string | null | undefined): string {
    if (!str) return '—'
    return str
        .split(' ')
        .map(s => s.charAt(0).toUpperCase() + s.slice(1))
        .join(' ')
}

function formatDate(dateStr: string | null): string {
    if (!dateStr) return '—'
    const date = new Date(dateStr)
    return date.toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    })
}
</script>

<template>
    <div class="space-y-4">
        <!-- Primary Details -->
        <dl class="space-y-4">
            <div>
                <dt class="font-semibold inline">Description: </dt>
                <dd class="inline">{{ activity.description }}</dd>
            </div>
            <div>
                <dt class="font-semibold inline">Subject Type: </dt>
                <dd class="inline">{{ capitalize(activity.subject_type) }}</dd>
            </div>
            <div v-if="activity.subject_name">
                <dt class="font-semibold inline">Subject Name: </dt>
                <dd class="inline">{{ activity.subject_name }}</dd>
            </div>
        </dl>

        <!-- Metadata Grid -->
        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 text-sm text-gray-600 pt-2 border-t border-gray-200">
            <div v-if="activity.creator">
                <dt class="font-semibold inline">Created By: </dt>
                <dd class="inline">{{ activity.creator.name }}</dd>
            </div>
            <div v-if="activity.created_at">
                <dt class="font-semibold inline">Created: </dt>
                <dd class="inline">
                    <time :datetime="activity.created_at">
                        {{ formatDate(activity.created_at) }}
                    </time>
                </dd>
            </div>
            <div v-if="activity.updater">
                <dt class="font-semibold inline">Last Updated By: </dt>
                <dd class="inline">{{ activity.updater.name }}</dd>
            </div>
            <div v-if="activity.updated_at">
                <dt class="font-semibold inline">Last Updated: </dt>
                <dd class="inline">
                    <time :datetime="activity.updated_at">
                        {{ formatDate(activity.updated_at) }}
                    </time>
                </dd>
            </div>
            <div v-if="activity.deleter" class="md:col-span-2">
                <dt class="font-semibold inline">Deleted By: </dt>
                <dd class="inline text-red-600">{{ activity.deleter.name }}</dd>
            </div>
        </dl>
    </div>
</template>