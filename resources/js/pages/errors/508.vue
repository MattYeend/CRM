<script setup>
import ErrorPage from './ErrorPage.vue'
</script>

<template>
    <ErrorPage 
        :code="508" 
        title="Loop Detected" 
        description="Infinite loop detected while processing the request." 
    />
</template>