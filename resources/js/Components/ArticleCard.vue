<template>
    <article
        class="border-b border-gray-200/70 py-8 first:pt-0 last:border-b-0 md:py-10 dark:border-white/10"
    >
        <div class="flex flex-col gap-4 md:gap-5">
            <!-- Автор: аватар, имя, категория и дата -->
            <Link
                :href="route('authors.show', article.user.id)"
                class="group inline-flex items-center gap-3"
            >
                <UserAvatar :user="article.user" :size="40" />
                <span class="min-w-0">
                    <span
                        class="block truncate text-sm font-semibold text-gray-900 transition-colors group-hover:text-emerald-700 dark:text-stone-100 dark:group-hover:text-emerald-300"
                    >
                        {{ article.user.name }}
                    </span>
                    <span
                        class="mt-0.5 flex flex-wrap items-center gap-x-1.5 text-xs text-gray-500 dark:text-stone-500"
                    >
                        <span
                            v-if="categoryName"
                            class="font-medium uppercase tracking-wide text-emerald-600 dark:text-emerald-400"
                        >
                            {{ categoryName }}
                        </span>
                        <time>{{ formatDate(article.created_at) }}</time>
                    </span>
                </span>
            </Link>

            <!-- Заголовок -->
            <Link :href="`/articles/${article.slug}`" class="group inline-block">
                <h2
                    class="line-clamp-2 text-xl font-bold leading-snug tracking-tight text-gray-900 transition-colors group-hover:text-emerald-700 md:text-2xl dark:text-stone-100 dark:group-hover:text-emerald-300"
                >
                    {{ article.title }}
                </h2>
            </Link>

            <!-- Превью: между заголовком и описанием -->
            <Link
                :href="`/articles/${article.slug}`"
                class="block overflow-hidden rounded-md bg-zinc-100 dark:bg-zinc-800"
                aria-label="Читать статью"
            >
                <img
                    v-if="article.card_image_path"
                    :src="article.card_image_path"
                    alt="Обложка статьи"
                    class="aspect-video w-full object-cover transition-transform duration-500 hover:scale-105"
                    loading="lazy"
                />
                <div
                    v-else
                    class="flex aspect-video w-full items-center justify-center"
                >
                    <svg
                        class="h-10 w-10 text-zinc-300 dark:text-zinc-600"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.5"
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                        />
                    </svg>
                </div>
            </Link>

            <!-- Первые строки статьи -->
            <div class="flex flex-col gap-1.5">
                <p
                    class="line-clamp-3 text-sm leading-7 text-stone-500 md:text-[15px] dark:text-stone-400"
                >
                    {{ plainText }}
                </p>
                <Link
                    :href="`/articles/${article.slug}`"
                    class="inline-flex items-center gap-1 text-sm font-semibold text-emerald-600 transition-colors hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300"
                >
                    Читать дальше
                    <svg
                        class="h-3.5 w-3.5"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M17 8l4 4m0 0l-4 4m4-4H3"
                        />
                    </svg>
                </Link>
            </div>

            <!-- Статистика -->
            <div
                class="flex items-center gap-5 pt-1 text-xs text-gray-500 dark:text-stone-500"
            >
                <span class="inline-flex items-center gap-1.5">
                    <svg
                        class="h-3.5 w-3.5 text-gray-400 dark:text-stone-500"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.5"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                        />
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                        />
                    </svg>
                    <span>{{ article.views_count || 0 }}</span>
                </span>

                <span class="inline-flex items-center gap-1.5">
                    <svg
                        class="h-3.5 w-3.5 text-gray-400 dark:text-stone-500"
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
                    <span>{{ article.likes_count || 0 }}</span>
                </span>
            </div>
        </div>
    </article>
</template>

<script setup>
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";
import UserAvatar from "./UserAvatar.vue";

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

const categoryName = computed(() => {
    const slug = props.article.category_slug;
    const found = props.categories.find((c) => c.slug === slug);
    return found ? found.name : "";
});

const plainText = computed(() => {
    const html = props.article.content || "";
    if (!html) return (props.article.excerpt || "").trim();

    const div = document.createElement("div");
    div.innerHTML = html;
    return (div.textContent || "").replace(/\s+/g, " ").trim();
});

const formatDate = (dateString) => {
    const date = new Date(dateString);
    const day = date.toLocaleDateString("ru-RU", {
        year: "numeric",
        month: "long",
        day: "numeric",
    });
    const time = date.toLocaleTimeString("ru-RU", {
        hour: "2-digit",
        minute: "2-digit",
    });
    return `${day}, ${time}`;
};
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>