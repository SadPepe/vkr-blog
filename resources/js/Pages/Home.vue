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
            <div class="grid gap-y-10 xl:grid-cols-12 xl:gap-x-14">
                <div class="xl:col-span-8">
                    <!-- Заголовок ленты: дата и счётчик в строке с ним, внутри колонки -->
                    <div class="flex flex-wrap items-baseline gap-x-6 gap-y-2">
                        <h1 class="type-display-xl text-content">
                            {{ activeCategoryTitle || "Лента" }}
                        </h1>
                        <p class="type-meta">
                            <time :datetime="todayISO">{{ todayLabel }}</time>
                            <span aria-hidden="true"> · </span>
                            <span class="num">{{ articles.total }}</span> {{ pluralArticles(articles.total) }}
                        </p>
                    </div>

                    <p class="measure mt-6 text-[1.125rem] leading-snug text-content">
                        Тексты авторов блога: фотографы, музыканты, художники и те, кто пишет словами. Без редакции и повестки.
                    </p>

                    <!-- Сортировка: строкой текстовых ссылок, без пилюль. Категории живут в шапке -->
                    <nav aria-label="Сортировка" class="mt-8">
                        <ul class="grid grid-cols-3 items-baseline gap-x-4 gap-y-2 sm:grid-cols-5 lg:flex lg:flex-wrap">
                            <li v-for="option in sortOptions" :key="option.value">
                                <button
                                    type="button"
                                    class="inline-flex min-h-11 items-center text-sm transition-colors hover:text-accent lg:min-h-0"
                                    :class="sort === option.value ? 'text-content underline decoration-2 underline-offset-4' : 'text-muted'"
                                    :aria-current="sort === option.value ? 'true' : undefined"
                                    @click="onSortChange(option.value)"
                                >
                                    {{ option.label }}
                                </button>
                            </li>
                        </ul>
                    </nav>

                    <ul v-if="feed.length" class="mt-2 flex flex-col">
                        <li v-for="article in feed" :key="article.id" class="py-8 first:pt-0">
                            <!-- Вертикальный блок: автор, затем то, что он написал -->
                            <article>
                                <header class="flex items-center gap-3">
                                    <UserAvatar :user="article.user" :size="44" />
                                    <p class="min-w-0 text-[1.0625rem] leading-snug">
                                        <Link
                                            :href="route('authors.show', article.user.id)"
                                            class="font-medium text-content transition-colors hover:text-accent"
                                        >
                                            {{ article.user.name }}
                                        </Link>
                                        <span class="text-muted" aria-hidden="true"> · </span>
                                        <Link
                                            v-if="article.category_slug"
                                            :href="route('articlesByCategory', article.category_slug)"
                                            class="text-muted transition-colors hover:text-accent"
                                        >
                                            {{ categoryName(article.category_slug) }}
                                        </Link>
                                        <span v-else class="text-muted">Без категории</span>
                                        <span class="text-muted" aria-hidden="true"> · </span>
                                        <time :datetime="article.created_at" class="whitespace-nowrap text-muted">{{
                                            formatDate(article.created_at)
                                        }}</time>
                                    </p>
                                </header>

                                <h3
                                    v-if="postHeading(article)"
                                    class="mt-4 font-display text-[1.75rem] font-bold leading-[1.15] tracking-[-0.02em] text-content"
                                >
                                    <Link :href="route('articles.show', article.slug)" class="transition-colors hover:text-accent">
                                        {{ postHeading(article) }}
                                    </Link>
                                </h3>

                                <!-- Тело поста: авторский порядок блоков, без отдельной обложки -->
                                <div
                                    class="post-content mt-4 text-content"
                                    :class="{ 'feed-body--clamped': article.has_more && !isExpanded(article) }"
                                    v-html="postHtml(article)"
                                />

                                <button
                                    v-if="article.has_more || isExpanded(article)"
                                    type="button"
                                    class="mt-5 inline-flex min-h-11 items-center text-sm text-accent transition-colors hover:text-accent-hover"
                                    :aria-expanded="isExpanded(article)"
                                    @click="toggleExpand(article)"
                                >
                                    {{ isExpanded(article) ? "Свернуть" : isBusy(article) ? "Загружаю…" : "Развернуть" }}
                                </button>

                                <p class="mt-4 flex flex-wrap items-baseline gap-x-5 gap-y-1 text-[1.0625rem] text-muted">
                                    <span
                                        ><span class="num">{{ article.views_count }}</span> {{ pluralViews(article.views_count) }}</span
                                    >
                                    <span
                                        ><span class="num">{{ article.likes_count }}</span> {{ pluralLikes(article.likes_count) }}</span
                                    >
                                    <span
                                        ><span class="num">{{ article.comments_count }}</span>
                                        {{ pluralComments(article.comments_count) }}</span
                                    >
                                </p>
                            </article>
                        </li>
                    </ul>

                    <!-- Пустая лента: намеренная композиция, а не заглушка -->
                    <div v-if="articles.data.length === 0" class="mt-16 max-w-[46rem]">
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
                <aside v-if="popularArticles.length" class="xl:col-span-4 xl:sticky xl:top-8 xl:self-start">
                    <h2 class="type-h2 text-content">Афиша: самые читаемые</h2>
                    <ul class="mt-6 flex flex-col gap-6">
                        <li v-for="article in railArticles" :key="article.id">
                            <Link
                                :href="route('articles.show', article.slug)"
                                class="group flex flex-wrap items-baseline gap-x-2.5 gap-y-1"
                            >
                                <span
                                    class="num font-display text-[1.75rem] font-bold leading-none tracking-[-0.02em] text-content transition-colors group-hover:text-accent"
                                >
                                    {{ formatCount(article.views_count) }}
                                </span>
                                <span class="text-[1.0625rem] text-muted">{{ pluralViews(article.views_count) }}</span>
                                <span
                                    class="basis-full text-[1.0625rem] font-medium leading-snug text-content transition-colors group-hover:text-accent"
                                >
                                    {{ article.title }}
                                </span>
                            </Link>
                        </li>
                    </ul>
                </aside>
            </div>
        </div>
    </Layout>
</template>

<script setup>
import { computed, ref } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import Layout from "@/Layouts/Layout.vue";
import Pagination from "@/Components/Pagination.vue";
import UserAvatar from "@/Components/UserAvatar.vue";

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

const categoryName = (slug) => props.categories.find((c) => c.slug === slug)?.name ?? "Без категории";

const railArticles = computed(() => [...props.popularArticles].sort((a, b) => (b.views_count ?? 0) - (a.views_count ?? 0)).slice(0, 5));

const trimExcerpt = (text) => {
    const value = (text ?? "").trim();
    if (!value) return "";
    return /[.!?…»)]$/.test(value) ? value : `${value}…`;
};

// Лид — только на первой странице пагинатора: на второй-третьей лента идёт списком
const isFirstPage = computed(() => (props.articles.current_page ?? 1) === 1);

const lead = computed(() => (isFirstPage.value ? (props.articles.data[0] ?? null) : null));
const feed = computed(() => (isFirstPage.value ? props.articles.data.slice(1) : props.articles.data));

const pluralArticles = (n) => {
    const mod10 = n % 10;
    const mod100 = n % 100;
    if (mod10 === 1 && mod100 !== 11) return "статья";
    if (mod10 >= 2 && mod10 <= 4 && (mod100 < 12 || mod100 > 14)) return "статьи";
    return "статей";
};

// Развёрнутые посты: полный html тянем тем же эндпоинтом, что и в ленте-карточке
const expanded = ref(new Set());
const fullHtml = ref({});
const loading = ref(new Set());

const isShort = (article) => article.has_more === false;
const isExpanded = (article) => expanded.value.has(article.id);
const isBusy = (article) => loading.value.has(article.id);
const bodyHtml = (article) => fullHtml.value[article.id] ?? "";

// В ленте авторские подзаголовки не должны конкурировать с заголовками страницы:
// оставляем их визуально, но как абзацы, а не как h2/h3
const demoteHeadings = (html) =>
    (html || "").replace(/<(\/?)h([1-6])\b[^>]*>/gi, (_, close) => (close ? "</p>" : '<p class="post-subhead">'));

const postHtml = (article) => demoteHeadings(isExpanded(article) ? bodyHtml(article) : article.preview);

const toggleExpand = async (article) => {
    const nextExpanded = new Set(expanded.value);

    if (nextExpanded.has(article.id)) {
        nextExpanded.delete(article.id);
        expanded.value = nextExpanded;
        return;
    }

    if (!fullHtml.value[article.id] && article.has_more) {
        const nextLoading = new Set(loading.value);
        nextLoading.add(article.id);
        loading.value = nextLoading;

        try {
            const response = await fetch(route("articles.excerpt", article.slug), {
                headers: { Accept: "application/json" },
            });
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            const data = await response.json();
            fullHtml.value = { ...fullHtml.value, [article.id]: data.html || article.preview };
        } catch {
            router.visit(route("articles.show", article.slug));
            return;
        } finally {
            const done = new Set(loading.value);
            done.delete(article.id);
            loading.value = done;
        }
    }

    expanded.value = new Set([...expanded.value, article.id]);
};
const postHeading = (article) => (article.title ?? "").trim();

// Текст поста: под заголовком — выдержка, без заголовка — текст автора целиком
const postText = (article) => (article.excerpt ?? "").trim();

const pluralLikes = (n) => {
    const mod10 = n % 10;
    const mod100 = n % 100;
    if (mod10 === 1 && mod100 !== 11) return "лайк";
    if (mod10 >= 2 && mod10 <= 4 && (mod100 < 12 || mod100 > 14)) return "лайка";
    return "лайков";
};

const pluralComments = (n) => {
    const mod10 = n % 10;
    const mod100 = n % 100;
    if (mod10 === 1 && mod100 !== 11) return "комментарий";
    if (mod10 >= 2 && mod10 <= 4 && (mod100 < 12 || mod100 > 14)) return "комментария";
    return "комментариев";
};

const pluralViews = (n) => {
    const mod10 = n % 10;
    const mod100 = n % 100;
    if (mod10 === 1 && mod100 !== 11) return "просмотр";
    if (mod10 >= 2 && mod10 <= 4 && (mod100 < 12 || mod100 > 14)) return "просмотра";
    return "просмотров";
};

const formatDate = (value) => new Intl.DateTimeFormat("ru-RU", { day: "numeric", month: "long", year: "numeric" }).format(new Date(value));

const today = new Date();
const todayISO = today.toISOString().slice(0, 10);
const todayLabel = new Intl.DateTimeFormat("ru-RU", {
    day: "numeric",
    month: "long",
    year: "numeric",
}).format(today);

const formatCount = (n) => new Intl.NumberFormat("ru-RU").format(n ?? 0);

const emptyTitle = computed(() => (props.activeCategory ? `Тут пока пусто` : "Здесь пока нет статей"));

const emptyText = computed(() =>
    props.activeCategory
        ? `В категории «${activeCategoryTitle.value}» ещё нет статей. Посмотрите всю ленту — там точно есть что почитать.`
        : "Лента наполнится, когда авторы опубликуют первые тексты. Можно начать с этой страницы.",
);

const pageDescription = computed(() =>
    props.activeCategory
        ? `Статьи в категории «${activeCategoryTitle.value}» — читайте на МОЙ.БЛОГ.`
        : "МОЙ.БЛОГ — площадка, где авторы делятся знаниями, опытом и историями. Свежие статьи, обсуждения и лайки.",
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
