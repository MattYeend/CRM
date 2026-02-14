<script setup>
import ErrorPage from './ErrorPage.vue'
</script>

<template>
    <ErrorPage 
        :code="408" 
        title="Request Timeout" 
        description="The request timed out." 
    />
</template>