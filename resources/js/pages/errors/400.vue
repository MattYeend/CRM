<script setup lang="ts">
import ErrorPage from './ErrorPage.vue'
</script>

<template>
    <ErrorPage
        :code="400"
        title="Bad Request"
        description="The request could not be understood by the server."
    />
</template>