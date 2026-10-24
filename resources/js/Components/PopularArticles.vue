<!-- resources/js/Components/PopularArticles.vue -->
<template>
    <div class="bg-[#FAF8F4] dark:bg-[#252525] rounded-md shadow-sm border border-gray-200/60 dark:border-stone-700 p-6">
        <h3 class="text-xl font-bold text-gray-900 dark:text-stone-100 mb-2 flex items-center">
            <svg class="w-4 h-4 mr-2 text-gray-400 dark:text-stone-500" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
            {{ title }}
        </h3>
        <p v-if="subtitle" class="mb-4 -mt-1 text-sm text-gray-500 dark:text-stone-500">
            {{ subtitle }}
        </p>
        
        <div class="space-y-4">
            <article 
                v-for="(article, index) in articles" 
                :key="article.id"
                class="group cursor-pointer transition-all duration-300 hover:bg-gray-50 dark:hover:bg-stone-800 rounded p-3 -mx-3"
            >
                <a :href="`/articles/${article.slug}`" class="flex items-start space-x-3">
                    <!-- Номер/медаль -->
                    <div class="flex-shrink-0 w-6 h-6 flex items-center justify-center rounded-full text-xs font-bold"
                         :class="getRankClass(index)">
                        {{ index + 1 }}
                    </div>
                    
                    <!-- Контент -->
                    <div class="flex-1 min-w-0">
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-stone-100 leading-tight group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors line-clamp-2 mb-1">
                            {{ article.title }}
                        </h4>
                        
                        <!-- Статистика -->
                        <div class="flex items-center space-x-3 text-xs text-gray-500 dark:text-stone-500">
                            <span class="flex items-center space-x-1">
                                <svg class="w-3 h-3 text-gray-400 dark:text-stone-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <span>{{ formatNumber(article.views_count) }}</span>
                            </span>
                            
                            <span class="flex items-center space-x-1">
                                <svg class="w-3 h-3 text-gray-400 dark:text-stone-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                </svg>
                                <span>{{ formatNumber(article.likes_count) }}</span>
                            </span>
                            
                            <span class="flex items-center space-x-1" v-if="article.comments_count">
                                <svg class="w-3 h-3 text-gray-400 dark:text-stone-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
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
    },
    title: {
        type: String,
        default: 'Популярные статьи'
    },
    subtitle: {
        type: String,
        default: ''
    }
});

const getRankClass = (index) => {
    switch (index) {
        case 0:
            return 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-sm';
        case 1:
            return 'bg-gray-200 text-gray-700 dark:bg-stone-600 dark:text-stone-200';
        case 2:
            return 'bg-gray-300/80 text-gray-700 dark:bg-stone-600 dark:text-stone-200';
        default:
            return 'bg-gray-100 text-gray-500 dark:bg-stone-700 dark:text-stone-400';
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