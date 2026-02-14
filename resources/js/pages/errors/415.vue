<script setup>
import ErrorPage from './ErrorPage.vue'
</script>

<template>
    <ErrorPage 
        :code="415" 
        title="Unsupported Media Type" 
        description="Unsupported media type." 
    />
</template>