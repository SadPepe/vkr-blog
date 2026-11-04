<template>
    <MainLayout>
        <Head :title="user.name" />

        <ProfileLayout :user="user" :stats="stats" :media="media">
            <template #actions>
                <Link
                    :href="route('profile.edit')"
                    class="inline-flex items-center justify-center rounded-md border border-line-strong px-4 py-2 text-sm font-medium text-content transition-colors hover:bg-surface-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2 focus-visible:ring-offset-surface"
                >
                    Редактировать профиль
                </Link>
                <Link
                    :href="route('create')"
                    class="inline-flex items-center justify-center rounded-md bg-accent px-4 py-2 text-sm font-semibold text-accent-contrast shadow-sm transition-colors hover:bg-accent-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2 focus-visible:ring-offset-surface"
                >
                    Новая статья
                </Link>
            </template>

            <div v-if="articles.data.length" class="divide-y divide-line overflow-hidden rounded-md border border-line bg-surface px-5 sm:px-6">
                <ArticleCard v-for="article in articles.data" :key="article.id" :article="article" :categories="categories">
                    <template #actions>
                        <Link
                            :href="route('articles.edit', { article: article.slug })"
                            class="rounded-md border border-line bg-surface p-2 text-muted shadow-sm transition-colors hover:bg-surface-muted hover:text-accent focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent"
                            title="Редактировать"
                            aria-label="Редактировать статью"
                        >
                            <svg
                                class="h-4 w-4"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.5"
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"
                                />
                            </svg>
                        </Link>
                        <button
                            type="button"
                            class="rounded-md border border-line bg-surface p-2 text-muted shadow-sm transition-colors hover:bg-surface-muted hover:text-red-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500 dark:hover:text-red-400"
                            title="Удалить"
                            aria-label="Удалить статью"
                            @click="deleteArticle(article)"
                        >
                            <svg
                                class="h-4 w-4"
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
                </ArticleCard>
            </div>

            <!-- Пустое состояние -->
            <div v-else class="rounded-md border border-dashed border-line-strong bg-surface px-6 py-14 text-center">
                <svg
                    class="mx-auto mb-4 h-12 w-12 text-muted"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.5"
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487z"
                    />
                </svg>
                <h3 class="text-lg font-semibold text-content">Пока нет статей</h3>
                <p class="mx-auto mt-1.5 max-w-sm text-sm text-muted">Напишите первую статью — она появится здесь и в общей ленте блога.</p>
                <Link
                    :href="route('create')"
                    class="mt-5 inline-flex items-center justify-center rounded-md bg-accent px-5 py-2.5 text-sm font-semibold text-accent-contrast shadow-sm transition-colors hover:bg-accent-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2 focus-visible:ring-offset-surface"
                >
                    Написать статью
                </Link>
            </div>

            <Pagination v-if="articles.links.length > 3" :links="articles.links" class="mt-8" />
        </ProfileLayout>

        <ConfirmDialog
            :show="articleToDelete !== null"
            title="Удалить статью?"
            :text="articleToDelete ? `«${articleToDelete.title}» и комментарии к ней будут удалены безвозвратно.` : ''"
            @confirm="confirmDeleteArticle"
            @cancel="articleToDelete = null"
        />
    </MainLayout>
</template>

<script setup>
import { computed, ref } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import MainLayout from "@/Layouts/Layout.vue";
import ProfileLayout from "@/Components/ProfileLayout.vue";
import ArticleCard from "@/Components/ArticleCard.vue";
import Pagination from "@/Components/Pagination.vue";
import ConfirmDialog from "@/Components/ConfirmDialog.vue";

const props = defineProps({
    articles: {
        type: Object,
        required: true,
    },
    media: {
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
