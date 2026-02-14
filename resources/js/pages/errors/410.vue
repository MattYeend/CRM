<script setup>
import ErrorPage from './ErrorPage.vue'
</script>

<template>
    <ErrorPage 
        :code="410" 
        title="Gone" 
        description="The resource requested is no longer available." 
    />
</template>