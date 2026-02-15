<script setup>
import ErrorPage from './ErrorPage.vue'
</script>

<template>
    <ErrorPage 
        :code="421" 
        title="Misdirected Request" 
        description="The request was directed at a server that cannot produce a response." 
    />
</template>