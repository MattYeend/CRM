<script setup lang="ts">
const props = defineProps<{ 
    form: any 
}>()

const form = props.form
</script>

<template>
    <div class="grid grid-cols-3 gap-4">
        <input type="number" v-model="form.subtotal" />
        <input type="number" v-model="form.tax" />
        <input type="number" v-model="form.total" />
    </div>
</template>