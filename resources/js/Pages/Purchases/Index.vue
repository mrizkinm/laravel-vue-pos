<template>
    <Head title="Purchases" />
    <MainLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Purchases</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <!-- Header Actions -->
                        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center space-y-4 lg:space-y-0 mb-6">
                            <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-4 sm:space-y-0 sm:space-x-4 w-full lg:w-auto">
                                <TextInput
                                    type="text"
                                    v-model="search"
                                    placeholder="Search purchases..."
                                    class="w-full sm:w-64"
                                    @input="debouncedSearch"
                                />
                                <select
                                    v-model="status"
                                    class="w-full sm:w-auto border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    @change="filterPurchases"
                                >
                                    <option value="">All Statuses</option>
                                    <option value="paid">Paid</option>
                                    <option value="pending">Pending</option>
                                    <option value="partial">Partial</option>
                                </select>
                                <div class="flex items-center space-x-2 w-full sm:w-auto">
                                    <input
                                        type="date"
                                        v-model="dateRange.start"
                                        class="w-full sm:w-auto border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        @change="filterPurchases"
                                    />
                                    <span class="text-gray-500">to</span>
                                    <input
                                        type="date"
                                        v-model="dateRange.end"
                                        class="w-full sm:w-auto border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        @change="filterPurchases"
                                    />
                                </div>
                            </div>
                            <PrimaryButton class="w-full sm:w-auto" @click="showCreateModal = true">New Purchase</PrimaryButton>
                        </div>

                        <!-- Summary Cards -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-6">
                            <div class="bg-white rounded-lg shadow p-6">
                                <h3 class="text-sm font-medium text-gray-500">Total Purchases</h3>
                                <p class="mt-2 text-3xl font-semibold text-gray-900">{{ formatPrice(summary.total) }}</p>
                                <p class="mt-1 text-sm text-gray-600">{{ summary.count }} orders</p>
                            </div>
                            <div class="bg-white rounded-lg shadow p-6">
                                <h3 class="text-sm font-medium text-gray-500">Paid</h3>
                                <p class="mt-2 text-3xl font-semibold text-green-600">{{ formatPrice(summary.paid) }}</p>
                                <p class="mt-1 text-sm text-gray-600">{{ summary.paid_count }} orders</p>
                            </div>
                            <div class="bg-white rounded-lg shadow p-6">
                                <h3 class="text-sm font-medium text-gray-500">Pending</h3>
                                <p class="mt-2 text-3xl font-semibold text-yellow-600">{{ formatPrice(summary.pending) }}</p>
                                <p class="mt-1 text-sm text-gray-600">{{ summary.pending_count }} orders</p>
                            </div>
                            <div class="bg-white rounded-lg shadow p-6">
                                <h3 class="text-sm font-medium text-gray-500">This Month</h3>
                                <p class="mt-2 text-3xl font-semibold text-indigo-600">{{ formatPrice(summary.this_month) }}</p>
                                <p class="mt-1 text-sm text-gray-600">{{ summary.this_month_count }} orders</p>
                            </div>
                        </div>

                        <!-- Purchases Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-if="purchases.data.length === 0">
                                        <td colspan="7" class="px-6 py-4 whitespace-nowrap text-center">No purchases found</td>
                                    </tr>
                                    <tr v-for="purchase in purchases.data" :key="purchase.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ formatDate(purchase.created_at) }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ purchase.reference_no }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-900">{{ purchase.supplier.company }}</div>
                                            <div class="text-sm text-gray-500">{{ purchase.supplier.name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ purchase.purchase_details_count }} items</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ formatPrice(purchase.grand_total) }}</div>
                                            <div v-if="purchase.payment_status === 'partial'" class="text-sm text-gray-500">
                                                Paid: {{ formatPrice(purchase.paid_amount) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="{
                                                'px-2 inline-flex text-xs leading-5 font-semibold rounded-full': true,
                                                'bg-green-100 text-green-800': purchase.payment_status === 'paid',
                                                'bg-yellow-100 text-yellow-800': purchase.payment_status === 'partial',
                                                'bg-red-100 text-red-800': purchase.payment_status === 'pending'
                                            }">
                                                {{ purchase.payment_status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                            <button
                                                @click="viewPurchase(purchase)"
                                                class="text-indigo-600 hover:text-indigo-900"
                                            >
                                                View
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <Pagination :links="purchases.links" class="flex justify-end mt-6" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <Modal :show="showCreateModal || showEditModal" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-6">
                    {{ showEditModal ? 'Edit Purchase' : 'New Purchase' }}
                </h2>
                <PurchaseForm
                    :suppliers="suppliers"
                    :products="products"
                    :purchase="editingPurchase"
                    :submit-label="showEditModal ? 'Update Purchase' : 'Create Purchase'"
                    @submitted="onSubmitted"
                    @cancel="closeModal"
                />
            </div>
        </Modal>

        <!-- View Purchase Modal -->
        <Modal :show="showViewModal" @close="closeViewModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Purchase Details</h2>
                <div v-if="selectedPurchase" class="space-y-6">
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Supplier</h3>
                            <p class="mt-1 text-sm text-gray-900">{{ selectedPurchase.supplier.company }}</p>
                            <p class="text-sm text-gray-600">{{ selectedPurchase.supplier.name }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Invoice Details</h3>
                            <p class="mt-1 text-sm text-gray-900">{{ selectedPurchase.reference_no }}</p>
                            <p class="text-sm text-gray-600">{{ formatDate(selectedPurchase.created_at) }}</p>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Items</h3>
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Cost Price</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-for="item in selectedPurchase.purchase_details" :key="item.id">
                                    <td class="px-4 py-2 text-sm text-gray-900">{{ item.product.name }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-900">{{ item.quantity }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-900">{{ formatPrice(item.unit_price) }}</td>
                                    <td class="px-4 py-2 text-sm font-medium text-gray-900">
                                        {{ formatPrice(item.quantity * item.unit_price) }}
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td colspan="3" class="px-4 py-2 text-sm font-medium text-gray-900">Subtotal</td>
                                    <td class="px-4 py-2 text-sm font-medium text-gray-900">
                                        {{ formatPrice(selectedPurchase.total) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="px-4 py-2 text-sm font-medium text-gray-900">
                                        Tax ({{ (selectedPurchase.tax / selectedPurchase.total * 100).toFixed(2) }}%)
                                    </td>
                                    <td class="px-4 py-2 text-sm font-medium text-gray-900">
                                        {{ formatPrice(selectedPurchase.tax) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="px-4 py-2 text-sm font-medium text-gray-900">Shipping</td>
                                    <td class="px-4 py-2 text-sm font-medium text-gray-900">
                                        {{ formatPrice(selectedPurchase.shipping) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="px-4 py-2 text-sm font-medium text-gray-900">Discount</td>
                                    <td class="px-4 py-2 text-sm font-medium text-gray-900">
                                        {{ formatPrice(selectedPurchase.discount) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="px-4 py-2 text-sm font-medium text-gray-900">Total</td>
                                    <td class="px-4 py-2 text-sm font-medium text-gray-900">
                                        {{ formatPrice(selectedPurchase.grand_total) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Payment Details</h3>
                            <p class="mt-1 text-sm text-gray-900">Method: {{ selectedPurchase.payment_method }}</p>
                            <p class="text-sm text-gray-900">Status: {{ selectedPurchase.payment_status }}</p>
                            <p v-if="selectedPurchase.paid_amount" class="text-sm text-gray-900">
                                Amount Paid: {{ formatPrice(selectedPurchase.paid_amount) }}
                            </p>
                            <p class="text-sm text-gray-900">
                                Due Amount: {{ formatPrice(selectedPurchase.due_amount) }}
                            </p>
                        </div>
                        <div v-if="selectedPurchase.notes">
                            <h3 class="text-sm font-medium text-gray-500">Notes</h3>
                            <p class="mt-1 text-sm text-gray-900">{{ selectedPurchase.notes }}</p>
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeViewModal">Close</SecondaryButton>
                </div>
            </div>
        </Modal>
    </MainLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import debounce from 'lodash/debounce';
import MainLayout from '@/Layouts/MainLayout.vue';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Pagination from '@/Components/Pagination.vue';
import PurchaseForm from '@/Components/Purchases/PurchaseForm.vue';

const props = defineProps({
    purchases: Object,
    suppliers: Array,
    products: Array,
    filters: Object,
    summary: Object
});

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');
const dateRange = ref(props.filters.date_range ? {
    start: props.filters.date_range[0],
    end: props.filters.date_range[1]
} : {
    start: '',
    end: ''
});

const showCreateModal = ref(false);
const showEditModal = ref(false);
const showViewModal = ref(false);
const editingPurchase = ref(null);
const selectedPurchase = ref(null);

const currentFilters = computed(() => ({
    search: search.value,
    status: status.value,
    date_range: dateRange.value.start && dateRange.value.end ? [dateRange.value.start, dateRange.value.end] : null
}));

const debouncedSearch = debounce(() => {
    router.get(route('purchases.index'), currentFilters.value, {
        preserveState: true,
        preserveScroll: true
    });
}, 300);

const filterPurchases = () => {
    router.get(route('purchases.index'), currentFilters.value, {
        preserveState: true,
        preserveScroll: true
    });
};

const closeModal = () => {
    console.log('closeModal Purchases');
    showCreateModal.value = false;
    showEditModal.value = false;
    editingPurchase.value = null;
};

const editPurchase = (purchase) => {
    editingPurchase.value = purchase;
    showEditModal.value = true;
};

const viewPurchase = (purchase) => {
    selectedPurchase.value = purchase;
    showViewModal.value = true;
};

const closeViewModal = () => {
    showViewModal.value = false;
    selectedPurchase.value = null;
};

const onSubmitted = () => {
    console.log('Purchase submitted');
    router.get(route('purchases.index'), currentFilters.value, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
        }
    });
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