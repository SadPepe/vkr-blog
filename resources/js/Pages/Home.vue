<!-- resources/js/Pages/Home.vue -->
<template>
    <Layout>
        <Head :title="pageTitle">
            <meta name="description" :content="pageDescription" />
            <meta property="og:title" :content="pageTitle" />
            <meta property="og:description" :content="pageDescription" />
            <meta property="og:type" content="website" />
        </Head>

        <div class="page-shell pb-24 pt-10 lg:pt-14">
            <div class="home-layout">
                <div class="min-w-0">
                    <!-- Заголовок доски: имя и счётчик одной строкой, без журнального лида -->
                    <header class="border-b border-line pb-4">
                        <div class="flex flex-wrap items-end justify-between gap-x-6 gap-y-2">
                            <h1 class="type-display-l text-content">
                                {{ activeCategoryTitle || "Лента" }}
                            </h1>
                            <p class="type-meta pb-1">
                                <span class="num">{{ articles.total }}</span> {{ pluralArticles(articles.total) }}
                                <span aria-hidden="true"> · </span>
                                <time :datetime="todayISO">{{ todayLabel }}</time>
                            </p>
                        </div>
                        <p class="mt-2 text-[1.125rem] leading-snug text-content">{{ pageLead }}</p>
                    </header>

                    <!-- Сортировка: мягкие pill-чипы вместо прямоугольных сегментов -->
                    <nav aria-label="Сортировка" class="mt-5 flex gap-2 overflow-x-auto pb-1">
                        <button
                            v-for="option in sortOptions"
                            :key="option.value"
                            type="button"
                            class="ui-pill shrink-0 whitespace-nowrap"
                            :class="sort === option.value ? 'ui-pill--solid' : ''"
                            :aria-current="sort === option.value ? 'true' : undefined"
                            @click="onSortChange(option.value)"
                        >
                            {{ option.label }}
                        </button>
                    </nav>

                    <!-- Открытая лента: посты без подложек, между ними линия и воздух -->
                    <ul v-if="feed.length" class="feed-list mt-6">
                        <li v-for="article in feed" :key="article.id">
                            <FeedPost :article="article" :categories="categories" />
                        </li>
                    </ul>

                    <!-- Пустая лента: намеренная композиция, а не заглушка -->
                    <div v-if="articles.data.length === 0" class="mt-5 max-w-[46rem] border border-line bg-surface p-6 sm:p-8">
                        <p class="type-display-l text-content">{{ emptyTitle }}</p>
                        <p class="measure mt-5 text-muted">{{ emptyText }}</p>
                        <div class="mt-8 flex flex-wrap items-center gap-x-8 gap-y-3">
                            <Link
                                v-if="activeCategory"
                                :href="route('home')"
                                class="bg-accent px-5 py-2.5 text-sm font-semibold text-accent-contrast transition-colors hover:bg-accent-hover"
                            >
                                Смотреть все статьи
                            </Link>
                            <Link
                                v-if="$page.props.auth.user"
                                :href="route('create')"
                                class="bg-accent px-5 py-2.5 text-sm font-semibold text-accent-contrast transition-colors hover:bg-accent-hover"
                            >
                                Написать статью
                            </Link>
                            <Link
                                v-else
                                :href="route('register')"
                                class="bg-accent px-5 py-2.5 text-sm font-semibold text-accent-contrast transition-colors hover:bg-accent-hover"
                            >
                                Стать автором
                            </Link>
                        </div>
                    </div>

                    <Pagination v-if="articles.links && articles.links.length > 3" :links="articles.links" class="mt-10" />
                </div>

                <!-- Рейл: активность, авторы и популярное. Открытыми блоками,
                     без общей панели — в одном языке с лентой. -->
                <aside v-if="hasRail" class="min-w-0">
                    <div class="space-y-10">
                        <PosterRail v-if="discussedArticles.length" :articles="discussedArticles" title="Сейчас обсуждают" />
                        <AuthorsRail v-if="activeAuthors.length" :authors="activeAuthors" />
                        <PosterRail v-if="railArticles.length" :articles="railArticles" title="По просмотрам за неделю" />

                        <Link
                            :href="$page.props.auth.user ? route('create') : route('register')"
                            class="ui-pill ui-pill--solid w-full"
                        >
                            {{ $page.props.auth.user ? "Написать статью" : "Стать автором" }}
                        </Link>
                    </div>
                </aside>
            </div>
        </div>
    </Layout>
</template>

<script setup>
import { computed } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import Layout from "@/Layouts/Layout.vue";
import FeedPost from "@/Components/FeedPost.vue";
import PosterRail from "@/Components/PosterRail.vue";
import AuthorsRail from "@/Components/AuthorsRail.vue";
import Pagination from "@/Components/Pagination.vue";

const page = usePage();

const props = defineProps({
    articles: {
        type: Object,
        required: true,
    },
    popularArticles: {
        type: Array,
        default: () => [],
    },
    discussedArticles: {
        type: Array,
        default: () => [],
    },
    activeAuthors: {
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

const sortOptions = [
    { value: "latest", label: "Свежее" },
    { value: "oldest", label: "Старые" },
    { value: "popular", label: "Просмотры" },
    { value: "likes", label: "Лайки" },
    { value: "comments", label: "Обсуждение" },
];

const activeCategoryTitle = computed(() => {
    if (!props.activeCategory) return null;
    const category = props.categories.find((c) => c.slug === props.activeCategory);
    return category ? category.name : props.activeCategory;
});

const railArticles = computed(() => [...props.popularArticles].sort((a, b) => (b.views_count ?? 0) - (a.views_count ?? 0)).slice(0, 5));

const hasRail = computed(() => props.discussedArticles.length > 0 || props.activeAuthors.length > 0 || railArticles.value.length > 0);

// Лента = все статьи страницы: лид из старого дизайна убран, иначе первая
// статья пропадала (при одной статье лента оставалась пустой)
const feed = computed(() => props.articles.data);

const pluralArticles = (n) => {
    const mod10 = n % 10;
    const mod100 = n % 100;
    if (mod10 === 1 && mod100 !== 11) return "статья";
    if (mod10 >= 2 && mod10 <= 4 && (mod100 < 12 || mod100 > 14)) return "статьи";
    return "статей";
};

const today = new Date();
const todayISO = today.toISOString().slice(0, 10);
const todayLabel = new Intl.DateTimeFormat("ru-RU", {
    day: "numeric",
    month: "long",
    year: "numeric",
}).format(today);

// Подзаголовок страницы: h1 — имя доски, под ним одной строкой, что здесь лежит
const pageLead = computed(() =>
    props.activeCategory ? `Все статьи раздела «${activeCategoryTitle.value}».` : "Статьи авторов блога и обсуждения под ними.",
);

const emptyTitle = computed(() => (props.activeCategory ? `Тут пока пусто` : "Здесь пока нет статей"));

const emptyText = computed(() =>
    props.activeCategory
        ? `В категории «${activeCategoryTitle.value}» ещё нет статей. Посмотрите всю ленту — там точно есть что почитать.`
        : "Лента наполнится, когда авторы опубликуют первые тексты. Можно начать с этой страницы.",
);

const pageDescription = computed(() =>
    props.activeCategory
        ? `Статьи в категории «${activeCategoryTitle.value}» — читайте на «Челны подземка».`
        : "«Челны подземка» — блог, где авторы делятся знаниями, опытом и историями. Свежие статьи, обсуждения и лайки.",
);

const pageTitle = computed(() => (props.activeCategory ? activeCategoryTitle.value : "Лента статей"));

const onSortChange = (value) => {
    if (value === props.sort) return;
    router.get(
        window.location.pathname,
        { sort: value },
        {
            preserveScroll: true,
            preserveState: false,
            replace: true,
        },
    );
};
</script>
