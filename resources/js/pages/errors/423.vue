<script setup lang="ts">
import ErrorPage from './ErrorPage.vue'
</script>

<template>
    <ErrorPage 
        :code="423" 
        title="Locked" 
        description="The resource is locked." 
    />
</template>