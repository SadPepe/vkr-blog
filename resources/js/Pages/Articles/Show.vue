<template>
    <MainLayout>
        <Head :title="displayTitle">
            <meta name="description" :content="description" />
            <meta property="og:type" content="article" />
            <meta property="og:title" :content="displayTitle" />
            <meta property="og:description" :content="description" />
            <meta v-if="article.card_image_path" property="og:image" :content="shareImage" />
            <meta property="article:published_time" :content="article.created_at" />
        </Head>

        <!-- Прогресс чтения -->
        <div class="pointer-events-none fixed inset-x-0 top-0 z-40 h-1 bg-accent/10">
            <div class="h-full bg-accent transition-[width] duration-150 ease-out" :style="{ width: `${progress}%` }"></div>
        </div>

        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:grid lg:grid-cols-[minmax(0,1fr)_320px] lg:gap-10">
            <div class="mx-auto max-w-2xl lg:mx-0">
                <!-- Хлебные крошки -->
                <nav class="flex items-center gap-1.5 text-xs text-muted" aria-label="Хлебные крошки">
                    <Link
                        :href="route('home')"
                        class="inline-flex items-center gap-1 transition-colors hover:text-accent dark:hover:text-accent"
                    >
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Все статьи
                    </Link>

                    <template v-if="category">
                        <span class="text-muted" aria-hidden="true">/</span>
                        <Link
                            :href="`/categories/${category.slug}`"
                            class="transition-colors hover:text-accent dark:hover:text-accent"
                        >
                            {{ category.name }}
                        </Link>
                    </template>
                </nav>

                <div class="article-container px-2 pt-6 md:px-0">
                    <!-- Метаданные: автор · дата · время чтения -->
                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-muted">
                        <span class="inline-flex items-center gap-2">
                            <UserAvatar :user="article.user" :size="24" />
                            <Link
                                :href="authorUrl"
                                class="font-semibold text-content transition-colors hover:text-accent dark:hover:text-accent"
                            >
                                {{ article.user.name }}
                            </Link>
                        </span>
                        <span class="text-muted" aria-hidden="true">·</span>
                        <time :datetime="article.created_at">{{ formatDate(article.created_at) }}</time>
                        <span class="text-muted" aria-hidden="true">·</span>
                        <span>{{ readingMinutes }} мин чтения</span>
                    </div>

                    <h1
                        v-if="article.title"
                        class="mt-3 text-2xl font-extrabold leading-snug tracking-tight text-content sm:text-3xl lg:text-4xl"
                    >
                        {{ article.title }}
                    </h1>

                    <ArticleStats
                        :article="article"
                        :views-count="article.views_count"
                        :initial-liked="article.is_liked"
                        :initial-likes-count="article.likes_count"
                        :comments-count="article.comments.length"
                        class="mt-6"
                    />

                    <!-- Главное изображение -->
                    <div
                        v-if="showHeroImage"
                        class="mt-10 overflow-hidden rounded-md bg-surface-muted"
                        :class="{ 'animate-pulse': !imageLoaded }"
                    >
                        <img
                            :src="article.card_image_path"
                            :alt="article.title || displayTitle"
                            class="aspect-video max-h-[400px] w-full cursor-zoom-in object-cover shadow-sm transition-opacity duration-300"
                            :class="imageLoaded ? 'opacity-100' : 'opacity-0'"
                            fetchpriority="high"
                            @load="imageLoaded = true"
                            @error="onImageError"
                            @click="openLightbox(article.card_image_path)"
                        />
                    </div>

                    <div ref="contentRef" class="article-content text-content" v-html="article.content"></div>
                </div>

                <!-- Автор статьи -->
                <div
                    class="mt-12 flex flex-col gap-4 rounded-md border border-line bg-surface p-5 shadow-sm sm:flex-row sm:items-center"
                >
                    <UserAvatar :user="article.user" :size="56" class="shrink-0" />
                    <div class="min-w-0 flex-1">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-muted">Автор</p>
                        <Link
                            :href="authorUrl"
                            class="mt-1 inline-block rounded text-base font-semibold text-content transition-colors hover:text-accent focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2 dark:hover:text-accent"
                        >
                            {{ article.user.name }}
                        </Link>
                        <p v-if="article.user.about" class="mt-1 line-clamp-2 text-sm text-muted">
                            {{ article.user.about }}
                        </p>
                    </div>
                    <Link
                        :href="authorUrl"
                        class="inline-flex shrink-0 items-center justify-center rounded border border-line-strong px-4 py-2 text-sm font-semibold text-content transition-colors hover:border-accent hover:text-accent focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2 dark:hover:border-accent dark:hover:text-accent"
                    >
                        Все статьи автора
                    </Link>
                </div>

                <!-- Поделиться -->
                <div class="mt-6 flex flex-wrap items-center gap-2">
                    <span class="mr-1 text-sm text-muted">Поделиться:</span>

                    <button
                        type="button"
                        @click="copyLink"
                        class="inline-flex items-center gap-1.5 rounded border border-line-strong px-3 py-1.5 text-sm font-medium text-content transition-colors hover:border-accent hover:text-accent focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2 dark:hover:border-accent dark:hover:text-accent"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M13.828 10.172a4 0 010 5.656l-2.828 2.829a4 0 01-5.657-5.657l1.414-1.414M10.172 13.828a4 0 010-5.656l2.828-2.829a4 0 015.657 5.657l-1.414"
                            />
                        </svg>
                        {{ copied ? "Ссылка скопирована" : "Скопировать ссылку" }}
                    </button>

                    <a
                        :href="telegramShareUrl"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-1.5 rounded border border-line-strong px-3 py-1.5 text-sm font-medium text-content transition-colors hover:border-accent hover:text-accent focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2 dark:hover:border-accent dark:hover:text-accent"
                    >
                        Telegram
                    </a>

                    <a
                        :href="vkShareUrl"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-1.5 rounded border border-line-strong px-3 py-1.5 text-sm font-medium text-content transition-colors hover:border-accent hover:text-accent focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2 dark:hover:border-accent dark:hover:text-accent"
                    >
                        ВКонтакте
                    </a>
                </div>

                <CommentSection :comments="article.comments" :article="article" :article-slug="article.slug" />
            </div>

            <!-- Похожие и рекомендуемые статьи -->
            <aside class="mx-auto mt-10 w-full max-w-2xl lg:mt-0 lg:max-w-none lg:self-start lg:sticky lg:top-24">
                <div class="space-y-6">
                    <RelatedArticles v-if="relatedArticles && relatedArticles.length > 0" :articles="relatedArticles" />
                    <PopularArticles v-if="popularArticles && popularArticles.length > 0" :articles="popularArticles" />
                </div>
            </aside>
        </div>

        <!-- Наверх -->
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0 translate-y-2"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 translate-y-2"
        >
            <button
                v-if="showTopButton"
                type="button"
                @click="scrollToTop"
                class="fixed bottom-6 right-6 z-30 inline-flex h-11 w-11 items-center justify-center rounded-full border border-line bg-surface text-muted shadow-lg transition-colors hover:text-accent focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2 dark:hover:text-accent"
                aria-label="Наверх"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 19V5M5 12l7-7 7 7" />
                </svg>
            </button>
        </Transition>

        <!-- Лайтбокс: картинка на полный экран -->
        <ImageLightbox :show="lightboxOpen" :src="lightboxSrc" :alt="article.title || displayTitle" @close="closeLightbox" />
    </MainLayout>
</template>

<script setup>
import { computed, onMounted, onBeforeUnmount, ref } from "vue";
import { Head, Link, usePage } from "@inertiajs/vue3";
import MainLayout from "@/Layouts/Layout.vue";
import CommentSection from "@/Components/Comments/CommentSection.vue";
import ArticleStats from "@/Components/ArticleStats.vue";
import UserAvatar from "@/Components/UserAvatar.vue";
import PopularArticles from "@/Components/PopularArticles.vue";
import RelatedArticles from "@/Components/RelatedArticles.vue";
import ImageLightbox from "@/Components/ImageLightbox.vue";

const page = usePage();

const props = defineProps({
    article: {
        type: Object,
        required: true,
    },
    popularArticles: {
        type: Array,
        default: () => [],
    },
    relatedArticles: {
        type: Array,
        default: () => [],
    },
});

// Своя страница автора не нужна: у себя ведём в профиль
const authorUrl = computed(() =>
    page.props.auth?.user?.id === props.article.user.id
        ? route("profile")
        : route("authors.show", props.article.user.id),
);

// Заголовок необязателен: для <title>/шаринга берём текст поста
const displayTitle = computed(() => {
    const title = (props.article.title || "").trim();
    if (title) return title;
    const excerpt = (props.article.excerpt || "").trim();
    return excerpt ? excerpt.slice(0, 70) : "Пост";
});

// Превью карточки — первая картинка из текста, поэтому на странице поста
// её не дублируем: показываем отдельный hero только для старых обложек
const showHeroImage = computed(() => {
    const image = props.article.card_image_path;
    if (!image) return false;
    return !String(props.article.content || "").includes(image);
});

const category = computed(() => {
    if (!props.article.category_slug) return null;
    const categories = page.props.categories ?? [];
    return categories.find((c) => c.slug === props.article.category_slug) ?? null;
});

const formatDate = (dateString) => {
    const date = new Date(dateString);
    const day = date.toLocaleDateString("ru-RU", {
        year: "numeric",
        month: "long",
        day: "numeric",
    });
    const time = date.toLocaleTimeString("ru-RU", {
        hour: "2-digit",
        minute: "2-digit",
    });
    return `${day}, ${time}`;
};

const readingMinutes = computed(() => {
    const text = String(props.article.content || "")
        .replace(/<[^>]+>/g, " ")
        .replace(/\s+/g, " ")
        .trim();
    const words = text ? text.split(" ").length : 0;
    return Math.max(1, Math.round(words / 200));
});

const imageLoaded = ref(false);

const onImageError = (event) => {
    event.target.style.display = "none";
};

const description = computed(() => {
    const text = (props.article.excerpt || "").trim();
    if (text) return text;

    return String(props.article.content || "")
        .replace(/<[^>]+>/g, " ")
        .replace(/\s+/g, " ")
        .trim()
        .slice(0, 200);
});

const absoluteUrl = computed(() => (typeof window === "undefined" ? "" : window.location.href));

const shareImage = computed(() =>
    typeof window === "undefined" || !props.article.card_image_path
        ? ""
        : new URL(props.article.card_image_path, window.location.origin).href,
);

const telegramShareUrl = computed(
    () => `https://t.me/share/url?url=${encodeURIComponent(absoluteUrl.value)}&text=${encodeURIComponent(displayTitle.value)}`,
);

const vkShareUrl = computed(
    () => `https://vk.com/share.php?url=${encodeURIComponent(absoluteUrl.value)}&title=${encodeURIComponent(displayTitle.value)}`,
);

const copied = ref(false);
let copiedTimer = null;

const copyLink = async () => {
    const url = window.location.href;

    try {
        await navigator.clipboard.writeText(url);
    } catch {
        // Резервный путь для браузеров без Clipboard API (http, старые движки)
        const input = document.createElement("textarea");
        input.value = url;
        input.setAttribute("readonly", "");
        input.style.position = "absolute";
        input.style.left = "-9999px";
        document.body.appendChild(input);
        input.select();
        document.execCommand("copy");
        document.body.removeChild(input);
    }

    copied.value = true;
    clearTimeout(copiedTimer);
    copiedTimer = setTimeout(() => {
        copied.value = false;
    }, 2500);
};

const progress = ref(0);
const showTopButton = ref(false);

const scrollToTop = () => {
    window.scrollTo({ top: 0, behavior: "smooth" });
};
const lightboxOpen = ref(false);
const lightboxSrc = ref("");
const contentRef = ref(null);

const openLightbox = (src) => {
    lightboxSrc.value = src;
    lightboxOpen.value = true;
    document.body.style.overflow = "hidden";
};

const closeLightbox = () => {
    lightboxOpen.value = false;
    document.body.style.overflow = "";
};

const handleContentImageClick = (event) => {
    const target = event.target;
    if (target && target.tagName === "IMG") {
        event.preventDefault();
        event.stopPropagation();
        openLightbox(target.currentSrc || target.src);
    }
};

const updateProgress = () => {
    const doc = document.documentElement;
    const max = doc.scrollHeight - window.innerHeight;
    progress.value = max > 0 ? Math.min(100, (window.scrollY / max) * 100) : 0;
    showTopButton.value = window.scrollY > 700;
};

onMounted(() => {
    updateProgress();
    window.addEventListener("scroll", updateProgress, { passive: true });
    contentRef.value?.addEventListener("click", handleContentImageClick);

    // Картинки в тексте: ленивая загрузка и декодирование в фоне
    contentRef.value?.querySelectorAll("img").forEach((img) => {
        img.setAttribute("loading", "lazy");
        img.setAttribute("decoding", "async");
    });
});

onBeforeUnmount(() => {
    window.removeEventListener("scroll", updateProgress);
    contentRef.value?.removeEventListener("click", handleContentImageClick);
    document.body.style.overflow = "";
});
</script>

<style scoped>
.article-content :deep(ul),
.article-content :deep(ol) {
    margin: 1.1rem 0;
    padding-inline-start: 1.5rem;
    list-style-position: outside;
}

.article-content :deep(li > p) {
    margin: 0.25rem 0;
}

/* Вложенные уровни: свой шаг отступа и свои маркеры */
.article-content :deep(li > ul),
.article-content :deep(li > ol) {
    margin: 0.5rem 0 0.5rem 0.25rem;
    padding-inline-start: 1.4rem;
}

.article-content :deep(ul ul) {
    list-style-type: circle;
}

.article-content :deep(ul ul ul) {
    list-style-type: square;
}

.article-content :deep(ol ol) {
    list-style-type: lower-alpha;
}

.article-content :deep(ol ol ol) {
    list-style-type: lower-roman;
}

.article-content :deep(p) {
    line-height: 1.7;
    margin: 0.4rem 0;
}

.article-content :deep(ol) {
    list-style-type: decimal;
}

.article-content :deep(ul) {
    list-style-type: disc;
}

.article-content :deep(hr) {
    border: none;
    border-top: 1px solid gray;
    margin: 2rem 0;

    &.ProseMirror-selectednode {
        border-top: 2px solid rgb(173, 0, 173);
    }
}

.article-content :deep(blockquote) {
    border-left: 3px solid rgb(var(--c-line-strong));
    color: rgb(var(--c-muted));
    margin: 1.5rem 0;
    padding-left: 1rem;
}

.article-content :deep(h1),
.article-content :deep(h2) {
    font-weight: 600;
    margin: 2rem 0 1rem;
}

.article-content :deep(h1) {
    text-wrap: pretty;
    font-size: 2.1rem;
    line-height: 1.2;
    margin-bottom: 1.5rem;
}

.article-content :deep(h2) {
    text-wrap: pretty;
    font-size: 1.875rem;
    margin: 0.8rem 0 1rem;
}

.article-content :deep(h3) {
    text-wrap: pretty;
    font-size: 1.5rem;
    margin: 0.8rem 0 1rem;
}

.article-content :deep(h4),
.article-content :deep(h5),
.article-content :deep(h6) {
    font-size: 1rem;
    margin: 1.25rem 0 0.5rem;
}

.article-content :deep(img) {
    display: block;
    margin: 1.5rem auto;
    border-radius: 0.75rem;
    max-width: 100%;
    max-height: 70vh;
    height: auto;
    cursor: zoom-in;
}

/* внутри figure отступы задаёт сама фигура */
.article-content :deep(figure img) {
    margin-block: 0;
}

.article-content :deep(a) {
    color: rgb(var(--c-accent));
    text-decoration: underline;
    text-underline-offset: 2px;
}

.article-content :deep(code) {
    font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
    font-size: 0.9em;
    background: rgba(15, 23, 42, 0.06);
    padding: 0.15em 0.35em;
    border-radius: 0.3rem;
}

.article-content :deep(pre) {
    margin: 1.5rem 0;
    padding: 1rem 1.25rem;
    overflow-x: auto;
    border-radius: 0.75rem;
    border: 1px solid rgba(15, 23, 42, 0.08);
    background: rgb(var(--c-surface));
    font-size: 0.9rem;
    line-height: 1.6;
}

.article-content :deep(pre code) {
    background: transparent;
    padding: 0;
    border-radius: 0;
    font-size: inherit;
    white-space: pre;
}

.article-content :deep(figure) {
    margin: 1.75rem 0;
}

.article-content :deep(figcaption) {
    margin-top: 0.5rem;
    text-align: center;
    font-size: 0.875rem;
    color: rgb(var(--c-muted));
}

.article-content :deep(li) {
    margin: 0.35rem 0;
}
</style>
