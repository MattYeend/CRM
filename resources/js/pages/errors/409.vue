<script setup lang="ts">
import ErrorPage from './ErrorPage.vue'
</script>

<template>
    <ErrorPage 
        :code="409" 
        title="Conflict" 
        description="The request conflicts with the current state of the server." 
    />
</template>