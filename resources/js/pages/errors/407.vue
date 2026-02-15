<script setup lang="ts">
import ErrorPage from './ErrorPage.vue'
</script>

<template>
    <ErrorPage 
        :code="407" 
        title="Proxy Authentication Required" 
        description="Proxy authentication is required." 
    />
</template>