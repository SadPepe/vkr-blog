<!-- resources/js/Pages/SearchResults.vue -->
<template>
    <Layout>
        <Head :title="searchQuery ? `Поиск: ${searchQuery}` : 'Поиск по блогу'" />

        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <header class="mb-8">
                <h1 class="text-2xl font-bold tracking-tight text-content sm:text-3xl">
                    <template v-if="searchQuery">Результаты поиска: «{{ searchQuery }}»</template>
                    <template v-else>Поиск по блогу</template>
                </h1>
                <p v-if="searchQuery && articles.total > 0" class="mt-2 text-sm text-muted">
                    Найдено: {{ articles.total }} {{ plural(articles.total, ["статья", "статьи", "статей"]) }}
                </p>
            </header>

            <div v-if="articles.data.length" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <ArticleGridCard v-for="article in articles.data" :key="article.id" :article="article" :categories="categories" />
            </div>

            <!-- Пустое состояние -->
            <div v-else class="py-16 text-center">
                <svg
                    class="mx-auto mb-4 h-16 w-16 text-muted"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <h2 class="mb-2 text-xl font-semibold text-content">
                    {{ searchQuery ? "Ничего не найдено" : "Введите запрос" }}
                </h2>
                <p v-if="searchQuery" class="mx-auto mb-6 max-w-md text-muted">
                    По запросу «{{ searchQuery }}» ничего не найдено. Попробуйте изменить формулировку или загляните в ленту.
                </p>
                <p v-else class="mx-auto mb-6 max-w-md text-muted">
                    Воспользуйтесь поиском в шапке сайта или вернитесь к ленте статей.
                </p>
                <Link
                    :href="route('home')"
                    class="inline-flex items-center justify-center rounded bg-accent hover:bg-accent-hover px-5 py-2.5 text-sm font-semibold text-accent-contrast shadow-md transition-all duration-200 hover:shadow-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2"
                >
                    На главную
                </Link>
            </div>

            <Pagination v-if="articles && articles.links && articles.links.length > 3" :links="articles.links" class="mt-10" />
        </div>
    </Layout>
</template>

<script setup>
import { computed } from "vue";
import { Head, Link, usePage } from "@inertiajs/vue3";
import Layout from "@/Layouts/Layout.vue";
import Pagination from "@/Components/Pagination.vue";
import ArticleGridCard from "@/Components/ArticleGridCard.vue";

defineProps({
    articles: {
        type: Object,
        required: true,
    },
    searchQuery: {
        type: String,
        default: null,
    },
});

const page = usePage();
const categories = computed(() => page.props.categories ?? []);

const plural = (count, [one, few, many]) => {
    const mod10 = count % 10;
    const mod100 = count % 100;
    if (mod10 === 1 && mod100 !== 11) return one;
    if (mod10 >= 2 && mod10 <= 4 && (mod100 < 10 || mod100 >= 20)) return few;
    return many;
};
</script>
