<script setup lang="ts">
defineProps<{ movements: any[] }>()
</script>

<template>
    <div class="mt-6">
        <h3 class="text-lg font-semibold mb-4">Movement History</h3>
        
        <div class="border rounded overflow-hidden">
            <table class="w-full text-sm">
                <thead class="border-b">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">Type</th>
                        <th class="px-4 py-3 text-right font-semibold">Quantity</th>
                        <th class="px-4 py-3 text-right font-semibold">Before</th>
                        <th class="px-4 py-3 text-right font-semibold">After</th>
                        <th class="px-4 py-3 text-left font-semibold">Reference</th>
                        <th class="px-4 py-3 text-left font-semibold">Created By</th>
                        <th class="px-4 py-3 text-left font-semibold">Date</th>
                    </tr>
                </thead>

                <tbody>
                    <tr 
                        v-for="m in movements" 
                        :key="m.id" 
                        class="border-t"
                    >
                        <td class="px-4 py-3">
                            <span
                                class="text-xs px-2 py-1 rounded-full font-semibold"
                                :class="m.is_inbound 
                                    ? 'bg-green-100 text-green-700' 
                                    : 'bg-red-100 text-red-700'"
                            >
                                {{ m.type?.charAt(0).toUpperCase() + m.type?.slice(1) }}
                            </span>
                        </td>

                        <td class="px-4 py-3 text-right font-medium">
                            <span :class="m.is_inbound ? 'text-green-600' : 'text-red-600'">
                                {{ m.is_inbound ? '+' : '-' }}{{ m.quantity }}
                            </span>
                        </td>

                        <td class="px-4 py-3 text-right">{{ m.quantity_before }}</td>
                        <td class="px-4 py-3 text-right font-medium">{{ m.quantity_after }}</td>
                        <td class="px-4 py-3">{{ m.reference ?? '—' }}</td>
                        <td class="px-4 py-3">{{ m.created_by?.name ?? '—' }}</td>
                        <td class="px-4 py-3 text-xs">
                            {{ new Date(m.created_at).toLocaleString() }}
                        </td>
                    </tr>

                    <tr v-if="movements.length === 0">
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                            No stock movements recorded yet.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>