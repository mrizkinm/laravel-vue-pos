<template>
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 lg:gap-6">
        <!-- Product Selection Section -->
        <div class="lg:col-span-8">
            <div class="bg-white rounded-lg shadow p-4 lg:p-6">
                <div class="mb-4 lg:mb-6">
                    <div class="flex flex-col lg:flex-row space-y-2 lg:space-y-0 lg:space-x-4">
                        <TextInput
                            type="text"
                            v-model="searchQuery"
                            placeholder="Search products..."
                            class="w-full"
                            @input="searchProducts"
                        />
                        <select
                            v-model="selectedCategory"
                            class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full lg:w-48"
                            @change="filterByCategory"
                        >
                            <option value="">All Categories</option>
                            <option v-for="category in categories" :key="category.id" :value="category.id">
                                {{ category.name }}
                            </option>
                        </select>
                    </div>
                </div>
                <div class="overflow-y-auto h-[600px] lg:h-[870px]">
                    <div class="grid grid-cols-1 sm:grid-cols-4 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        <div v-if="filteredProducts.length === 0" class="col-span-full">
                            <div class="text-center text-gray-500">No products found</div>
                        </div>
                        <div
                            v-for="product in filteredProducts"
                            :key="product.id"
                            class="border rounded-lg p-3 lg:p-4 cursor-pointer hover:shadow-md transition-shadow"
                            @click="addToCart(product)"
                        >
                            <img :src="product.image ? `${$page.props.baseURL}/${product.image}` : '/images/placeholder.png'"  :alt="product.name" class="w-full h-24 lg:h-32 object-cover rounded-md mb-2">
                            <h3 class="font-semibold text-gray-800 text-sm lg:text-base truncate">{{ product.name }}</h3>
                            <p :class="['text-sm', product.stock < product.alert_quantity ? 'text-red-600' : 'text-gray-600']">Stock: {{ product.stock }}</p>
                            <p class="text-indigo-600 font-semibold text-sm lg:text-base">{{ formatPrice(product.selling_price) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cart Section -->
        <div class="lg:col-span-4">
            <div class="bg-white rounded-lg shadow p-4 lg:p-6">
                <h2 class="text-lg font-semibold mb-4">Current Sale</h2>
                
                <!-- Error Alert -->
                <div v-if="flashError" class="mb-4 p-4 bg-red-50 border border-red-200 rounded-md">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700">{{ flashError }}</p>
                        </div>
                    </div>
                </div>

                <!-- Cart Items -->
                <div class="space-y-3 lg:space-y-4 mb-4 lg:mb-6 max-h-[300px] overflow-y-auto">
                    <div v-if="form.items.length === 0">
                        <div class="text-center text-gray-500">No items in cart</div>
                    </div>
                    <div v-for="(item, index) in form.items" :key="index" class="flex items-center justify-between p-2 bg-gray-50 rounded-lg">
                        <div class="flex-1 min-w-0">
                            <h4 class="font-medium text-sm lg:text-base truncate">{{ item.product.name }}</h4>
                            <div class="flex items-center space-x-2 mt-1">
                                <button
                                    type="button"
                                    @click="decrementQuantity(index)"
                                    class="text-gray-500 hover:text-gray-700 p-1"
                                >
                                    -
                                </button>
                                <span class="text-sm lg:text-base">{{ item.quantity }}</span>
                                <button
                                    type="button"
                                    @click="incrementQuantity(index)"
                                    class="text-gray-500 hover:text-gray-700 p-1"
                                >
                                    +
                                </button>
                            </div>
                        </div>
                        <div class="text-right ml-4">
                            <p class="font-medium text-sm lg:text-base">{{ formatPrice(item.quantity * item.selling_price) }}</p>
                            <button
                                type="button"
                                @click="removeItem(index)"
                                class="text-red-600 hover:text-red-800 text-xs lg:text-sm"
                            >
                                Remove
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Customer Selection -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Customer</label>
                    <select
                        v-model="form.customer_id"
                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                    >
                        <option value="">Select Customer</option>
                        <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                            {{ customer.name }}
                        </option>
                    </select>
                    <InputError :message="form.errors.customer_id" class="mt-2" />
                </div>

                <!-- Payment Details -->
                <div class="mt-4 space-y-3 lg:space-y-4">
                    <!-- Payment Method -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Payment Method</label>
                        <select
                            v-model="form.payment_method"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        >
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="bank_transfer">Bank Transfer</option>
                        </select>
                        <InputError :message="form.errors.payment_method" class="mt-2" />
                    </div>

                    <!-- Amount Paid -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Amount Paid</label>
                        <TextInput
                            type="number"
                            step="0.01"
                            v-model="form.paid_amount"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.paid_amount" class="mt-2" />
                    </div>

                    <!-- Tax -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tax (%)</label>
                        <TextInput
                            type="number"
                            step="0.01"
                            v-model="form.tax"
                            min="0"
                            max="100"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.tax" class="mt-2" />
                    </div>

                    <!-- Shipping -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Shipping</label>
                        <TextInput
                            type="number"
                            step="0.01"
                            v-model="form.shipping"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.shipping" class="mt-2" />
                    </div>

                    <!-- Discount -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Discount</label>
                        <div class="flex flex-col lg:flex-row space-y-2 lg:space-y-0 lg:space-x-4">
                            <select
                                v-model="discountType"
                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full lg:w-40"
                            >
                                <option value="amount">Amount (Rp)</option>
                                <option value="percentage">Percentage (%)</option>
                            </select>
                            <TextInput
                                type="number"
                                step="0.01"
                                v-model="form.discount"
                                class="mt-1 block w-full"
                            />
                        </div>
                        <InputError :message="form.errors.discount" class="mt-2" />
                    </div>

                    <!-- Note -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Note</label>
                        <TextInput
                            type="text"
                            v-model="form.note"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.note" class="mt-2" />
                    </div>
                </div>

                <!-- Totals -->
                <div class="border-t pt-4 space-y-2">
                    <div class="flex justify-between text-sm lg:text-base">
                        <span class="text-gray-600">Subtotal</span>
                        <span class="font-medium">{{ formatPrice(subtotal) }}</span>
                    </div>
                    <div class="flex justify-between text-sm lg:text-base">
                        <span class="text-gray-600">Tax ({{ form.tax }}%)</span>
                        <span class="font-medium">{{ formatPrice(taxAmount) }}</span>
                    </div>
                    <div class="flex justify-between text-sm lg:text-base">
                        <span class="text-gray-600">Discount</span>
                        <span class="font-medium">{{ formatPrice(discountAmount) }}</span>
                    </div>
                    <div class="flex justify-between text-sm lg:text-base">
                        <span class="text-gray-600">Shipping</span>
                        <span class="font-medium">{{ formatPrice(form.shipping) }}</span>
                    </div>
                    <div class="flex justify-between text-sm lg:text-base">
                        <span class="text-gray-600">Paid Amount</span>
                        <span class="font-medium">{{ formatPrice(form.paid_amount) }}</span>
                    </div>
                    <div class="flex justify-between text-sm lg:text-base">
                        <span class="text-gray-600">Change</span>
                        <span class="font-medium">{{ formatPrice(change) }}</span>
                    </div>
                    <div class="flex justify-between text-base lg:text-lg font-semibold">
                        <span>Grand Total</span>
                        <span>{{ formatPrice(grandTotal) }}</span>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="mt-6">
                    <PrimaryButton
                        class="w-full justify-center"
                        :disabled="form.processing || !form.items.length"
                        @click="processSale"
                    >
                        Complete Sale
                    </PrimaryButton>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import debounce from 'lodash/debounce';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    products: {
        type: Array,
        default: () => [],
        required: true
    },
    categories: {
        type: Array,
        required: true
    },
    customers: {
        type: Array,
        required: true
    }
});

const emit = defineEmits(['cancel', 'submitted']);

const searchQuery = ref('');
const selectedCategory = ref('');
const filteredProducts = ref([...props.products]);

const page = usePage();
const flashError = computed(() => page.props.flash?.error || null);

const form = useForm({
    customer_id: '',
    items: [],
    payment_method: 'cash',
    paid_amount: 0,
    note: '',
    tax: 0,
    shipping: 0,
    discount: 0
});

const searchProducts = debounce(() => {
    filteredProducts.value = props.products.filter(product => {
        const matchesSearch = product.name.toLowerCase().includes(searchQuery.value.toLowerCase());
        const matchesCategory = !selectedCategory.value || product.category_id === selectedCategory.value;
        return matchesSearch && matchesCategory;
    });
}, 300);

const filterByCategory = () => {
    searchProducts();
};

const addToCart = (product) => {
    const existingItem = form.items.find(item => item.product_id === product.id);
    
    if (existingItem) {
        if (existingItem.quantity < product.stock) {
            existingItem.quantity++;
        }
    } else {
        form.items.push({
            product_id: product.id,
            product: product,
            quantity: 1,
            selling_price: parseFloat(product.selling_price)
        });
    }
};

const removeItem = (index) => {
    form.items.splice(index, 1);
};

const incrementQuantity = (index) => {
    const item = form.items[index];
    if (item.quantity < item.product.stock) {
        item.quantity++;
    }
};

const decrementQuantity = (index) => {
    const item = form.items[index];
    if (item.quantity > 1) {
        item.quantity--;
    } else {
        removeItem(index);
    }
};

const subtotal = computed(() => {
    return form.items.reduce((total, item) => total + (item.quantity * item.selling_price), 0);
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

const change = computed(() => {
    return Number(form.paid_amount) - grandTotal.value;
});

const processSale = () => {
    // Transform items to match backend expectations
    const transformedItems = form.items.map(item => ({
        product_id: item.product_id,
        quantity: item.quantity,
        selling_price: item.selling_price
    }));

    form.discount = discountAmount.value;
    form.shipping = parseFloat(form.shipping) || 0;
    form.paid_amount = parseFloat(form.paid_amount) || 0;

    // Ensure all required fields are present
    const formData = {
        customer_id: form.customer_id || null,
        items: transformedItems,
        tax: form.tax,
        discount: form.discount,
        shipping: form.shipping,
        paid_amount: form.paid_amount,
        payment_method: form.payment_method,
        note: form.note || '',
    };

    // console.log(form);
    // return;

    form.post(route('sales.store'), {
        onSuccess: () => {
            form.reset();
            emit('submitted')
        },
        onFinish: () => {
            console.log('finish');
        },
        onCancel: () => {
            console.log('cancel');
        },
        onError: (errors) => {
            console.log(errors);
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

// Watch for changes in search query or category
watch([searchQuery, selectedCategory], () => {
    searchProducts();
});
</script> 