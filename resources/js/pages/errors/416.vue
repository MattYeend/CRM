<script setup>
import ErrorPage from './ErrorPage.vue'
</script>

<template>
    <ErrorPage 
        :code="416" 
        title="Range Not Satisfiable" 
        description="The requested range is not satisfiable." 
    />
</template>