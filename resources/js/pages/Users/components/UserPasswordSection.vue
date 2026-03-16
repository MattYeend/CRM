<script setup lang="ts">
    defineProps<{
        form: any
    }>()
</script>

<template>
    <div class="space-y-5">
        <h2 class="text-lg font-semibold">
            Password
        </h2>

        <div>
            <label class="block font-medium">Password</label>
            <input
                type="password"
                v-model="form.password"
                class="border rounded w-full p-2"
            />
        </div>

        <div>
            <label class="block font-medium">Confirm Password</label>
            <input
                type="password"
                v-model="form.password_confirmation"
                class="border rounded w-full p-2"
            />
        </div>
    </div>
</template>