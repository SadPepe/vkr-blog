<!-- Действия под статьёй: лайк, комментарии и «поделиться» одним рядом иконок,
     как в ленте — без пилюль и подписей. -->
<template>
    <div class="mt-10 flex flex-wrap items-center gap-x-1 gap-y-2">
        <LikeButton
            :article="article"
            :initial-liked="article.is_liked"
            :initial-likes-count="article.likes_count"
            class="post-action"
        />

        <a
            href="#comments"
            class="post-action"
            :aria-label="commentsCount ? `Комментарии: ${commentsCount}` : 'К комментариям'"
        >
            <svg class="h-[1.375rem] w-[1.375rem] sm:h-[1.125rem] sm:w-[1.125rem] shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
                />
            </svg>
            <span v-if="commentsCount" class="num">{{ commentsCount }}</span>
        </a>

        <ArticleShare :url="shareUrl" />

        <time
            :datetime="article.created_at"
            :title="formatDateTime(article.created_at)"
            class="num ml-auto text-[0.8125rem] tracking-[0.02em] text-muted"
        >
            {{ relativeTime(article.created_at) }}
        </time>
    </div>
</template>

<script setup>
import { computed } from "vue";
import ArticleShare from "@/Components/ArticleShare.vue";
import LikeButton from "@/Components/LikeButton.vue";
import { formatDateTime, relativeTime } from "@/Support/relativeTime";

const props = defineProps({
    article: {
        type: Object,
        required: true,
    },
    shareUrl: {
        type: String,
        required: true,
    },
});

// Считаем вместе с ответами — ровно столько, сколько видно в обсуждении ниже
const commentsCount = computed(() =>
    (props.article.comments ?? []).reduce((sum, comment) => sum + 1 + (comment.replies?.length ?? 0), 0),
);
</script>
