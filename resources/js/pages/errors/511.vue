<script setup lang="ts">
import ErrorPage from './ErrorPage.vue'
</script>

<template>
    <ErrorPage 
        :code="511" 
        title="Network Authentication Required" 
        description="Network authentication required." 
    />
</template>