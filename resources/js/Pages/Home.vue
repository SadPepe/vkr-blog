<!-- resources/js/Pages/Home.vue -->
<template>
    <Layout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                <!-- Основной контент -->
                <div class="lg:col-span-3">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Карточки статей -->
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
                                                <span>{{ article.views_count }}</span>
                                            </span>
                                            
                                            <!-- Лайки -->
                                            <span class="flex items-center space-x-1">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                                                </svg>
                                                <span>{{ article.likes_count }}</span>
                                            </span>

                                            <!-- Комментарии -->
                                            <span class="flex items-center space-x-1" v-if="article.comments_count">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                                </svg>
                                                <span>{{ article.comments_count }}</span>
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

                    <!-- Пагинация -->
                    <Pagination
                        v-if="articles && articles.links && articles.links.length > 3"
                        :links="articles.links"
                        class="mt-8"
                    />

                    <!-- Сообщение если нет статей -->
                    <div 
                        v-if="articles.data.length === 0" 
                        class="text-center py-12"
                    >
                        <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <h3 class="text-xl font-semibold text-gray-600 dark:text-stone-400 mb-2">
                            Статьи не найдены
                        </h3>
                        <p class="text-gray-500 dark:text-stone-500">
                            Попробуйте изменить параметры поиска или зайти позже.
                        </p>
                    </div>
                </div>

                <!-- Сайдбар -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Популярные статьи -->
                    <PopularArticles 
                        v-if="popularArticles && popularArticles.length > 0"
                        :articles="popularArticles" 
                    />

                    <!-- Виджет призыва к действию -->
                    <div class="bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
                        <h3 class="text-xl font-bold mb-3">Начните писать</h3>
                        <p class="text-blue-100 text-sm mb-4">
                            Поделитесь своими знаниями и опытом с сообществом
                        </p>
                        <a 
                            v-if="$page.props.auth.user"
                            :href="route('create')"
                            class="block w-full bg-white text-blue-600 text-center py-2 px-4 rounded-lg font-semibold hover:bg-blue-50 transition-colors"
                        >
                            Новая статья
                        </a>
                        <div v-else class="space-y-3">
                            <a 
                                :href="route('register')"
                                class="block w-full bg-white text-blue-600 text-center py-2 px-4 rounded-lg font-semibold hover:bg-blue-50 transition-colors"
                            >
                                Зарегистрироваться
                            </a>
                            <a 
                                :href="route('login')"
                                class="block w-full bg-transparent border border-white text-white text-center py-2 px-4 rounded-lg font-semibold hover:bg-white hover:bg-opacity-10 transition-colors"
                            >
                                Войти
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>

<script setup>
import Layout from "@/Layouts/Layout.vue";
import Pagination from "@/Components/Pagination.vue";
import PopularArticles from "@/Components/PopularArticles.vue";

const props = defineProps({
    articles: {
        type: Object,
        required: true
    },
    popularArticles: {
        type: Array,
        default: () => []
    },
    categories: {
        type: Array,
        default: () => []
    },
    activeCategory: {
        type: String,
        default: null
    }
});

const formatDate = (dateString) => {
    const date = new Date(dateString);
    const now = new Date();
    const diffTime = Math.abs(now - date);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    
    if (diffDays === 1) {
        return 'Сегодня';
    } else if (diffDays === 2) {
        return 'Вчера';
    } else if (diffDays <= 7) {
        return `${diffDays} дней назад`;
    } else {
        return date.toLocaleDateString('ru-RU', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    }
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

.transit {
    transition: all 0.3s ease;
}

/* Стили для темной темы */
:deep(.prose) {
    max-width: none !important;
}

:deep(.prose h1) {
    color: rgb(31 41 55 / var(--tw-text-opacity, 1));
}

.dark :deep(.prose h1) {
    color: rgb(231 229 228 / var(--tw-text-opacity, 1));
}

:deep(.prose p) {
    color: rgb(75 85 99 / var(--tw-text-opacity, 1));
}

.dark :deep(.prose p) {
    color: rgb(168 162 158 / var(--tw-text-opacity, 1));
}
</style>