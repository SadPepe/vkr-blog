<script setup>
defineProps({
    value: {
        type: String,
    },
});
</script>

<template>
    <label>
        <span v-if="value" class="block text-sm font-medium text-content">{{ value }}</span>
        <span v-else><slot /></span>
    </label>
</template>
