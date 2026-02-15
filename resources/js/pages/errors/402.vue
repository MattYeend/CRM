<script setup lang="ts">
import ErrorPage from './ErrorPage.vue'
</script>

<template>
    <ErrorPage 
        :code="402" 
        title="Payment Required" 
        description="Payment is required to access this resource." 
    />
</template>