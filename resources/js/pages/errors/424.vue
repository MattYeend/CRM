<script setup>
import ErrorPage from './ErrorPage.vue'
</script>

<template>
    <ErrorPage 
        :code="424" 
        title="Failed Dependency" 
        description="The request failed due to a failed dependency." 
    />
</template>