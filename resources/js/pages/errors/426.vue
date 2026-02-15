<script setup>
import ErrorPage from './ErrorPage.vue'
</script>

<template>
    <ErrorPage 
        :code="426" 
        title="Upgrade Required" 
        description="Upgrade to a different protocol is required." 
    />
</template>