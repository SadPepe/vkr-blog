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

                    <!-- Сортировка: сегменты на подложке, как переключатель разделов на форуме -->
                    <nav aria-label="Сортировка" class="mt-4 flex gap-1 overflow-x-auto bg-surface-muted p-1">
                        <button
                            v-for="option in sortOptions"
                            :key="option.value"
                            type="button"
                            class="inline-flex min-h-10 shrink-0 items-center whitespace-nowrap px-3 text-sm transition-colors"
                            :class="sort === option.value ? 'bg-accent font-medium text-accent-contrast' : 'text-content hover:bg-surface'"
                            :aria-current="sort === option.value ? 'true' : undefined"
                            @click="onSortChange(option.value)"
                        >
                            {{ option.label }}
                        </button>
                    </nav>

                    <!-- Лента как на DTF: панели с радиусом, между ними шов --layout-feed-gap-y (4px) -->
                    <ul v-if="feed.length" class="mt-5 flex flex-col gap-[var(--layout-feed-gap-y)]">
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

                <!-- Афиша: реальные просмотры крупными цифрами -->
                <aside v-if="popularArticles.length" class="min-w-0 lg:sticky lg:top-8 lg:self-start">
                    <div class="border border-line bg-surface">
                        <h2 class="border-b border-line bg-surface-muted px-4 py-2.5 text-[0.9375rem] font-semibold text-content">
                            По просмотрам
                        </h2>
                        <ul>
                            <li v-for="article in railArticles" :key="article.id">
                                <Link
                                    :href="route('articles.show', article.slug)"
                                    class="group block px-4 py-3 transition-colors hover:bg-surface-muted"
                                >
                                    <span class="flex items-baseline gap-1.5">
                                        <span
                                            class="num font-display text-[1.25rem] font-bold leading-none tracking-[-0.02em] text-content transition-colors group-hover:text-accent"
                                        >
                                            {{ formatCount(article.views_count) }}
                                        </span>
                                        <span class="text-[0.8125rem] text-muted">{{ pluralViews(article.views_count) }}</span>
                                    </span>
                                    <span
                                        class="mt-1 block text-[0.9375rem] font-medium leading-snug text-content transition-colors group-hover:text-accent"
                                    >
                                        {{ article.title }}
                                    </span>
                                    <span class="mt-1 flex items-center gap-2 text-[0.8125rem] text-muted">
                                        <span class="hidden sm:inline-flex"><UserAvatar :user="article.user" :size="18" /></span>
                                        <span>{{ article.user.name }}</span>
                                    </span>
                                </Link>
                            </li>
                        </ul>
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
import UserAvatar from "@/Components/UserAvatar.vue";
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

const pluralViews = (n) => {
    const mod10 = n % 10;
    const mod100 = n % 100;
    if (mod10 === 1 && mod100 !== 11) return "просмотр";
    if (mod10 >= 2 && mod10 <= 4 && (mod100 < 12 || mod100 > 14)) return "просмотра";
    return "просмотров";
};

const today = new Date();
const todayISO = today.toISOString().slice(0, 10);
const todayLabel = new Intl.DateTimeFormat("ru-RU", {
    day: "numeric",
    month: "long",
    year: "numeric",
}).format(today);

const formatCount = (n) => new Intl.NumberFormat("ru-RU").format(n ?? 0);

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
