<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
</script>
<template>
    <AppLayout title="Dashboard">
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                About Us
            </h2>
        </template>

        <div class="flex flex-col items-center justify-center w-full gap-5 mt-5 text-center">
            <h1 class="text-5xl">DPST-<b class="font-extrabold">IMS</b></h1>
            <h1>An Inventory Management System exclusive<br>for Dellosa Packaging Supplies Trading</h1>
            <h1 class="text-xl font-extrabold">ABOUT</h1>
            <h1>The DPST-IMS is a thesis project required to pass the 200A and 200B courses
            <br>of the Bachelor of Science in Computer Science Program.</h1>
            <h1 class="text-xl font-extrabold">FEATURES</h1>
            <h1>Point-of-Sales
            <br>Demand Forecast
            <br>Administrative Controls</h1>
            <h1 class="text-xl font-extrabold">DEVELOPERS</h1>
            <h1>Garcia, Mark Allen C.
            <br>Reynales, Ivan R.</h1>
        </div>
    </AppLayout>
</template>