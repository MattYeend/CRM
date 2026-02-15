<script setup lang="ts">
import ErrorLayout from './ErrorLayout.vue'

const props = defineProps({
    code: Number,
    title: String,
    description: String
})
</script>

<template>
  <ErrorLayout
    :code="code"
    :title="title"
    :description="description"
  />
</template>