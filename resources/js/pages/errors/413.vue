<script setup lang="ts">
import ErrorPage from './ErrorPage.vue'
</script>

<template>
    <ErrorPage 
        :code="413" 
        title="Payload Too Large" 
        description="The request payload is too large." 
    />
</template>