<script setup>
import ErrorPage from './ErrorPage.vue'
</script>

<template>
    <ErrorPage 
        :code="429" 
        title="Too Many Requests" 
        description="Too many requests have been made in a given amount of time." 
    />
</template>