<script setup>
import { ref, onMounted, watch } from "vue";
import { usePage } from '@inertiajs/vue3';

const page = usePage();

const categories = ref([
    { id: 1, name: "Красота и стиль", slug: "beauty" },
    { id: 2, name: "Спорт", slug: "sport" },
    { id: 3, name: "Путешествия", slug: "travel" },
    { id: 4, name: "Еда", slug: "food" },
    { id: 5, name: "Наука", slug: "science" },
    { id: 6, name: "Технологии", slug: "tech" },
    { id: 7, name: "Искусство", slug: "art" },
    { id: 8, name: "Игры", slug: "games" },
]);

const activeCategory = ref(null);

const updateActiveCategory = () => {
    const path = window.location.pathname;
    
    if (path.includes('/categories/')) {
        const slug = path.split('/categories/')[1];
        activeCategory.value = slug;
    } else {
        activeCategory.value = 'all'; // Главная страница
    }
};

watch(() => page.url, updateActiveCategory);
onMounted(updateActiveCategory);
</script>

<template>
    <div class="absolute z-[-50] top-0 w-full">
        <div class="pt-[85px] border border-gray-100 dark:border-0 bg-white/80 dark:bg-[#252525] shadow backdrop-blur-lg xl:rounded-3xl">
            <div class="flex space-x-2 overflow-x-auto pb-4 scrollbar-hide category-carousel px-4">
                <!-- Все категории -->
                <a
                    href="/"
                    :class="{
                        'border-2 border-indigo-500 bg-indigo-50 text-indigo-700 dark:bg-indigo-900/20 dark:text-indigo-300 dark:border-indigo-400': 
                            activeCategory === 'all',
                        'border border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-stone-300 dark:hover:bg-gray-800': 
                            activeCategory !== 'all',
                    }"
                    class="inline-flex items-center flex-shrink-0 px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 whitespace-nowrap"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    Все категории
                </a>

                <!-- Категории -->
                <a
                    v-for="category in categories"
                    :key="category.id"
                    :href="`/categories/${category.slug}`"
                    :class="{
                        'border-2 border-indigo-500 bg-indigo-50 text-indigo-700 dark:bg-indigo-900/20 dark:text-indigo-300 dark:border-indigo-400': 
                            activeCategory === category.slug,
                        'border border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-stone-300 dark:hover:bg-gray-800': 
                            activeCategory !== category.slug,
                    }"
                    class="inline-flex items-center flex-shrink-0 px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 whitespace-nowrap"
                >
                    {{ category.name }}
                </a>
            </div>
        </div>
    </div>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}

.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

/* Плавные переходы */
.transition-all {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 300ms;
}
</style>
