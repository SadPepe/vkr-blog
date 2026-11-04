<template>
    <img
        v-if="user?.avatar_url"
        :src="user.avatar_url"
        :alt="user.name"
        class="object-cover"
        :class="square ? '' : 'rounded-full'"
        :style="{ width: `${size}px`, height: `${size}px` }"
    />
    <span
        v-else
        class="inline-flex shrink-0 select-none items-center justify-center bg-surface-muted font-semibold text-content"
        :class="square ? '' : 'rounded-full'"
        :style="{
            width: `${size}px`,
            height: `${size}px`,
            fontSize: `${Math.round(size * 0.38)}px`,
        }"
        :aria-label="user?.name"
        role="img"
    >
        {{ initials }}
    </span>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
    size: {
        type: Number,
        default: 32,
    },
    square: {
        type: Boolean,
        default: false,
    },
});

const initials = computed(() => {
    const parts = (props.user?.name || "?").trim().split(/\s+/).filter(Boolean);
    return ((parts[0]?.[0] || "") + (parts[1]?.[0] || "")).toUpperCase() || "?";
});
</script>
