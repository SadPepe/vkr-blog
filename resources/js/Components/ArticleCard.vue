<template>
    <article
        class="group relative rounded-md border border-line bg-surface p-5 shadow-sm transition-all duration-200 hover:border-accent hover:shadow-md /30"
    >
        <!-- Одна ссылка на статью: прозрачный оверлей на всю карточку -->
        <Link
            :href="`/articles/${article.slug}`"
            prefetch="hover"
            class="absolute inset-0 z-10 rounded-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2"
            :aria-label="article.title ? `Читать пост: ${article.title}` : 'Читать пост'"
        />

        <!-- Действия владельца (редактировать/удалить) -->
        <div v-if="$slots.actions" class="absolute right-3 top-3 z-20 flex items-center gap-1">
            <slot name="actions" />
        </div>

        <div class="flex flex-col gap-4 md:gap-5">
            <!-- Автор: аватар, имя, категория и дата -->
            <Link
                :href="route('authors.show', article.user.id)"
                class="relative z-20 inline-flex items-center gap-3 self-start rounded focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2"
                :class="{ 'pr-20': $slots.actions }"
            >
                <UserAvatar :user="article.user" :size="40" />
                <span class="min-w-0">
                    <span
                        class="block truncate text-sm font-semibold text-content transition-colors group-hover:text-accent dark:group-hover:text-accent"
                    >
                        {{ article.user.name }}
                    </span>
                    <span class="mt-0.5 flex flex-wrap items-center gap-x-1.5 text-xs text-muted">
                        <span v-if="categoryName" class="font-medium uppercase tracking-wide text-accent">
                            {{ categoryName }}
                        </span>
                        <time>{{ formatDate(article.created_at) }}</time>
                    </span>
                </span>
            </Link>

            <!-- Заголовок -->
            <h2
                v-if="article.title"
                class="line-clamp-2 text-xl font-bold leading-snug tracking-tight text-content transition-colors group-hover:text-accent md:text-2xl dark:group-hover:text-accent"
            >
                {{ article.title }}
            </h2>

            <!-- Пост как есть: текст и картинки в авторском порядке -->
            <div v-if="article.preview" class="relative">
                <div
                    class="post-preview max-h-[22rem] overflow-hidden text-[15px] leading-7 text-content"
                    v-html="article.preview"
                ></div>
                <div
                    class="pointer-events-none absolute inset-x-0 bottom-0 h-14 bg-gradient-to-t from-surface to-transparent"
                    aria-hidden="true"
                ></div>
            </div>

            <span v-if="article.preview" class="text-sm font-semibold text-accent">Читать полностью →</span>

            <!-- Статистика -->
            <div class="flex items-center gap-5 text-xs text-muted">
                <span class="inline-flex items-center gap-1.5">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                        />
                    </svg>
                    <span>{{ article.views_count || 0 }}</span>
                </span>

                <span class="inline-flex items-center gap-1.5">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
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

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString("ru-RU", {
        year: "numeric",
        month: "long",
        day: "numeric",
    });
};
</script>

<style scoped>
.post-preview :deep(p) {
    margin: 0 0 0.75rem;
}

.post-preview :deep(p:last-child) {
    margin-bottom: 0;
}

.post-preview :deep(h1),
.post-preview :deep(h2),
.post-preview :deep(h3) {
    margin: 0.75rem 0 0.5rem;
    font-weight: 700;
    line-height: 1.3;
}

.post-preview :deep(img) {
    display: block;
    max-width: 100%;
    height: auto;
    margin: 0.75rem 0;
    border-radius: 0.375rem;
}

.post-preview :deep(figure) {
    margin: 0.75rem 0;
}

.post-preview :deep(figcaption) {
    margin-top: 0.35rem;
    font-size: 0.8rem;
    color: var(--c-muted);
}

.post-preview :deep(blockquote) {
    margin: 0.75rem 0;
    border-left: 3px solid var(--c-accent);
    padding-left: 0.85rem;
    color: var(--c-muted);
}

.post-preview :deep(ul),
.post-preview :deep(ol) {
    margin: 0.5rem 0;
    padding-left: 1.25rem;
}

.post-preview :deep(ul) {
    list-style: disc;
}

.post-preview :deep(ol) {
    list-style: decimal;
}

.post-preview :deep(a) {
    color: var(--c-accent);
    text-decoration: underline;
}

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
