<!-- resources/js/Pages/SearchResults.vue -->
<template>
    <Layout>
        <h1
            v-if="searchQuery"
            class="pt-2 pl-4 pr-2 mb-1 text-4xl leading-tight font-bold text-gray-800 dark:text-stone-300"
        >
            Результаты поиска: {{ searchQuery }}
        </h1>
        <p
            v-if="searchQuery && articles.total > 0"
            class="mb-9 pl-4 text-sm text-gray-500 dark:text-stone-400"
        >
            Найдено: {{ articles.total }} {{ plural(articles.total, ['статья', 'статьи', 'статей']) }}
        </p>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3 md:px-6 sm:px-4">
            <ArticleCard
                v-for="article in articles.data"
                :key="article.id"
                :article="article"
            />
        </div>

        <!-- Пустое состояние -->
        <div
            v-if="!articles.data.length"
            class="py-16 text-center"
        >
            <svg class="mx-auto mb-4 h-16 w-16 text-gray-400 dark:text-stone-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <h3 class="mb-2 text-xl font-semibold text-gray-600 dark:text-stone-400">
                {{ searchQuery ? 'Ничего не найдено' : 'Поиск по блогу' }}
            </h3>
            <p v-if="searchQuery" class="mb-6 text-gray-500 dark:text-stone-500">
                По запросу «{{ searchQuery }}» ничего не найдено. Попробуйте изменить запрос.
            </p>
            <p v-else class="mb-6 text-gray-500 dark:text-stone-500">
                Введите в шапке сайта запрос, чтобы найти интересные статьи.
            </p>
            <Link
                :href="route('home')"
                class="inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-indigo-600 to-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-indigo-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg"
            >
                На главную
            </Link>
        </div>

        <Pagination
            v-if="articles && articles.links && articles.links.length > 3"
            :links="articles.links"
        />
    </Layout>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";
import Layout from "@/Layouts/Layout.vue";
import Pagination from "@/Components/Pagination.vue";
import ArticleCard from "@/Components/ArticleCard.vue";

defineProps({
    articles: Object,
    searchQuery: String,
});

const plural = (count, [one, few, many]) => {
    const mod10 = count % 10;
    const mod100 = count % 100;
    if (mod10 === 1 && mod100 !== 11) return one;
    if (mod10 >= 2 && mod10 <= 4 && (mod100 < 12 || mod100 > 14)) return few;
    return many;
};
</script>