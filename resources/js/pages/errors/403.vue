<script setup>
import ErrorPage from './ErrorPage.vue'
</script>

<template>
    <ErrorPage 
        :code="403" 
        title="Forbidden" 
        description="You do not have permission to access this resource." 
    />
</template>