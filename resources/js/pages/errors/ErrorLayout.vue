<script setup lang="ts">
    defineProps({
        code: Number,
        title: String,
        description: String
    })
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-100">
        <div class="text-center p-10 bg-white shadow rounded-xl max-w-lg w-full">
            <h1 class="text-6xl font-bold mb-4">{{ code }}</h1>
            <h2 class="text-2xl font-semibold mb-2">{{ title }}</h2>
            <p class="text-gray-600 mb-6">
                {{ description }}
            </p>
            <router-link
                to="/"
                class="px-6 py-2 bg-black text-white rounded hover:bg-gray-800"
            >
                Go Home
            </router-link>
        </div>
    </div>
</template>