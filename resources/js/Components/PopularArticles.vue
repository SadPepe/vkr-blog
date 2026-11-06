<!-- resources/js/Components/PopularArticles.vue -->
<template>
    <div class="bg-surface rounded-md shadow-sm border border-line p-6">
        <h3 class="text-xl font-bold text-content mb-2 flex items-center">
            <svg class="w-4 h-4 mr-2 text-muted" fill="currentColor" viewBox="0 0 20 20">
                <path
                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                />
            </svg>
            {{ title }}
        </h3>
        <p v-if="subtitle" class="mb-4 -mt-1 text-sm text-muted">
            {{ subtitle }}
        </p>

        <div class="space-y-4">
            <article
                v-for="(article, index) in articles"
                :key="article.id"
                class="group cursor-pointer transition-all duration-300 hover:bg-surface-muted rounded p-3 -mx-3"
            >
                <Link
                    :href="route('articles.show', article.slug)"
                    prefetch="hover"
                    class="flex items-start space-x-3 rounded focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2"
                >
                    <!-- Номер/медаль -->
                    <div
                        class="flex-shrink-0 w-6 h-6 flex items-center justify-center rounded-full text-xs font-bold"
                        :class="getRankClass(index)"
                    >
                        {{ index + 1 }}
                    </div>

                    <!-- Контент -->
                    <div class="flex-1 min-w-0">
                        <h4
                            class="text-sm font-semibold text-content leading-tight group-hover:text-accent dark:group-hover:text-accent transition-colors line-clamp-2 mb-1"
                        >
                            {{ article.title || (article.excerpt || "").trim().slice(0, 80) || "Пост" }}
                        </h4>

                        <!-- Статистика -->
                        <div class="flex items-center space-x-3 text-xs text-muted">
                            <span class="flex items-center space-x-1">
                                <svg
                                    class="w-3 h-3 text-muted"
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
                                <span>{{ formatNumber(article.views_count) }}</span>
                            </span>

                            <span class="flex items-center space-x-1">
                                <svg
                                    class="w-3 h-3 text-muted"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
                                    />
                                </svg>
                                <span>{{ formatNumber(article.likes_count) }}</span>
                            </span>

                            <span class="flex items-center space-x-1" v-if="article.comments_count">
                                <svg
                                    class="w-3 h-3 text-muted"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
                                    />
                                </svg>
                                <span>{{ formatNumber(article.comments_count) }}</span>
                            </span>
                        </div>
                    </div>
                </Link>
            </article>
        </div>
    </div>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";

const props = defineProps({
    articles: {
        type: Array,
        required: true,
        default: () => [],
    },
    title: {
        type: String,
        default: "Популярные статьи",
    },
    subtitle: {
        type: String,
        default: "",
    },
});

const getRankClass = (index) => {
    switch (index) {
        case 0:
            return "bg-accent hover:bg-accent-hover text-accent-contrast shadow-sm";
        case 1:
            return "bg-surface-muted text-content  ";
        case 2:
            return "bg-surface-muted text-content";
        default:
            return "bg-surface-muted text-muted ";
    }
};

const formatNumber = (num) => {
    if (num >= 1000) {
        return (num / 1000).toFixed(1).replace(".", ",") + " тыс.";
    }
    return num;
};
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
