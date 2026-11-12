<template>
    <Layout>
        <Head title="Авторы">
            <meta name="description" content="Авторы «Челны подземка»: найдите интересных людей и их публикации." />
        </Head>

        <div class="page-shell pb-24 pt-10 lg:pt-14">
            <div class="mx-auto max-w-[74rem]">
                <header class="border-b border-line pb-5">
                    <h1 class="type-display-l text-content">Авторы</h1>
                    <p class="mt-2 max-w-[52ch] text-[1.125rem] leading-snug text-content">
                        Люди, которые пишут для «Челны подземка». Найдите автора по имени или посмотрите, кто публиковался недавно.
                    </p>
                </header>

                <form class="mt-6 flex flex-col gap-3 sm:flex-row" role="search" @submit.prevent="submitSearch">
                    <div class="flex min-h-11 flex-1 items-center gap-2 rounded-[14px] border border-line-strong bg-surface px-4 transition-colors focus-within:border-accent">
                        <SearchIcon class="h-4 w-4 shrink-0 text-muted" />
                        <input
                            v-model="searchQuery"
                            type="search"
                            placeholder="Найти автора по имени"
                            aria-label="Поиск автора по имени"
                            class="w-full border-0 bg-transparent p-0 text-[1.0625rem] text-content placeholder:text-muted focus:outline-none focus:ring-0"
                        />
                    </div>
                    <button type="submit" class="ui-pill ui-pill--solid shrink-0">Найти</button>
                </form>

                <nav class="mt-4 flex flex-wrap gap-2" aria-label="Сортировка авторов">
                    <button
                        v-for="option in sortOptions"
                        :key="option.value"
                        type="button"
                        class="ui-pill"
                        :class="filters.sort === option.value ? 'ui-pill--solid' : ''"
                        :aria-current="filters.sort === option.value ? 'true' : undefined"
                        @click="changeSort(option.value)"
                    >
                        {{ option.label }}
                    </button>
                </nav>

                <p v-if="authors.total" class="mt-6 type-meta">
                    <span class="num">{{ authors.total }}</span> {{ pluralAuthors(authors.total) }}
                </p>

                <ul v-if="authors.data.length" class="mt-2 divide-y divide-line">
                    <li v-for="author in authors.data" :key="author.id">
                        <Link
                            :href="route('authors.show', author.id)"
                            class="group flex items-start gap-4 py-6 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-accent"
                        >
                            <UserAvatar :user="author" :size="52" class="mt-0.5 shrink-0" />
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-baseline gap-x-3 gap-y-1">
                                    <h2 class="text-[1.25rem] font-semibold leading-snug text-content transition-colors group-hover:text-accent">
                                        {{ author.name }}
                                    </h2>
                                    <span v-if="author.username" class="text-[0.875rem] text-accent">@{{ author.username }}</span>
                                    <span class="text-[0.875rem] text-muted">
                                        <span class="num">{{ author.articles_count }}</span> {{ pluralArticles(author.articles_count) }}
                                    </span>
                                </div>
                                <p v-if="author.about" class="mt-2 max-w-[60ch] text-[1rem] leading-relaxed text-content">
                                    {{ author.about }}
                                </p>
                                <p v-else-if="author.last_article" class="mt-2 max-w-[60ch] text-[1rem] leading-relaxed text-muted">
                                    Последняя публикация: {{ author.last_article.label }}
                                </p>
                                <p v-if="author.last_published_at" class="mt-2 text-[0.875rem] text-muted">
                                    Последняя публикация — {{ formatDate(author.last_published_at) }}
                                </p>
                            </div>
                            <span class="hidden shrink-0 self-center text-sm font-medium text-accent sm:inline">Профиль →</span>
                        </Link>
                    </li>
                </ul>

                <div v-else class="mt-8 rounded-[18px] border border-line bg-surface p-6 sm:p-8">
                    <p class="type-h2 text-content">Авторы не нашлись</p>
                    <p class="mt-3 max-w-[46ch] text-muted">
                        Попробуйте изменить запрос или сбросить поиск — в каталоге показываются только авторы с опубликованными статьями.
                    </p>
                    <button type="button" class="ui-pill mt-5" @click="resetSearch">Сбросить поиск</button>
                </div>

                <Pagination v-if="authors.links && authors.links.length > 3" :links="authors.links" class="mt-10" />
            </div>
        </div>
    </Layout>
</template>

<script setup>
import { ref, watch } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import Layout from "@/Layouts/Layout.vue";
import Pagination from "@/Components/Pagination.vue";
import UserAvatar from "@/Components/UserAvatar.vue";
import SearchIcon from "vue-material-design-icons/Magnify.vue";

const props = defineProps({
    authors: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({ q: "", sort: "active" }),
    },
});

const sortOptions = [
    { value: "active", label: "Активные" },
    { value: "new", label: "Новые" },
];

const searchQuery = ref(props.filters.q ?? "");

watch(
    () => props.filters.q,
    (value) => {
        searchQuery.value = value ?? "";
    },
);

const visit = (params) => {
    router.get(route("authors.index"), params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const submitSearch = () => {
    visit({
        q: searchQuery.value.trim() || undefined,
        sort: props.filters.sort,
    });
};

const changeSort = (sort) => {
    if (sort === props.filters.sort) return;

    visit({
        q: props.filters.q || undefined,
        sort,
    });
};

const resetSearch = () => {
    searchQuery.value = "";
    visit({ sort: props.filters.sort });
};

const pluralAuthors = (n) => {
    const mod10 = n % 10;
    const mod100 = n % 100;
    if (mod10 === 1 && mod100 !== 11) return "автор";
    if (mod10 >= 2 && mod10 <= 4 && (mod100 < 12 || mod100 > 14)) return "автора";
    return "авторов";
};

const pluralArticles = (n) => {
    const mod10 = n % 10;
    const mod100 = n % 100;
    if (mod10 === 1 && mod100 !== 11) return "статья";
    if (mod10 >= 2 && mod10 <= 4 && (mod100 < 12 || mod100 > 14)) return "статьи";
    return "статей";
};

const formatDate = (value) => {
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return "";

    return new Intl.DateTimeFormat("ru-RU", { day: "numeric", month: "long", year: "numeric" }).format(date);
};
</script>
