<script setup>
import { ref, watch, computed } from 'vue';
import { useForm, router, usePage, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import MonthlyIncomeChart from '@/Components/Charts/MonthlyIncomeChart.vue';
import TodaysSalesChart from '@/Components/Charts/TodaysSalesChart.vue';
import WeeklySalesChart from '@/Components/Charts/WeeklySalesChart.vue';
import MonthlySalesQuantityChart from '@/Components/Charts/MonthlySalesQuantityChart.vue';
import { PhX } from '@phosphor-icons/vue';
const props = defineProps({
    sales: Object,
    todayTotalSales: { type: Number, default: 0, },
    weeklyTotalSales: { type: Number, default: 0, },
    monthlySales: { type: Number, default: 0, },
    availableProducts: { type: Number, default: 0, },
    lowStatusProducts: { type: Number, default: 0, },
    exceedingProducts: { type: Number, default: 0, },
    filters: Object,
    currentMonth: String,
});

const tabs = [
    { label: 'Total Sales', value: 'monthly-sales' },
    { label: 'Sales per Item', value: 'item-sales' },
];

const isTodayVisible = ref(false);
const isWeekVisible = ref(false);
const isMonthVisible = ref(false);
const isAvailableVisible = ref(false);
const isLowVisible = ref(false);
const isHighVisible = ref(false);

function toggleFormVisibility() {
    isTodayVisible.value = !isTodayVisible.value;
}
function toggleFormWeeklyVisibility() {
    isWeekVisible.value = !isWeekVisible.value;
}
function toggleForm2Visibility() {
    isMonthVisible.value = !isMonthVisible.value;
}
function toggleForm3Visibility() {
    isAvailableVisible.value = !isAvailableVisible.value;
}
function toggleForm4Visibility() {
    isLowVisible.value = !isLowVisible.value;
}
function toggleForm5Visibility() {
    isHighVisible.value = !isHighVisible.value;
}

const activeTab = ref('monthly-sales');
</script>
<template>
    <AppLayout title="Dashboard">
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Dashboard
            </h2>
        </template>
        <Modal :show="isTodayVisible" class="fixed inset-0 z-50">
            <div v-if="isTodayVisible">
                <div
                    class="fixed top-0 z-40 flex items-center justify-between w-full px-8 py-1 bg-white border-b border-black dark:border-gray-500 dark:bg-gray-800">
                    <div>
                        <h1 class="text-2xl font-extrabold dark:text-gray-200">Today's Total Sales</h1>
                    </div>
                    <button @click="toggleFormVisibility"
                        class="p-3 text-white bg-red-700 rounded-full hover:bg-red-900">
                        <PhX :size="16" />
                    </button>
                </div>
                <div>
                    <TodaysSalesChart />
                </div>
            </div>
        </Modal>
        <Modal :show="isWeekVisible" class="fixed inset-0 z-50">
            <div v-if="isWeekVisible">
                <div
                    class="fixed top-0 z-40 flex items-center justify-between w-full px-8 py-1 bg-white border-b border-black dark:border-gray-500 dark:bg-gray-800">
                    <div>
                        <h1 class="text-2xl font-extrabold dark:text-gray-200">Weekly Total Sales</h1>
                    </div>
                    <button @click="toggleFormWeeklyVisibility"
                        class="p-3 text-white bg-red-700 rounded-full hover:bg-red-900">
                        <PhX :size="16" />
                    </button>
                </div>
                <div>
                    <WeeklySalesChart />
                </div>
            </div>
        </Modal>
        <Modal :show="isMonthVisible" class="fixed inset-0 z-50">
            <div class="h-[80vh]" v-if="isMonthVisible">
                <div
                    class="fixed top-0 z-40 flex items-center justify-between w-full px-8 py-1 bg-white border-b border-black dark:border-gray-500 dark:bg-gray-800">
                    <div>
                        <h1 class="text-2xl font-extrabold dark:text-gray-200">{{ currentMonth }} Total Sales</h1>
                    </div>
                    <button @click="toggleForm2Visibility"
                        class="p-3 text-white bg-red-700 rounded-full hover:bg-red-900">
                        <PhX :size="16" />
                    </button>
                </div>
                <div class="flex items-center h-full">
                    <div class="w-full">
                        <MonthlyIncomeChart />
                    </div>
                </div>
            </div>
        </Modal>

        <div class="px-5">
            <div class="grid grid-cols-1 gap-2 lg:grid-cols-3 2xl:grid-cols-6">

                <button @click="toggleFormVisibility">
                    <div class="px-4 py-1 text-sm text-white md:text-base bg-lime-500">
                        <p class="py-2">Today's Total Sales</p>
                        <h1 class="w-full py-2 text-lg text-center md:text-2xl">Php {{ todayTotalSales.toLocaleString()
                            }}</h1>
                    </div>
                </button>
                <button @click="toggleFormWeeklyVisibility">
                    <div class="px-4 py-1 text-sm text-white md:text-base bg-lime-700">
                        <p class="py-2">Weekly Total Sales</p>
                        <h1 class="w-full py-2 text-lg text-center md:text-2xl">Php Sample</h1>
                    </div>
                </button>
                <button @click="toggleForm2Visibility">
                    <div class="px-4 py-1 text-sm text-white md:text-base bg-lime-900">
                        <p class="py-2">{{ currentMonth }} Total Sales</p>
                        <h1 class="w-full py-2 text-lg text-center md:text-2xl">Php {{ monthlySales.toLocaleString() }}
                        </h1>
                    </div>
                </button>
                <Link href="/inventory" class="">
                <div class="px-4 py-1 text-sm text-white bg-green-900 md:text-base hover:bg-green-800">
                    <p class="py-2 text-sm text-center">Available Products</p>
                    <h1 class="w-full py-2 text-lg text-center md:text-2xl">{{ availableProducts }}</h1>
                </div>
                </Link>
                <Link href="/inventory?search=low&sort_direction=asc&sort_field=id">
                <div class="px-4 py-1 text-sm text-white bg-blue-900 md:text-base hover:bg-blue-800">
                    <p class="py-2 text-sm">Fast Moving Products</p>
                    <h1 class="w-full py-2 text-lg text-center md:text-2xl">{{ lowStatusProducts }}</h1>
                </div>
                </Link>
                <Link href="/inventory?search=high&sort_direction=asc&sort_field=id">
                <div class="px-4 py-1 text-sm text-white bg-purple-700 md:text-base hover:bg-purple-600">
                    <p class="py-2 text-sm">Critically Low Products</p>
                    <h1 class="w-full py-2 text-lg text-center md:text-2xl">{{ exceedingProducts }}</h1>
                </div>
                </Link>
                <div class="col-span-1 lg:col-span-3 2xl:col-span-6">
                    <p class="p-1 text-lg font-extrabold md:text-2xl dark:text-white">SALES STATISTICS</p>
                </div>
                <div class="mb-4 border-b">
                    <div class="flex flex-wrap -mb-px">
                        <button v-for="tab in tabs" :key="tab.value" @click="activeTab = tab.value"
                            class="flex items-center px-4 py-1 font-medium" :class="[
                                activeTab === tab.value
                                    ? 'border-b-2 border-lime-500 text-lime-700 bg-lime-200 rounded-t-md dark:bg-lime-700 dark:text-lime-200 dark:border-lime-100'
                                    : 'text-gray-500 hover:text-gray-500 dark:text-gray-100'
                            ]">
                            <component :is="tab.icon" size="20" v-if="tab.icon" class="mr-1" />
                            {{ tab.label }}
                        </button>
                    </div>
                </div>
                <div v-if="activeTab === 'monthly-sales'"
                    class="col-span-1 px-4 py-1 bg-white rounded-lg dark:bg-gray-700 lg:col-span-3 2xl:col-span-6">
                    <MonthlyIncomeChart />
                </div>
                <div v-if="activeTab === 'item-sales'"
                    class="col-span-1 px-4 py-1 bg-white rounded-lg dark:bg-gray-700 lg:col-span-3 2xl:col-span-6">
                    <MonthlySalesQuantityChart />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
