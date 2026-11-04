<template>
    <!-- Пост в ленте: часть статьи как в блоге, без «карточки» -->
    <article class="py-8">
        <!-- Автор: аватар, имя, категория и дата -->
        <header class="flex items-start justify-between gap-3">
            <Link
                :href="authorUrl"
                class="group/author inline-flex min-w-0 items-center gap-3 rounded focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2"
            >
                <UserAvatar :user="article.user" :size="40" />
                <span class="min-w-0">
                    <span class="block truncate text-sm font-semibold text-content transition-colors group-hover/author:text-accent">
                        {{ article.user.name }}
                    </span>
                    <span class="mt-0.5 flex flex-wrap items-center gap-x-1.5 text-xs text-muted">
                        <span v-if="categoryName" class="font-medium uppercase tracking-wide text-accent">
                            {{ categoryName }}
                        </span>
                        <time :datetime="article.created_at">{{ formatDate(article.created_at) }}</time>
                    </span>
                </span>
            </Link>

            <!-- Действия владельца (редактировать/удалить) -->
            <div v-if="$slots.actions" class="flex shrink-0 items-center gap-1">
                <slot name="actions" />
            </div>
        </header>

        <!-- Заголовок — ссылка на страницу статьи -->
        <h2 v-if="article.title" class="mt-4 text-xl font-bold leading-snug tracking-tight text-content md:text-2xl">
            <Link
                :href="route('articles.show', article.slug)"
                prefetch="hover"
                class="rounded transition-colors hover:text-accent focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2"
            >
                {{ article.title }}
            </Link>
        </h2>

        <!-- Текст и картинки в авторском порядке: свернуто — первые блоки, развёрнуто — больше -->
        <div v-if="article.preview" class="relative mt-4">
            <div
                ref="previewEl"
                class="post-preview text-base leading-7 text-content"
                :class="collapsed ? 'is-collapsed max-h-[38rem] overflow-hidden' : ''"
                v-html="displayHtml"
                @click="handlePreviewClick"
                @load.capture="onPreviewImageLoad"
            ></div>

            <div
                v-if="collapsed && overflowing"
                class="pointer-events-none absolute inset-x-0 bottom-0 h-14 bg-gradient-to-t from-surface via-surface/70 to-transparent"
                aria-hidden="true"
            ></div>

            <p v-if="loading" class="mt-2 text-sm text-muted">Загружаем продолжение…</p>

            <p v-else-if="!collapsed && hasMore" class="mt-3 text-sm text-muted">
                Показана часть текста — полностью на
                <Link :href="route('articles.show', article.slug)" class="font-semibold text-accent hover:underline"> странице статьи </Link
                >.
            </p>
        </div>

        <!-- Действия и статистика -->
        <footer class="mt-5 flex flex-wrap items-center justify-between gap-x-4 gap-y-2">
            <div class="flex items-center gap-4 text-xs text-muted">
                <span class="inline-flex items-center gap-1.5" :title="`Просмотры: ${article.views_count || 0}`">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                        />
                    </svg>
                    <span>{{ article.views_count || 0 }}</span>
                </span>

                <span class="inline-flex items-center gap-1.5" :title="`Лайки: ${article.likes_count || 0}`">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
                        />
                    </svg>
                    <span>{{ article.likes_count || 0 }}</span>
                </span>

                <span class="inline-flex items-center gap-1.5" :title="`Комментарии: ${article.comments_count || 0}`">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.86 9.86 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
                        />
                    </svg>
                    <span>{{ article.comments_count || 0 }}</span>
                </span>
            </div>

            <div class="flex items-center gap-1.5">
                <button
                    v-if="canExpand"
                    type="button"
                    class="inline-flex items-center gap-1.5 rounded px-2.5 py-1.5 text-sm font-semibold text-muted transition-colors hover:bg-surface-muted hover:text-content focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent disabled:opacity-60"
                    :aria-expanded="!collapsed"
                    :disabled="loading"
                    @click="toggleExpanded"
                >
                    <svg
                        class="h-4 w-4 transition-transform duration-200"
                        :class="{ 'rotate-180': !collapsed }"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                    {{ collapsed ? (hasMore ? "Развернуть" : "Показать полностью") : "Свернуть" }}
                </button>

                <Link
                    :href="route('articles.show', article.slug)"
                    prefetch="hover"
                    class="inline-flex items-center gap-1.5 rounded px-2.5 py-1.5 text-sm font-semibold text-accent transition-colors hover:text-accent-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent"
                >
                    Читать полностью
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </Link>
            </div>
        </footer>

        <!-- Фото из поста открывается на полный экран, не уводя со страницы -->
        <ImageLightbox :show="lightboxOpen" :src="lightboxSrc" :alt="lightboxAlt" @close="lightboxOpen = false" />
    </article>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import UserAvatar from "./UserAvatar.vue";
import ImageLightbox from "./ImageLightbox.vue";

const props = defineProps({
    article: {
        type: Object,
        required: true,
    },
    categories: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();

// Своя страница автора не нужна: у себя ведём в профиль
const authorUrl = computed(() =>
    page.props.auth?.user?.id === props.article.user.id
        ? route("profile")
        : route("authors.show", props.article.user.id),
);

const expanded = ref(false);
const loading = ref(false);
/** Реально ли превью обрезано по высоте (иначе градиент и кнопка не нужны). */
const previewEl = ref(null);
const lightboxOpen = ref(false);
const lightboxSrc = ref("");
const lightboxAlt = ref("");
const overflowing = ref(false);
/** Превью хоть раз не помещалось по высоте: кнопка нужна и после разворота, чтобы свернуть. */
const everOverflowed = ref(false);
/** Развёрнутый html приходит лениво, только по кнопке «Развернуть». */
const fullHtml = ref(null);
/** Осталось ли что-то за пределами развёрнутого превью. */
const hasMore = ref(Boolean(props.article.has_more));

const categoryName = computed(() => {
    const slug = props.article.category_slug;
    const found = props.categories.find((c) => c.slug === slug);
    return found ? found.name : "";
});

const collapsed = computed(() => !expanded.value);
const canExpand = computed(() => hasMore.value || fullHtml.value !== null || everOverflowed.value);
const displayHtml = computed(() => (expanded.value && fullHtml.value ? fullHtml.value : props.article.preview));

/** Помечает вертикальные фото, чтобы в свёрнутом превью показывать их целиком. */
const markImageOrientation = (img) => {
    if (!img || img.tagName !== "IMG" || !img.naturalWidth) return;

    img.classList.toggle("is-portrait", img.naturalHeight > img.naturalWidth * 1.05);
};

const onPreviewImageLoad = (event) => markImageOrientation(event.target);

const markRenderedImages = () => {
    previewEl.value?.querySelectorAll("img").forEach((img) => {
        if (img.complete) markImageOrientation(img);
    });
};

const handlePreviewClick = (event) => {
    const target = event.target;

    if (target instanceof HTMLImageElement) {
        event.preventDefault();
        event.stopPropagation();

        lightboxSrc.value = target.currentSrc || target.src;
        lightboxAlt.value = target.alt || "";
        lightboxOpen.value = true;
    }
};

const measureOverflow = () => {
    const el = previewEl.value;

    if (!el || !collapsed.value) {
        overflowing.value = false;
        return;
    }

    overflowing.value = el.scrollHeight - el.clientHeight > 4;

    if (overflowing.value) {
        everOverflowed.value = true;
    }
};

watch([displayHtml, collapsed], () =>
    nextTick(() => {
        measureOverflow();
        markRenderedImages();
    }),
);

onMounted(() => {
    nextTick(() => {
        measureOverflow();
        markRenderedImages();
    });
    // Картинки в превью меняют высоту после загрузки
    window.addEventListener("load", measureOverflow, { once: true });
    window.addEventListener("resize", measureOverflow);
});

onBeforeUnmount(() => {
    window.removeEventListener("resize", measureOverflow);
});

const toggleExpanded = async () => {
    if (expanded.value) {
        expanded.value = false;
        nextTick(measureOverflow);
        return;
    }

    if (fullHtml.value === null && hasMore.value && !loading.value) {
        loading.value = true;

        try {
            const response = await fetch(route("articles.excerpt", props.article.slug), {
                headers: { Accept: "application/json" },
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();
            fullHtml.value = data.html || props.article.preview;
            hasMore.value = !data.complete;
        } catch {
            // Если продолжение не загрузилось — уводим на страницу статьи
            router.visit(route("articles.show", props.article.slug));
            return;
        } finally {
            loading.value = false;
        }
    }

    expanded.value = true;
    nextTick(measureOverflow);
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString("ru-RU", {
        year: "numeric",
        month: "long",
        day: "numeric",
    });
};
</script>

<style scoped>
.post-preview :deep(p) {
    margin: 0 0 1rem;
}

.post-preview :deep(p:last-child) {
    margin-bottom: 0;
}

.post-preview :deep(h1),
.post-preview :deep(h2),
.post-preview :deep(h3) {
    margin: 1rem 0 0.6rem;
    font-weight: 700;
    line-height: 1.3;
}

.post-preview :deep(h1) {
    font-size: 1.35rem;
}

.post-preview :deep(h2) {
    font-size: 1.2rem;
}

/* Фото не должно занимать больше экрана: в развёрнутом виде вписываем в 70vh,
   в свёрнутом — показываем как ровный широкий кадр */
.post-preview :deep(img) {
    display: block;
    width: 100%;
    max-width: 100%;
    height: auto;
    max-height: min(70vh, 40rem);
    margin: 1rem 0;
    border-radius: 0.5rem;
    background: rgb(var(--c-surface-muted));
    object-fit: contain;
    cursor: zoom-in;
}

.post-preview.is-collapsed :deep(img) {
    max-height: 22rem;
    object-fit: cover;
    object-position: center;
}

/* Вертикальные фото не обрезаем и не меняем в размере при развороте:
   и в свёрнутом, и в развёрнутом превью показываем их целиком */
.post-preview :deep(img.is-portrait) {
    width: auto;
    max-width: 100%;
    height: auto;
    max-height: min(26rem, 65vh);
    margin: 1rem auto;
    object-fit: contain;
}

.post-preview :deep(figure) {
    margin: 1rem 0;
}

.post-preview :deep(figcaption) {
    margin-top: 0.35rem;
    font-size: 0.8rem;
    color: rgb(var(--c-muted));
}

.post-preview :deep(blockquote) {
    margin: 1rem 0;
    border-left: 3px solid rgb(var(--c-accent));
    padding-left: 0.85rem;
    color: rgb(var(--c-muted));
}

.post-preview :deep(ul),
.post-preview :deep(ol) {
    margin: 0.75rem 0;
    padding-left: 1.25rem;
}

.post-preview :deep(ul) {
    list-style: disc;
}

.post-preview :deep(ol) {
    list-style: decimal;
}

.post-preview :deep(pre) {
    margin: 1rem 0;
    overflow-x: auto;
    border-radius: 0.375rem;
    background: rgb(var(--c-surface-muted));
    padding: 0.75rem 0.9rem;
    font-size: 0.85rem;
    line-height: 1.5;
}

.post-preview :deep(code) {
    font-size: 0.9em;
}

.post-preview :deep(hr) {
    margin: 1rem 0;
    border-color: rgb(var(--c-line));
}

.post-preview :deep(a) {
    color: rgb(var(--c-accent));
    text-decoration: underline;
}
</style>
