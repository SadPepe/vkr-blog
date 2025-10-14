<!-- resources/js/Components/PopularArticles.vue -->
<template>
    <div class="bg-white dark:bg-[#252525] rounded-xl shadow-sm border border-gray-200 dark:border-stone-700 p-6">
        <h3 class="text-xl font-bold text-gray-800 dark:text-stone-300 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-orange-500" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
            Популярные статьи
        </h3>
        
        <div class="space-y-4">
            <article 
                v-for="(article, index) in articles" 
                :key="article.id"
                class="group cursor-pointer transition-all duration-300 hover:bg-gray-50 dark:hover:bg-stone-800 rounded-lg p-3 -mx-3"
            >
                <a :href="`/articles/${article.slug}`" class="flex items-start space-x-3">
                    <!-- Номер/медаль -->
                    <div class="flex-shrink-0 w-6 h-6 flex items-center justify-center rounded-full text-xs font-bold"
                         :class="getRankClass(index)">
                        {{ index + 1 }}
                    </div>
                    
                    <!-- Контент -->
                    <div class="flex-1 min-w-0">
                        <h4 class="text-sm font-semibold text-gray-800 dark:text-stone-300 leading-tight group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors line-clamp-2 mb-1">
                            {{ article.title }}
                        </h4>
                        
                        <!-- Статистика -->
                        <div class="flex items-center space-x-3 text-xs text-gray-500 dark:text-stone-500">
                            <span class="flex items-center space-x-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <span>{{ formatNumber(article.views_count) }}</span>
                            </span>
                            
                            <span class="flex items-center space-x-1">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                                </svg>
                                <span>{{ formatNumber(article.likes_count) }}</span>
                            </span>
                            
                            <span class="flex items-center space-x-1" v-if="article.comments_count">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                                <span>{{ formatNumber(article.comments_count) }}</span>
                            </span>
                        </div>
                    </div>
                </a>
            </article>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    articles: {
        type: Array,
        required: true,
        default: () => []
    }
});

const getRankClass = (index) => {
    switch (index) {
        case 0:
            return 'bg-gradient-to-r from-yellow-400 to-orange-400 text-white shadow-lg';
        case 1:
            return 'bg-gradient-to-r from-gray-300 to-gray-400 text-gray-800 shadow-md';
        case 2:
            return 'bg-gradient-to-r from-orange-300 to-amber-300 text-amber-900 shadow-sm';
        default:
            return 'bg-gray-100 dark:bg-stone-700 text-gray-600 dark:text-stone-400';
    }
};

const formatNumber = (num) => {
    if (num >= 1000) {
        return (num / 1000).toFixed(1) + 'k';
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