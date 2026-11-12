<!-- resources/js/Components/ArticleStats.vue -->
<template>
    <!-- Просмотры — метрика, а не действие: текстом, как счётчики в карточках рейла.
         Лайк и обсуждение — пилюли, как в ленте на главной. -->
    <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-muted">
        <span class="inline-flex items-center gap-1.5">
            <svg
                class="h-4 w-4 text-muted"
                fill="none"
                stroke="currentColor"
                stroke-width="1.5"
                viewBox="0 0 24 24"
            >
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                />
            </svg>
            <span>{{ viewsCount }}</span>
        </span>

        <span class="flex flex-wrap items-center gap-2">
            <LikeButton
                :article="article"
                :initial-liked="initialLiked"
                :initial-likes-count="initialLikesCount"
                :label="pluralLikes"
                pill
            />

            <a
                :href="commentsHref"
                class="ui-pill"
                :aria-label="`${commentsCount} ${pluralComments}`"
                :title="`${commentsCount} ${pluralComments}`"
            >
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
                    />
                </svg>
                <span>{{ commentsCount }}</span>
            </a>
        </span>
    </div>
</template>

<script setup>
import { computed } from "vue";
import LikeButton from "@/Components/LikeButton.vue";

const props = defineProps({
    article: {
        type: Object,
        required: true,
    },
    viewsCount: {
        type: Number,
        default: 0,
    },
    initialLiked: {
        type: Boolean,
        default: false,
    },
    initialLikesCount: {
        type: Number,
        default: 0,
    },
    commentsCount: {
        type: Number,
        default: 0,
    },
    commentsHref: {
        type: String,
        default: "#comments",
    },
});

const pluralize = (n, forms) => {
    const count = Math.abs(n ?? 0) % 100;
    const last = count % 10;
    if (count > 10 && count < 20) return forms[2];
    if (last > 1 && last < 5) return forms[1];
    if (last === 1) return forms[0];
    return forms[2];
};

const pluralLikes = computed(() => pluralize(props.initialLikesCount, ["лайк", "лайка", "лайков"]));
const pluralComments = computed(() => pluralize(props.commentsCount, ["комментарий", "комментария", "комментариев"]));
</script>
