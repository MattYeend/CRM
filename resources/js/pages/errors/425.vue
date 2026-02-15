<script setup lang="ts">
import ErrorPage from './ErrorPage.vue'
</script>

<template>
    <ErrorPage 
        :code="425" 
        title="Too Early" 
        description="The server is unwilling to risk processing a replayed request." 
    />
</template>