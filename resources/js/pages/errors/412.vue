<script setup lang="ts">
import ErrorPage from './ErrorPage.vue'
</script>

<template>
    <ErrorPage 
        :code="412" 
        title="Precondition Failed" 
        description="One or more preconditions failed." 
    />
</template>