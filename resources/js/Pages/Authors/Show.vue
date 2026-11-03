<template>
    <Layout>
        <Head :title="author.name">
            <meta name="description" :content="authorDescription" />
            <meta property="og:title" :content="author.name" />
            <meta property="og:description" :content="authorDescription" />
            <meta property="og:type" content="profile" />
        </Head>

        <ProfileLayout :user="author" :stats="stats" :media="media">
            <div v-if="articles.data.length" class="flex flex-col gap-5">
                <ArticleCard v-for="article in articles.data" :key="article.id" :article="article" :categories="categories" />
            </div>

            <!-- Пустое состояние -->
            <div v-else class="rounded-md border border-dashed border-line-strong bg-surface px-6 py-14 text-center">
                <svg class="mx-auto mb-4 h-12 w-12 text-muted" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                    />
                </svg>
                <h3 class="text-lg font-semibold text-content">У автора пока нет статей</h3>
                <p class="mx-auto mt-1.5 max-w-sm text-sm text-muted">Загляните позже — здесь появятся новые публикации.</p>
            </div>

            <Pagination v-if="articles && articles.links && articles.links.length > 3" :links="articles.links" class="mt-8" />
        </ProfileLayout>
    </Layout>
</template>

<script setup>
import { computed } from "vue";
import { Head, usePage } from "@inertiajs/vue3";
import Layout from "@/Layouts/Layout.vue";
import ProfileLayout from "@/Components/ProfileLayout.vue";
import ArticleCard from "@/Components/ArticleCard.vue";
import Pagination from "@/Components/Pagination.vue";

const props = defineProps({
    author: {
        type: Object,
        required: true,
    },
    media: {
        type: Array,
        default: () => [],
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

const page = usePage();

const categories = computed(() => page.props.categories ?? []);

const authorDescription = computed(() => (props.author.about || "").trim() || `Статьи автора ${props.author.name} на МОЙ.БЛОГ.`);
</script>
