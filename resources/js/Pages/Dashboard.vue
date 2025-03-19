<script setup>
    import { ref, watch, computed } from 'vue';
    import { useForm, router, usePage } from '@inertiajs/vue3';
    import AppLayout from '@/Layouts/AppLayout.vue';
    import MonthlyIncomeChart from '@/Components/MonthlyIncomeChart.vue';

    const props = defineProps({
        sales: Object,
        todayTotalSales: {
            type: Number,
            default: 0,
        },
        januaryTotalSales: {
            type: Number,
            default: 0,
        },
        filters: Object,
    });
</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            Dashboard
            </h2>
        </template>

        <div class="px-5">
            <div class="grid grid-cols-1 gap-2 md:grid-cols-4">
                <div class="px-4 py-1 text-white bg-lime-600">
                    <p class="py-3">Today's Total Sales</p>
                    <h1 class="w-full py-5 text-6xl text-center">Php {{ todayTotalSales.toLocaleString() }}</h1>
                </div>
                <div class="px-4 py-1 text-white bg-lime-800">
                    <p class="py-3">January Total Sales</p>
                    <h1 class="w-full py-5 text-6xl text-center">Php {{ januaryTotalSales.toLocaleString() }}</h1>
                </div>
                <div class="px-4 py-1 text-white bg-green-900">
                    <p class="py-3">Available Products</p>
                    <h1 class="w-full py-5 text-6xl text-center">500</h1>
                </div>
                <div class="px-4 py-1 text-white bg-teal-900">
                    <p class="py-3">Product on Low Status</p>
                    <h1 class="w-full py-5 text-6xl text-center">25</h1>
                </div>
                <div class="col-span-1 md:col-span-4">
                    <p class="p-5 text-4xl font-extrabold">DEMAND FORECAST</p>
                </div>
                <div class="col-span-1 px-4 py-1 bg-white md:col-span-4">
                    <MonthlyIncomeChart />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
