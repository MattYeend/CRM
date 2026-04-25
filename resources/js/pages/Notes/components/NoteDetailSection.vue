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
    creator: User | null
    meta: Record<string, unknown> | null
}

defineProps<{ note: Note }>()

function notableTypeLabel(type: string): string {
    return type.split('\\').pop() ?? type
}
</script>

<template>
    <div class="text-sm">
        <div class="space-y-2">
            <div>
                <span class="font-semibold">Body: </span>
                <span class="whitespace-pre-wrap">{{ note.body }}</span>
            </div>
        </div>

        <div class="space-y-2 mt-2">
            <div>
                <span class="font-semibold">Related To: </span>
                <span v-if="note.notable_name">
                    <span class="rounded bg-gray-100 px-1.5 py-0.5 text-xs font-medium text-gray-600">
                        {{ notableTypeLabel(note.notable_type) }}
                    </span>
                    {{ note.notable_name }}
                </span>
                <span v-else>—</span>
            </div>
        </div>

        <div class="space-y-2 mt-2">
            <div>
                <span class="font-semibold">Created By: </span>
                <span>{{ note.creator?.name ?? '—' }}</span>
            </div>
        </div>
    </div>
</template>