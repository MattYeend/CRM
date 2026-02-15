<script setup lang="ts">
import ErrorPage from './ErrorPage.vue'
</script>

<template>
    <ErrorPage 
        :code="451" 
        title="Unavailable For Legal Reasons" 
        description="This resource is unavailable for legal reasons." 
    />
</template>