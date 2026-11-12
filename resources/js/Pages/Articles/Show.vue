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

        <!-- Прогресс чтения: линия акцентом, без подложки -->
        <div class="pointer-events-none fixed inset-x-0 top-0 z-40 h-0.5 bg-transparent">
            <div class="h-full bg-accent transition-[width] duration-150 ease-out" :style="{ width: `${progress}%` }"></div>
        </div>

        <div class="article-spread page-shell pb-24 pt-8">
            <!-- Хлебные крошки: текстом, без иконок и разделителей -->
            <nav class="type-meta lg:mx-auto lg:max-w-[60rem] xl:max-w-[65.5rem]" aria-label="Хлебные крошки">
                <Link :href="route('home')" class="transition-colors hover:text-accent">Все статьи</Link>
                <template v-if="category">
                    <span aria-hidden="true"> · </span>
                    <Link :href="route('articlesByCategory', category.slug)" class="transition-colors hover:text-accent">
                        {{ category.name }}
                    </Link>
                </template>
            </nav>

            <div class="article-layout">
                <div class="min-w-0">
                    <div class="article-head">
                        <h1
                            v-if="article.title"
                            class="mt-6 font-display text-[clamp(2rem,4.4vw,3.5rem)] font-bold leading-[1.05] tracking-[-0.02em] [text-wrap:balance] text-content"
                        >
                            {{ article.title }}
                        </h1>

                        <!-- Дата и время чтения — строкой после заголовка.
                             Категория уже есть в хлебных крошках, поэтому здесь её нет -->
                        <p class="type-meta mt-4">
                            <time :datetime="article.created_at">{{ formatDate(article.created_at) }}</time>
                            <span aria-hidden="true"> · </span>
                            <span class="whitespace-nowrap">{{ readingMinutes }} мин чтения</span>
                        </p>
                    </div>

                    <!-- Картинки показываем как есть — в том месте текста, куда их вставил автор -->
                    <div ref="contentRef" class="article-body mt-12 text-content" v-html="article.content"></div>

                    <!-- Поделиться на десктопе — те же пилюли, что и в блоке под постом;
                         на узких экранах этим занимается ArticleFooter -->
                    <div class="article-measure mx-auto mt-10 hidden lg:block">
                        <ArticleShare :url="absoluteUrl" :title="displayTitle" />
                    </div>

                    <div class="article-measure">
                        <ArticleFooter
                            :article="article"
                            :author-url="authorUrl"
                            :share-url="absoluteUrl"
                            :share-title="displayTitle"
                            :is-owner="isOwner"
                            :comments-count="commentsCount"
                            @report="reportOpen = true"
                        />
                    </div>

                    <div class="article-measure">
                        <CommentSection :comments="article.comments" :article="article" :article-slug="article.slug" />
                    </div>
                </div>

                <!-- Рейл: похожие и афиша закрывают правую сторону -->
                <aside class="article-rail">
                    <!-- Автор и действия: с lg это единственная копия — на узких экранах
                         то же самое показывает ArticleFooter до комментариев -->
                    <div class="hidden lg:block">
                        <!-- Автор статьи: аватар и имя одной строкой, описание — ниже во всю ширину -->
                        <div class="flex items-center gap-3">
                            <UserAvatar :user="article.user" :size="56" class="shrink-0" />
                            <Link
                                :href="authorUrl"
                                class="min-w-0 text-[1.0625rem] font-medium text-content transition-colors hover:text-accent"
                            >
                                {{ article.user.name }}
                            </Link>
                        </div>
                        <p v-if="article.user.about" class="mt-3 text-muted">
                            {{ article.user.about }}
                        </p>
                        <Link :href="authorUrl" class="mt-2 inline-block text-sm text-accent transition-colors hover:text-accent-hover">
                            Все статьи автора
                        </Link>

                        <!-- Метрики и действия — рядом с автором; на узких экранах рейл уходит под статью -->
                        <ArticleStats
                            :article="article"
                            :views-count="article.views_count"
                            :initial-liked="article.is_liked"
                            :initial-likes-count="article.likes_count"
                            :comments-count="commentsCount"
                            class="mt-6"
                        />

                        <div class="mt-4 flex flex-wrap items-center gap-2">
                            <Link
                                v-if="isOwner"
                                :href="route('articles.edit', { article: article.slug })"
                                class="ui-pill ui-pill--quiet min-h-9 px-3 text-[0.875rem]"
                            >
                                <svg
                                    class="h-[1.125rem] w-[1.125rem]"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125"
                                    />
                                </svg>
                                Редактировать
                            </Link>
                            <button
                                v-else-if="page.props.auth?.user"
                                type="button"
                                class="ui-pill ui-pill--quiet min-h-9 px-3 text-[0.875rem]"
                                @click="reportOpen = true"
                            >
                                Пожаловаться
                            </button>
                        </div>
                    </div>

                    <PosterRail
                        v-if="relatedArticles && relatedArticles.length > 0"
                        class="mt-12"
                        :articles="relatedArticles.slice(0, 4)"
                        title="Ещё в разделе"
                    />

                    <PosterRail
                        v-if="popularArticles && popularArticles.length > 0"
                        class="mt-10"
                        :articles="popularArticles.slice(0, 5)"
                        title="По просмотрам за неделю"
                    />
                </aside>
            </div>
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
                class="fixed bottom-6 right-6 z-30 inline-flex h-11 w-11 items-center justify-center bg-surface-muted text-content transition-colors hover:text-accent focus-visible:outline-none"
                aria-label="Наверх"
                @click="scrollToTop"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 19V5M5 12l7-7 7 7" />
                </svg>
            </button>
        </Transition>

        <!-- Лайтбокс: картинка на полный экран, карусель листается по кадрам -->
        <ImageLightbox
            :show="lightboxOpen"
            :src="lightboxSrc"
            :alt="article.title || displayTitle"
            :images="lightboxImages"
            :index="lightboxIndex"
            @close="closeLightbox"
        />

        <ReportDialog :show="reportOpen" type="article" :id="article.id" @close="reportOpen = false" />
    </MainLayout>
</template>

<script setup>
import { computed, onMounted, onBeforeUnmount, ref } from "vue";
import { Head, Link, usePage } from "@inertiajs/vue3";
import MainLayout from "@/Layouts/Layout.vue";
import CommentSection from "@/Components/Comments/CommentSection.vue";
import ArticleFooter from "@/Components/ArticleFooter.vue";
import ArticleShare from "@/Components/ArticleShare.vue";
import ArticleStats from "@/Components/ArticleStats.vue";
import UserAvatar from "@/Components/UserAvatar.vue";
import ImageLightbox from "@/Components/ImageLightbox.vue";
import PosterRail from "@/Components/PosterRail.vue";
import ReportDialog from "@/Components/ReportDialog.vue";

const page = usePage();
const reportOpen = ref(false);

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

// Владелец может править статью прямо со страницы просмотра
const isOwner = computed(() => page.props.auth?.user?.id === props.article.user.id);

// Своя страница автора не нужна: у себя ведём в профиль
const authorUrl = computed(() =>
    page.props.auth?.user?.id === props.article.user.id ? route("profile") : route("authors.show", props.article.user.id),
);

// Заголовок необязателен: для <title>/шаринга берём текст поста
const displayTitle = computed(() => {
    const title = (props.article.title || "").trim();
    if (title) return title;
    const excerpt = (props.article.excerpt || "").trim();
    return excerpt ? excerpt.slice(0, 70) : "Пост";
});

const category = computed(() => {
    if (!props.article.category_slug) return null;
    const categories = page.props.categories ?? [];
    return categories.find((c) => c.slug === props.article.category_slug) ?? null;
});

// Дата без года: свежие посты читают в основном в том же году,
// а год только удлинял строку меты
const formatDate = (dateString) => {
    const date = new Date(dateString);
    const day = date.toLocaleDateString("ru-RU", {
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

// Комментарии считаются вместе с ответами — как в рейле
const commentsCount = computed(() => props.article.comments?.length ?? 0);

const progress = ref(0);
const showTopButton = ref(false);

const scrollToTop = () => {
    window.scrollTo({ top: 0, behavior: "smooth" });
};
const lightboxOpen = ref(false);
const lightboxSrc = ref("");
const lightboxImages = ref([]);
const lightboxIndex = ref(0);
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
    if (!target || target.tagName !== "IMG") return;

    event.preventDefault();
    event.stopPropagation();

    // Фото из карусели открываем вместе с соседними кадрами — их можно листать
    const carousel = target.closest(".image-carousel");
    const frames = carousel ? [...carousel.querySelectorAll(":scope > img")] : [];
    const index = frames.indexOf(target);

    lightboxImages.value = frames.map((img) => ({ src: img.currentSrc || img.src, alt: img.alt || "" }));
    lightboxIndex.value = index >= 0 ? index : 0;

    openLightbox(target.currentSrc || target.src);
};

const updateProgress = () => {
    const doc = document.documentElement;
    const max = doc.scrollHeight - window.innerHeight;
    progress.value = max > 0 ? Math.min(100, (window.scrollY / max) * 100) : 0;
    showTopButton.value = window.scrollY > 700;
};

// Код и карусели: модули грузим лениво, только если на странице они есть
const enhanceContent = async () => {
    const root = contentRef.value;
    if (!root) return;

    if (root.querySelector("pre code")) {
        const { enhanceCodeBlocks } = await import("@/Support/codeHighlight");
        enhanceCodeBlocks(root);
    }

    if (root.querySelector(".image-carousel")) {
        const { enhanceImageCarousels } = await import("@/Support/imageCarousel");
        enhanceImageCarousels(root);
    }

    if (root.querySelector(".video-embed")) {
        const { enhanceVideoEmbeds } = await import("@/Support/videoEmbed");
        enhanceVideoEmbeds(root);
    }
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

    enhanceContent();
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
