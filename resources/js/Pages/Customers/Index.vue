<template>
    <Head title="Customers" />
    <MainLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Customers</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <!-- Header Actions -->
                        <div class="flex justify-between items-center mb-6">
                            <div class="flex items-center space-x-4">
                                <TextInput
                                    type="text"
                                    v-model="search"
                                    placeholder="Search customers..."
                                    class="w-64"
                                    @input="debouncedSearch"
                                />
                                <select
                                    v-model="filter"
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    @change="filterCustomers"
                                >
                                    <option value="">All Customers</option>
                                    <option value="with_balance">With Balance</option>
                                    <option value="no_balance">No Balance</option>
                                </select>
                            </div>
                            <PrimaryButton @click="showCreateModal = true">Add Customer</PrimaryButton>
                        </div>

                        <!-- Customers Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Sales</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Purchase</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-if="customers.data.length === 0">
                                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-center">No customers found</td>
                                    </tr>
                                    <tr v-for="customer in customers.data" :key="customer.id">
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900">{{ customer.name }}</div>
                                            <div class="text-sm text-gray-500">{{ customer.tax_number }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-900">{{ customer.phone }}</div>
                                            <div class="text-sm text-gray-500">{{ customer.email }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-900">{{ formatPrice(customer.sales_sum_grand_total) }}</div>
                                            <div class="text-sm text-gray-500">{{ customer.sales_count }} orders</div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500">
                                            {{ customer.last_purchase ? formatDate(customer.last_purchase) : 'Never' }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <span :class="[
                                                'px-2 py-1 text-xs font-semibold rounded-full',
                                                customer.balance > 0 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'
                                            ]">
                                                {{ formatPrice(customer.balance) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm font-medium space-x-2">
                                            <!-- <button
                                                @click="showPaymentHandlerModal(customer)"
                                                class="text-green-600 hover:text-green-900"
                                                v-if="customer.balance > 0"
                                            >
                                                Record Payment
                                            </button> -->
                                            <button
                                                @click="editCustomer(customer)"
                                                class="text-indigo-600 hover:text-indigo-900"
                                            >
                                                Edit
                                            </button>
                                            <button
                                                @click="confirmDelete(customer)"
                                                class="text-red-600 hover:text-red-900"
                                            >
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <Pagination :links="customers.links" class="flex justify-end mt-6" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <Modal :show="showCreateModal || showEditModal" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    {{ showEditModal ? 'Edit Customer' : 'Add Customer' }}
                </h2>
                <CustomerForm
                    :customer="editingCustomer"
                    :submit-label="showEditModal ? 'Update Customer' : 'Add Customer'"
                    :show-balance="!showEditModal"
                    @submitted="onSubmitted"
                    @cancel="closeModal"
                />
            </div>
        </Modal>

        <!-- Payment Modal -->
        <Modal :show="showPaymentModal" @close="closePaymentHandlerModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Record Payment</h2>
                <form @submit.prevent="submitPayment" class="space-y-4">
                    <div v-if="selectedCustomer">
                        <p class="text-sm text-gray-600">Customer</p>
                        <p class="font-medium">{{ selectedCustomer.name }}</p>
                        <p class="text-sm text-gray-600 mt-2">Current Balance</p>
                        <p class="font-medium text-red-600">{{ formatPrice(selectedCustomer.balance) }}</p>
                    </div>

                    <div>
                        <InputLabel for="amount" value="Payment Amount" />
                        <TextInput
                            id="amount"
                            type="number"
                            step="0.01"
                            class="mt-1 block w-full"
                            v-model="paymentForm.amount"
                            required
                        />
                        <InputError class="mt-2" :message="paymentForm.errors.amount" />
                    </div>

                    <div>
                        <InputLabel for="payment_method" value="Payment Method" />
                        <select
                            id="payment_method"
                            v-model="paymentForm.payment_method"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            required
                        >
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="bank_transfer">Bank Transfer</option>
                        </select>
                        <InputError class="mt-2" :message="paymentForm.errors.payment_method" />
                    </div>

                    <div>
                        <InputLabel for="notes" value="Notes (Optional)" />
                        <textarea
                            id="notes"
                            v-model="paymentForm.notes"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            rows="2"
                        ></textarea>
                        <InputError class="mt-2" :message="paymentForm.errors.notes" />
                    </div>

                    <div class="flex justify-end space-x-4">
                        <SecondaryButton type="button" @click="closePaymentHandlerModal">Cancel</SecondaryButton>
                        <PrimaryButton :disabled="paymentForm.processing">Record Payment</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">Delete Customer</h2>
                <p class="mt-1 text-sm text-gray-600">
                    Are you sure you want to delete this customer? This action cannot be undone.
                </p>
                <div class="mt-6 flex justify-end space-x-4">
                    <SecondaryButton @click="showDeleteModal = false">Cancel</SecondaryButton>
                    <DangerButton @click="deleteCustomer" :disabled="form.processing">Delete</DangerButton>
                </div>
            </div>
        </Modal>
    </MainLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router, useForm, Head } from '@inertiajs/vue3';
import debounce from 'lodash/debounce';
import MainLayout from '@/Layouts/MainLayout.vue';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Pagination from '@/Components/Pagination.vue';
import CustomerForm from '@/Components/Customers/CustomerForm.vue';

const props = defineProps({
    customers: Object,
    filters: Object
});

const search = ref(props.filters.search || '');
const filter = ref(props.filters.filter || '');
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showPaymentModal = ref(false);
const showDeleteModal = ref(false);
const editingCustomer = ref(null);
const selectedCustomer = ref(null);
const deletingCustomer = ref(null);

const form = useForm({});
const paymentForm = useForm({
    amount: '',
    payment_method: 'cash',
    notes: ''
});

const debouncedSearch = debounce(() => {
    router.get(route('customers.index'), {
        search: search.value,
        filter: filter.value
    }, {
        preserveState: true,
        preserveScroll: true
    });
}, 300);

const filterCustomers = () => {
    router.get(route('customers.index'), {
        search: search.value,
        filter: filter.value
    }, {
        preserveState: true,
        preserveScroll: true
    });
};

const closeModal = () => {
    showCreateModal.value = false;
    showEditModal.value = false;
    editingCustomer.value = null;
};

const editCustomer = (customer) => {
    editingCustomer.value = customer;
    showEditModal.value = true;
};

const onSubmitted = () => {
    closeModal();
};

const showPaymentHandlerModal = (customer) => {
    selectedCustomer.value = customer;
    paymentForm.reset();
    showPaymentModal.value = true;
};

const closePaymentHandlerModal = () => {
    showPaymentModal.value = false;
    selectedCustomer.value = null;
    paymentForm.reset();
};

const submitPayment = () => {
    paymentForm.post(route('customers.record-payment', selectedCustomer.value.id), {
        onSuccess: () => closePaymentHandlerModal()
    });
};

const confirmDelete = (customer) => {
    deletingCustomer.value = customer;
    showDeleteModal.value = true;
};

const deleteCustomer = () => {
    form.delete(route('customers.destroy', deletingCustomer.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
            deletingCustomer.value = null;
        },
    });
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const formatPrice = (price) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(price);
};
</script> 