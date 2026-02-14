<script setup>
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