<template>
    <MainLayout>
        <div class="max-w-3xl mx-auto relative ">

            <div class="max-w-2xl mx-auto">


                <div class="article-container px-2 md:px-0">
                    <ArticleStats :article="article" :views-count="article.views_count"
                        :initial-liked="article.is_liked" :initial-likes-count="article.likes_count"
                        :comments-count="article.comments.length" class="mb-6" />

                    <p class="text-gray-800 dark:text-stone-300 text-lg mt-6">Автор:
                        <a href="#"
                            class="text-indigo-600 dark:text-indigo-400 font-medium hover:text-gray-900 transition duration-500 ease-in-out">
                            {{ article.user.name }}
                        </a>
                    </p>
                    <h1 href="#" class="font-bold text-gray-800 dark:text-stone-300 leading-tight mt-0  mb-2">
                        {{ article.title }}</h1>
                    <hr class="mt-4">

                    <img :src="article.card_image_path" alt="Mountain"
                        class="w-full h-full max-h-[360px] object-cover mb-4" loading="lazy"
                        v-if="article.card_image_path" />


                    <div v-html="article.content"></div>


                </div>

                <CommentSection :comments="article.comments" :article="article" />


            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import MainLayout from '@/Layouts/Layout.vue'
import CommentSection from '@/Components/Comments/CommentSection.vue';
import ArticleStats from '@/Components/ArticleStats.vue';

defineProps({
    article: {
        type: Object,
        required: true
    }
});
</script>

<style>
ul,
ol {
    padding: 0 1rem;
    margin: 1.25rem 1rem 1.25rem 0.4rem;
    color: rgb(231 229 228 / var(--tw-text-opacity, 1));

    li p {
        margin-top: 0.25em;
        margin-bottom: 0.25em;
    }
}

p {
    line-height: 1.7;
    margin: 0.4rem 0;
}

ol {
    list-style-type: decimal;
}

ul {
    list-style-type: disc;
}

hr {
    border: none;
    border-top: 1px solid gray;
    cursor: pointer;
    margin: 2rem 0;

    &.ProseMirror-selectednode {
        border-top: 2px solid rgb(173, 0, 173);
    }
}

blockquote {
    border-left: 3px solid rgb(179, 179, 179);
    margin: 1.5rem 0;
    padding-left: 1rem;
}

h1,
h2 {
    font-weight: 600;
    margin-top: 2rem;
    margin-bottom: 1rem;
}

h1 {
    text-wrap: pretty;
    font-size: 2.1rem;
    /* 40px */
    font-weight: 700;
    /* Bold */
    line-height: 1.2;
    /* 48px */
    margin-bottom: 1.5rem;
    color: rgb(231 229 228 / var(--tw-text-opacity, 1));
}

h2 {
    text-wrap: pretty;
    font-size: 1.875rem;
    /* 30px */
    font-weight: 600;
    /* Semi-bold */
    margin: 0.8rem 0 1rem;
    color: rgb(231 229 228 / var(--tw-text-opacity, 1));
}

h3 {
    text-wrap: pretty;
    font-size: 1.5rem;
    /* 24px */
    font-weight: 600;
    /* Semi-bold */
    margin: 0.8rem 0 1rem;
    color: rgb(231 229 228 / var(--tw-text-opacity, 1));
}




h4,
h5,
h6 {
    font-size: 1rem;
}
</style>