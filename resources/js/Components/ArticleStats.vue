<!-- resources/js/Components/ArticleStats.vue -->
<template>
    <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-gray-500 dark:text-stone-500">
        <!-- Просмотры -->
        <span class="inline-flex items-center gap-1.5">
            <svg class="h-4 w-4 text-gray-400 dark:text-stone-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            <span>{{ viewsCount }}</span>
        </span>

        <!-- Лайки -->
        <span class="inline-flex items-center">
            <LikeButton
                :article="article"
                :initial-liked="initialLiked"
                :initial-likes-count="initialLikesCount"
            />
        </span>

        <!-- Комментарии -->
        <span class="inline-flex items-center gap-1.5">
            <svg class="h-4 w-4 text-gray-400 dark:text-stone-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            <span>{{ commentsCount }}</span>
        </span>
    </div>
</template>

<script setup>
import LikeButton from '@/Components/LikeButton.vue';

const props = defineProps({
    article: {
        type: Object,
        required: true
    },
    viewsCount: {
        type: Number,
        default: 0
    },
    initialLiked: {
        type: Boolean,
        default: false
    },
    initialLikesCount: {
        type: Number,
        default: 0
    },
    commentsCount: {
        type: Number,
        default: 0
    }
});
</script>