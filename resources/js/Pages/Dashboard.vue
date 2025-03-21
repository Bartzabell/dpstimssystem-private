<script setup>
    import { ref, watch, computed } from 'vue';
    import { useForm, router, usePage } from '@inertiajs/vue3';
    import AppLayout from '@/Layouts/AppLayout.vue';
    import MonthlyIncomeChart from '@/Components/Charts/MonthlyIncomeChart.vue';
    import MonthlySalesQuantityChart from '@/Components/Charts/MonthlySalesQuantityChart.vue';
    const props = defineProps({
        sales: Object,
        todayTotalSales: { type: Number, default: 0, },
        monthlySales: { type: Number, default: 0, },
        availableProducts: { type: Number, default: 0, },
        lowStatusProducts: { type: Number, default: 0, },
        exceedingProducts: { type: Number, default: 0, },
        filters: Object,
        currentMonth: String,
    });

    const tabs = [
        { label: 'Total Sales', value: 'monthly-sales' },
        { label: 'Sales per Item', value: 'item-sales'},
    ];

    const activeTab = ref('monthly-sales');
</script>
<template>
    <AppLayout title="Dashboard">
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            Dashboard
            </h2>
        </template>
        <div class="px-5">
            <div class="grid grid-cols-1 gap-2 md:grid-cols-5">
                <div class="px-4 py-1 text-sm text-white md:text-base bg-lime-600">
                    <p class="py-2">Today's Total Sales</p>
                    <h1 class="w-full py-2 text-lg text-center md:text-2xl">Php {{ todayTotalSales.toLocaleString() }}</h1>
                </div>
                <div class="px-4 py-1 text-sm text-white md:text-base bg-lime-800">
                    <p class="py-2">{{ currentMonth }} Total Sales</p>
                    <h1 class="w-full py-2 text-lg text-center md:text-2xl">Php {{ monthlySales.toLocaleString() }}</h1>
                </div>
                <div class="px-4 py-1 text-sm text-white bg-green-900 md:text-base">
                    <p class="py-2">Available Products</p>
                    <h1 class="w-full py-2 text-lg text-center md:text-2xl">{{ availableProducts }}</h1>
                </div>
                <div class="px-4 py-1 text-sm text-white bg-teal-900 md:text-base">
                    <p class="py-2">Product on Low Status</p>
                    <h1 class="w-full py-2 text-lg text-center md:text-2xl">{{ lowStatusProducts }}</h1>
                </div>
                <div class="px-4 py-1 text-sm text-white md:text-base bg-amber-700">
                    <p class="py-2">Products on High Status</p>
                    <h1 class="w-full py-2 text-lg text-center md:text-2xl">{{ exceedingProducts }}</h1>
                </div>
                <div class="col-span-1 md:col-span-5">
                    <p class="p-1 text-lg font-extrabold md:text-2xl dark:text-white">DEMAND FORECAST</p>
                </div>
                <div class="mb-4 border-b">
                    <div class="flex flex-wrap -mb-px">
                        <button
                            v-for="tab in tabs"
                            :key="tab.value"
                            @click="activeTab = tab.value"
                            class="flex items-center px-4 py-1 font-medium"
                            :class="[
                                activeTab === tab.value
                                ? 'border-b-2 border-lime-500 text-lime-700 bg-lime-200 rounded-t-md dark:bg-lime-700 dark:text-lime-200 dark:border-lime-100'
                                : 'text-gray-500 hover:text-gray-500 dark:text-gray-100'
                            ]"
                        >
                            <component :is="tab.icon" size="20" v-if="tab.icon" class="mr-1" />
                            {{ tab.label }}
                        </button>
                    </div>
                </div>
                <div v-if="activeTab === 'monthly-sales'" class="col-span-1 px-4 py-1 bg-white rounded-lg dark:bg-gray-700 md:col-span-5">
                    <MonthlyIncomeChart />
                </div>
                <div v-if="activeTab === 'item-sales'" class="col-span-1 px-4 py-1 bg-white rounded-lg dark:bg-gray-700 md:col-span-5">
                    <MonthlySalesQuantityChart />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
