<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
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
                    <h1 class="w-full py-5 text-6xl text-center">Php 2,955.00</h1>
                </div>
                <div class="px-4 py-1 text-white bg-lime-800">
                    <p class="py-3">January Total Sales</p>
                    <h1 class="w-full py-5 text-6xl text-center">Php 120,080.00</h1>
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
                <div class="col-span-1 px-4 py-1 bg-gray-300 md:col-span-4">
                    <h1 class="w-full py-5 text-6xl text-center">Chart Here</h1>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
