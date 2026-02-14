<script setup>
import ErrorPage from './ErrorPage.vue'
</script>

<template>
    <ErrorPage 
        :code="405" 
        title="Method Not Allowed" 
        description="This HTTP method is not allowed for this route." 
    />
</template>