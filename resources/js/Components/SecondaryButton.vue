<script setup>
defineProps({
    type: {
        type: String,
        default: "button",
    },
});
</script>

<template>
    <button
        :type="type"
        class="inline-flex items-center rounded-md border border-line-strong bg-surface px-4 py-2 text-xs font-semibold uppercase tracking-widest text-content shadow-sm transition duration-150 ease-in-out hover:bg-surface-muted focus:outline-none focus:ring-2 focus:ring-accent focus:ring-offset-2 disabled:opacity-25"
    >
        <slot />
    </button>
</template>
