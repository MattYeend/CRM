<script setup  lang="ts">
import ErrorPage from './ErrorPage.vue'
</script>

<template>
    <ErrorPage 
        :code="500" 
        title="Internal Server Error" 
        description="Something went wrong on the server."
    />
</template>