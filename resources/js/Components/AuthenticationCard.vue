<template>
    <div class="flex flex-col items-center min-h-screen pt-6 bg-gray-300 sm:justify-center sm:pt-0 dark:bg-gray-900">


        <div class="w-full sm:max-w-md">
            <div class="flex items-center justify-center w-full py-2 overflow-hidden shadow-md sm:rounded-t-lg bg-lime-900">
                <h1 class="text-6xl text-white">DPST-<b class="font-extrabold">IMS</b></h1>
            </div>
            <div class="w-full py-1 bg-gray-300">
            </div>
            <div class="px-6 pt-1 pb-4 overflow-hidden bg-white shadow-md dark:bg-gray-800 sm:rounded-t-none sm:rounded-lg">
                <h1 class="flex justify-center w-full text-sm font-thin text-gray-700">To proceed, please log in</h1>
                <slot />
            </div>
        </div>
    </div>
</template>
