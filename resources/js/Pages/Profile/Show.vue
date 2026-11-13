<template>
    <MainLayout>
        <Head :title="user.name" />

        <ProfileLayout :user="user" :stats="stats" :top-articles="topArticles">
            <h2 class="type-h2 text-content">{{ activeTab === "hidden" ? "Скрытые статьи" : "Мои статьи" }}</h2>

            <!-- Вкладки: скрытые посты отдельно, чтобы не смешивались с обычной лентой -->
            <nav class="mt-5 flex items-center gap-6 border-b border-line" aria-label="Разделы профиля">
                <Link
                    v-for="tab in tabs"
                    :key="tab.key"
                    :href="tab.href"
                    class="-mb-px border-b-2 pb-2 text-sm font-semibold transition-colors"
                    :class="tab.active ? 'border-accent text-content' : 'border-transparent text-muted hover:text-content'"
                    :aria-current="tab.active ? 'page' : undefined"
                >
                    {{ tab.label }} <span class="num font-normal text-muted">{{ tab.count }}</span>
                </Link>
            </nav>

            <!-- Та же лента, что на главной и у автора; действия автора — в меню «⋯» у просмотров -->
            <ul v-if="articles.data.length" class="feed-list mt-6">
                <li v-for="article in articles.data" :key="article.id">
                    <FeedPost :article="article" :categories="categories" :show-author="false">
                        <template #actions>
                            <ArticleActionsMenu :article="article" show-delete @delete="deleteArticle" />
                        </template>
                    </FeedPost>
                </li>
            </ul>

            <!-- Пустое состояние зависит от вкладки -->
            <div v-else class="mt-5 max-w-[46rem] border border-line bg-surface p-6 sm:p-8">
                <template v-if="activeTab === 'hidden'">
                    <p class="type-display-l text-content">Скрытых статей нет</p>
                    <p class="measure mt-4 text-muted">
                        Скрывайте посты из меню «⋯» в карточке — они останутся здесь и не попадут в ленты и поиск.
                    </p>
                </template>
                <template v-else>
                    <p class="type-display-l text-content">Пока нет статей</p>
                    <p class="measure mt-4 text-muted">Напишите первую статью — она появится здесь и в общей ленте блога.</p>
                    <Link
                        :href="route('create')"
                        class="mt-6 inline-flex items-center justify-center bg-accent px-5 py-2.5 text-sm font-semibold text-accent-contrast transition-colors hover:bg-accent-hover"
                    >
                        Написать статью
                    </Link>
                </template>
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
import ArticleActionsMenu from "@/Components/ArticleActionsMenu.vue";
import ProfileLayout from "@/Components/ProfileLayout.vue";
import Pagination from "@/Components/Pagination.vue";
import ConfirmDialog from "@/Components/ConfirmDialog.vue";

const props = defineProps({
    articles: {
        type: Object,
        required: true,
    },
    // Какая вкладка открыта: обычные посты или скрытые
    tab: {
        type: String,
        default: "articles",
    },
    counts: {
        type: Object,
        default: () => ({}),
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

const activeTab = computed(() => props.tab);

const tabs = computed(() => [
    {
        key: "articles",
        label: "Мои статьи",
        count: props.counts.articles ?? 0,
        href: route("profile"),
        active: props.tab !== "hidden",
    },
    {
        key: "hidden",
        label: "Скрытые",
        count: props.counts.hidden ?? 0,
        href: route("profile", { tab: "hidden" }),
        active: props.tab === "hidden",
    },
]);

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
