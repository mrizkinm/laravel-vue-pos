<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Line, Bar, Doughnut } from 'vue-chartjs';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    ArcElement,
    Title,
    Tooltip,
    Legend
} from 'chart.js';

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    ArcElement,
    Title,
    Tooltip,
    Legend
);

// Props to receive data from the backend
const props = defineProps({
    salesData: Object,
    topProducts: Object,
    dailyTransactions: Object,
    stats: Object,
    recentTransactions: Array
});
</script>

<template>
    <Head title="Dashboard" />

    <MainLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 gap-6 mb-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="p-6 bg-white rounded-lg shadow-sm">
                        <h3 class="text-sm font-medium text-gray-500">Total Sales</h3>
                        <p class="mt-2 text-3xl font-semibold text-gray-900">{{ stats.totalSales }}</p>
                    </div>
                    <div class="p-6 bg-white rounded-lg shadow-sm">
                        <h3 class="text-sm font-medium text-gray-500">Total Transactions</h3>
                        <p class="mt-2 text-3xl font-semibold text-gray-900">{{ stats.totalTransactions }}</p>
                    </div>
                    <div class="p-6 bg-white rounded-lg shadow-sm">
                        <h3 class="text-sm font-medium text-gray-500">Average Transaction</h3>
                        <p class="mt-2 text-3xl font-semibold text-gray-900">{{ stats.averageTransaction }}</p>
                    </div>
                    <div class="p-6 bg-white rounded-lg shadow-sm">
                        <h3 class="text-sm font-medium text-gray-500">Total Products</h3>
                        <p class="mt-2 text-3xl font-semibold text-gray-900">{{ stats.totalProducts }}</p>
                    </div>
                </div>

                <!-- Charts -->
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <!-- Monthly Sales Chart -->
                    <div class="p-6 bg-white rounded-lg shadow-sm">
                        <h3 class="mb-4 text-lg font-medium text-gray-900">Monthly Sales</h3>
                        <Line :data="salesData" :options="{ 
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'top',
                                },
                                title: {
                                    display: false
                                }
                            }
                        }" />
                    </div>

                    <!-- Top Products Chart -->
                    <div class="p-6 bg-white rounded-lg shadow-sm">
                        <h3 class="mb-4 text-lg font-medium text-gray-900">Top Products</h3>
                        <Doughnut :data="topProducts" :options="{ 
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'top',
                                },
                                title: {
                                    display: false
                                }
                            }
                        }" />
                    </div>

                    <!-- Daily Transactions Chart -->
                    <div class="p-6 bg-white rounded-lg shadow-sm">
                        <h3 class="mb-4 text-lg font-medium text-gray-900">Daily Transactions</h3>
                        <Bar :data="dailyTransactions" :options="{ 
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'top',
                                },
                                title: {
                                    display: false
                                }
                            }
                        }" />
                    </div>

                    <!-- Recent Transactions Table -->
                    <div class="p-6 bg-white rounded-lg shadow-sm">
                        <h3 class="mb-4 text-lg font-medium text-gray-900">Recent Transactions</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <tr v-for="transaction in recentTransactions" :key="transaction.id">
                                        <td class="px-6 py-4 text-sm text-gray-900">#{{ transaction.id }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-900">{{ transaction.date }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-900">{{ transaction.amount }}</td>
                                        <td class="px-6 py-4 text-sm" :class="{
                                            'text-green-600': transaction.status === 'paid',
                                            'text-yellow-600': transaction.status === 'partial',
                                            'text-red-600': transaction.status === 'unpaid'
                                        }">
                                            {{ transaction.status }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </MainLayout>
</template>
