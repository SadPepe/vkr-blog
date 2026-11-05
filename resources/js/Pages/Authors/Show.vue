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

            <!-- Лента постов: заголовок, мета, выдержка и превью, без карточек -->
            <ul v-if="articles.data.length" class="mt-8 flex flex-col">
                <li v-for="article in articles.data" :key="article.id" class="py-6 first:pt-0">
                    <div class="flex gap-5 sm:gap-6" :class="postTitle(article) ? 'items-start' : 'items-center'">
                        <div class="min-w-0 flex-1">
                            <h3
                                v-if="postTitle(article)"
                                class="font-display text-[1.25rem] font-bold leading-snug tracking-[-0.02em] text-content sm:text-[1.75rem] sm:leading-[1.15]"
                            >
                                <Link :href="route('articles.show', article.slug)" class="transition-colors hover:text-accent">
                                    {{ postTitle(article) }}
                                </Link>
                            </h3>
                            <p class="flex flex-wrap items-baseline gap-x-1.5 text-[0.9375rem] text-muted mt-2.5">
                                <time :datetime="article.created_at" class="whitespace-nowrap">{{ formatDate(article.created_at) }}</time>
                                <span aria-hidden="true"> · </span>
                                <span class="num">{{ article.views_count }}</span> просмотров
                            </p>
                            <p v-if="article.title && article.excerpt" class="measure mt-3 line-clamp-2 text-content">
                                {{ trimExcerpt(article.excerpt) }}
                            </p>
                        </div>
                        <Link
                            v-if="article.card_image_path"
                            :href="route('articles.show', article.slug)"
                            class="block shrink-0 bg-surface-muted"
                            :class="{ 'order-first': !postTitle(article) }"
                        >
                            <img
                                :src="article.card_image_path"
                                :alt="article.title || 'Изображение поста'"
                                width="352"
                                height="264"
                                :class="
                                    postTitle(article) ? 'aspect-[4/3] w-32 object-cover sm:w-44' : 'aspect-[4/3] w-24 object-cover sm:w-72'
                                "
                                loading="lazy"
                            />
                        </Link>
                    </div>
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
import { Head, Link, usePage } from "@inertiajs/vue3";
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

const postTitle = (article) => {
    const title = (article.title ?? "").trim();
    if (title) return title;
    const words = (article.excerpt ?? "").trim().split(/\s+/).filter(Boolean);
    return words.slice(0, 8).join(" ") + (words.length > 8 ? "…" : "");
};

const trimExcerpt = (text) => {
    const value = (text ?? "").trim();
    if (!value) return "";
    return /[.!?…»)]$/.test(value) ? value : `${value}…`;
};
</script>
