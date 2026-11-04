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

        <ProfileLayout :user="author" :stats="stats" :media="media">
            <h2 class="type-h2 text-content">Статьи</h2>

            <!-- Лента постов: строки с заголовком и метой, без карточек -->
            <ul v-if="articles.data.length" class="mt-8 flex flex-col">
                <li v-for="article in articles.data" :key="article.id" class="py-8 first:pt-0">
                    <h3 class="font-display text-[1.75rem] font-bold leading-[1.15] tracking-[-0.02em] text-content">
                        <Link :href="route('articles.show', article.slug)" class="transition-colors hover:text-accent">
                            {{ article.title || trimExcerpt(article.excerpt) }}
                        </Link>
                    </h3>
                    <p class="type-meta mt-2.5">
                        <time :datetime="article.created_at">{{ formatDate(article.created_at) }}</time>
                        <span aria-hidden="true"> · </span>
                        <span class="num">{{ article.views_count }}</span> просмотров
                    </p>
                    <p v-if="article.excerpt" class="measure mt-3 line-clamp-2 text-content">
                        {{ trimExcerpt(article.excerpt) }}
                    </p>
                </li>
            </ul>

            <!-- Пустое состояние: крупный текст и действие -->
            <div v-else class="mt-10 max-w-[40rem]">
                <p class="type-display-l text-content">У автора пока нет статей</p>
                <p class="measure mt-4 text-muted">Загляните позже — здесь появятся новые публикации.</p>
            </div>

            <Pagination v-if="articles && articles.links && articles.links.length > 3" :links="articles.links" />
        </ProfileLayout>
    </Layout>
</template>

<script setup>
import { computed } from "vue";
import { Head, usePage } from "@inertiajs/vue3";
import Layout from "@/Layouts/Layout.vue";
import ProfileLayout from "@/Components/ProfileLayout.vue";
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

const metaDescription = computed(() => authorDescription.value.slice(0, 160));

const formatDate = (value) =>
    new Intl.DateTimeFormat("ru-RU", { day: "numeric", month: "long", year: "numeric" }).format(new Date(value)).replace(/ /g, "\u00A0");

const trimExcerpt = (text) => {
    const value = (text ?? "").trim();
    if (!value) return "";
    return /[.!?…»)]$/.test(value) ? value : `${value}…`;
};
</script>
