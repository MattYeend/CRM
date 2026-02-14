<script setup>
import ErrorPage from './ErrorPage.vue'
</script>

<template>
    <ErrorPage 
        :code="401" 
        title="Unauthorized" 
        description="Authentication is required." 
    />
</template>