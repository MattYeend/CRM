<script setup lang="ts">
import ErrorPage from './ErrorPage.vue'
</script>

<template>
    <ErrorPage 
        :code="504" 
        title="Gateway Timeout" 
        description="The upstream server failed to respond in time." 
    />
</template>