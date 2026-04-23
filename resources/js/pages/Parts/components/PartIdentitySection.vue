<script setup lang="ts">
interface Category {
    id: number
    name: string
}

interface Product {
    id: number
    name: string
}

interface PartForm {
    sku: string
    part_number: string
    barcode: string
    name: string
    description: string
    brand: string
    manufacturer: string
    type: string
    status: string
    unit_of_measure: string
    colour: string
    material: string
    category_id: number | null
    product_id: number | null
    errors: Record<string, string>
    [key: string]: any
}

const props = defineProps<{
    form: PartForm
    categories: Category[]
    products: Product[]
}>()

const form = props.form
</script>

<template>
    <div class="space-y-4">
        <h2 class="text-lg font-semibold border-b pb-2">Part Details</h2>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">
                    Name <span class="text-red-500">*</span>
                </label>
                <input
                    v-model="form.name"
                    type="text"
                    class="w-full border rounded px-3 py-2"
                    placeholder="Widget Assembly"
                />
                <p v-if="form.errors.name" class="text-red-500 text-sm mt-1">
                    {{ form.errors.name }}
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">SKU</label>
                <input
                    v-model="form.sku"
                    type="text"
                    class="w-full border rounded px-3 py-2"
                    placeholder="WGT-001"
                />
                <p v-if="form.errors.sku" class="text-red-500 text-sm mt-1">
                    {{ form.errors.sku }}
                </p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Part Number</label>
                <input
                    v-model="form.part_number"
                    type="text"
                    class="w-full border rounded px-3 py-2"
                    placeholder="PN-12345"
                />
                <p v-if="form.errors.part_number" class="text-red-500 text-sm mt-1">
                    {{ form.errors.part_number }}
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Barcode</label>
                <input
                    v-model="form.barcode"
                    type="text"
                    class="w-full border rounded px-3 py-2"
                    placeholder="1234567890123"
                />
                <p v-if="form.errors.barcode" class="text-red-500 text-sm mt-1">
                    {{ form.errors.barcode }}
                </p>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Description</label>
            <textarea
                v-model="form.description"
                class="w-full border rounded px-3 py-2"
                rows="3"
                placeholder="A brief description of this part..."
            />
            <p v-if="form.errors.description" class="text-red-500 text-sm mt-1">
                {{ form.errors.description }}
            </p>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Category</label>
                <select v-model="form.category_id" class="w-full border rounded px-3 py-2">
                    <option :value="null">-- Select a category --</option>
                    <option
                        v-for="category in categories"
                        :key="category.id"
                        :value="category.id"
                    >
                        {{ category.name }}
                    </option>
                </select>
                <p v-if="form.errors.category_id" class="text-red-500 text-sm mt-1">
                    {{ form.errors.category_id }}
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Product</label>
                <select v-model="form.product_id" class="w-full border rounded px-3 py-2">
                    <option :value="null">-- Select a product --</option>
                    <option
                        v-for="product in products"
                        :key="product.id"
                        :value="product.id"
                    >
                        {{ product.name }}
                    </option>
                </select>
                <p v-if="form.errors.product_id" class="text-red-500 text-sm mt-1">
                    {{ form.errors.product_id }}
                </p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Brand</label>
                <input
                    v-model="form.brand"
                    type="text"
                    class="w-full border rounded px-3 py-2"
                    placeholder="Acme"
                />
                <p v-if="form.errors.brand" class="text-red-500 text-sm mt-1">
                    {{ form.errors.brand }}
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Manufacturer</label>
                <input
                    v-model="form.manufacturer"
                    type="text"
                    class="w-full border rounded px-3 py-2"
                    placeholder="Acme Corp"
                />
                <p v-if="form.errors.manufacturer" class="text-red-500 text-sm mt-1">
                    {{ form.errors.manufacturer }}
                </p>
            </div>
        </div>

        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Type</label>
                <input
                    v-model="form.type"
                    type="text"
                    class="w-full border rounded px-3 py-2"
                    placeholder="Assembly"
                />
                <p v-if="form.errors.type" class="text-red-500 text-sm mt-1">
                    {{ form.errors.type }}
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Status</label>
                <select v-model="form.status" class="w-full border rounded px-3 py-2">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="discontinued">Discontinued</option>
                </select>
                <p v-if="form.errors.status" class="text-red-500 text-sm mt-1">
                    {{ form.errors.status }}
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Unit of Measure</label>
                <input
                    v-model="form.unit_of_measure"
                    type="text"
                    class="w-full border rounded px-3 py-2"
                    placeholder="each"
                />
                <p v-if="form.errors.unit_of_measure" class="text-red-500 text-sm mt-1">
                    {{ form.errors.unit_of_measure }}
                </p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Colour</label>
                <input
                    v-model="form.colour"
                    type="text"
                    class="w-full border rounded px-3 py-2"
                    placeholder="Black"
                />
                <p v-if="form.errors.colour" class="text-red-500 text-sm mt-1">
                    {{ form.errors.colour }}
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Material</label>
                <input
                    v-model="form.material"
                    type="text"
                    class="w-full border rounded px-3 py-2"
                    placeholder="Aluminium"
                />
                <p v-if="form.errors.material" class="text-red-500 text-sm mt-1">
                    {{ form.errors.material }}
                </p>
            </div>
        </div>
    </div>
</template>