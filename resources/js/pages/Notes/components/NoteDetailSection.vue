<script setup lang="ts">
interface User {
    id: number
    name: string
    email?: string
}

interface Notable {
    id: number
    name?: string
    title?: string
}

interface Note {
    id: number
    body: string
    notable_type: string
    notable_id: number
    notable_name: string | null
    notable: Notable | null
    user: User | null
    meta: Record<string, unknown> | null
    creator: { name: string } | null
    updater: { name: string } | null
    deleter: { name: string } | null
    created_at: string | null
    updated_at: string | null
}

defineProps<{ note: Note }>()

function notableTypeLabel(type: string): string {
    return type.split('\\').pop() ?? type
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
    <div class="space-y-6 text-sm">
        <!-- Note Content -->
        <dl class="space-y-3">
            <div>
                <dt class="font-semibold">Body</dt>
                <dd class="whitespace-pre-wrap mt-1">{{ note.body }}</dd>
            </div>

            <div>
                <dt class="font-semibold">Related To</dt>
                <dd class="mt-1">
                    <span v-if="note.notable_name">
                        <span class="rounded bg-gray-100 px-1.5 py-0.5 text-xs font-medium text-gray-600">
                            {{ notableTypeLabel(note.notable_type) }}
                        </span>
                        {{ note.notable_name }}
                    </span>
                    <span v-else>—</span>
                </dd>
            </div>
        </dl>

        <!-- Audit Information -->
        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 text-gray-600 pt-2 border-t border-gray-200">
            <div v-if="note.creator">
                <dt class="font-semibold inline">Created By: </dt>
                <dd class="inline">{{ note.creator.name }}</dd>
            </div>
            <div v-if="note.created_at">
                <dt class="font-semibold inline">Created: </dt>
                <dd class="inline">
                    <time :datetime="note.created_at">
                        {{ formatDate(note.created_at) }}
                    </time>
                </dd>
            </div>
            <div v-if="note.updater">
                <dt class="font-semibold inline">Last Updated By: </dt>
                <dd class="inline">{{ note.updater.name }}</dd>
            </div>
            <div v-if="note.updated_at">
                <dt class="font-semibold inline">Last Updated: </dt>
                <dd class="inline">
                    <time :datetime="note.updated_at">
                        {{ formatDate(note.updated_at) }}
                    </time>
                </dd>
            </div>
            <div v-if="note.deleter" class="md:col-span-2">
                <dt class="font-semibold inline">Deleted By: </dt>
                <dd class="inline text-red-600">{{ note.deleter.name }}</dd>
            </div>
        </dl>
    </div>
</template>