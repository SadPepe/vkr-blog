<template>
    <Layout>
        <h1 v-if="searchQuery" class="pt-2 pl-4 pr-2 font-bold text-gray-800 dark:text-stone-300 leading-tight text-4xl mb-9">
        Результаты поиска: {{ searchQuery }}
    </h1>
    
      
       <div
            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 md:px-6 sm:px-4"
        >
            <div
                class="bg-white dark:bg-[#252525] cursor-pointer shadow-md overflow-hidden hover:shadow-lg transit sm:rounded-lg"
                v-for="article in articles.data"
                :key="article.id"
            >
                <a :href="'/articles/' + article.slug" class="cursor-pointer">
                    <img
                        :src="article.card_image_path"
                        alt="Mountain"
                        class="w-full h-48 object-cover"
                        loading="lazy"
                        v-if="article.card_image_path"
                    />

                    <div class="">
                        <div class="pt-4 pl-4 pr-2 flex items-center">
                            <a href="#"
                                ><img
                                    class="w-10 h-10 rounded-full mr-4"
                                    src="/storage/avatars/altAvat.png"
                                    alt="Avatar of Jonathan Reinink"
                            /></a>
                            <div class="">
                                <a
                                    href="#"
                                    class="text-xl font-bold text-gray-800 dark:text-stone-300 leading-tight"
                                    >{{ article.user.name }}</a
                                >
                                <p class="text-sm text-gray-800 dark:text-stone-300">{{ formatDate(article.created_at) }}</p>
                            </div>
                        </div>
                        <h2
                            class="pt-2 pl-4 pr-2 text-xl font-bold text-gray-800 dark:text-stone-300 leading-tight"
                        >
                            {{ article.title }}
                        </h2>
                        <p
                            class="line-clamp-3 px-4 mb-4 text-stone-700 dark:text-gray-400 text-sm leading-relaxed"
                        >
                            {{ article.excerpt }}
                        </p>
                    </div>
                </a>
            </div>
        </div>

        <Pagination
            v-if="articles"
            :links="articles?.links"
            active-class="bg-red-500"
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

<style scoped></style>
