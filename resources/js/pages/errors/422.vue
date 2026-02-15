<script setup>
import ErrorPage from './ErrorPage.vue'
</script>

<template>
    <ErrorPage 
        :code="422" 
        title="Unprocessable Entity" 
        description="The request was well-formed but unable to be followed." 
    />
</template>