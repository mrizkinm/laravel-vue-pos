<template>
    <Head title="Sales" />
    <MainLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Sales</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Tabs -->
                <div class="mb-6">
                    <nav class="flex space-x-4">
                        <button
                            @click="activeTab = 'new'"
                            :class="[
                                'px-3 py-2 rounded-md text-sm font-medium',
                                activeTab === 'new'
                                    ? 'bg-indigo-100 text-indigo-700'
                                    : 'text-gray-500 hover:text-gray-700'
                            ]"
                        >
                            New Sale
                        </button>
                        <button
                            @click="activeTab = 'history'"
                            :class="[
                                'px-3 py-2 rounded-md text-sm font-medium',
                                activeTab === 'history'
                                    ? 'bg-indigo-100 text-indigo-700'
                                    : 'text-gray-500 hover:text-gray-700'
                            ]"
                        >
                            Sales History
                        </button>
                    </nav>
                </div>

                <!-- New Sale Tab -->
                <div v-if="activeTab === 'new'" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <SaleForm
                            :products="products"
                            :customers="customers"
                            :categories="categories"
                            @submitted="onSubmitted"
                            @cancel="closeDetailsModal"
                        />
                    </div>
                </div>

                <!-- Sales History Tab -->
                <div v-if="activeTab === 'history'" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <!-- Filters -->
                        <div class="mb-6 flex items-center space-x-4">
                            <TextInput
                                type="text"
                                v-model="search"
                                placeholder="Search sales..."
                                class="w-64"
                                @input="debouncedSearch"
                            />
                            <div class="flex items-center space-x-2">
                                <label class="text-sm text-gray-600">From:</label>
                                <TextInput
                                    type="date"
                                    v-model="dateRange.start"
                                    @change="filterByDate"
                                />
                            </div>
                            <div class="flex items-center space-x-2">
                                <label class="text-sm text-gray-600">To:</label>
                                <TextInput
                                    type="date"
                                    v-model="dateRange.end"
                                    @change="filterByDate"
                                />
                            </div>
                        </div>

                        <!-- Sales Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-if="sales.data.length === 0">
                                        <td colspan="7" class="px-6 py-4 whitespace-nowrap text-center">No sales found</td>
                                    </tr>
                                    <tr v-for="sale in sales.data" :key="sale.id">
                                        <td class="px-6 py-4 whitespace-nowrap">{{ formatDate(sale.created_at) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ sale.reference_no }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ sale?.customer?.name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ sale.sale_details_count }} items</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ formatPrice(sale.grand_total) }}</div>
                                            <div v-if="sale.payment_status === 'partial'" class="text-sm text-gray-500">
                                                Paid: {{ formatPrice(sale.paid_amount) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="{
                                                'px-2 inline-flex text-xs leading-5 font-semibold rounded-full': true,
                                                'bg-green-100 text-green-800': sale.payment_status === 'paid',
                                                'bg-yellow-100 text-yellow-800': sale.payment_status === 'partial',
                                                'bg-red-100 text-red-800': sale.payment_status === 'pending'
                                            }">
                                                {{ sale.payment_status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <button
                                                @click="viewSaleDetails(sale)"
                                                class="text-indigo-600 hover:text-indigo-900"
                                            >
                                                View
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <Pagination :links="sales.links" class="flex justify-end mt-6" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Sale Details Modal -->
        <Modal :show="showDetailsModal" @close="closeDetailsModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Sale Details</h2>
                
                <div v-if="selectedSale" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Invoice Number</p>
                            <p class="font-medium">{{ selectedSale.reference_no }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Date</p>
                            <p class="font-medium">{{ formatDate(selectedSale.created_at) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Customer</p>
                            <p class="font-medium">{{ selectedSale?.customer?.name || 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Payment Method</p>
                            <p class="font-medium capitalize">{{ selectedSale.payment_method }}</p>
                        </div>
                    </div>

                    <div class="mt-6">
                        <h3 class="text-sm font-medium text-gray-900 mb-2">Items</h3>
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-for="item in selectedSale.sale_details" :key="item.id">
                                    <td class="px-4 py-2">{{ item.product.name }}</td>
                                    <td class="px-4 py-2">{{ item.quantity }}</td>
                                    <td class="px-4 py-2">{{ formatPrice(item.unit_price) }}</td>
                                    <td class="px-4 py-2">{{ formatPrice(item.quantity * item.unit_price) }}</td>
                                </tr>
                            </tbody>
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td colspan="3" class="px-4 py-2 text-sm font-medium text-gray-900">Subtotal</td>
                                    <td class="px-4 py-2 text-sm font-medium text-gray-900">
                                        {{ formatPrice(selectedSale.total) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="px-4 py-2 text-sm font-medium text-gray-900">Tax ({{ (selectedSale.tax / selectedSale.total * 100).toFixed(2) }}%)</td>
                                    <td class="px-4 py-2 text-sm font-medium text-gray-900">
                                        {{ formatPrice(selectedSale.tax) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="px-4 py-2 text-sm font-medium text-gray-900">Shipping</td>
                                    <td class="px-4 py-2 text-sm font-medium text-gray-900">
                                        {{ formatPrice(selectedSale.shipping) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="px-4 py-2 text-sm font-medium text-gray-900">Discount</td>
                                    <td class="px-4 py-2 text-sm font-medium text-gray-900">
                                        {{ formatPrice(selectedSale.discount) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="px-4 py-2 text-sm font-medium text-gray-900">Total</td>
                                    <td class="px-4 py-2 text-sm font-medium text-gray-900">
                                        {{ formatPrice(selectedSale.grand_total) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Payment Details</h3>
                            <p class="mt-1 text-sm text-gray-900">Method: {{ selectedSale.payment_method }}</p>
                            <p class="text-sm text-gray-900">Status: {{ selectedSale.payment_status }}</p>
                            <p v-if="selectedSale.paid_amount" class="text-sm text-gray-900">
                                Amount Paid: {{ formatPrice(selectedSale.paid_amount) }}
                            </p>
                            <p v-if="selectedSale.due_amount" class="text-sm text-gray-900">
                                Due Amount: {{ formatPrice(selectedSale.due_amount) }}
                            </p>
                        </div>
                        <div v-if="selectedSale.notes">
                            <h3 class="text-sm font-medium text-gray-500">Notes</h3>
                            <p class="mt-1 text-sm text-gray-900">{{ selectedSale.notes }}</p>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeDetailsModal">Close</SecondaryButton>
                </div>
            </div>
        </Modal>
    </MainLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import debounce from 'lodash/debounce';
import MainLayout from '@/Layouts/MainLayout.vue';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Pagination from '@/Components/Pagination.vue';
import SaleForm from '@/Components/Sales/SaleForm.vue';

const props = defineProps({
    sales: Object,
    products: {
        type: Array,
        required: true,
        default: () => []
    },
    customers: {
        type: Array,
        required: true,
        default: () => []
    },
    categories: {
        type: Array,
        required: true,
        default: () => []
    },
    filters: Object,
    summary: Object
});

const activeTab = ref('new');
const search = ref(props.filters.search || '');
const dateRange = ref(props.filters.date_range ? {
    start: props.filters.date_range[0],
    end: props.filters.date_range[1]
} : {
    start: '',
    end: ''
});
const showDetailsModal = ref(false);
const selectedSale = ref(null);

const currentFilters = computed(() => ({
    search: search.value,
    date_range: dateRange.value.start && dateRange.value.end ? [dateRange.value.start, dateRange.value.end] : null
}));

const debouncedSearch = debounce(() => {
    router.get(route('sales.index'), currentFilters.value, {
        preserveState: true,
        preserveScroll: true
    });
}, 300);

const filterByDate = () => {
    router.get(route('sales.index'), currentFilters.value, {
        preserveState: true,
        preserveScroll: true
    });
};

const viewSaleDetails = (sale) => {
    selectedSale.value = sale;
    showDetailsModal.value = true;
};

const closeDetailsModal = () => {
    showDetailsModal.value = false;
    selectedSale.value = null;
};

const onSubmitted = () => {
    console.log('Sale submitted');
    // Optionally switch to history tab after sale
    activeTab.value = 'history';
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const formatPrice = (price) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(price);
};
</script> 