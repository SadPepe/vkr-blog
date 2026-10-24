<template>
    <MainLayout>
        <!-- Контейнер -->
        <div class="mx-auto w-full max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-[minmax(0,1fr)_360px]">
                <!-- Главная колонка: лента статей -->
                <div class="min-w-0">
                    <!-- Заголовок списка -->
                    <div class="mb-5 flex items-baseline justify-between">
                        <h2 class="text-lg font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
                            Статьи
                        </h2>
                        <span class="text-sm text-zinc-400 dark:text-zinc-500">
                            {{ articles.length }}
                        </span>
                    </div>

                    <!-- Список статей -->
                    <div v-if="articles.length" class="flex flex-col gap-4 pb-12">
                        <article
                            v-for="article in articles"
                            :key="article.id"
                            class="group relative flex items-center gap-4 rounded-md border border-zinc-200/80 bg-[#FAF8F4] p-4 transition-all hover:border-zinc-300 hover:shadow-sm sm:gap-6 sm:p-5 dark:border-white/10 dark:bg-[#252525] dark:hover:border-white/20"
                        >
                            <!-- Кнопки действий: минималистичные иконки в углу -->
                            <div
                                v-if="$page.props.auth.user.id === article.user.id"
                                class="absolute right-3 top-3 z-10 flex items-center gap-1 opacity-90 transition-opacity group-hover:opacity-100 sm:right-4 sm:top-4"
                            >
                                <Link
                                    :href="route('articles.edit', { article: article.slug })"
                                    class="rounded-md border border-zinc-200 bg-[#FAF8F4] p-1.5 text-zinc-400 transition-colors hover:border-zinc-300 hover:text-emerald-600 dark:border-zinc-700 dark:bg-[#252525] dark:text-stone-400 dark:hover:border-zinc-600 dark:hover:text-emerald-400"
                                    title="Редактировать"
                                    aria-label="Редактировать статью"
                                >
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                    </svg>
                                </Link>
                                <button
                                    type="button"
                                    @click="deleteArticle(article)"
                                    class="rounded-md border border-zinc-200 bg-[#FAF8F4] p-1.5 text-zinc-400 transition-colors hover:border-zinc-300 hover:text-red-500 dark:border-zinc-700 dark:bg-[#252525] dark:text-stone-400 dark:hover:border-zinc-600 dark:hover:text-red-400"
                                    title="Удалить"
                                    aria-label="Удалить статью"
                                >
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Текст слева -->
                            <div class="min-w-0 flex-1 pr-8 sm:pr-10">
                                <Link
                                    :href="route('articles.show', { article: article.slug })"
                                    class="block"
                                >
                                    <h3 class="line-clamp-1 text-base font-semibold leading-snug tracking-tight text-zinc-900 transition-colors group-hover:text-emerald-700 sm:text-lg dark:text-zinc-100 dark:group-hover:text-emerald-300">
                                        {{ article.title }}
                                    </h3>
                                    <p
                                        v-if="article.excerpt"
                                        class="mt-1.5 line-clamp-2 text-sm leading-relaxed text-zinc-500 dark:text-zinc-400"
                                    >
                                        {{ article.excerpt }}
                                    </p>
                                </Link>
                            </div>

                            <!-- Миниатюра справа -->
                            <Link
                                v-if="article.card_image_path && article.card_image_path.trim()"
                                :href="route('articles.show', { article: article.slug })"
                                class="shrink-0"
                                aria-label="Читать статью"
                            >
                                <div class="h-20 w-24 overflow-hidden rounded-md bg-zinc-100 sm:h-28 sm:w-40 dark:bg-zinc-800">
                                    <img
                                        :src="article.card_image_path"
                                        :alt="article.title"
                                        class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                                        loading="lazy"
                                    />
                                </div>
                            </Link>
                            <div
                                v-else
                                class="flex h-20 w-24 shrink-0 items-center justify-center rounded-md bg-zinc-100 text-zinc-300 sm:h-28 sm:w-40 dark:bg-zinc-800 dark:text-zinc-600"
                            >
                                <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 011.5 5.25v13.5A2.25 2.25 0 003.75 21z" />
                                </svg>
                            </div>
                        </article>
                    </div>

                    <!-- Пустое состояние -->
                    <div v-else class="pb-12 rounded-md border border-dashed border-zinc-300 py-14 text-center dark:border-zinc-700">
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">
                            У пользователя пока нет статей
                        </p>
                    </div>
                </div>

                <!-- Правая колонка: профиль -->
                <aside class="self-start lg:sticky lg:top-24 lg:max-h-[calc(100vh_-_8rem)] lg:overflow-y-auto lg:overscroll-contain scrollbar-slim lg:pr-1 lg:-mr-1">
                    <section
                        class="rounded-md border border-zinc-200/80 bg-[#FAF8F4] px-5 py-6 shadow-sm sm:px-6 dark:border-white/10 dark:bg-[#252525]"
                    >
                        <!-- Шапка: аватар слева, кнопки справа -->
                        <div class="flex items-start justify-between gap-4">
                            <UserAvatar :user="user" :size="80" class="shrink-0" />
                            <div class="flex shrink-0 flex-col gap-2.5">
                                <Link
                                    href="/profile/edit"
                                    class="inline-flex items-center justify-center whitespace-nowrap rounded-md border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:border-zinc-400 hover:bg-zinc-100 dark:border-zinc-600 dark:text-zinc-300 dark:hover:border-zinc-500 dark:hover:bg-zinc-800"
                                >
                                    Редактировать профиль
                                </Link>
                                <Link
                                    :href="route('create')"
                                    class="inline-flex items-center justify-center whitespace-nowrap rounded-md bg-gradient-to-r from-emerald-600 to-teal-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-shadow hover:shadow-md"
                                >
                                    Новая статья
                                </Link>
                            </div>
                        </div>

                        <!-- Имя -->
                        <h1
                            class="mt-6 break-words text-3xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100"
                        >
                            {{ user.name || 'Гость' }}
                        </h1>

                        <hr class="my-6 border-zinc-200 dark:border-zinc-700/60" />

                        <!-- Обо мне -->
                        <div>
                            <span
                                class="text-[11px] font-semibold uppercase tracking-[0.18em] text-zinc-400 dark:text-zinc-500"
                            >
                                Обо мне
                            </span>
                            <p class="mt-2.5 text-[15px] leading-relaxed text-zinc-600 dark:text-zinc-300">
                                {{ user.about || 'Добро пожаловать!' }}
                            </p>
                        </div>
                    </section>
                </aside>
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import { Link, router } from "@inertiajs/vue3";
import MainLayout from "@/Layouts/Layout.vue";
import UserAvatar from "@/Components/UserAvatar.vue";

const deleteArticle = (article) => {
    router.delete(route("articles.destroy", { article: article.slug }), {
        preserveScroll: true,
        onError: () => {},
    });
};

defineProps({
    articles: {
        type: Array,
        default: () => []
    },
    user: {
        type: Object,
        required: true
    }
});
</script>