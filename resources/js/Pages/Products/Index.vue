<template>
    <Head title="Products" />
    <MainLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Products</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex justify-between items-center mb-6">
                            <div class="flex items-center space-x-4">
                                <TextInput
                                    type="text"
                                    v-model="search"
                                    placeholder="Search products..."
                                    class="w-64"
                                    @input="debouncedSearch"
                                />
                                <select
                                    v-model="selectedCategory"
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    @change="filterByCategory"
                                >
                                    <option value="">All Categories</option>
                                    <option v-for="category in categories" :key="category.id" :value="category.id">
                                        {{ category.name }}
                                    </option>
                                </select>
                            </div>
                            <PrimaryButton @click="showCreateModal = true">Add Product</PrimaryButton>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Image</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-if="products.data.length === 0">
                                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-center">No products found</td>
                                    </tr>
                                    <tr v-for="product in products.data" :key="product.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <img :src="product.image ? `${$page.props.baseURL}/${product.image}` : '/images/placeholder.png'"  :alt="product.name" class="h-10 w-10 rounded-md object-cover">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ product.name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ product.category.name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="[
                                                'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                                product.stock > product.alert_quantity ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                            ]">
                                                {{ product.stock }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ formatPrice(product.selling_price) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                            <button @click="editProduct(product)" class="text-indigo-600 hover:text-indigo-900">Edit</button>
                                            <button @click="confirmDelete(product)" class="text-red-600 hover:text-red-900">Delete</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <Pagination :links="products.links" class="flex justify-end mt-6" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <Modal :show="showCreateModal || showEditModal" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    {{ showEditModal ? 'Edit Product' : 'Create Product' }}
                </h2>
                <ProductForm
                    :categories="categories"
                    :product="editingProduct"
                    :submit-label="showEditModal ? 'Update Product' : 'Create Product'"
                    @submitted="onSubmitted"
                    @cancel="closeModal"
                />
            </div>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">Delete Product</h2>
                <p class="mt-1 text-sm text-gray-600">
                    Are you sure you want to delete this product? This action cannot be undone.
                </p>
                <div class="mt-6 flex justify-end space-x-4">
                    <SecondaryButton @click="showDeleteModal = false">Cancel</SecondaryButton>
                    <DangerButton @click="deleteProduct" :disabled="form.processing">Delete</DangerButton>
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
import ProductForm from '@/Components/Products/ProductForm.vue';

const props = defineProps({
    products: Object,
    categories: Array,
    filters: Object
});

const search = ref(props.filters.search || '');
const selectedCategory = ref(props.filters.category || '');
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const editingProduct = ref(null);
const deletingProduct = ref(null);

const form = useForm({});

const debouncedSearch = debounce(() => {
    router.get(route('products.index'), { 
        search: search.value,
        category: selectedCategory.value
    }, {
        preserveState: true,
        preserveScroll: true
    });
}, 300);

const filterByCategory = () => {
    router.get(route('products.index'), {
        search: search.value,
        category: selectedCategory.value
    }, {
        preserveState: true,
        preserveScroll: true
    });
};

const closeModal = () => {
    showCreateModal.value = false;
    showEditModal.value = false;
    editingProduct.value = null;
};

const editProduct = (product) => {
    editingProduct.value = product;
    showEditModal.value = true;
};

const onSubmitted = () => {
    closeModal();
};

const confirmDelete = (product) => {
    deletingProduct.value = product;
    showDeleteModal.value = true;
};

const deleteProduct = () => {
    form.delete(route('products.destroy', deletingProduct.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
            deletingProduct.value = null;
        },
    });
};

const formatPrice = (price) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(price);
};
</script> 