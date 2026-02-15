<script setup lang="ts">
import ErrorPage from './ErrorPage.vue'
</script>

<template>
    <ErrorPage 
        :code="507" 
        title="Insufficient Storage" 
        description="Insufficient storage to complete the request." 
    />
</template>