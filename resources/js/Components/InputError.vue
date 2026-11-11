<script setup>
defineProps({
    message: {
        type: String,
    },
});
</script>

<template>
    <div v-show="message">
        <p class="text-[0.875rem] font-medium leading-snug text-accent">
            {{ message }}
        </p>
    </div>
</template>
