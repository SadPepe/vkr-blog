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
                    <!-- Закрепление живёт в профиле автора и на его странице, не в общей ленте -->
                    <span
                        v-if="article.is_pinned && !showAuthor"
                        class="inline-flex items-center gap-1 font-medium text-accent"
                    >
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M17.25 3.75H6.75A1.5 1.5 0 005.25 5.25v15L12 15.75l6.75 4.5v-15a1.5 1.5 0 00-1.5-1.5z"
                            />
                        </svg>
                        Закреплено
                    </span>

                    <span
                        v-if="article.status && article.status !== 'published'"
                        class="inline-flex items-center gap-1 rounded-sm border border-line bg-surface-muted px-1.5 py-0.5 font-medium text-muted"
                    >
                        {{ statusLabel }}
                    </span>

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

                    <!-- Действия автора — «⋯» рядом с просмотрами; в чужой ленте слота нет -->
                    <div v-if="$slots.actions" class="-my-1 flex items-center"><slot name="actions" /></div>
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
        <div v-if="article.has_more || expanded" class="mt-3 flex flex-wrap items-center gap-x-5">
            <button
                type="button"
                class="inline-flex min-h-10 items-center text-sm font-medium text-accent transition-colors hover:text-accent-hover"
                :aria-expanded="expanded"
                @click="toggleExpand"
            >
                {{ expanded ? "Свернуть" : loading ? "Загружаю…" : "Читать дальше" }}
            </button>
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

            <Link :href="articleUrl" class="ui-pill">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                    />
                </svg>
                <span>К статье</span>
            </Link>

            <!-- Если обсуждения ещё нет и гость не может писать, ведём к форме на странице поста.
                 Автор комментария пишет прямо в ленте, поэтому дубля кнопки нет. -->
            <Link
                v-if="!commentsCount && !canComment"
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
        </footer>

        <!-- Комментарии — как на странице поста: подпись с числом, знакомое поле ввода
             и несколько свежих веток с ответами. Полная лента — по «Все комментарии». -->
        <section
            v-if="commentsCount > 0 || canComment"
            class="mt-6 border-t border-line/70 pt-5"
            aria-label="Комментарии к посту"
        >
            <p class="flex items-baseline gap-2 text-[0.9375rem] font-semibold text-content">
                Комментарии
                <span class="num font-normal text-muted">{{ commentsCount }}</span>
            </p>

            <form v-if="canComment" class="mt-4" @submit.prevent="submitComment">
                <CommentInput
                    :id="`feed-comment-${article.id}`"
                    v-model="newComment"
                    v-model:image="newImage"
                    label="Ваш комментарий"
                    placeholder="..."
                    submit-label="Отправить комментарий"
                    :loading="commentLoading"
                    :error="commentErrors.content"
                    :image-error="commentErrors.image"
                    :hint="commentHint"
                />
            </form>

            <p v-else class="mt-4 text-sm text-muted">
                <Link :href="route('login')" class="font-medium text-accent transition-colors hover:text-accent-hover">
                    Войдите
                </Link>, чтобы комментировать.
            </p>

            <ol v-if="recentComments.length" class="mt-6 space-y-6" aria-label="Комментарии">
                <li v-for="comment in recentComments" :key="comment.id">
                    <div class="flex items-start gap-3">
                        <UserAvatar :user="comment.user" :size="30" class="mt-0.5 shrink-0" />
                        <div class="min-w-0">
                            <p class="flex flex-wrap items-baseline gap-x-2 text-[0.9375rem] leading-snug">
                                <span class="font-semibold text-content">{{ comment.user?.name }}</span>
                                <time :datetime="comment.created_at" class="num text-[0.8125rem] text-muted">
                                    {{ formatDateTime(comment.created_at) }}
                                </time>
                            </p>
                            <p
                                v-if="comment.body"
                                class="comment-content mt-1 break-words text-[0.9375rem] leading-snug text-content"
                            >
                                <MentionText :text="comment.body" />
                            </p>
                            <img
                                v-if="comment.image_url"
                                :src="comment.image_url"
                                alt="Фото в комментарии"
                                loading="lazy"
                                class="mt-2 max-h-56 w-auto max-w-full rounded-lg border border-line object-cover"
                            />

                            <!-- Ответы — одной веткой с линией, как в полном обсуждении -->
                            <ul v-if="comment.replies?.length" class="mt-3 space-y-3 border-l-2 border-line pl-3.5">
                                <li v-for="reply in comment.replies" :key="reply.id" class="flex items-start gap-2.5">
                                    <UserAvatar :user="reply.user" :size="24" class="mt-0.5 shrink-0" />
                                    <div class="min-w-0">
                                        <p class="flex flex-wrap items-baseline gap-x-2 text-[0.9375rem] leading-snug">
                                            <span class="font-semibold text-content">{{ reply.user?.name }}</span>
                                            <time :datetime="reply.created_at" class="num text-[0.8125rem] text-muted">
                                                {{ formatDateTime(reply.created_at) }}
                                            </time>
                                        </p>
                                        <p
                                            v-if="reply.body"
                                            class="comment-content mt-1 break-words text-[0.9375rem] leading-snug text-content"
                                        >
                                            <MentionText :text="reply.body" />
                                        </p>
                                        <img
                                            v-if="reply.image_url"
                                            :src="reply.image_url"
                                            alt="Фото в комментарии"
                                            loading="lazy"
                                            class="mt-2 max-h-48 w-auto max-w-full rounded-lg border border-line object-cover"
                                        />
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </li>
            </ol>

            <!-- Одна точка входа в полное обсуждение — в конце ветки, а не в заголовке -->
            <div v-if="commentsCount > 0" class="mt-3 flex justify-end">
                <Link
                    :href="articleUrl + '#comments'"
                    class="text-sm font-semibold text-accent transition-colors hover:text-accent-hover"
                >
                    Все комментарии
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
import { computed, nextTick, onMounted, reactive, ref, watch } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import LikeButton from "@/Components/LikeButton.vue";
import CommentInput from "@/Components/Comments/CommentInput.vue";
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

const page = usePage();
const canComment = computed(() => Boolean(page.props.auth?.user));

// Комментарий из ленты: то же поле, что и на странице поста, но без отдельного компонента формы
const MAX_COMMENT_LENGTH = 1000;
const newComment = ref("");
const newImage = ref(null);
const commentLoading = ref(false);
const commentErrors = reactive({});

// Комментарии обновляем локально после AJAX-ответа: Inertia не перерисовывает
// всю страницу и не восстанавливает scroll, поэтому форма не «прыгает».
const commentsCount = ref(props.article.comments_count ?? 0);
const recentComments = ref([...(props.article.recent_comments ?? [])]);

watch(
    () => props.article.comments_count,
    (value) => {
        commentsCount.value = value ?? 0;
    },
);

watch(
    () => props.article.recent_comments,
    (value) => {
        recentComments.value = [...(value ?? [])];
    },
);

const commentHint = computed(() => {
    const remaining = MAX_COMMENT_LENGTH - newComment.value.length;

    return remaining <= 200 ? `${remaining} ${pluralize(remaining, ["символ", "символа", "символов"])} осталось` : "";
});

const submitComment = async () => {
    if ((!newComment.value.trim() && !newImage.value) || commentLoading.value) return;

    commentLoading.value = true;
    commentErrors.content = "";
    commentErrors.image = "";

    const payload = new FormData();
    payload.append("content", newComment.value);
    if (newImage.value) payload.append("image", newImage.value);

    try {
        const response = await fetch(route("comments.store", { article: props.article.slug }), {
            method: "POST",
            headers: {
                Accept: "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.getAttribute("content"),
            },
            body: payload,
        });

        if (response.status === 401) {
            router.visit(route("login"));
            return;
        }

        if (response.status === 422) {
            const data = await response.json();
            if (data.errors?.content) commentErrors.content = data.errors.content[0];
            if (data.errors?.image) commentErrors.image = data.errors.image[0];
            return;
        }

        if (!response.ok) throw new Error(`HTTP ${response.status}`);

        const created = await response.json();
        recentComments.value = [
            {
                id: created.id,
                body: created.content ?? "",
                image_url: created.image_url,
                created_at: created.created_at,
                user: {
                    name: created.user?.name ?? "",
                    avatar_url: created.user?.avatar_url,
                },
                replies: [],
            },
            ...recentComments.value.filter((comment) => comment.id !== created.id),
        ].slice(0, 3);
        commentsCount.value += 1;
        newComment.value = "";
        newImage.value = null;
    } catch (error) {
        console.error("Не удалось отправить комментарий:", error);
        commentErrors.content = "Не удалось отправить комментарий. Попробуйте ещё раз.";
    } finally {
        commentLoading.value = false;
    }
};

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

// В своём профиле видно и черновики, и скрытое — подписываем их, чтобы
// не путать с опубликованным
const statusLabel = computed(() => (props.article.status === "draft" ? "Черновик" : "Скрыто"));

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
