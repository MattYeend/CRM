<script setup lang="ts">
import ErrorPage from './ErrorPage.vue'
</script>

<template>
    <ErrorPage 
        :code="431" 
        title="Request Header Fields Too Large" 
        description="Request header fields are too large." 
    />
</template>