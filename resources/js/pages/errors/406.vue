<script setup lang="ts">
import ErrorPage from './ErrorPage.vue'
</script>

<template>
    <ErrorPage 
        :code="406" 
        title="Not Acceptable" 
        description="The requested format is not acceptable." 
    />
</template>