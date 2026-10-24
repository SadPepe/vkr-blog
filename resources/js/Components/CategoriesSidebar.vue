<!-- resources/js/Components/CategoriesSidebar.vue -->
<template>
    <nav
        class="rounded-md border border-gray-200/60 bg-[#FAF8F4] p-4 shadow-sm dark:border-white/5 dark:bg-[#252525]"
    >
        <p
            class="mb-3 px-2 text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-stone-500"
        >
            Категории
        </p>
        <div class="flex flex-col gap-1">
            <Link href="/" :class="itemClass(active)" :aria-current="active === 'all' ? 'page' : undefined">
                Все категории
            </Link>
            <Link
                v-for="category in categories"
                :key="category.id"
                :href="`/categories/${category.slug}`"
                :class="itemClass(active)"
                :aria-current="active === category.slug ? 'page' : undefined"
            >
                {{ category.name }}
            </Link>
        </div>
    </nav>
</template>

<script setup>
import { computed } from "vue";
import { Link, usePage } from "@inertiajs/vue3";

const page = usePage();

const props = defineProps({
    categories: {
        type: Array,
        default: () => [],
    },
    activeCategory: {
        type: String,
        default: null,
    },
});

const active = computed(() => {
    if (props.activeCategory) return props.activeCategory;
    const path = page.url;
    if (path.includes("/categories/")) {
        return path.split("/categories/")[1];
    }
    return "all";
});

const itemClass = (item) => {
    const base =
        "flex items-center rounded px-2.5 py-2 text-sm font-medium transition-colors";
    if (item === active.value) {
        return `${base} bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-300`;
    }
    return `${base} text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-stone-400 dark:hover:bg-white/5 dark:hover:text-stone-200`;
};
</script>