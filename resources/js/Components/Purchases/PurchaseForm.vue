<template>
    <form @submit.prevent="submit" class="space-y-6">
        <!-- Supplier Selection -->
        <div>
            <InputLabel for="supplier" value="Supplier" />
            <select
                id="supplier"
                v-model="form.supplier_id"
                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
            >
                <option value="">Select Supplier</option>
                <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">
                    {{ supplier.company }} ({{ supplier.name }})
                </option>
            </select>
            <InputError class="mt-2" :message="form.errors.supplier_id" />
        </div>

        <!-- Product Selection -->
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900">Products</h3>
                <SecondaryButton type="button" @click="addProduct">Add Product</SecondaryButton>
            </div>

            <div v-for="(item, index) in form.items" :key="index" class="bg-gray-50 p-4 rounded-lg space-y-4">
                <div class="grid grid-cols-12 gap-4">
                    <!-- Product -->
                    <div class="col-span-4">
                        <InputLabel :for="'product_' + index" value="Product" />
                        <select
                            :id="'product_' + index"
                            v-model="item.product_id"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            required
                            @change="updateProductDetails(index)"
                        >
                            <option value="">Select Product</option>
                            <option v-for="product in products" :key="product.id" :value="product.id">
                                {{ product.name }}
                            </option>
                        </select>
                        <InputError class="mt-2" :message="form.errors['items.' + index + '.product_id']" />
                    </div>

                    <!-- Quantity -->
                    <div class="col-span-2">
                        <InputLabel :for="'quantity_' + index" value="Quantity" />
                        <TextInput
                            :id="'quantity_' + index"
                            type="number"
                            class="mt-1 block w-full"
                            v-model="item.quantity"
                            required
                            min="1"
                        />
                        <InputError class="mt-2" :message="form.errors['items.' + index + '.quantity']" />
                    </div>

                    <!-- Cost Price -->
                    <div class="col-span-2">
                        <InputLabel :for="'cost_price_' + index" value="Cost Price" />
                        <TextInput
                            :id="'cost_price_' + index"
                            type="number"
                            class="mt-1 block w-full"
                            v-model="item.cost_price"
                            required
                            min="0"
                            step="0.01"
                        />
                        <InputError class="mt-2" :message="form.errors['items.' + index + '.cost_price']" />
                    </div>

                    <!-- Subtotal -->
                    <div class="col-span-3">
                        <InputLabel value="Subtotal" />
                        <div class="mt-1 text-gray-900 font-medium">
                            {{ formatPrice(item.quantity * item.cost_price) }}
                        </div>
                    </div>

                    <!-- Remove Button -->
                    <div class="col-span-1 flex items-end">
                        <button
                            type="button"
                            class="text-red-600 hover:text-red-900 text-sm"
                            @click="removeProduct(index)"
                        >
                            Remove
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Totals -->
        <div class="grid grid-cols-2 gap-6">
            <div class="space-y-4">
                <!-- Tax -->
                <div>
                    <InputLabel for="tax" value="Tax (%)" />
                    <TextInput
                        id="tax"
                        type="number"
                        class="mt-1 block w-full"
                        v-model="form.tax"
                        required
                        min="0"
                        max="100"
                        step="0.01"
                    />
                    <InputError class="mt-2" :message="form.errors.tax" />
                </div>

                <!-- Discount -->
                <div>
                    <InputLabel for="discount" value="Discount" />
                    <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                        <select
                            v-model="discountType"
                            class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        >
                            <option value="amount">Amount (Rp)</option>
                            <option value="percentage">Percentage (%)</option>
                        </select>
                        <TextInput
                            id="discount"
                            type="number"
                            class="mt-1 block w-full"
                            v-model="form.discount"
                            step="0.01"
                        />
                    </div>
                    <InputError class="mt-2" :message="form.errors.discount" />
                </div>

                <!-- Shipping -->
                <div>
                    <InputLabel for="shipping" value="Shipping" />
                    <TextInput
                        id="shipping"
                        type="number"
                        class="mt-1 block w-full"
                        v-model="form.shipping"
                        required
                        min="0"
                        step="0.01"
                    />
                    <InputError class="mt-2" :message="form.errors.shipping" />
                </div>
            </div>

            <div class="space-y-4">
                <!-- Payment Method -->
                <div>
                    <InputLabel for="payment_method" value="Payment Method" />
                    <select
                        id="payment_method"
                        v-model="form.payment_method"
                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        required
                    >
                        <option value="">Select Payment Method</option>
                        <option value="cash">Cash</option>
                        <option value="card">Card</option>
                        <option value="bank_transfer">Bank Transfer</option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.payment_method" />
                </div>

                <!-- Amount Paid -->
                <div>
                    <InputLabel for="paid_amount" value="Amount Paid" />
                    <TextInput
                        id="paid_amount"
                        type="number"
                        class="mt-1 block w-full"
                        v-model="form.paid_amount"
                        required
                        min="0"
                        step="0.01"
                    />
                    <InputError class="mt-2" :message="form.errors.paid_amount" />
                </div>

                <!-- Notes -->
                <div>
                    <InputLabel for="note" value="Notes" />
                    <textarea
                        id="note"
                        v-model="form.note"
                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        rows="3"
                    ></textarea>
                    <InputError class="mt-2" :message="form.errors.note" />
                </div>
            </div>
        </div>

        <!-- Summary -->
        <div class="bg-gray-50 p-4 rounded-lg">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <div class="text-sm text-gray-600">Total Items: {{ form.items?.length }}</div>
                    <div class="text-sm text-gray-600">Total Quantity: {{ totalQuantity }}</div>
                </div>
                <div class="space-y-1">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Subtotal:</span>
                        <span class="text-sm font-medium">{{ formatPrice(subtotal) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Tax ({{ form.tax }}%):</span>
                        <span class="text-sm font-medium">{{ formatPrice(taxAmount) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Discount {{ discountType === 'percentage' ? `(${form.discount}%)` : '' }}:</span>
                        <span class="text-sm font-medium">{{ formatPrice(discountAmount) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Shipping:</span>
                        <span class="text-sm font-medium">{{ formatPrice(form.shipping) }}</span>
                    </div>
                    <div class="flex justify-between border-t border-gray-200 pt-1">
                        <span class="text-sm font-medium">Grand Total:</span>
                        <span class="text-sm font-medium">{{ formatPrice(grandTotal) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Amount Paid:</span>
                        <span class="text-sm font-medium">{{ formatPrice(form.paid_amount) }}</span>
                    </div>
                    <div class="flex justify-between border-t border-gray-200 pt-1">
                        <span class="text-sm font-medium">Due Amount:</span>
                        <span class="text-sm font-medium">{{ formatPrice(dueAmount) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end space-x-4">
            <SecondaryButton type="button" @click="$emit('cancel')">Cancel</SecondaryButton>
            <PrimaryButton :disabled="form.processing">Create Purchase</PrimaryButton>
        </div>
    </form>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    suppliers: {
        type: Array,
        required: true
    },
    products: {
        type: Array,
        required: true
    }
});

const emit = defineEmits(['cancel', 'submitted']);

const form = useForm({
    supplier_id: '',
    items: [],
    tax: 0,
    discount: 0,
    shipping: 0,
    paid_amount: 0,
    payment_method: 'cash',
    note: ''
});

const addProduct = () => {
    form.items.push({
        product_id: '',
        quantity: 1,
        cost_price: 0
    });
};

const removeProduct = (index) => {
    form.items.splice(index, 1);
};

const updateProductDetails = (index) => {
    const product = props.products.find(p => p.id === form.items[index].product_id);
    if (product) {
        form.items[index].cost_price = product.cost_price;
    }
};

const subtotal = computed(() => {
    return form.items.reduce((total, item) => total + (item.quantity * item.cost_price), 0);
});

const taxAmount = computed(() => {
    return (subtotal.value * Number(form.tax)) / 100;
});

const discountType = ref('amount');

const discountAmount = computed(() => {
    if (discountType.value === 'percentage') {
        return (subtotal.value * Number(form.discount)) / 100;
    }
    return Number(form.discount);
});

const grandTotal = computed(() => {
    return subtotal.value + taxAmount.value + Number(form.shipping) - discountAmount.value;
});

const dueAmount = computed(() => {
    return grandTotal.value - Number(form.paid_amount);
});

const totalQuantity = computed(() => {
    return form.items.reduce((total, item) => total + Number(item.quantity), 0);
});

const submit = () => {
    // Transform items to match backend expectations
    const transformedItems = form.items.map(item => ({
        product_id: item.product_id,
        quantity: item.quantity,
        cost_price: item.cost_price
    }));

    form.discount = discountAmount.value;
    form.shipping = parseFloat(form.shipping) || 0;
    form.paid_amount = parseFloat(form.paid_amount) || 0;

    const formData = {
        supplier_id: form.supplier_id || null,
        items: transformedItems,
        tax: form.tax,
        discount: form.discount,
        shipping: form.shipping,
        paid_amount: form.paid_amount,
        payment_method: form.payment_method,
        note: form.note || ''
    };

    // console.log('Final FormData:', form);
    // return;

    form.post(route('purchases.store'), {
        onSuccess: () => {
            form.reset();
            emit('submitted')
        },
        onFinish: () => {
            console.log('finish');
        },
        onError: (errors) => {
            console.log(errors);
        },
        onCancel: () => {
            console.log('cancel');
        },
        preserveScroll: true
    });
};

const formatPrice = (price) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(price);
};
</script> 