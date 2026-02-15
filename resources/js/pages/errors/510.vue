<script setup lang="ts">
import ErrorPage from './ErrorPage.vue'
</script>

<template>
    <ErrorPage 
        :code="510" 
        title="Not Extended" 
        description="Further extensions to the request are required." 
    />
</template>