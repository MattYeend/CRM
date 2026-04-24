<script setup lang="ts">
defineProps<{ movements: any[] }>()
</script>

<template>
    <table class="w-full border text-sm">
        <thead>
            <tr>
                <th class="p-2 text-left">Type</th>
                <th class="p-2 text-right">Qty</th>
                <th class="p-2 text-right">Before</th>
                <th class="p-2 text-right">After</th>
                <th class="p-2 text-left">Reference</th>
                <th class="p-2 text-left">By</th>
            </tr>
        </thead>

        <tbody>
            <tr v-for="m in movements" :key="m.id" class="border-t">
                <td class="p-2">
                    <span
                        class="text-xs px-2 py-0.5 rounded-full"
                        :class="m.is_inbound ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                    >
                        {{ m.type }}
                    </span>
                </td>

                <td class="p-2 text-right">
                    <span :class="m.is_inbound ? 'text-green-600' : 'text-red-600'">
                        {{ m.is_inbound ? '+' : '-' }}{{ m.quantity }}
                    </span>
                </td>

                <td class="p-2 text-right">{{ m.quantity_before }}</td>
                <td class="p-2 text-right">{{ m.quantity_after }}</td>
                <td class="p-2">{{ m.reference ?? '—' }}</td>
                <td class="p-2">{{ m.created_by?.name ?? '—' }}</td>
            </tr>
        </tbody>
    </table>
</template>