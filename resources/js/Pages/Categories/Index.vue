<template>
    <Head title="Categories" />
    <MainLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Categories</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <!-- Header Actions -->
                        <div class="flex justify-between items-center mb-6">
                            <TextInput
                                type="text"
                                v-model="search"
                                placeholder="Search categories..."
                                class="w-64"
                                @input="debouncedSearch"
                            />
                            <PrimaryButton @click="showCreateModal = true">Add Category</PrimaryButton>
                        </div>

                        <!-- Categories Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Image
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Name
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Description
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Products
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-if="categories.data.length === 0">
                                        <td colspan="5" class="px-6 py-4 whitespace-nowrap text-center">No categories found</td>
                                    </tr>
                                    <tr v-for="category in categories.data" :key="category.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <img 
                                                :src="category.image ? `${$page.props.baseURL}/${category.image}` : '/images/placeholder.png'" 
                                                :alt="category.name"
                                                class="h-10 w-10 rounded-md object-cover"
                                            >
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ category.name }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-500">{{ category.description }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ category.products_count }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button
                                                @click="editCategory(category)"
                                                class="text-indigo-600 hover:text-indigo-900 mr-3"
                                            >
                                                Edit
                                            </button>
                                            <button
                                                @click="confirmDelete(category)"
                                                class="text-red-600 hover:text-red-900"
                                            >
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <Pagination :links="categories.links" class="flex justify-end mt-6" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <Modal :show="showCreateModal || showEditModal" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    {{ showEditModal ? 'Edit Category' : 'Create Category' }}
                </h2>
                <CategoryForm
                    :category="editingCategory"
                    :submit-label="showEditModal ? 'Update Category' : 'Create Category'"
                    @submitted="onSubmitted"
                    @cancel="closeModal"
                />
            </div>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">Delete Category</h2>
                <p class="mt-1 text-sm text-gray-600">
                    Are you sure you want to delete this category? All products in this category will need to be reassigned.
                </p>
                <div class="mt-6 flex justify-end space-x-4">
                    <SecondaryButton @click="showDeleteModal = false">Cancel</SecondaryButton>
                    <DangerButton @click="deleteCategory" :disabled="form.processing">Delete</DangerButton>
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
import CategoryForm from '@/Components/Categories/CategoryForm.vue';

const props = defineProps({
    categories: Object,
    filters: Object
});

const search = ref(props.filters.search || '');
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const editingCategory = ref(null);
const deletingCategory = ref(null);

const form = useForm({});

const debouncedSearch = debounce(() => {
    router.get(route('categories.index'), { 
        search: search.value
    }, {
        preserveState: true,
        preserveScroll: true
    });
}, 300);

const closeModal = () => {
    showCreateModal.value = false;
    showEditModal.value = false;
    editingCategory.value = null;
};

const editCategory = (category) => {
    editingCategory.value = category;
    showEditModal.value = true;
};

const onSubmitted = () => {
    closeModal();
};

const confirmDelete = (category) => {
    deletingCategory.value = category;
    showDeleteModal.value = true;
};

const deleteCategory = () => {
    form.delete(route('categories.destroy', deletingCategory.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
            deletingCategory.value = null;
        },
    });
};
</script> 