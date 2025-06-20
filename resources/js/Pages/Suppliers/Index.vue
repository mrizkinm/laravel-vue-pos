<template>
    <Head title="Suppliers" />
    <MainLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Suppliers</h2>
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
                                    placeholder="Search suppliers..."
                                    class="w-64"
                                    @input="debouncedSearch"
                                />
                                <select
                                    v-model="filter"
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    @change="filterSuppliers"
                                >
                                    <option value="">All Suppliers</option>
                                    <option value="with_balance">With Balance</option>
                                    <option value="no_balance">No Balance</option>
                                </select>
                            </div>
                            <PrimaryButton @click="showCreateModal = true">Add Supplier</PrimaryButton>
                        </div>

                        <!-- Suppliers Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tax Info</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bank Details</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Purchases</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Purchase</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-if="suppliers.data.length === 0">
                                        <td colspan="8" class="px-6 py-4 whitespace-nowrap text-center">No suppliers found</td>
                                    </tr>
                                    <tr v-for="supplier in suppliers.data" :key="supplier.id">
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900">{{ supplier.company }}</div>
                                            <div class="text-sm text-gray-500">{{ supplier.name }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-900">{{ supplier.phone }}</div>
                                            <div class="text-sm text-gray-500">{{ supplier.email }}</div>
                                            <div class="text-sm text-gray-500">{{ supplier.address }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-900">{{ supplier.tax_number || 'N/A' }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div v-if="supplier.bank_name" class="text-sm text-gray-900">
                                                {{ supplier.bank_name }}
                                            </div>
                                            <div v-if="supplier.bank_account" class="text-sm text-gray-500">
                                                {{ supplier.bank_account }}
                                            </div>
                                            <div v-if="!supplier.bank_name && !supplier.bank_account" class="text-sm text-gray-500">
                                                No bank details
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-900">{{ formatPrice(supplier.purchases_sum_grand_total) }}</div>
                                            <div class="text-sm text-gray-500">{{ supplier.purchases_count }} orders</div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500">
                                            {{ supplier.last_purchase ? formatDate(supplier.last_purchase) : 'Never' }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <span :class="[
                                                'px-2 py-1 text-xs font-semibold rounded-full',
                                                supplier.balance > 0 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'
                                            ]">
                                                {{ formatPrice(supplier.balance) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm font-medium space-x-2">
                                            <!-- <button
                                                @click="viewPurchaseHistory(supplier)"
                                                class="text-indigo-600 hover:text-indigo-900"
                                            >
                                                History
                                            </button> -->
                                            <button
                                                @click="editSupplier(supplier)"
                                                class="text-indigo-600 hover:text-indigo-900"
                                            >
                                                Edit
                                            </button>
                                            <button
                                                @click="confirmDelete(supplier)"
                                                class="text-red-600 hover:text-red-900"
                                            >
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <Pagination :links="suppliers.links" class="flex justify-end mt-6" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <Modal :show="showCreateModal || showEditModal" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    {{ showEditModal ? 'Edit Supplier' : 'Add Supplier' }}
                </h2>
                <SupplierForm
                    :supplier="editingSupplier"
                    :submit-label="showEditModal ? 'Update Supplier' : 'Add Supplier'"
                    @submitted="onSubmitted"
                    @cancel="closeModal"
                />
            </div>
        </Modal>

        <!-- Purchase History Modal -->
        <Modal :show="showHistoryModal" @close="closeHistoryModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Purchase History</h2>
                <div v-if="selectedSupplier" class="space-y-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="font-medium">{{ selectedSupplier.company }}</h3>
                            <p class="text-sm text-gray-500">{{ selectedSupplier.name }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-600">Total Purchases</p>
                            <p class="font-medium">{{ formatPrice(selectedSupplier.purchases_sum_grand_total) }}</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Invoice</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Items</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-for="purchase in supplierPurchases" :key="purchase.id">
                                    <td class="px-4 py-2 text-sm">{{ formatDate(purchase.date) }}</td>
                                    <td class="px-4 py-2 text-sm">{{ purchase.invoice_number }}</td>
                                    <td class="px-4 py-2 text-sm">{{ purchase.items_count }} items</td>
                                    <td class="px-4 py-2 text-sm font-medium">{{ formatPrice(purchase.total_amount) }}</td>
                                    <td class="px-4 py-2">
                                        <span :class="[
                                            'px-2 py-1 text-xs font-semibold rounded-full',
                                            purchase.status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'
                                        ]">
                                            {{ purchase.status }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">Delete Supplier</h2>
                <p class="mt-1 text-sm text-gray-600">
                    Are you sure you want to delete this supplier? This action cannot be undone.
                </p>
                <div class="mt-6 flex justify-end space-x-4">
                    <SecondaryButton @click="showDeleteModal = false">Cancel</SecondaryButton>
                    <DangerButton @click="deleteSupplier" :disabled="form.processing">Delete</DangerButton>
                </div>
            </div>
        </Modal>
    </MainLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import debounce from 'lodash/debounce';
import MainLayout from '@/Layouts/MainLayout.vue';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Pagination from '@/Components/Pagination.vue';
import SupplierForm from '@/Components/Suppliers/SupplierForm.vue';

const props = defineProps({
    suppliers: Object,
    filters: Object
});

const search = ref(props.filters.search || '');
const filter = ref(props.filters.filter || '');
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const showHistoryModal = ref(false);
const editingSupplier = ref(null);
const selectedSupplier = ref(null);
const deletingSupplier = ref(null);
const supplierPurchases = ref([]);

const form = useForm({});

const debouncedSearch = debounce(() => {
    router.get(route('suppliers.index'), { 
        search: search.value,
        filter: filter.value
    }, {
        preserveState: true,
        preserveScroll: true
    });
}, 300);

const filterSuppliers = () => {
    router.get(route('suppliers.index'), { 
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
    editingSupplier.value = null;
};

const editSupplier = (supplier) => {
    editingSupplier.value = supplier;
    showEditModal.value = true;
};

const onSubmitted = () => {
    closeModal();
};

const closeHistoryModal = () => {
    showHistoryModal.value = false;
    selectedSupplier.value = null;
    supplierPurchases.value = [];
};

const confirmDelete = (supplier) => {
    deletingSupplier.value = supplier;
    showDeleteModal.value = true;
};

const deleteSupplier = () => {
    form.delete(route('suppliers.destroy', deletingSupplier.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
            deletingSupplier.value = null;
        },
    });
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
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