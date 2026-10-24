<template>
    <MainLayout>
        <!-- Прогресс чтения -->
        <div class="pointer-events-none fixed inset-x-0 top-0 z-40 h-1 bg-emerald-600/10">
            <div class="h-full bg-gradient-to-r from-emerald-600 to-teal-600 transition-[width] duration-150 ease-out"
                :style="{ width: `${progress}%` }"></div>
        </div>

        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:grid lg:grid-cols-[minmax(0,1fr)_320px] lg:gap-10">
            <div class="mx-auto max-w-2xl lg:mx-0">
                <!-- Хлебные крошки -->
                <nav class="flex items-center gap-1.5 text-xs text-gray-400 dark:text-stone-500" aria-label="Хлебные крошки">
                    <Link :href="route('home')" class="inline-flex items-center gap-1 transition-colors hover:text-emerald-600 dark:hover:text-emerald-400">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Все статьи
                    </Link>

                    <template v-if="category">
                        <span class="text-gray-300 dark:text-stone-600" aria-hidden="true">/</span>
                        <Link :href="`/categories/${category.slug}`" class="transition-colors hover:text-emerald-600 dark:hover:text-emerald-400">
                            {{ category.name }}
                        </Link>
                    </template>
                </nav>

                <div class="article-container px-2 pt-6 md:px-0">
                    <!-- Метаданные: автор · дата · время чтения -->
                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-gray-500 dark:text-stone-500">
                        <span class="inline-flex items-center gap-2">
                            <UserAvatar :user="article.user" :size="24" />
                            <Link
                                :href="route('authors.show', article.user.id)"
                                class="font-semibold text-gray-700 transition-colors hover:text-emerald-600 dark:text-stone-300 dark:hover:text-emerald-400"
                            >
                                {{ article.user.name }}
                            </Link>
                        </span>
                        <span class="text-gray-300 dark:text-stone-600" aria-hidden="true">·</span>
                        <time :datetime="article.created_at">{{ formatDate(article.created_at) }}</time>
                        <span class="text-gray-300 dark:text-stone-600" aria-hidden="true">·</span>
                        <span>{{ readingMinutes }} мин чтения</span>
                    </div>

                    <h1
                        class="mt-3 text-2xl font-extrabold leading-snug tracking-tight text-gray-900 sm:text-3xl lg:text-4xl dark:text-stone-100"
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
                    <div class="mt-10">
                        <img
                            v-if="article.card_image_path"
                            :src="article.card_image_path"
                            :alt="article.title"
                            class="max-h-[400px] w-full cursor-zoom-in rounded-md object-cover shadow-sm"
                            loading="lazy"
                            @error="onImageError"
                            @click="openLightbox(article.card_image_path)"
                        />
                    </div>

                    <div ref="contentRef" class="article-content text-gray-800 dark:text-stone-300" v-html="article.content"></div>
                </div>

                <CommentSection :comments="article.comments" :article="article" :article-slug="article.slug" />
            </div>

            <!-- Похожие и рекомендуемые статьи -->
            <aside
                class="mx-auto mt-10 w-full max-w-2xl lg:mt-0 lg:max-w-none lg:self-start lg:sticky lg:top-24"
            >
                <div class="space-y-6 scrollbar-slim lg:max-h-[calc(100vh_-_8rem)] lg:overflow-y-auto lg:overscroll-contain lg:pr-1 lg:-mr-1 lg:py-1">
                    <RelatedArticles
                        v-if="relatedArticles && relatedArticles.length > 0"
                        :articles="relatedArticles"
                    />
                    <PopularArticles
                        v-if="popularArticles && popularArticles.length > 0"
                        :articles="popularArticles"
                    />
                </div>
            </aside>
        </div>

        <!-- Лайтбокс: картинка на полный экран -->
        <Teleport to="body">
            <Transition name="lightbox">
                <div
                    v-if="lightboxOpen"
                    class="fixed inset-0 z-[100] flex items-center justify-center bg-black/90 p-4 backdrop-blur-sm"
                    role="dialog"
                    aria-modal="true"
                    :aria-label="`Изображение: ${article.title}`"
                    @click="closeLightbox"
                >
                    <button
                        @click="closeLightbox"
                        class="absolute right-4 top-4 z-10 rounded-full bg-white/10 p-2 text-white transition-colors hover:bg-white/25"
                        aria-label="Закрыть"
                        title="Закрыть (Esc)"
                    >
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    <img
                        :src="lightboxSrc"
                        :alt="article.title"
                        class="max-h-[90vh] max-w-full rounded-lg object-contain shadow-2xl"
                        @click.stop
                    />
                </div>
            </Transition>
        </Teleport>
    </MainLayout>
</template>

<script setup>
import { computed, onMounted, onBeforeUnmount, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/Layout.vue';
import CommentSection from '@/Components/Comments/CommentSection.vue';
import ArticleStats from '@/Components/ArticleStats.vue';
import UserAvatar from '@/Components/UserAvatar.vue';
import PopularArticles from '@/Components/PopularArticles.vue';
import RelatedArticles from '@/Components/RelatedArticles.vue';

const page = usePage();

const props = defineProps({
    article: {
        type: Object,
        required: true
    },
    popularArticles: {
        type: Array,
        default: () => []
    },
    relatedArticles: {
        type: Array,
        default: () => []
    }
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

const onImageError = (event) => {
    event.target.style.display = 'none';
};

const progress = ref(0);
const lightboxOpen = ref(false);
const lightboxSrc = ref('');
const contentRef = ref(null);

const openLightbox = (src) => {
    lightboxSrc.value = src;
    lightboxOpen.value = true;
    document.body.style.overflow = 'hidden';
};

const closeLightbox = () => {
    lightboxOpen.value = false;
    document.body.style.overflow = '';
};

const onKeydown = (e) => {
    if (e.key === 'Escape') closeLightbox();
};

const handleContentImageClick = (event) => {
    const target = event.target;
    if (target && target.tagName === 'IMG') {
        event.preventDefault();
        event.stopPropagation();
        openLightbox(target.currentSrc || target.src);
    }
};

const updateProgress = () => {
    const doc = document.documentElement;
    const max = doc.scrollHeight - window.innerHeight;
    progress.value = max > 0 ? Math.min(100, (window.scrollY / max) * 100) : 0;
};

onMounted(() => {
    document.title = (props.article.title ? `${props.article.title} — ` : '') + 'Блог';

    updateProgress();
    window.addEventListener('scroll', updateProgress, { passive: true });
    window.addEventListener('keydown', onKeydown);
    contentRef.value?.addEventListener('click', handleContentImageClick);
});

onBeforeUnmount(() => {
    window.removeEventListener('scroll', updateProgress);
    window.removeEventListener('keydown', onKeydown);
    contentRef.value?.removeEventListener('click', handleContentImageClick);
    document.body.style.overflow = '';
});
</script>

<style scoped>
.article-content :deep(ul),
.article-content :deep(ol) {
    padding: 0 1rem;
    margin: 1.25rem 0;

    li p {
        margin-top: 0.25em;
        margin-bottom: 0.25em;
    }
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
    border-left: 3px solid rgba(179, 179, 179, 0.6);
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
}

.article-content :deep(img) {
    border-radius: 0.75rem;
    max-width: 100%;
    height: auto;
    cursor: zoom-in;
}

.lightbox-enter-active,
.lightbox-leave-active {
    transition: opacity 0.2s ease;
}

.lightbox-enter-from,
.lightbox-leave-to {
    opacity: 0;
}
</style>