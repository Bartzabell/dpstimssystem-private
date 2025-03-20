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
                <div class="px-4 py-1 text-white bg-lime-600">
                    <p class="py-2">Today's Total Sales</p>
                    <h1 class="w-full py-5 text-4xl text-center">Php {{ todayTotalSales.toLocaleString() }}</h1>
                </div>
                <div class="px-4 py-1 text-white bg-lime-800">
                    <p class="py-2">{{ currentMonth }} Total Sales</p>
                    <h1 class="w-full py-5 text-4xl text-center">Php {{ monthlySales.toLocaleString() }}</h1>
                </div>
                <div class="px-4 py-1 text-white bg-green-900">
                    <p class="py-2">Available Products</p>
                    <h1 class="w-full py-5 text-4xl text-center">{{ availableProducts }}</h1>
                </div>
                <div class="px-4 py-1 text-white bg-teal-900">
                    <p class="py-2">Product on Low Status</p>
                    <h1 class="w-full py-5 text-4xl text-center">{{ lowStatusProducts }}</h1>
                </div>
                <div class="px-4 py-1 text-white bg-amber-700">
                    <p class="py-2">Products on High Status</p>
                    <h1 class="w-full py-5 text-4xl text-center">{{ exceedingProducts }}</h1>
                </div>
                <div class="col-span-1 md:col-span-5">
                    <p class="p-1 text-4xl font-extrabold">DEMAND FORECAST</p>
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
                                ? 'border-b-2 border-blue-500 text-blue-600'
                                : 'text-gray-500 hover:text-gray-700'
                            ]"
                        >
                            <component :is="tab.icon" size="20" v-if="tab.icon" class="mr-1" />
                            {{ tab.label }}
                        </button>
                    </div>
                </div>
                <div v-if="activeTab === 'monthly-sales'" class="col-span-1 px-4 py-1 bg-white md:col-span-5">
                    <MonthlyIncomeChart />
                </div>
                <div v-if="activeTab === 'item-sales'" class="col-span-1 px-4 py-1 bg-white md:col-span-5">
                    <MonthlySalesQuantityChart />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
