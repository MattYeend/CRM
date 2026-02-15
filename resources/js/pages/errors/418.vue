<script setup lang="ts">
import ErrorPage from './ErrorPage.vue'
</script>

<template>
    <ErrorPage 
        :code="418" 
        title="I'm a teapot ☕" 
        description="The server refuses to brew coffee because it is a teapot." 
    />
</template>