<script setup>
import ErrorPage from './ErrorPage.vue'
</script>

<template>
    <ErrorPage 
        :code="404" 
        title="Not Found" 
        description="The requested resource could not be found." 
    />
</template>