<script setup lang="ts">
const props = defineProps<{
    form: any
    errors?: Partial<Record<'password' | 'password_confirmation', string>>
}>()

const form = props.form
</script>

<template>
    <div class="space-y-5">
        <h2 class="text-lg font-semibold">Password</h2>

        <div>
            <label class="block font-medium">Password</label>
            <input
                type="password"
                v-model="form.password"
                :class="{ 'border-red-500': errors?.password_confirmation }"
            />
            <p v-if="errors?.password" class="mt-1 text-sm text-red-600">
                {{ errors.password }}
            </p>
        </div>

        <div>
            <label class="block font-medium">Confirm Password</label>
            <input
                type="password"
                v-model="form.password_confirmation"
                :class="{ 'border-red-500': errors?.password_confirmation }"
            />
            <p v-if="errors?.password_confirmation" class="mt-1 text-sm text-red-600">
                {{ errors.password_confirmation }}
            </p>
        </div>
    </div>
</template>