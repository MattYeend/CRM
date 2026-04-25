<script setup lang="ts">
interface Task {
    description?: string | null
    assignee?: { id: number; name: string } | null
    due_at?: string | null
    taskable_type: string
    taskable_name: string | null
    creator?: { id: number; name: string } | null
}

defineProps<{ task: Task }>()

function formatDate(date?: string | null) {
    if (!date) return '—'
    return new Date(date).toLocaleDateString('en-GB', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    })
}

function taskableTypeLabel(type: string): string {
    return type.split('\\').pop() ?? type
}
</script>

<template>
    <div>
        <!-- Description -->
        <div v-if="task.description" class="mb-6">
            <h2 class="text-lg font-semibold border-b pb-2 mb-3">Description</h2>
            <p class="whitespace-pre-wrap">{{ task.description }}</p>
        </div>

        <!-- Details -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 mb-6">
            <div v-if="task.assignee">
                <span class="font-semibold">Assigned To: </span>
                <span>{{ task.assignee.name }}</span>
            </div>
            <div v-if="task.due_at">
                <span class="font-semibold">Due Date: </span>
                <span>{{ formatDate(task.due_at) }}</span>
            </div>
            <div class="space-y-2 mt-2">
                <div>
                    <span class="font-semibold">Related To: </span>
                    <span v-if="task.taskable_name">
                        <span class="rounded bg-gray-100 px-1.5 py-0.5 text-xs font-medium text-gray-600">
                            {{ taskableTypeLabel(task.taskable_type) }}
                        </span>
                        {{ task.taskable_name }}
                    </span>
                    <span v-else>—</span>
                </div>
            </div>
        </div>
        <div v-if="task.creator" class="mt-6 pt-4 border-t text-sm text-gray-500">
            <span class="font-semibold">Created By: </span>
            <span>{{ task.creator.name }}</span>
        </div>
    </div>
</template>