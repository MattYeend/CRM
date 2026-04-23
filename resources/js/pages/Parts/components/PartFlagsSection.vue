<script setup lang="ts">
interface PartForm {
    is_active: boolean
    is_purchasable: boolean
    is_sellable: boolean
    is_manufactured: boolean
    is_serialised: boolean
    is_batch_tracked: boolean
    errors: Record<string, string>
    [key: string]: any
}

const props = defineProps<{ form: PartForm }>()
const form = props.form

const flags: { key: keyof PartForm; label: string; description: string }[] = [
    { key: 'is_active', label: 'Active', description: 'This part is currently active' },
    { key: 'is_purchasable', label: 'Purchasable', description: 'This part can be purchased from a supplier' },
    { key: 'is_sellable', label: 'Sellable', description: 'This part can be sold to a customer' },
    { key: 'is_manufactured', label: 'Manufactured', description: 'This part is manufactured in-house' },
    { key: 'is_serialised', label: 'Serialised', description: 'Track individual serial numbers for this part' },
    { key: 'is_batch_tracked', label: 'Batch Tracked', description: 'Track this part by batch or lot number' },
]
</script>

<template>
    <div class="space-y-4">
        <h2 class="text-lg font-semibold border-b pb-2">Flags</h2>

        <div class="space-y-3">
            <label
                v-for="flag in flags"
                :key="flag.key"
                class="flex items-start gap-3 cursor-pointer"
            >
                <input
                    v-model="form[flag.key]"
                    type="checkbox"
                    class="mt-0.5 h-4 w-4 rounded border-gray-300"
                />
                <div>
                    <span class="text-sm font-medium">{{ flag.label }}</span>
                    <p class="text-xs text-gray-500">{{ flag.description }}</p>
                </div>
            </label>
        </div>
    </div>
</template>