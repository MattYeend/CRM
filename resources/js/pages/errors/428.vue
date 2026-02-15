<script setup>
import ErrorPage from './ErrorPage.vue'
</script>

<template>
    <ErrorPage 
        :code="428" 
        title="Precondition Required" 
        description="Precondition required." 
    />
</template>