<template>
    <Link
        :href="route('articles.show', article.slug)"
        prefetch="hover"
        class="group flex h-full flex-col overflow-hidden rounded-md border border-line bg-surface shadow-sm transition-all duration-200 hover:border-accent hover:shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2 focus-visible:ring-offset-page /30"
    >
        <div
            class="aspect-video w-full overflow-hidden bg-surface-muted"
            :class="{ 'animate-pulse': article.card_image_path && !imageLoaded }"
        >
            <img
                v-if="article.card_image_path"
                :src="article.card_image_path"
                :alt="article.title || article.excerpt || 'Изображение поста'"
                class="h-full w-full object-cover transition-all duration-500 group-hover:scale-105"
                :class="imageLoaded ? 'opacity-100' : 'opacity-0'"
                loading="lazy"
                decoding="async"
                @load="imageLoaded = true"
            />
            <div v-else class="flex h-full w-full items-center justify-center">
                <svg
                    class="h-8 w-8 text-muted"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.5"
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M4 16l4.586-4.586a2 0 012.828 0L16 16m-2-2l1.586-1.586a2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 0 002-2V6a2 0 00-2-2H6a2 0 00-2 2v12a2 0 002 2z"
                    />
                </svg>
            </div>
        </div>

        <div class="flex flex-1 flex-col gap-2.5 p-4">
            <div class="flex flex-wrap items-center gap-x-2 text-xs text-muted">
                <span v-if="categoryName" class="font-medium uppercase tracking-wide text-accent">
                    {{ categoryName }}
                </span>
                <span v-if="categoryName" class="text-muted" aria-hidden="true">·</span>
                <time :datetime="article.created_at">{{ formatDate(article.created_at) }}</time>
            </div>

            <h3
                v-if="article.title"
                class="line-clamp-3 text-base font-bold leading-snug text-content transition-colors group-hover:text-accent dark:group-hover:text-accent"
            >
                {{ article.title }}
            </h3>

            <p v-if="summary" class="line-clamp-2 text-sm leading-relaxed text-muted">
                {{ summary }}
            </p>

            <div class="mt-auto flex items-center justify-between gap-3 pt-2">
                <span class="flex min-w-0 items-center gap-2">
                    <UserAvatar :user="article.user" :size="24" />
                    <span class="truncate text-xs font-medium text-content">
                        {{ article.user.name }}
                    </span>
                </span>

                <span class="flex shrink-0 items-center gap-3 text-xs text-muted">
                    <span class="inline-flex items-center gap-1" :aria-label="`Просмотров: ${article.views_count || 0}`">
                        <svg
                            class="h-3.5 w-3.5"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.5"
                            viewBox="0 0 24 24"
                            aria-hidden="true"
                        >
                            <circle cx="12" cy="12" r="3" />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                            />
                        </svg>
                        {{ article.views_count || 0 }}
                    </span>
                    <span class="inline-flex items-center gap-1" :aria-label="`Лайков: ${article.likes_count || 0}`">
                        <svg
                            class="h-3.5 w-3.5"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.5"
                            viewBox="0 0 24 24"
                            aria-hidden="true"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"
                            />
                        </svg>
                        {{ article.likes_count || 0 }}
                    </span>
                </span>
            </div>
        </div>
    </Link>
</template>

<script setup>
import { computed, ref } from "vue";
import { Link } from "@inertiajs/vue3";
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
});

const imageLoaded = ref(false);

const categoryName = computed(() => {
    const found = props.categories.find((c) => c.slug === props.article.category_slug);
    return found ? found.name : "";
});

const summary = computed(() => (props.article.excerpt || "").trim());

const formatDate = (dateString) =>
    new Date(dateString).toLocaleDateString("ru-RU", {
        day: "numeric",
        month: "long",
        year: "numeric",
    });
</script>
