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
                        <!-- Автор, просмотры и действия — одной строкой над постом:
                             аватар и имя слева, метрика и «⋯» у правого края.
                             Ссылки «Все статьи автора» нет: имя уже ведёт в профиль. -->
                        <div class="mt-6 flex flex-wrap items-center gap-x-3 gap-y-2">
                            <UserAvatar :user="article.user" :size="40" class="shrink-0" />
                            <Link
                                :href="authorUrl"
                                class="min-w-0 truncate text-[1.0625rem] font-medium leading-snug text-content transition-colors hover:text-accent"
                            >
                                {{ article.user.name }}
                            </Link>

                            <div class="ml-auto flex items-center gap-3">
                                <span class="whitespace-nowrap text-sm text-muted">{{ readingMinutes }} мин чтения</span>

                                <span
                                    class="inline-flex items-center gap-1.5 text-sm text-muted"
                                    :aria-label="`${article.views_count} ${pluralViews(article.views_count)}`"
                                >
                                    <svg
                                        class="h-4 w-4 shrink-0"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.5"
                                        viewBox="0 0 24 24"
                                        aria-hidden="true"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                        />
                                    </svg>
                                    <span class="num">{{ formatCount(article.views_count) }}</span>
                                </span>

                                <!-- Действия автора — в меню «⋯» рядом с просмотрами,
                                     чтобы шапка поста оставалась спокойной -->
                                <ArticleActionsMenu v-if="isOwner" :article="article" />

                                <button
                                    v-if="!isOwner && page.props.auth?.user"
                                    type="button"
                                    class="ui-pill ui-pill--quiet h-9 w-9 min-h-9 px-0"
                                    aria-label="Пожаловаться на статью"
                                    title="Пожаловаться"
                                    @click="reportOpen = true"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 21V4m0 0h11.5l-1.6 3.2a1 1 0 000 .9L14.5 11H3" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Скрытый пост: объясняем, почему его не видят читатели -->
                        <div
                            v-if="isOwner && !isPublished"
                            class="mt-5 flex flex-wrap items-center gap-x-3 gap-y-2 border border-line bg-surface-muted px-4 py-3 text-sm text-muted"
                        >
                            <span>
                                Пост скрыт — его видят только вы и модераторы. Он остаётся в профиле и не попадает в ленты.
                            </span>
                            <button
                                type="button"
                                class="inline-flex min-h-9 items-center font-semibold text-accent transition-colors hover:text-accent-hover"
                                @click="toggleVisibility"
                            >
                                Опубликовать снова
                            </button>
                        </div>

                        <h1
                            v-if="article.title"
                            class="mt-5 font-display text-[clamp(2rem,4.4vw,3.5rem)] font-bold leading-[1.05] tracking-[-0.02em] [text-wrap:balance] text-content"
                        >
                            {{ article.title }}
                        </h1>

                        <!-- В мете под заголовком осталось только закрепление:
                             дата ушла в ряд действий, время чтения — к просмотрам -->
                        <p v-if="article.is_pinned" class="type-meta mt-4">
                            <span class="font-medium text-accent">Закреплено</span>
                        </p>
                    </div>

                    <!-- Картинки показываем как есть — в том месте текста, куда их вставил автор -->
                    <div ref="contentRef" class="article-body mt-12 text-content" v-html="article.content"></div>

                    <!-- Действия под постом: лайк и «поделиться» одной строкой
                         на всех ширинах, отдельных копий для lg больше нет -->
                    <div class="article-measure">
                        <ArticleFooter :article="article" :share-url="absoluteUrl" />
                    </div>

                    <div class="article-measure">
                        <CommentSection :comments="article.comments" :article="article" :article-slug="article.slug" />
                    </div>
                </div>

                <!-- Рейл: похожие и афиша закрывают правую сторону -->
                <aside class="article-rail">
                    <PosterRail
                        v-if="relatedArticles && relatedArticles.length > 0"
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
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import MainLayout from "@/Layouts/Layout.vue";
import CommentSection from "@/Components/Comments/CommentSection.vue";
import ArticleFooter from "@/Components/ArticleFooter.vue";
import ArticleActionsMenu from "@/Components/ArticleActionsMenu.vue";
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

// Скрытый пост доступен владельцу, но не читателям — интерфейс это проговаривает
const isPublished = computed(() => props.article.status === "published");

// Публикация из баннера скрытого поста: остальные действия — в меню «⋯»
const toggleVisibility = () => {
    router.patch(route("articles.visibility", { article: props.article.slug }), {}, { preserveScroll: true });
};

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

const readingMinutes = computed(() => {
    const text = String(props.article.content || "")
        .replace(/<[^>]+>/g, " ")
        .replace(/\s+/g, " ")
        .trim();
    const words = text ? text.split(" ").length : 0;
    return Math.max(1, Math.round(words / 200));
});

// Просмотры в строке автора: число с разделителем, падеж — для скринридера
const formatCount = (value) => new Intl.NumberFormat("ru-RU").format(value ?? 0);

const pluralViews = (n) => {
    const mod10 = n % 10;
    const mod100 = n % 100;
    if (mod10 === 1 && mod100 !== 11) return "просмотр";
    if (mod10 >= 2 && mod10 <= 4 && (mod100 < 12 || mod100 > 14)) return "просмотра";
    return "просмотров";
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

// Комментарии считаются вместе с ответами — как в рейле

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
