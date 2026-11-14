<!-- resources/js/Pages/SearchResults.vue -->
<template>
    <Layout>
        <Head :title="searchQuery ? `Поиск: ${searchQuery}` : 'Поиск по блогу'" />

        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- Поле поиска на самой странице: из нижней панели сюда попадают
                 с пустым запросом, и раньше вписывать было некуда -->
            <form class="mb-6" role="search" @submit.prevent="submit">
                <label for="search-input" class="sr-only">Поиск по блогу</label>
                <div class="flex flex-wrap items-center gap-2">
                    <input
                        id="search-input"
                        ref="inputEl"
                        v-model="query"
                        type="search"
                        inputmode="search"
                        autocomplete="off"
                        placeholder="Слово из заголовка или текста"
                        class="min-h-11 w-full max-w-xl rounded-full border border-line-strong bg-surface px-4 py-2 text-content placeholder:text-muted focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/20 sm:w-auto sm:flex-1"
                    />
                    <button
                        type="submit"
                        class="min-h-11 shrink-0 rounded-full bg-accent px-5 py-2 text-sm font-semibold text-accent-contrast transition-colors hover:bg-accent-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2"
                    >
                        Найти
                    </button>
                </div>
            </form>

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
                    Введите запрос в поле выше — поиск идёт по заголовкам и тексту публикаций.
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
import { computed, onMounted, ref, watch } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import Layout from "@/Layouts/Layout.vue";
import Pagination from "@/Components/Pagination.vue";
import ArticleGridCard from "@/Components/ArticleGridCard.vue";

const props = defineProps({
    articles: {
        type: Object,
        required: true,
    },
    searchQuery: {
        type: String,
        default: null,
    },
});

const query = ref(props.searchQuery ?? "");
const inputEl = ref(null);

// Переход по истории и по ссылкам меняет prop — поле не должно отставать
watch(
    () => props.searchQuery,
    (value) => (query.value = value ?? ""),
);

const submit = () => {
    router.get(route("articles.search"), { query: query.value.trim() }, { preserveScroll: false });
};

onMounted(() => inputEl.value?.focus());

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
