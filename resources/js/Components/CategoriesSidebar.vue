<!-- resources/js/Components/CategoriesSidebar.vue -->
<template>
    <!-- Мобильные чипы: компактно, не отнимают экран у ленты -->
    <div class="lg:hidden">
        <div class="-mx-4 flex gap-2 overflow-x-auto px-4 pb-1">
            <Link
                :href="route('home')"
                prefetch="hover"
                class="inline-flex shrink-0 items-center gap-1.5 rounded-full border px-3.5 py-1.5 text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2 focus-visible:ring-offset-page"
                :class="
                    active === 'all'
                        ? 'border-accent bg-accent text-accent-contrast'
                        : 'border-line bg-surface text-content hover:border-accent hover:text-accent'
                "
                :aria-current="active === 'all' ? 'page' : undefined"
            >
                Все статьи
                <span class="text-[11px] tabular-nums" :class="active === 'all' ? 'opacity-80' : 'text-muted'">
                    {{ total }}
                </span>
            </Link>

            <Link
                v-for="category in categories"
                :key="category.id"
                :href="route('articlesByCategory', category.slug)"
                prefetch="hover"
                class="inline-flex shrink-0 items-center gap-1.5 rounded-full border px-3.5 py-1.5 text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2 focus-visible:ring-offset-page"
                :class="
                    active === category.slug
                        ? 'border-accent bg-accent text-accent-contrast'
                        : 'border-line bg-surface text-content hover:border-accent hover:text-accent'
                "
                :aria-current="active === category.slug ? 'page' : undefined"
            >
                {{ category.name }}
                <span class="text-[11px] tabular-nums" :class="active === category.slug ? 'opacity-80' : 'text-muted'">
                    {{ category.articles_count ?? 0 }}
                </span>
            </Link>
        </div>
    </div>

    <!-- Десктопный сайдбар -->
    <nav class="hidden rounded-md border border-line bg-surface shadow-sm lg:block" aria-label="Категории">
        <div class="flex items-center justify-between border-b border-line px-4 py-3">
            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-muted">Категории</p>
            <span class="text-xs tabular-nums text-muted">{{ total }}</span>
        </div>

        <ul class="space-y-0.5 p-2">
            <li v-for="item in items" :key="item.key">
                <Link
                    :href="item.href"
                    prefetch="hover"
                    class="group flex items-center justify-between gap-3 rounded-md px-3 py-2 text-sm transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2 focus-visible:ring-offset-surface"
                    :class="active === item.key ? 'bg-accent font-semibold text-accent-contrast' : 'text-content hover:bg-surface-muted'"
                    :aria-current="active === item.key ? 'page' : undefined"
                >
                    <span class="truncate">{{ item.name }}</span>
                    <span
                        class="shrink-0 text-xs tabular-nums"
                        :class="active === item.key ? 'text-accent-contrast/80' : 'text-muted group-hover:text-accent'"
                    >
                        {{ item.count }}
                    </span>
                </Link>
            </li>
        </ul>
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

    const path = page.url.split("?")[0];

    if (path.startsWith("/categories/")) {
        return decodeURIComponent(path.replace("/categories/", ""));
    }

    return "all";
});

const total = computed(() => props.categories.reduce((sum, category) => sum + (category.articles_count ?? 0), 0));

const items = computed(() => [
    { key: "all", name: "Все статьи", href: route("home"), count: total.value },
    ...props.categories.map((category) => ({
        key: category.slug,
        name: category.name,
        href: route("articlesByCategory", category.slug),
        count: category.articles_count ?? 0,
    })),
]);
</script>
