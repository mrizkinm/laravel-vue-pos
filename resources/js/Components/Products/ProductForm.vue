<template>
    <form @submit.prevent="submit" class="space-y-6">
        <div>
            <InputLabel for="name" value="Product Name" />
            <TextInput
                id="name"
                type="text"
                class="mt-1 block w-full"
                v-model="form.name"
                required
                autofocus
            />
            <InputError class="mt-2" :message="form.errors.name" />
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <InputLabel for="code" value="Product Code" />
                <TextInput
                    id="code"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.code"
                    required
                />
                <InputError class="mt-2" :message="form.errors.code" />
            </div>

            <div>
                <InputLabel for="barcode" value="Barcode (Optional)" />
                <TextInput
                    id="barcode"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.barcode"
                />
                <InputError class="mt-2" :message="form.errors.barcode" />
            </div>
        </div>

        <div>
            <InputLabel for="category" value="Category" />
            <select
                id="category"
                v-model="form.category_id"
                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                required
            >
                <option value="">Select Category</option>
                <option v-for="category in categories" :key="category.id" :value="category.id">
                    {{ category.name }}
                </option>
            </select>
            <InputError class="mt-2" :message="form.errors.category_id" />
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <InputLabel for="cost_price" value="Cost Price" />
                <TextInput
                    id="cost_price"
                    type="number"
                    step="0.01"
                    class="mt-1 block w-full"
                    v-model="form.cost_price"
                    required
                />
                <InputError class="mt-2" :message="form.errors.cost_price" />
            </div>

            <div>
                <InputLabel for="selling_price" value="Selling Price" />
                <TextInput
                    id="selling_price"
                    type="number"
                    step="0.01"
                    class="mt-1 block w-full"
                    v-model="form.selling_price"
                    required
                />
                <InputError class="mt-2" :message="form.errors.selling_price" />
            </div>
        </div>

        <div class="grid grid-cols-3 gap-4">
            <div>
                <InputLabel for="stock" value="Current Stock" />
                <TextInput
                    id="stock"
                    type="number"
                    class="mt-1 block w-full"
                    v-model="form.stock"
                    required
                />
                <InputError class="mt-2" :message="form.errors.stock" />
            </div>

            <div>
                <InputLabel for="alert_quantity" value="Alert Quantity" />
                <TextInput
                    id="alert_quantity"
                    type="number"
                    class="mt-1 block w-full"
                    v-model="form.alert_quantity"
                    required
                />
                <InputError class="mt-2" :message="form.errors.alert_quantity" />
            </div>

            <div>
                <InputLabel for="unit" value="Unit" />
                <select
                    id="unit"
                    v-model="form.unit"
                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                    required
                >
                    <option value="pcs">Pieces (pcs)</option>
                    <option value="kg">Kilogram (kg)</option>
                    <option value="g">Gram (g)</option>
                    <option value="l">Liter (l)</option>
                    <option value="ml">Milliliter (ml)</option>
                    <option value="box">Box</option>
                    <option value="pack">Pack</option>
                </select>
                <InputError class="mt-2" :message="form.errors.unit" />
            </div>
        </div>

        <div>
            <InputLabel for="description" value="Description" />
            <textarea
                id="description"
                v-model="form.description"
                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                rows="3"
            ></textarea>
            <InputError class="mt-2" :message="form.errors.description" />
        </div>

        <div>
            <InputLabel for="image" value="Product Image" />
            <div
                class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md"
                :class="{ 'border-indigo-500': isDragging }"
                @dragover.prevent="isDragging = true"
                @dragleave.prevent="isDragging = false"
                @drop.prevent="handleDrop"
            >
                <div v-if="!previewUrl" class="space-y-1 text-center">
                    <svg
                        class="mx-auto h-12 w-12 text-gray-400"
                        stroke="currentColor"
                        fill="none"
                        viewBox="0 0 48 48"
                        aria-hidden="true"
                    >
                        <path
                            d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                    <div class="flex text-sm text-gray-600">
                        <label
                            for="file-upload"
                            class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500"
                        >
                            <span>Upload a file</span>
                            <input
                                id="file-upload"
                                name="file-upload"
                                type="file"
                                class="sr-only"
                                accept="image/*"
                                @change="handleFileSelect"
                            />
                        </label>
                        <p class="pl-1">or drag and drop</p>
                    </div>
                    <p class="text-xs text-gray-500">PNG, JPG, GIF up to 2MB</p>
                </div>
                <div v-else class="relative">
                    <img
                        :src="previewUrl"
                        class="h-32 w-32 object-cover rounded-lg"
                        alt="Preview"
                    />
                    <button
                        type="button"
                        class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600 focus:outline-none"
                        @click="removeImage"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
            <InputError class="mt-2" :message="form.errors.image" />
        </div>

        <div class="flex justify-end space-x-4">
            <PrimaryButton :disabled="form.processing" class="relative">
                <span v-if="form.processing" class="absolute inset-0 flex items-center justify-center">
                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </span>
                <span :class="{ 'opacity-0': form.processing }">{{ submitLabel }}</span>
            </PrimaryButton>
            <SecondaryButton type="button" @click="$emit('cancel')">Cancel</SecondaryButton>
        </div>
    </form>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { ref } from 'vue';

const props = defineProps({
    categories: {
        type: Array,
        required: true
    },
    product: {
        type: Object,
        default: () => ({
            id: null,
            name: '',
            category_id: '',
            cost_price: '',
            selling_price: '',
            stock: '',
            description: '',
            image: null
        })
    },
    submitLabel: {
        type: String,
        default: 'Save Product'
    }
});

const emit = defineEmits(['cancel', 'submitted']);
const isDragging = ref(false);
const previewUrl = ref(props.product?.image ? `/storage/${props.product.image}` : null);

const form = useForm({
    name: props.product?.name,
    code: props.product?.code || '',
    barcode: props.product?.barcode || '',
    category_id: props.product?.category_id,
    cost_price: props.product?.cost_price,
    selling_price: props.product?.selling_price,
    stock: props.product?.stock,
    alert_quantity: props.product?.alert_quantity || 0,
    unit: props.product?.unit || 'pcs',
    description: props.product?.description,
    image: props.product?.image || null,
    _method: props.product?.id ? 'PUT' : 'POST'
});

const handleFileSelect = (event) => {
    const file = event.target.files[0];
    if (file) {
        handleFile(file);
    }
};

const handleDrop = (event) => {
    isDragging.value = false;
    const file = event.dataTransfer.files[0];
    if (file) {
        handleFile(file);
    }
};

const handleFile = (file) => {
    if (file.size > 2 * 1024 * 1024) {
        alert('File size must be less than 2MB');
        return;
    }

    if (!file.type.startsWith('image/')) {
        alert('File must be an image');
        return;
    }

    form.image = file;
    previewUrl.value = URL.createObjectURL(file);
};

const removeImage = () => {
    form.image = null;
    previewUrl.value = null;
};

const submit = () => {
    if (props.product?.id && !(form.image instanceof File)) {
        form.image = props.product.image;
    }

    if (props.product?.id) {
        form.post(route('products.update', props.product.id), {
            onSuccess: () => emit('submitted'),
            preserveScroll: true,
            forceFormData: true
        });
    } else {
        form.post(route('products.store'), {
            onSuccess: () => emit('submitted'),
            preserveScroll: true,
            forceFormData: true
        });
    }
};
</script>