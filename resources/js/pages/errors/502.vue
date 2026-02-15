<script setup>
import ErrorPage from './ErrorPage.vue'
</script>

<template>
    <ErrorPage 
        :code="502" 
        title="Bad Gateway" 
        description="Invalid response from upstream server." 
    />
</template>