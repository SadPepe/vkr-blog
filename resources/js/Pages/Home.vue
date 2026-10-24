<!-- resources/js/Pages/Home.vue -->
<template>
    <Layout>
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-[260px_minmax(0,1fr)_300px]">
                <!-- Левая колонка: категории -->
                <aside class="self-start lg:sticky lg:top-24">
                    <div class="scrollbar-slim lg:max-h-[calc(100vh_-_8rem)] lg:overflow-y-auto lg:overscroll-contain lg:pr-1 lg:-mr-1 lg:py-1">
                        <CategoriesSidebar
                            :categories="categories"
                            :active-category="activeCategory"
                        />
                    </div>
                </aside>

                <!-- Основной контент -->
                <div>
                    <!-- Заголовок + сортировка -->
                    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-stone-200">
                            {{ activeCategoryTitle || 'Лента статей' }}
                        </h2>

                        <div class="relative">
                            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400 dark:text-stone-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h13M3 8h9m-9 4h9m-9 4h7m10-8l-2-2m0 0l-2 2m2-2v12" />
                            </svg>
                            <select
                                :value="sort"
                                @change="onSortChange"
                                class="appearance-none rounded border border-gray-200 bg-[#FAF8F4] py-2 pl-9 pr-8 text-sm font-medium text-gray-700 shadow-sm outline-none transition-colors hover:border-gray-300 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-500/20 dark:border-white/10 dark:bg-[#252525] dark:text-stone-200 dark:hover:border-white/20 dark:focus:border-emerald-500/50"
                            >
                                <option value="latest">Сначала новые</option>
                                <option value="oldest">Сначала старые</option>
                                <option value="popular">По просмотрам</option>
                                <option value="likes">По лайкам</option>
                                <option value="comments">По комментариям</option>
                            </select>
                            <svg class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400 dark:text-stone-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>

                    <div class="flex flex-col">
                        <ArticleCard
                            v-for="article in articles.data"
                            :key="article.id"
                            :article="article"
                            :categories="categories"
                        />
                    </div>

                    <!-- Пагинация -->
                    <Pagination
                        v-if="articles && articles.links && articles.links.length > 3"
                        :links="articles.links"
                        class="mt-8"
                    />

                    <!-- Сообщение если нет статей -->
                    <div
                        v-if="articles.data.length === 0"
                        class="py-12 text-center"
                    >
                        <svg class="mx-auto mb-4 h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <h3 class="mb-2 text-xl font-semibold text-gray-600 dark:text-stone-400">
                            Статей пока нет
                        </h3>
                        <p class="mb-6 text-gray-500 dark:text-stone-500">
                            Станьте первым автором блога и поделитесь интересной статьёй с сообществом.
                        </p>
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('create')"
                            class="inline-flex items-center justify-center rounded bg-gradient-to-r from-emerald-600 to-teal-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-emerald-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-[#252525]"
                        >
                            Написать статью
                        </Link>
                    </div>
                </div>

                <!-- Сайдбар -->
                <div class="self-start lg:sticky lg:top-24">
                    <div class="space-y-6 scrollbar-slim lg:max-h-[calc(100vh_-_8rem)] lg:overflow-y-auto lg:overscroll-contain lg:pr-1 lg:-mr-1 lg:py-1">
                        <!-- Рекомендации -->
                        <PopularArticles
                            v-if="popularArticles && popularArticles.length > 0"
                            :articles="popularArticles"
                            title="Рекомендации"
                            subtitle="Популярное за неделю"
                        />

                    <!-- Приглашение написать статью -->
                    <div class="rounded-md border border-gray-200/60 bg-[#FAF8F4] p-6 shadow-sm dark:border-white/5 dark:bg-[#252525]">
                        <!-- Залогиненный пользователь -->
                        <template v-if="$page.props.auth.user">
                            <h3 class="mb-1 text-lg font-bold text-gray-800 dark:text-stone-300">
                                Привет, {{ firstName }}!
                            </h3>
                            <p class="mb-4 text-sm text-gray-500 dark:text-stone-400">
                                Поделитесь своими знаниями и опытом с сообществом
                            </p>
                            <Link
                                :href="route('create')"
                                class="inline-flex w-full items-center justify-center gap-2 rounded bg-gradient-to-r from-emerald-600 to-teal-600 px-4 py-2.5 text-center text-sm font-semibold text-white shadow-md shadow-emerald-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-[#252525]"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Написать статью
                            </Link>
                        </template>

                        <!-- Гость -->
                        <template v-else>
                            <h3 class="mb-1 text-lg font-bold text-gray-800 dark:text-stone-300">
                                Хотите делиться статьями?
                            </h3>
                            <p class="mb-4 text-sm text-gray-500 dark:text-stone-400">
                                Зарегистрируйтесь, чтобы публиковать статьи и оставлять комментарии
                            </p>
                            <div class="space-y-2.5">
                                <Link
                                    :href="route('register')"
                                    class="inline-flex w-full items-center justify-center rounded bg-gradient-to-r from-emerald-600 to-teal-600 px-4 py-2.5 text-center text-sm font-semibold text-white shadow-md shadow-emerald-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg"
                                >
                                    Зарегистрироваться
                                </Link>
                                <Link
                                    :href="route('login')"
                                    class="inline-flex w-full items-center justify-center rounded border border-gray-300 bg-[#FAF8F4] px-4 py-2.5 text-center text-sm font-semibold text-gray-700 transition-colors hover:bg-gray-50 dark:border-stone-600 dark:bg-transparent dark:text-stone-200 dark:hover:bg-stone-700"
                                >
                                    Войти
                                </Link>
                            </div>
                        </template>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>

<script setup>
import { computed } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import Layout from "@/Layouts/Layout.vue";
import Pagination from "@/Components/Pagination.vue";
import CategoriesSidebar from "@/Components/CategoriesSidebar.vue";
import PopularArticles from "@/Components/PopularArticles.vue";
import ArticleCard from "@/Components/ArticleCard.vue";

const page = usePage();

const firstName = computed(() => {
    const name = page.props.auth?.user?.name ?? "";
    return name.split(" ")[0] || name;
});

const props = defineProps({
    articles: {
        type: Object,
        required: true,
    },
    popularArticles: {
        type: Array,
        default: () => [],
    },
    categories: {
        type: Array,
        default: () => [],
    },
    activeCategory: {
        type: String,
        default: null,
    },
    sort: {
        type: String,
        default: "latest",
    },
});

const activeCategoryTitle = computed(() => {
    if (!props.activeCategory) return null;
    const category = props.categories.find((c) => c.slug === props.activeCategory);
    return category ? category.name : props.activeCategory;
});

const onSortChange = (event) => {
    router.get(window.location.pathname, { sort: event.target.value }, {
        preserveScroll: true,
        preserveState: false,
        replace: true,
    });
};
</script>

<style scoped>
/* Стили для темной темы */
:deep(.prose) {
    max-width: none !important;
}

:deep(.prose h1) {
    color: rgb(31 41 55 / var(--tw-text-opacity, 1));
}

.dark :deep(.prose h1) {
    color: rgb(231 229 228 / var(--tw-text-opacity, 1));
}

:deep(.prose p) {
    color: rgb(75 85 99 / var(--tw-text-opacity, 1));
}

.dark :deep(.prose p) {
    color: rgb(168 162 158 / var(--tw-text-opacity, 1));
}
</style>