<script setup>
import ErrorPage from './ErrorPage.vue'
</script>

<template>
    <ErrorPage 
        :code="501" 
        title="Not Implemented" 
        description="This functionality is not implemented." 
    />
</template>