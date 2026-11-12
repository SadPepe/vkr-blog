<!-- resources/js/Components/FeedPost.vue -->
<template>
    <!-- Пост в открытой ленте: без подложки, рамки и скругления.
         Посты разделяет линия и воздух в родительском .feed-list. -->
    <article class="min-w-0">
        <!-- Шапка: автор, категория и дата; просмотры остаются справа -->
        <header class="flex items-start gap-3 leading-snug text-muted">
            <UserAvatar v-if="showAuthor" :user="article.user" :size="44" class="shrink-0" />
            <div class="min-w-0 flex-1">
                <Link
                    v-if="showAuthor"
                    :href="route('authors.show', article.user.id)"
                    class="block truncate text-[1rem] font-semibold text-content transition-colors hover:text-accent"
                >
                    {{ article.user.name }}
                </Link>
                <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-[0.8125rem]">
                    <Link
                        v-if="article.category_slug"
                        :href="route('articlesByCategory', article.category_slug)"
                        class="transition-colors hover:text-accent"
                    >
                        {{ categoryName }}
                    </Link>
                    <span v-else>Без категории</span>

                    <time :datetime="article.created_at" class="num tracking-[0.02em]">
                        {{ formatDateTime(article.created_at) }}
                    </time>

                    <span
                        class="ml-auto inline-flex items-center gap-1"
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
                        <span class="num font-medium">{{ article.views_count }}</span>
                    </span>
                </div>
            </div>
        </header>

        <h3
            v-if="heading"
            class="mt-4 font-display text-[1.5rem] font-bold leading-[1.2] tracking-[-0.02em] text-content sm:text-[1.75rem]"
        >
            <Link :href="route('articles.show', article.slug)" class="transition-colors hover:text-accent">
                {{ heading }}
            </Link>
        </h3>

        <div
            ref="bodyRef"
            class="post-content mt-3 text-content"
            :class="{ 'feed-body--clamped': article.has_more && !expanded }"
            v-html="postHtml"
            @click="handleContentClick"
        />

        <!-- Текстовые действия чтения остаются ссылками, без лишних пилюль -->
        <div class="mt-3 flex flex-wrap items-center gap-x-5">
            <button
                v-if="article.has_more || expanded"
                type="button"
                class="inline-flex min-h-10 items-center text-sm font-medium text-accent transition-colors hover:text-accent-hover"
                :aria-expanded="expanded"
                @click="toggleExpand"
            >
                {{ expanded ? "Свернуть" : loading ? "Загружаю…" : "Читать дальше" }}
            </button>

            <Link
                :href="articleUrl"
                class="inline-flex min-h-10 items-center text-sm font-medium text-accent transition-colors hover:text-accent-hover"
            >
                Открыть пост
            </Link>
        </div>

        <!-- Взаимодействия — мягкие пилюли: единственный скруглённый элемент в посте -->
        <footer class="mt-4 flex flex-wrap items-center gap-2">
            <LikeButton
                :article="article"
                :initial-liked="Boolean(article.is_liked)"
                :initial-likes-count="article.likes_count ?? 0"
                :label="pluralLikes(article.likes_count ?? 0)"
                pill
            />

            <!-- Если обсуждения ещё нет, действие ведёт к форме комментария.
                 При наличии комментариев единственная точка входа — блок «Обсуждение» ниже. -->
            <Link
                v-if="!article.comments_count"
                :href="articleUrl + '#comments'"
                class="ui-pill"
            >
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
                    />
                </svg>
                <span>Комментировать</span>
            </Link>

            <button type="button" class="ui-pill" :aria-label="copied ? 'Ссылка скопирована' : 'Скопировать ссылку на пост'" @click="share">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M13.828 10.172a4 4 0 010 5.656l-3 3a4 4 0 11-5.656-5.656l1.5-1.5m9.656-1.5l1.5-1.5a4 4 0 10-5.656-5.656l-3 3a4 4 0 000 5.656"
                    />
                </svg>
                <span class="hidden sm:inline">{{ copied ? "Скопировано" : "Поделиться" }}</span>
            </button>

            <span v-if="$slots.actions" class="ml-auto flex items-center gap-1"><slot name="actions" /></span>
        </footer>

        <!-- Обсуждение — отдельная тред-зона без подложки: пилюля-заголовок,
             вертикальная линия и последний комментарий. -->
        <section v-if="article.comments_count > 0" class="mt-6 border-t border-line/70 pt-5" aria-label="Обсуждение поста">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <Link :href="articleUrl + '#comments'" class="ui-pill ui-pill--active">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
                        />
                    </svg>
                    <span>Обсуждение</span>
                    <span class="num">· {{ article.comments_count }}</span>
                </Link>

                <Link
                    :href="articleUrl + '#comments'"
                    class="text-sm font-medium text-accent transition-colors hover:text-accent-hover"
                >
                    Все комментарии →
                </Link>
            </div>

            <div v-if="article.latest_comment" class="comment-thread mt-4">
                <Link
                    :href="articleUrl + '#comments'"
                    class="group flex items-start gap-3 py-1"
                    aria-label="Читать обсуждение поста"
                >
                    <UserAvatar :user="article.latest_comment.user" :size="26" class="mt-0.5" />
                    <div class="min-w-0">
                        <p class="text-[0.9375rem] leading-snug">
                            <span class="font-medium text-content transition-colors group-hover:text-accent">{{
                                article.latest_comment.user.name
                            }}</span>
                            <span class="text-muted"> · </span>
                            <span class="text-muted">последний комментарий</span>
                        </p>
                        <p class="comment-content mt-1 line-clamp-2 break-words text-[0.9375rem] leading-snug text-content">
                            <MentionText v-if="article.latest_comment.body" :text="article.latest_comment.body" :linkify="false" />
                            <template v-else>{{ article.latest_comment.image_url ? "Фото в комментарии" : "Комментарий" }}</template>
                        </p>
                    </div>
                </Link>
            </div>
        </section>

        <!-- Лайтбокс: фото из поста на полный экран, карусель листается по кадрам -->
        <ImageLightbox
            :show="lightboxOpen"
            :src="lightboxSrc"
            :alt="lightboxAlt"
            :images="lightboxImages"
            :index="lightboxIndex"
            @close="closeLightbox"
        />
    </article>
</template>

<script setup>
import { computed, nextTick, onMounted, ref, watch } from "vue";
import { Link, router } from "@inertiajs/vue3";
import LikeButton from "@/Components/LikeButton.vue";
import UserAvatar from "@/Components/UserAvatar.vue";
import ImageLightbox from "@/Components/ImageLightbox.vue";
import MentionText from "@/Components/MentionText.vue";

const props = defineProps({
    article: {
        type: Object,
        required: true,
    },
    categories: {
        type: Array,
        default: () => [],
    },
    // Строка автора нужна в общей ленте и не нужна на странице самого автора
    showAuthor: {
        type: Boolean,
        default: true,
    },
});

const expanded = ref(false);
const copied = ref(false);
const loading = ref(false);
const fullHtml = ref("");

// Фото в посте открываются в лайтбоксе по клику — как на странице статьи
const lightboxOpen = ref(false);
const lightboxSrc = ref("");
const lightboxAlt = ref("");
const lightboxImages = ref([]);
const lightboxIndex = ref(0);

const closeLightbox = () => {
    lightboxOpen.value = false;
};

// Делегирование: работает и для превью, и для картинок, догруженных кнопкой «Читать дальше»
const handleContentClick = (event) => {
    const target = event.target;

    if (!(target instanceof HTMLImageElement)) return;

    event.preventDefault();
    event.stopPropagation();

    // Фото из карусели открываем вместе с соседними кадрами — их можно листать
    const carousel = target.closest(".image-carousel");
    const frames = carousel ? [...carousel.querySelectorAll(":scope > img")] : [];
    const index = frames.indexOf(target);

    lightboxImages.value = frames.map((img) => ({ src: img.currentSrc || img.src, alt: img.alt || "" }));
    lightboxIndex.value = index >= 0 ? index : 0;

    lightboxSrc.value = target.currentSrc || target.src;
    lightboxAlt.value = target.alt || "";
    lightboxOpen.value = true;
};

const articleUrl = computed(() => route("articles.show", props.article.slug));

// «Поделиться» = скопировать ссылку на пост, чтобы её можно было отправить куда угодно
const share = async () => {
    const url = window.location.origin + articleUrl.value;

    try {
        await navigator.clipboard.writeText(url);
    } catch {
        window.prompt("Скопируйте ссылку", url);
        return;
    }

    copied.value = true;
    window.setTimeout(() => (copied.value = false), 2000);
};

const categoryName = computed(() => props.categories.find((c) => c.slug === props.article.category_slug)?.name ?? "Без категории");

const heading = computed(() => (props.article.title ?? "").trim());

// Авторские подзаголовки не должны конкурировать с заголовками страницы:
// оставляем их визуально, но как абзацы, а не как h2/h3
const demoteHeadings = (html) =>
    (html || "").replace(/<(\/?)h([1-6])\b[^>]*>/gi, (_, close) => (close ? "</p>" : '<p class="post-subhead">'));

const postHtml = computed(() => demoteHeadings(expanded.value ? fullHtml.value : props.article.preview));

// Код и карусели в ленте: модули грузим лениво, только когда они нужны
const bodyRef = ref(null);

const enhanceContent = async () => {
    const root = bodyRef.value;
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

onMounted(enhanceContent);

// При раскрытии поста HTML меняется целиком — оформляем блоки заново
watch(postHtml, async () => {
    await nextTick();
    enhanceContent();
});

const toggleExpand = async () => {
    if (expanded.value) {
        expanded.value = false;
        return;
    }

    if (!fullHtml.value && props.article.has_more) {
        loading.value = true;

        try {
            const response = await fetch(route("articles.excerpt", props.article.slug), {
                headers: { Accept: "application/json" },
            });
            if (!response.ok) throw new Error("HTTP " + response.status);
            const data = await response.json();
            fullHtml.value = data.html || props.article.preview;
        } catch {
            router.visit(route("articles.show", props.article.slug));
            return;
        } finally {
            loading.value = false;
        }
    }

    expanded.value = true;
};

// Дата и время в шапке — по-русски, без года: 4 ноября, 14:20
const MONTHS = ["января", "февраля", "марта", "апреля", "мая", "июня", "июля", "августа", "сентября", "октября", "ноября", "декабря"];

const formatDateTime = (value) => {
    const date = new Date(value);
    const pad = (n) => String(n).padStart(2, "0");
    return `${date.getDate()} ${MONTHS[date.getMonth()]}, ${pad(date.getHours())}:${pad(date.getMinutes())}`;
};

const pluralize = (n, [one, few, many]) => {
    const mod10 = n % 10;
    const mod100 = n % 100;
    if (mod10 === 1 && mod100 !== 11) return one;
    if (mod10 >= 2 && mod10 <= 4 && (mod100 < 12 || mod100 > 14)) return few;
    return many;
};

const pluralViews = (n) => pluralize(n, ["просмотр", "просмотра", "просмотров"]);
const pluralLikes = (n) => pluralize(n, ["лайк", "лайка", "лайков"]);
const pluralComments = (n) => pluralize(n, ["комментарий", "комментария", "комментариев"]);
</script>
