<template>
    <MainLayout>
        <Head :title="user.name" />

        <ProfileLayout :user="user" :stats="stats" :top-articles="topArticles">
            <h2 class="type-h2 text-content">Мои статьи</h2>

            <!-- Та же лента, что на главной и у автора; свои посты можно править и удалять -->
            <ul v-if="articles.data.length" class="mt-5 flex flex-col gap-[var(--layout-feed-gap-y)]">
                <li v-for="article in articles.data" :key="article.id">
                    <FeedPost :article="article" :categories="categories" :show-author="false">
                        <template #actions>
                            <Link
                                :href="route('articles.edit', { article: article.slug })"
                                class="inline-flex h-9 w-9 items-center justify-center text-muted transition-colors hover:text-accent focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent"
                                title="Редактировать"
                                :aria-label="`Редактировать: ${article.slug}`"
                            >
                                <svg
                                    class="h-[1.125rem] w-[1.125rem]"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125"
                                    />
                                </svg>
                            </Link>
                            <button
                                type="button"
                                class="inline-flex h-9 w-9 items-center justify-center text-muted transition-colors hover:text-accent focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent"
                                title="Удалить"
                                aria-label="Удалить статью"
                                @click="deleteArticle(article)"
                            >
                                <svg
                                    class="h-[1.125rem] w-[1.125rem]"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                    />
                                </svg>
                            </button>
                        </template>
                    </FeedPost>
                </li>
            </ul>

            <!-- Пустое состояние: как у автора, без карточки -->
            <div v-else class="mt-5 max-w-[46rem] border border-line bg-surface p-6 sm:p-8">
                <p class="type-display-l text-content">Пока нет статей</p>
                <p class="measure mt-4 text-muted">Напишите первую статью — она появится здесь и в общей ленте блога.</p>
                <Link
                    :href="route('create')"
                    class="mt-6 inline-flex items-center justify-center bg-accent px-5 py-2.5 text-sm font-semibold text-accent-contrast transition-colors hover:bg-accent-hover"
                >
                    Написать статью
                </Link>
            </div>

            <Pagination v-if="articles.links.length > 3" :links="articles.links" class="mt-10" />
        </ProfileLayout>

        <ConfirmDialog
            :show="articleToDelete !== null"
            title="Удалить статью?"
            :text="
                articleToDelete ? `«${articleToDelete.title || articleToDelete.slug}» и комментарии к ней будут удалены безвозвратно.` : ''
            "
            @confirm="confirmDeleteArticle"
            @cancel="articleToDelete = null"
        />
    </MainLayout>
</template>

<script setup>
import { computed, ref } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import MainLayout from "@/Layouts/Layout.vue";
import FeedPost from "@/Components/FeedPost.vue";
import ProfileLayout from "@/Components/ProfileLayout.vue";
import Pagination from "@/Components/Pagination.vue";
import ConfirmDialog from "@/Components/ConfirmDialog.vue";

const props = defineProps({
    articles: {
        type: Object,
        required: true,
    },
    topArticles: {
        type: Array,
        default: () => [],
    },
    user: {
        type: Object,
        required: true,
    },
    stats: {
        type: Object,
        default: () => ({}),
    },
});

const page = usePage();

const categories = computed(() => page.props.categories ?? []);
const articleToDelete = ref(null);

const deleteArticle = (article) => {
    articleToDelete.value = article;
};

const confirmDeleteArticle = () => {
    const article = articleToDelete.value;
    articleToDelete.value = null;

    if (!article) return;

    router.delete(route("articles.destroy", { article: article.slug }), {
        preserveScroll: true,
        onError: () => {},
    });
};
</script>
