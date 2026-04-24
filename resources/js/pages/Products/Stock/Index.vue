<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
import { route } from 'ziggy-js'

defineProps<{ products: any }>()
</script>

<template>
    <AppLayout>
        <Head title="Product Stock" />

        <div class="p-6">
            <h1 class="text-2xl font-bold mb-4">Product Stock</h1>

            <table class="w-full border">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th class="text-right">Stock</th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="p in products.data" :key="p.id">
                        <td>
                            <Link :href="route('products.stock.show', { product: p.id })">
                                {{ p.name }}
                            </Link>
                        </td>
                        <td class="text-right">{{ p.stock ?? '—' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>