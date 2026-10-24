<template>
    <Layout>
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- Профиль автора -->
            <div class="mb-10 overflow-hidden rounded-2xl border border-gray-200 bg-[#FAF8F4] shadow-md dark:border-white/5 dark:bg-[#252525]">
                <div class="h-32 bg-gradient-to-r from-indigo-600 via-blue-600 to-purple-600"></div>
                <div class="flex flex-col gap-6 p-6 sm:flex-row sm:items-end sm:gap-8 sm:p-8">
                    <div class="-mt-20 flex-shrink-0">
                        <div class="rounded-full ring-4 ring-white dark:ring-[#252525]">
                            <UserAvatar :user="author" :size="112" />
                        </div>
                    </div>

                    <div class="min-w-0 flex-1">
                        <h1 class="text-2xl font-bold text-gray-800 dark:text-stone-300">
                            {{ author.name }}
                        </h1>
                        <p class="mt-1 text-sm text-gray-500 dark:text-stone-500">
                            На блоге {{ formatDate(author.created_at) }}
                        </p>
                        <p v-if="author.about" class="mt-3 max-w-2xl text-gray-600 dark:text-stone-400">
                            {{ author.about }}
                        </p>
                    </div>

                    <!-- Статистика -->
                    <div class="grid grid-cols-2 gap-x-8 gap-y-3 sm:flex sm:items-center sm:gap-8">
                        <div>
                            <p class="text-2xl font-bold text-gray-800 dark:text-stone-300">{{ stats.articles_count }}</p>
                            <p class="text-xs text-gray-500 dark:text-stone-500">{{ plural(stats.articles_count, ['статья', 'статьи', 'статей']) }}</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-800 dark:text-stone-300">{{ stats.views_count }}</p>
                            <p class="text-xs text-gray-500 dark:text-stone-500">{{ plural(stats.views_count, ['просмотр', 'просмотра', 'просмотров']) }}</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-800 dark:text-stone-300">{{ stats.likes_count }}</p>
                            <p class="text-xs text-gray-500 dark:text-stone-500">{{ plural(stats.likes_count, ['лайк', 'лайка', 'лайков']) }}</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-800 dark:text-stone-300">{{ stats.comments_count }}</p>
                            <p class="text-xs text-gray-500 dark:text-stone-500">{{ plural(stats.comments_count, ['комментарий', 'комментария', 'комментариев']) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Статьи автора -->
            <h2 class="mb-6 text-xl font-bold text-gray-800 dark:text-stone-300">
                Статьи автора
            </h2>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                <ArticleCard
                    v-for="article in articles.data"
                    :key="article.id"
                    :article="article"
                    :categories="categories"
                />
            </div>

            <!-- Пустое состояние -->
            <div v-if="!articles.data.length" class="py-16 text-center">
                <svg class="mx-auto mb-4 h-16 w-16 text-gray-400 dark:text-stone-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="mb-2 text-xl font-semibold text-gray-600 dark:text-stone-400">
                    У автора пока нет статей
                </h3>
                <p class="text-gray-500 dark:text-stone-500">
                    Загляните позже — здесь появятся новые публикации.
                </p>
            </div>

            <Pagination
                v-if="articles && articles.links && articles.links.length > 3"
                :links="articles.links"
                class="mt-8"
            />
        </div>
    </Layout>
</template>

<script setup>
import { computed, onMounted } from "vue";
import { usePage } from "@inertiajs/vue3";
import Layout from "@/Layouts/Layout.vue";
import ArticleCard from "@/Components/ArticleCard.vue";
import Pagination from "@/Components/Pagination.vue";
import UserAvatar from "@/Components/UserAvatar.vue";

const page = usePage();

const props = defineProps({
    author: {
        type: Object,
        required: true,
    },
    articles: {
        type: Object,
        required: true,
    },
    stats: {
        type: Object,
        required: true,
    },
});

const categories = computed(() => page.props.categories ?? []);

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString("ru-RU", {
        year: "numeric",
        month: "long",
    });
};

const plural = (count, [one, few, many]) => {
    const mod10 = count % 10;
    const mod100 = count % 100;
    if (mod10 === 1 && mod100 !== 11) return one;
    if (mod10 >= 2 && mod10 <= 4 && (mod100 < 12 || mod100 > 14)) return few;
    return many;
};

onMounted(() => {
    document.title = `${props.author.name} — автор блога`;
});
</script>