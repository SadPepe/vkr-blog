<!-- resources/js/Pages/Home.vue -->
<template>
    <Layout>
        <Head :title="pageTitle">
            <meta name="description" :content="pageDescription" />
            <meta property="og:title" :content="pageTitle" />
            <meta property="og:description" :content="pageDescription" />
            <meta property="og:type" content="website" />
        </Head>

        <div class="page-shell pb-24 pt-5 lg:pt-14">
            <div class="home-layout">
                <div class="min-w-0">
                    <!-- Заголовок доски: имя слева, сортировка ленты справа, без лида -->
                    <header class="pb-10">
                        <div class="flex flex-wrap items-center justify-between gap-x-6 gap-y-3">
                            <h1 class="type-display-l text-content">
                                {{ activeCategoryTitle || "Лента" }}
                            </h1>
                            <!-- Сортировка ленты: один селект справа вместо ряда чипов.
                                 Подпись видна с sm, на телефоне остаётся только пилюля. -->
                            <div class="flex shrink-0 items-center gap-3">
                                <label for="feed-sort" class="type-meta sr-only sm:not-sr-only">Сортировка</label>
                                <div class="relative">
                                    <select
                                        id="feed-sort"
                                        :value="sort"
                                        class="min-h-10 appearance-none rounded-full border border-line-strong bg-surface bg-none py-1 pl-4 pr-9 text-[0.9375rem] font-medium text-content transition-colors hover:border-accent hover:bg-accent-soft focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/20"
                                        @change="onSortChange($event.target.value)"
                                    >
                                        <option v-for="option in sortOptions" :key="option.value" :value="option.value">
                                            {{ option.label }}
                                        </option>
                                    </select>
                                    <ChevronDownIcon
                                        :size="16"
                                        class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-muted"
                                    />
                                </div>
                            </div>
                        </div>
                    </header>

                    <!-- Открытая лента: посты без подложек, между ними линия и воздух -->
                    <ul v-if="feed.length" class="feed-list mt-6">
                        <li v-for="article in feed" :key="article.id">
                            <!-- Меню «⋯» появляется только у своего поста (проверка внутри FeedPost) -->
                            <FeedPost :article="article" :categories="categories">
                                <template #actions>
                                    <ArticleActionsMenu
                                        :article="article"
                                        show-delete
                                        @delete="requestDelete"
                                        @pin="applyPin"
                                        @visibility="applyVisibility"
                                    />
                                </template>
                            </FeedPost>
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

                    <!-- Лента догружается сама: маркер стоит под ней и попадает в кадр
                         за экран до конца, поэтому кнопки нет — только статус. -->
                    <div v-if="feed.length" class="mt-10 flex flex-col items-center gap-3">
                        <p
                            v-if="hasMoreArticles && loadingMore"
                            class="type-meta inline-flex items-center gap-2"
                            aria-live="polite"
                        >
                            <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2" stroke-opacity="0.25" />
                                <path
                                    d="M21 12a9 9 0 0 0-9-9"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                />
                            </svg>
                            Загружаю…
                        </p>
                        <button
                            v-else-if="loadError || !canAutoLoad"
                            type="button"
                            class="ui-pill ui-pill--solid min-h-11 px-5"
                            :disabled="loadingMore"
                            @click="loadMore"
                        >
                            {{ loadError ? "Повторить" : "Показать ещё" }}
                        </button>
                        <p v-else-if="!hasMoreArticles" class="type-meta" aria-live="polite">Это все статьи</p>

                        <p v-if="hasMoreArticles" class="type-meta num" aria-live="polite">
                            Показано {{ feed.length }} из {{ articles.total }}
                        </p>

                        <!-- Маркер автодогрузки: места не занимает, ловит приближение к концу -->
                        <span ref="sentinelRef" class="h-px w-px" aria-hidden="true"></span>
                    </div>
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

        <BackToTop />

        <!-- Удаление своего поста прямо из ленты подтверждаем тем же диалогом, что в профиле -->
        <ConfirmDialog
            :show="articleToDelete !== null"
            title="Удалить статью?"
            :text="
                articleToDelete ? `«${articleToDelete.title || articleToDelete.slug}» и комментарии к ней будут удалены безвозвратно.` : ''
            "
            @confirm="confirmDelete"
            @cancel="articleToDelete = null"
        />
    </Layout>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import Layout from "@/Layouts/Layout.vue";
import FeedPost from "@/Components/FeedPost.vue";
import ArticleActionsMenu from "@/Components/ArticleActionsMenu.vue";
import ConfirmDialog from "@/Components/ConfirmDialog.vue";
import PosterRail from "@/Components/PosterRail.vue";
import AuthorsRail from "@/Components/AuthorsRail.vue";
import BackToTop from "@/Components/BackToTop.vue";
import ChevronDownIcon from "vue-material-design-icons/ChevronDown.vue";

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
    { value: "latest", label: "Новые" },
    { value: "oldest", label: "Старые" },
    { value: "popular", label: "Просмотры" },
    { value: "likes", label: "Лайки" },
    { value: "comments", label: "Обсуждения" },
];

const activeCategoryTitle = computed(() => {
    if (!props.activeCategory) return null;
    const category = props.categories.find((c) => c.slug === props.activeCategory);
    return category ? category.name : props.activeCategory;
});

const railArticles = computed(() => [...props.popularArticles].sort((a, b) => (b.views_count ?? 0) - (a.views_count ?? 0)).slice(0, 5));

const hasRail = computed(() => props.discussedArticles.length > 0 || props.activeAuthors.length > 0 || railArticles.value.length > 0);

// Лента накапливается сама: первая страница приходит из Inertia, следующие
// подгружаются частичным визитом, когда маркер под лентой попадает в кадр.
const feed = ref([...(props.articles.data ?? [])]);
const loadingMore = ref(false);
const loadError = ref(false);
const sentinelRef = ref(null);

// Старый браузер без IntersectionObserver — оставляем кнопку, как было раньше
const canAutoLoad = typeof window !== "undefined" && "IntersectionObserver" in window;

const hasMoreArticles = computed(() => Boolean(props.articles.next_page_url));

// Начинаем грузить заранее, чтобы лента не обрывалась на краю экрана
const LOAD_AHEAD_PX = 600;

let observer = null;

const observeSentinel = () => {
    if (!observer || !sentinelRef.value) return;

    // Перезапуск нужен для высоких экранов: если маркер после подгрузки
    // всё ещё в кадре, сам IntersectionObserver второго раза не даст
    observer.unobserve(sentinelRef.value);
    observer.observe(sentinelRef.value);
};

const loadMore = () => {
    if (loadingMore.value || !props.articles.next_page_url) return;

    loadingMore.value = true;
    loadError.value = false;

    const before = feed.value.length;
    const nextPage = new URL(props.articles.next_page_url, window.location.origin);
    const query = Object.fromEntries(nextPage.searchParams.entries());

    router.get(nextPage.pathname, query, {
        only: ["articles"],
        preserveState: true,
        preserveScroll: true,
        preserveUrl: true,
        replace: true,
        onSuccess: (page) => {
            const incoming = page.props.articles?.data ?? [];
            const visibleIds = new Set(feed.value.map((article) => article.id));

            feed.value.push(...incoming.filter((article) => !visibleIds.has(article.id)));
        },
        onError: () => {
            // Не получилось — показываем «Повторить» вместо бесконечных попыток
            loadError.value = true;
        },
        onFinish: () => {
            loadingMore.value = false;

            // Наблюдение возобновляем только когда лента выросла: иначе
            // повторный заход на ту же страницу дал бы цикл запросов
            if (!loadError.value && feed.value.length > before) observeSentinel();
        },
    });
};

onMounted(() => {
    if (!canAutoLoad) return;

    observer = new IntersectionObserver(
        (entries) => {
            if (entries.some((entry) => entry.isIntersecting)) loadMore();
        },
        { rootMargin: `${LOAD_AHEAD_PX}px 0px` },
    );

    observeSentinel();
});

onBeforeUnmount(() => {
    observer?.disconnect();
    observer = null;
});

// ——— Действия автора со своим постом прямо в ленте ———

const articleToDelete = ref(null);

const requestDelete = (article) => {
    articleToDelete.value = article;
};

const confirmDelete = () => {
    const article = articleToDelete.value;
    articleToDelete.value = null;

    if (!article) return;

    router.delete(route("articles.destroy", { article: article.slug }), {
        preserveScroll: true,
        onSuccess: () => {
            // Лента живёт в локальном срезе (см. loadMore), props её не пересоздают
            feed.value = feed.value.filter((item) => item.id !== article.id);
        },
    });
};

// Пин в общей ленте посты не переставляет — меняется только метка «Закреплено»
const applyPin = (article, pinned) => {
    // У автора закреплён один пост: новый снимает прежний, и метку надо убрать и здесь
    if (pinned) {
        feed.value.forEach((item) => {
            if (item.id !== article.id && item.user?.id === article.user?.id) {
                item.is_pinned = false;
            }
        });
    }

    article.is_pinned = pinned;
};

// Скрытый пост убираем из ленты сразу: иначе он висел бы до перезагрузки
const applyVisibility = (article, hidden) => {
    if (hidden) {
        feed.value = feed.value.filter((item) => item.id !== article.id);
    }
};

// Счётчик статей и дату из шапки убрали: справа живёт сортировка,
// а «Показано N из M» осталось под лентой, у кнопки догрузки.

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
