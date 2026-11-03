<template>
    <div class="rounded-md border border-line bg-surface p-6 shadow-sm">
        <h3 class="mb-4 flex items-center text-xl font-bold text-content">
            <svg class="mr-2 h-5 w-5 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"
                />
            </svg>
            Похожие статьи
        </h3>

        <div class="space-y-4">
            <Link
                v-for="article in articles"
                :key="article.id"
                :href="`/articles/${article.slug}`"
                prefetch="hover"
                class="group flex gap-3 rounded focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2"
            >
                <div class="h-16 w-24 shrink-0 overflow-hidden rounded-md bg-surface-muted">
                    <img
                        v-if="article.card_image_path"
                        :src="article.card_image_path"
                        :alt="article.title || postLabel(article)"
                        class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                        loading="lazy"
                    />
                    <div
                        v-else
                        class="flex h-full w-full items-center justify-center bg-surface-muted"
                    >
                        <svg class="h-6 w-6 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.5"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                            />
                        </svg>
                    </div>
                </div>

                <div class="min-w-0 flex-1">
                    <h4
                        class="line-clamp-2 text-sm font-semibold leading-tight text-content transition-colors group-hover:text-accent dark:group-hover:text-accent"
                    >
                        {{ article.title || postLabel(article) }}
                    </h4>

                    <div class="mt-1.5 flex items-center gap-3 text-xs text-muted">
                        <span class="inline-flex items-center gap-1">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                />
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                />
                            </svg>
                            {{ article.views_count || 0 }}
                        </span>
                        <span class="inline-flex items-center gap-1">
                            <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    fill-rule="evenodd"
                                    d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                            {{ article.likes_count || 0 }}
                        </span>
                    </div>
                </div>
            </Link>
        </div>
    </div>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";

// У поста может не быть заголовка — в списке показываем начало текста
const postLabel = (article) => (article.excerpt || "").trim().slice(0, 80) || "Пост";

defineProps({
    articles: {
        type: Array,
        required: true,
        default: () => [],
    },
});
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
