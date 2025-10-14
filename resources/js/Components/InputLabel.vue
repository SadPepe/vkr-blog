<script setup>
defineProps({
    value: {
        type: String,
    },
});
</script>

<template>
    <label>
        <span v-if="value" class="block text-sm font-medium text-gray-700 dark:text-stone-300">{{ value }}</span>
        <span v-else><slot /></span>
    </label>
</template>
