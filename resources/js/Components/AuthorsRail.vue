<!-- resources/js/Components/AuthorsRail.vue -->
<template>
    <section>
        <div class="flex items-baseline justify-between gap-4 border-b border-line pb-3">
            <h2 class="type-h3 text-content">Авторы</h2>
            <Link :href="route('authors.index')" class="text-sm font-medium text-accent transition-colors hover:text-accent-hover">
                Все →
            </Link>
        </div>

        <ul class="mt-1">
            <li v-for="author in authors" :key="author.id">
                <Link
                    :href="route('authors.show', author.id)"
                    class="group flex items-start gap-3 py-4 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-accent"
                >
                    <UserAvatar :user="author" :size="36" class="mt-0.5 shrink-0" />
                    <div class="min-w-0">
                        <p class="truncate text-[0.9375rem] font-semibold text-content transition-colors group-hover:text-accent">
                            {{ author.name }}
                        </p>
                        <p v-if="author.about" class="mt-0.5 line-clamp-2 text-[0.8125rem] leading-snug text-muted">
                            {{ author.about }}
                        </p>
                        <p v-else-if="author.last_article" class="mt-0.5 line-clamp-1 text-[0.8125rem] leading-snug text-muted">
                            {{ author.last_article.label }}
                        </p>
                        <p class="mt-1 text-[0.8125rem] text-muted">
                            <span class="num">{{ author.articles_count }}</span>
                            {{ pluralArticles(author.articles_count) }}
                            <template v-if="author.last_published_at">
                                <span aria-hidden="true"> · </span>
                                {{ formatDate(author.last_published_at) }}
                            </template>
                        </p>
                    </div>
                </Link>
            </li>
        </ul>
    </section>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";
import UserAvatar from "@/Components/UserAvatar.vue";

defineProps({
    authors: {
        type: Array,
        default: () => [],
    },
});

const pluralArticles = (n) => {
    const mod10 = n % 10;
    const mod100 = n % 100;
    if (mod10 === 1 && mod100 !== 11) return "статья";
    if (mod10 >= 2 && mod10 <= 4 && (mod100 < 12 || mod100 > 14)) return "статьи";
    return "статей";
};

const formatDate = (value) => {
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return "";

    return new Intl.DateTimeFormat("ru-RU", { day: "numeric", month: "short" }).format(date);
};
</script>
