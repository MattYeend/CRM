<script setup lang="ts">
interface Industry {
    slug: string
}

defineProps<{ industry: Industry }>()
</script>

<template>
    <div class="space-y-3">
        <div>
            <span class="font-semibold">Slug: </span>
            <span>{{ industry.slug }}</span>
        </div>
    </div>
</template>