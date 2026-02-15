<script setup lang="ts">
import ErrorLayout from './ErrorLayout.vue'

defineProps<{
  code: number
  title: string
  description: string
}>()
</script>

<template>
  <ErrorLayout
    :code="code"
    :title="title"
    :description="description"
  />
</template>