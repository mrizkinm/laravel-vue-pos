<template>
    <form @submit.prevent="submit" class="space-y-6">
        <!-- Company Information -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Company Information</h3>
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <InputLabel for="company_name" value="Company Name" />
                    <TextInput
                        id="company_name"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.company_name"
                        required
                    />
                    <InputError class="mt-2" :message="form.errors.company_name" />
                </div>

                <div>
                    <InputLabel for="tax_number" value="Tax/VAT Number" />
                    <TextInput
                        id="tax_number"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.tax_number"
                    />
                    <InputError class="mt-2" :message="form.errors.tax_number" />
                </div>

                <div>
                    <InputLabel for="phone" value="Phone Number" />
                    <TextInput
                        id="phone"
                        type="tel"
                        class="mt-1 block w-full"
                        v-model="form.phone"
                        required
                    />
                    <InputError class="mt-2" :message="form.errors.phone" />
                </div>

                <div>
                    <InputLabel for="email" value="Email" />
                    <TextInput
                        id="email"
                        type="email"
                        class="mt-1 block w-full"
                        v-model="form.email"
                        required
                    />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div class="col-span-2">
                    <InputLabel for="address" value="Address" />
                    <textarea
                        id="address"
                        v-model="form.address"
                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        rows="2"
                        required
                    ></textarea>
                    <InputError class="mt-2" :message="form.errors.address" />
                </div>

                <div class="col-span-2">
                    <InputLabel for="logo" value="Company Logo" />
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
                    <InputError class="mt-2" :message="form.errors.logo" />
                </div>
            </div>
        </div>

        <!-- Financial Settings -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Financial Settings</h3>
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <InputLabel for="currency" value="Currency" />
                    <select
                        id="currency"
                        v-model="form.currency"
                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        required
                    >
                        <option value="">Select Currency</option>
                        <option value="IDR">Indonesian Rupiah (IDR)</option>
                        <option value="USD">US Dollar (USD)</option>
                        <option value="EUR">Euro (EUR)</option>
                        <option value="GBP">British Pound (GBP)</option>
                        <option value="JPY">Japanese Yen (JPY)</option>
                        <option value="AUD">Australian Dollar (AUD)</option>
                        <option value="CAD">Canadian Dollar (CAD)</option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.currency" />
                </div>

                <div>
                    <InputLabel for="tax_rate" value="Default Tax Rate (%)" />
                    <TextInput
                        id="tax_rate"
                        type="number"
                        step="0.01"
                        class="mt-1 block w-full"
                        v-model="form.tax_rate"
                        required
                    />
                    <InputError class="mt-2" :message="form.errors.tax_rate" />
                </div>

                <div>
                    <InputLabel for="low_stock_threshold" value="Low Stock Alert Threshold" />
                    <TextInput
                        id="low_stock_threshold"
                        type="number"
                        class="mt-1 block w-full"
                        v-model="form.low_stock_threshold"
                        required
                    />
                    <InputError class="mt-2" :message="form.errors.low_stock_threshold" />
                </div>

                <div>
                    <InputLabel for="default_payment_term" value="Default Payment Term (days)" />
                    <TextInput
                        id="default_payment_term"
                        type="number"
                        class="mt-1 block w-full"
                        v-model="form.default_payment_term"
                        required
                    />
                    <InputError class="mt-2" :message="form.errors.default_payment_term" />
                </div>
            </div>
        </div>

        <!-- Receipt Settings -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Receipt Settings</h3>
            <div class="space-y-4">
                <div>
                    <InputLabel for="receipt_footer" value="Receipt Footer Message" />
                    <textarea
                        id="receipt_footer"
                        v-model="form.receipt_footer"
                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        rows="2"
                    ></textarea>
                    <InputError class="mt-2" :message="form.errors.receipt_footer" />
                </div>

                <div class="flex items-center">
                    <input
                        type="checkbox"
                        id="show_tax_number"
                        v-model="form.show_tax_number_on_receipt"
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                    >
                    <label for="show_tax_number" class="ml-2 text-sm text-gray-600">
                        Show Tax Number on Receipt
                    </label>
                </div>
            </div>
        </div>

        <div class="flex justify-end space-x-4">
            <SecondaryButton type="button" @click="$emit('cancel')">Cancel</SecondaryButton>
            <PrimaryButton :disabled="form.processing">Save Settings</PrimaryButton>
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
    settings: {
        type: Object,
        required: true
    },
    submitLabel: {
        type: String,
        default: 'Save Settings'
    }
});

const emit = defineEmits(['cancel', 'submitted']);
const isDragging = ref(false);
const previewUrl = ref(props.settings?.logo ? `/storage/${props.settings.logo}` : null);

const form = useForm({
    company_name: props.settings.company_name || '',
    tax_number: props.settings.tax_number || '',
    phone: props.settings.phone || '',
    email: props.settings.email || '',
    address: props.settings.address || '',
    logo: props.settings.logo || null,
    currency: props.settings.currency || 'USD',
    tax_rate: props.settings.tax_rate || 0,
    low_stock_threshold: props.settings.low_stock_threshold || 10,
    default_payment_term: props.settings.default_payment_term || 30,
    receipt_footer: props.settings.receipt_footer || '',
    show_tax_number_on_receipt: props.settings.show_tax_number_on_receipt || false
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

    form.logo = file;
    previewUrl.value = URL.createObjectURL(file);
};

const removeImage = () => {
    form.logo = null;
    previewUrl.value = null;
};

const submit = () => {
    if (!(form.logo instanceof File)) {
        form.logo = props.settings.logo;
    }

    form.post(route('settings.update'), {
        onSuccess: () => emit('submitted'),
        preserveScroll: true,
        forceFormData: true
    });
};
</script> 