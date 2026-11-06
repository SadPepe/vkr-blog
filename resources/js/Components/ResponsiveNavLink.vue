<script setup>
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
});

const classes = computed(() =>
    props.active
        ? "block w-full ps-3 pe-4 py-2 border-l-4 border-accent text-start text-base font-medium text-accent bg-accent-soft focus:outline-none focus:text-accent focus:bg-accent-soft focus:border-accent transition duration-150 ease-in-out"
        : "block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-muted hover:text-content hover:bg-surface-muted hover:border-line-strong focus:outline-none focus:text-content focus:bg-surface-muted focus:border-line-strong transition duration-150 ease-in-out",
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
