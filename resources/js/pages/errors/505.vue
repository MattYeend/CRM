<script setup lang="ts">
import ErrorPage from './ErrorPage.vue'
</script>

<template>
    <ErrorPage 
        :code="505" 
        title="HTTP Version Not Supported" 
        description="HTTP version not supported." 
    />
</template>