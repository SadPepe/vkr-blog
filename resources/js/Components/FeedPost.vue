<!-- resources/js/Components/FeedPost.vue -->
<template>
    <article>
        <!-- Автор, категория и дата одной строкой; на странице автора строка автора не нужна -->
        <header v-if="showAuthor" class="flex items-center gap-3">
            <UserAvatar :user="article.user" :size="44" />
            <p class="min-w-0 text-[1.0625rem] leading-snug">
                <Link :href="route('authors.show', article.user.id)" class="font-medium text-content transition-colors hover:text-accent">
                    {{ article.user.name }}
                </Link>
                <span class="text-muted" aria-hidden="true"> · </span>
                <Link
                    v-if="article.category_slug"
                    :href="route('articlesByCategory', article.category_slug)"
                    class="text-muted transition-colors hover:text-accent"
                >
                    {{ categoryName }}
                </Link>
                <span v-else class="text-muted">Без категории</span>
                <span class="text-muted" aria-hidden="true"> · </span>
                <time :datetime="article.created_at" class="whitespace-nowrap text-muted">{{ formatDate(article.created_at) }}</time>
            </p>
        </header>

        <p v-else class="text-[1.0625rem] leading-snug text-muted">
            <Link
                v-if="article.category_slug"
                :href="route('articlesByCategory', article.category_slug)"
                class="transition-colors hover:text-accent"
            >
                {{ categoryName }}
            </Link>
            <span v-else>Без категории</span>
            <span aria-hidden="true"> · </span>
            <time :datetime="article.created_at" class="whitespace-nowrap">{{ formatDate(article.created_at) }}</time>
        </p>

        <h3
            v-if="heading"
            class="mt-4 font-display text-[1.75rem] font-bold leading-[1.15] tracking-[-0.02em] text-content"
        >
            <Link :href="route('articles.show', article.slug)" class="transition-colors hover:text-accent">
                {{ heading }}
            </Link>
        </h3>

        <!-- Тело поста: авторский порядок блоков, без отдельной обложки -->
        <div
            class="post-content mt-4 text-content"
            :class="{ 'feed-body--clamped': article.has_more && !isExpanded }"
            v-html="postHtml"
        />

        <button
            v-if="article.has_more || isExpanded"
            type="button"
            class="mt-5 inline-flex min-h-11 items-center text-sm text-accent transition-colors hover:text-accent-hover"
            :aria-expanded="isExpanded"
            @click="toggleExpand"
        >
            {{ isExpanded ? "Свернуть" : busy ? "Загружаю…" : "Развернуть" }}
        </button>

        <div class="mt-5 flex flex-wrap items-center gap-x-5 gap-y-2 text-[1.0625rem] text-muted">
            <span
                class="inline-flex items-center gap-1.5"
                :aria-label="`${article.views_count} ${pluralViews(article.views_count)}`"
            >
                <svg class="h-[1.125rem] w-[1.125rem]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                    />
                </svg>
                <span class="num">{{ article.views_count }}</span>
            </span>

            <LikeButton :article="article" :initial-liked="Boolean(article.is_liked)" :initial-likes-count="article.likes_count ?? 0" />

            <Link
                :href="`${articleUrl}#comments`"
                class="inline-flex items-center gap-1.5 transition-colors hover:text-accent"
                :aria-label="`${article.comments_count} ${pluralComments(article.comments_count)} — к обсуждению`"
            >
                <svg class="h-[1.125rem] w-[1.125rem]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
                    />
                </svg>
                <span class="num">{{ article.comments_count }}</span>
            </Link>

            <button
                type="button"
                class="inline-flex items-center gap-1.5 transition-colors hover:text-accent"
                :aria-label="copied ? 'Ссылка скопирована' : 'Поделиться ссылкой'"
                @click="share"
            >
                <svg class="h-[1.125rem] w-[1.125rem]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M13.828 10.172a4 4 0 010 5.656l-3 3a4 4 0 11-5.656-5.656l1.5-1.5m9.656-1.5l1.5-1.5a4 4 0 10-5.656-5.656l-3 3a4 4 0 000 5.656"
                    />
                </svg>
                <span>{{ copied ? "Скопировано" : "Поделиться" }}</span>
            </button>

            <!-- Свои посты: править и удалить — справа -->
            <span v-if="$slots.actions" class="ml-auto flex items-center gap-1"><slot name="actions" /></span>
        </div>

        <!-- Один комментарий под постом: отбит линией и кеглем, чтобы не читался как ещё один пост -->
        <Link
            v-if="article.latest_comment"
            :href="`${articleUrl}#comments`"
            class="group mt-6 flex items-start gap-3 border-l-2 border-line pl-4"
            aria-label="Читать обсуждение поста"
        >
            <UserAvatar :user="article.latest_comment.user" :size="24" class="mt-0.5" />
            <div class="min-w-0">
                <p class="text-[0.9375rem] leading-snug text-muted">
                    <span class="font-medium text-content transition-colors group-hover:text-accent">{{
                        article.latest_comment.user.name
                    }}</span>
                    <span aria-hidden="true"> · </span>
                    <span>комментарий</span>
                </p>
                <p class="mt-1 line-clamp-2 text-[1rem] leading-snug text-content">{{ article.latest_comment.body }}</p>
            </div>
        </Link>
    </article>
</template>

<script setup>
import { computed, ref } from "vue";
import { Link, router } from "@inertiajs/vue3";
import LikeButton from "@/Components/LikeButton.vue";
import UserAvatar from "@/Components/UserAvatar.vue";

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

const articleUrl = computed(() => route("articles.show", props.article.slug));

// «Поделиться» = скопировать ссылку на пост, чтобы её можно было отправить куда угодно
const share = async () => {
    const url = `${window.location.origin}${articleUrl.value}`;

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
const demoteHeadings = (html) => (html || "").replace(/<(\/?)h([1-6])\b[^>]*>/gi, (_, close) => (close ? "</p>" : '<p class="post-subhead">'));

const postHtml = computed(() => demoteHeadings(expanded.value ? fullHtml.value : props.article.preview));

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
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
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

const formatDate = (value) => new Intl.DateTimeFormat("ru-RU", { day: "numeric", month: "long", year: "numeric" }).format(new Date(value));

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
