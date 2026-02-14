<script setup>
import ErrorPage from './ErrorPage.vue'
</script>

<template>
    <ErrorPage 
        :code="414" 
        title="URI Too Long" 
        description="The requested URI is too long." 
    />
</template>