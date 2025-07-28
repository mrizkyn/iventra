<script setup>
defineProps({
    type: {
        type: String,
        default: "submit",
    },
});
</script>

<template>
    <button
        :type="type"
        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 focus:bg-green-500 active:bg-green-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 dark:bg-yellow-500 dark:text-gray-900 dark:hover:bg-yellow-600 dark:focus:bg-yellow-600 dark:active:bg-yellow-700 dark:focus:ring-offset-gray-800"
    >
        <slot />
    </button>
</template>
