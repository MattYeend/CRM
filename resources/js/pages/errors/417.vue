<script setup lang="ts">
import ErrorPage from './ErrorPage.vue'
</script>

<template>
    <ErrorPage 
        :code="417" 
        title="Expectation Failed" 
        description="Expectation failed." 
    />
</template>