<!-- resources/js/Components/ArticleStats.vue -->
<template>
    <div class="flex items-center space-x-6 text-sm text-gray-600 dark:text-stone-400">
        <!-- Просмотры -->
        <div class="flex items-center space-x-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            <span>{{ viewsCount }} просмотров</span>
        </div>

        <!-- Лайки -->
        <div class="flex items-center space-x-2">
            <LikeButton 
                :article="article"
                :initial-liked="initialLiked"
                :initial-likes-count="initialLikesCount"
            />
        </div>

        <!-- Комментарии -->
        <div class="flex items-center space-x-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            <span>{{ commentsCount }} комментариев</span>
        </div>
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