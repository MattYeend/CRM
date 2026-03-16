<script setup lang="ts">
    import { router } from '@inertiajs/vue3'

    const props = defineProps<{
        userId: number
    }>()

    const confirmDelete = () => {
        if (!confirm('Are you sure you want to delete this user?')) {
            return
        }

        router.delete(`/users/${props.userId}`, {
            preserveScroll: true
        })
}
</script>

<template>
    <button
        type="button"
        @click="confirmDelete"
        class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700"
    >
    Delete User
    </button>
</template>