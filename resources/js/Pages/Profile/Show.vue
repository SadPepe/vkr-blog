<template>
    <MainLayout>
        <div class="w-full">
            <!--ОПИСАНИЕ-->
            <div
                class="mx-auto my-4 bg-white dark:bg-[#252525] shadow-md max-w-4xl w-full p-4 transition-all duration-300 animate-fade-in md:rounded-xl"
            >
                <div class="flex flex-col md:flex-row">
                    <div class="md:w-1/3 text-center mb-8 md:mb-0">
                        <img
                            src="/storage/avatars/altAvat.png"
                            alt="Profile Picture"
                            class="rounded-full w-48 h-48 mx-auto mb-4 transition-transform duration-300 hover:scale-105"
                        />
                        <h1 class="text-2xl font-bold text-[#333] dark:text-stone-300">
                            {{ user.name }}
                        </h1>
                    </div>
                    <div class="md:w-2/3 md:pl-8">
                        <h2 class="text-xl font-semibold text-[#333] dark:text-stone-300 mb-0">
                            Обо мне
                        </h2>
                        <p class="text-[#333] font-normal dark:text-stone-300 pb-4 sm:pb-2">
                            {{ user.about }}
                        </p>
                    </div>
                </div>
                <div class="flex flex-wrap space-x-4 justify-end">
                    <button
                        class="mt-2 mb-2 bg-gray-100 hover:bg-gray-200 dark:bg-[#303030] dark:text-stone-300 dark:hover:bg-[#404040] relative flex-shrink-0 px-4 py-2 rounded-full text-sm font-medium transition-colors"
                    >
                        <a href="/profile/edit">Редактировать профиль</a>
                    </button>
                    <button
                        class="mt-2 mb-2 bg-gray-100 hover:bg-gray-200 dark:bg-[#303030] dark:text-stone-300 dark:hover:bg-[#404040] relative flex-shrink-0 px-4 py-2 rounded-full text-sm font-medium transition-colors"
                    >
                        <a :href="route('create')">новая статья</a>
                    </button>
                </div>
            </div>
        </div>

        <!--СТАТЬИ-->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 md:px-6 sm:px-4">
            <div
                class="flex flex-col h-full bg-white dark:bg-[#252525] shadow-md overflow-hidden hover:shadow-lg transit sm:rounded-lg"
                v-for="article in articles"
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
                </a>
                <div class="flex-grow">
                    <h3
                        class="m-0 pt-4 pl-4 pr-2 text-xl font-bold text-gray-800 dark:text-stone-300 leading-tight"
                    >
                        {{ article.title }}
                    </h3>
                    <p
                        class="line-clamp-3 px-4 mb-2 text-gray-500 dark:text-gray-400 text-sm leading-relaxed"
                    >
                        {{ article.excerpt }}
                    </p>
                    </div>
                    <div
                        class="p-2 border-t border-gray-100 flex justify-end space-x-3"
                        v-if="$page.props.auth.user.id === article.user.id"
                    >
                        <a
                            :href="
                                route('articles.edit', {
                                    slug: article.slug,
                                })
                            "
                            class="inline-flex items-center font-semibold text-sky-600 m-0 p-0 hover:text-neutral-600"
                            title="read more"
                        >
                            Редактировать
                        </a>
                        <button
                            @click="confirmDelete(article.slug)"
                            class="inline-flex items-center font-semibold text-red-600 m-0 p-0 hover:text-neutral-600"
                            title="read more"
                        >
                            Удалить
                        </button>
                    </div>
              
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import MainLayout from "@/Layouts/Layout.vue";
import { Link, router } from "@inertiajs/vue3";
import { confirm } from "@/Utilities/confirm";

defineProps({
    articles: Array,
    user: {
        type: Object,
        required: true,
    },
});

const confirmDelete = (slug) => {
    if (confirm("Вы уверены? Это действие нельзя отменить")) {
        router.delete(route("articles.destroy", { slug: slug }));
    }
};
</script>
