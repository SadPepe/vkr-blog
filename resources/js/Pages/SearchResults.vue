<!-- resources/js/Pages/SearchResults.vue -->
<template>
    <Layout>
        <h1 v-if="searchQuery" class="pt-2 pl-4 pr-2 font-bold text-gray-800 dark:text-stone-300 leading-tight text-4xl mb-9">
            Результаты поиска: {{ searchQuery }}
        </h1>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 md:px-6 sm:px-4">
            <div
                class="bg-white dark:bg-[#252525] cursor-pointer shadow-md overflow-hidden hover:shadow-lg transit sm:rounded-lg"
                v-for="article in articles.data"
                :key="article.id"
            >
                <a :href="'/articles/' + article.slug" class="cursor-pointer block">
                    <img
                        :src="article.card_image_path"
                        alt="Изображение статьи"
                        class="w-full h-48 object-cover"
                        loading="lazy"
                        v-if="article.card_image_path"
                    />

                    <div class="p-4">
                        <!-- Статистика в карточке -->
                        <div class="flex items-center justify-between text-sm text-gray-500 dark:text-stone-400 mb-3">
                            <div class="flex items-center space-x-4">
                                <!-- Просмотры -->
                                <span class="flex items-center space-x-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <span>{{ article.views_count || 0 }}</span>
                                </span>
                                
                                <!-- Лайки -->
                                <span class="flex items-center space-x-1">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>{{ article.likes_count || 0 }}</span>
                                </span>
                            </div>
                        </div>

                        <!-- Автор и дата -->
                        <div class="flex items-center mb-3">
                            <img
                                class="w-8 h-8 rounded-full mr-3"
                                src="/storage/avatars/altAvat.png"
                                :alt="article.user.name"
                            />
                            <div>
                                <p class="text-sm font-medium text-gray-800 dark:text-stone-300">
                                    {{ article.user.name }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-stone-500">
                                    {{ formatDate(article.created_at) }}
                                </p>
                            </div>
                        </div>

                        <!-- Заголовок и описание -->
                        <h2 class="text-lg font-bold text-gray-800 dark:text-stone-300 leading-tight mb-2 line-clamp-2">
                            {{ article.title }}
                        </h2>
                        <p class="text-stone-600 dark:text-gray-400 text-sm leading-relaxed line-clamp-3">
                            {{ article.excerpt }}
                        </p>
                    </div>
                </a>
            </div>
        </div>

        <Pagination
            v-if="articles"
            :links="articles?.links"
        />
    </Layout>
</template>

<script setup>
import Layout from "@/Layouts/Layout.vue";
import Pagination from "@/Components/Pagination.vue";

const props = defineProps({
    articles: Object,
    searchQuery: String 
});

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('ru-RU', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};
</script>