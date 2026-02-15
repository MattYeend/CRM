<script setup>
import ErrorPage from './ErrorPage.vue'
</script>

<template>
    <ErrorPage 
        :code="503" 
        title="Service Unavailable" 
        description="The server is currently unavailable." 
    />
</template>