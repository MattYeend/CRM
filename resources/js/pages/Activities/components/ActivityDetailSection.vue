<script setup lang="ts">
interface Activity {
    type: string
    description: string
    username: string
    subject_type: string
    subject_id?: number
    subject_name?: string
    creator?: { name: string }
}

defineProps<{ activity: Activity }>()

function capitalize(str: string | null | undefined) {
    if (!str) return '—'
    return str
        .split(' ')
        .map(s => s.charAt(0).toUpperCase() + s.slice(1))
        .join(' ')
}
</script>

<template>
    <div class="space-y-4">
        <div>
            <span class="font-semibold">Description: </span>
            <span>{{ activity.description }}</span>
        </div>
        <div>
            <span class="font-semibold">Subject Type: </span>
            <span>{{ capitalize(activity.subject_type) }}</span>
        </div>
        <div v-if="activity.subject_name">
            <span class="font-semibold">Subject Name: </span>
            <span>{{ activity.subject_name }}</span>
        </div>
        <div v-if="activity.creator">
            <span class="font-semibold">Created By: </span>
            <span>{{ activity.creator.name }}</span>
        </div>
    </div>
</template>