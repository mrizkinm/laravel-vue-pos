<template>
    <Head title="Settings" />
    <MainLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Settings</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <!-- Tabs -->
                        <div class="border-b border-gray-200 mb-6">
                            <nav class="-mb-px flex space-x-8">
                                <button
                                    @click="activeTab = 'general'"
                                    :class="[
                                        'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm',
                                        activeTab === 'general'
                                            ? 'border-indigo-500 text-indigo-600'
                                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                    ]"
                                >
                                    General Settings
                                </button>
                                <button
                                    @click="activeTab = 'backup'"
                                    :class="[
                                        'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm',
                                        activeTab === 'backup'
                                            ? 'border-indigo-500 text-indigo-600'
                                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                    ]"
                                >
                                    Backup & Restore
                                </button>
                            </nav>
                        </div>

                        <!-- General Settings Tab -->
                        <div v-if="activeTab === 'general'">
                            <SettingsForm
                                :settings="settings"
                                @submitted="onSettingsUpdated"
                            />
                        </div>

                        <!-- Backup & Restore Tab -->
                        <div v-if="activeTab === 'backup'" class="space-y-6">
                            <!-- Database Backup -->
                            <div class="bg-white p-6 rounded-lg shadow">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Database Backup</h3>
                                <p class="text-sm text-gray-600 mb-4">
                                    Create a backup of your entire database including all transactions, products, and customer data.
                                </p>
                                <div class="flex items-center justify-between">
                                    <div class="text-sm text-gray-500">
                                        Last backup: {{ lastBackup ? formatDate(lastBackup) : 'Never' }}
                                    </div>
                                    <PrimaryButton
                                        @click="createBackup"
                                        :disabled="isCreatingBackup"
                                    >
                                        {{ isCreatingBackup ? 'Creating Backup...' : 'Create Backup' }}
                                    </PrimaryButton>
                                </div>
                            </div>

                            <!-- Restore Database -->
                            <div class="bg-white p-6 rounded-lg shadow">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Restore Database</h3>
                                <p class="text-sm text-gray-600 mb-4">
                                    Restore your database from a previous backup file. This will overwrite all current data.
                                </p>
                                <div class="space-y-4">
                                    <input
                                        type="file"
                                        @input="backupFile = $event.target.files[0]"
                                        accept=".sql,.zip"
                                        class="block w-full text-sm text-gray-500
                                            file:mr-4 file:py-2 file:px-4
                                            file:rounded-full file:border-0
                                            file:text-sm file:font-semibold
                                            file:bg-indigo-50 file:text-indigo-700
                                            hover:file:bg-indigo-100"
                                    />
                                    <div class="flex justify-end">
                                        <DangerButton
                                            @click="confirmRestore"
                                            :disabled="!backupFile || isRestoring"
                                        >
                                            {{ isRestoring ? 'Restoring...' : 'Restore Backup' }}
                                        </DangerButton>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Restore Confirmation Modal -->
        <Modal :show="showRestoreModal" @close="closeRestoreModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">Confirm Database Restore</h2>
                <p class="mt-1 text-sm text-gray-600">
                    Are you sure you want to restore the database? This action will overwrite all current data and cannot be undone.
                </p>
                <div class="mt-6 flex justify-end space-x-4">
                    <SecondaryButton @click="closeRestoreModal">Cancel</SecondaryButton>
                    <DangerButton @click="restoreBackup" :disabled="isRestoring">
                        Yes, Restore Database
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </MainLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SettingsForm from '@/Components/Settings/SettingsForm.vue';

const props = defineProps({
    settings: Object,
    lastBackup: String
});

const activeTab = ref('general');
const showRestoreModal = ref(false);
const isCreatingBackup = ref(false);
const isRestoring = ref(false);
const backupFile = ref(null);

const createBackup = () => {
    isCreatingBackup.value = true;
    router.post(route('settings.backup'), {}, {
        onSuccess: () => {
            isCreatingBackup.value = false;
        },
        onError: () => {
            isCreatingBackup.value = false;
        }
    });
};

const confirmRestore = () => {
    if (backupFile.value) {
        showRestoreModal.value = true;
    }
};

const closeRestoreModal = () => {
    showRestoreModal.value = false;
};

const restoreBackup = () => {
    if (!backupFile.value) return;

    const form = new FormData();
    form.append('backup_file', backupFile.value);

    isRestoring.value = true;
    router.post(route('settings.restore'), form, {
        onSuccess: () => {
            isRestoring.value = false;
            showRestoreModal.value = false;
            backupFile.value = null;
        },
        onError: () => {
            isRestoring.value = false;
        }
    });
};

const onSettingsUpdated = () => {
    // Optionally show a success message or perform additional actions
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script> 