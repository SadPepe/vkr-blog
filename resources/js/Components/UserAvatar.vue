<template>
    <img
        v-if="user?.avatar_url"
        :src="user.avatar_url"
        :alt="user.name"
        class="rounded-full object-cover ring-1 ring-black/5 dark:ring-white/10"
        :style="{ width: `${size}px`, height: `${size}px` }"
    />
    <span
        v-else
        :class="colorClass"
        class="inline-flex shrink-0 select-none items-center justify-center rounded-full font-semibold text-white"
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
});

const palettes = [
    "bg-gradient-to-br from-indigo-500 to-blue-500",
    "bg-gradient-to-br from-emerald-500 to-teal-500",
    "bg-gradient-to-br from-orange-400 to-amber-500",
    "bg-gradient-to-br from-rose-400 to-pink-500",
    "bg-gradient-to-br from-violet-500 to-purple-500",
    "bg-gradient-to-br from-sky-400 to-cyan-500",
    "bg-gradient-to-br from-fuchsia-500 to-pink-500",
    "bg-gradient-to-br from-lime-500 to-green-600",
];

const initials = computed(() => {
    const parts = (props.user?.name || "?").trim().split(/\s+/).filter(Boolean);
    return ((parts[0]?.[0] || "") + (parts[1]?.[0] || "")).toUpperCase() || "?";
});

const colorClass = computed(() => {
    const name = props.user?.name || "";
    let hash = 0;
    for (let i = 0; i < name.length; i++) {
        hash = (hash * 31 + name.charCodeAt(i)) >>> 0;
    }
    return palettes[hash % palettes.length];
});
</script>