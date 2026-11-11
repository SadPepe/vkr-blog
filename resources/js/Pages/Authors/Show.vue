<template>
    <Layout>
        <Head :title="author.name">
            <meta name="description" :content="metaDescription" />
            <meta property="og:title" :content="author.name" />
            <meta property="og:description" :content="metaDescription" />
            <meta property="og:type" content="profile" />
            <meta property="og:url" :content="route('authors.show', author.id)" />
            <meta v-if="author.cover_url" property="og:image" :content="author.cover_url" />
            <meta name="twitter:card" :content="author.cover_url ? 'summary_large_image' : 'summary'" />
            <link rel="canonical" :href="route('authors.show', author.id)" />
        </Head>

        <ProfileLayout :user="author" :stats="stats" :top-articles="topArticles">
            <h2 class="type-h2 text-content">Статьи</h2>

            <!-- Та же лента, что на главной: тело поста целиком, «Развернуть», счётчики -->
            <ul v-if="articles.data.length" class="mt-5 flex flex-col gap-[var(--layout-feed-gap-y)]">
                <li v-for="article in articles.data" :key="article.id">
                    <FeedPost :article="article" :categories="categories" :show-author="false" />
                </li>
            </ul>

            <!-- Пустое состояние: крупный текст и действие -->
            <div v-else class="mt-5 max-w-[40rem] border border-line bg-surface p-6 sm:p-8">
                <p class="type-display-l text-content">У автора пока нет статей</p>
                <p class="measure mt-4 text-muted">Загляните позже — здесь появятся новые публикации.</p>
            </div>

            <Pagination v-if="articles && articles.links && articles.links.length > 3" :links="articles.links" class="mt-10" />
        </ProfileLayout>
    </Layout>
</template>

<script setup>
import { computed } from "vue";
import { Head, usePage } from "@inertiajs/vue3";
import Layout from "@/Layouts/Layout.vue";
import FeedPost from "@/Components/FeedPost.vue";
import ProfileLayout from "@/Components/ProfileLayout.vue";
import Pagination from "@/Components/Pagination.vue";

const props = defineProps({
    author: {
        type: Object,
        required: true,
    },
    topArticles: {
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

const authorDescription = computed(
    () => (props.author.about || "").trim() || `Статьи автора ${props.author.name} на сайте «Челны подземка».`,
);

const metaDescription = computed(() => authorDescription.value.slice(0, 160));
</script>
