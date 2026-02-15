<script setup lang="ts">
import ErrorPage from './ErrorPage.vue'
</script>

<template>
    <ErrorPage 
        :code="411" 
        title="Length Required" 
        description="Content-Length header is required." 
    />
</template>
