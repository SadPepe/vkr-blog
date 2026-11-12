<!-- resources/js/Components/RailPost.vue -->
<template>
    <!-- Строка афиши (рейл «По просмотрам» на главной, «Афиша» на статье, «Чаще читают»
         в профиле): аватар → имя автора → заголовок → счётчики. Порядок сверху вниз,
         заголовок стоит под автором и начинается от левого края, а не от аватара. -->
    <Link
        :href="route('articles.show', article.slug)"
        prefetch="hover"
        class="group flex items-start gap-3 py-4 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-accent"
    >
        <div class="min-w-0 flex-1">
            <div v-if="author" class="flex items-center gap-2">
                <UserAvatar :user="author" :size="22" class="shrink-0" />
                <span class="truncate text-[0.8125rem] font-medium text-muted transition-colors group-hover:text-content">
                    {{ author.name }}
                </span>
            </div>

            <!-- Фотопост: вместо бесполезной подписи «Фотопост» показываем
                 сам кадр. Это единственный медийный формат в рейле. -->
            <div v-if="isPhotoPost && article.card_image_path" class="mt-2 overflow-hidden rounded-xl border border-line">
                <img
                    :src="article.card_image_path"
                    alt="Фотопост"
                    class="aspect-[12/5] w-full object-cover transition-transform duration-300 group-hover:scale-[1.02]"
                    loading="lazy"
                    decoding="async"
                />
            </div>

            <!-- Без заголовка подписью служит начало текста -->
            <span
                v-else
                class="line-clamp-2 text-[0.9375rem] font-medium leading-snug transition-colors group-hover:text-accent"
                :class="[author ? 'mt-1' : '', isPhotoPost ? 'text-muted' : 'text-content']"
            >
                {{ displayLabel }}
            </span>

            <span class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-1 text-[0.8125rem] text-muted">
                <span v-if="isPhotoPost" class="inline-flex items-center gap-1 font-medium text-content">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M3 8.25A2.25 2.25 0 015.25 6h1.5l1.125-1.5h6.25L15.25 6h1.5A2.25 2.25 0 0119 8.25v8.5A2.25 2.25 0 0116.75 19H5.25A2.25 2.25 0 013 16.75v-8.5z"
                        />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15.5a3.25 3.25 0 100-6.5 3.25 3.25 0 000 6.5z" />
                    </svg>
                    <span>Фотопост</span>
                </span>
                <span v-if="isPhotoPost" aria-hidden="true">·</span>

                <span class="inline-flex items-center gap-1" :aria-label="`${article.views_count} ${pluralViews(article.views_count)}`">
                    <svg
                        class="h-4 w-4 shrink-0"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.5"
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                        />
                    </svg>
                    <span class="num font-medium">{{ formatCount(article.views_count) }}</span>
                </span>

                <span
                    v-if="article.comments_count"
                    class="inline-flex items-center gap-1"
                    :aria-label="`${article.comments_count} ${pluralComments(article.comments_count)}`"
                >
                    <svg
                        class="h-4 w-4 shrink-0"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.5"
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
                        />
                    </svg>
                    <span class="num font-medium">{{ formatCount(article.comments_count) }}</span>
                </span>
            </span>
        </div>
    </Link>
</template>

<script setup>
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";
import UserAvatar from "@/Components/UserAvatar.vue";

const props = defineProps({
    article: {
        type: Object,
        required: true,
    },
    // В профиле автора строка автора не нужна: и так его страница
    showAuthor: {
        type: Boolean,
        default: true,
    },
});

const author = computed(() => (props.showAuthor ? props.article.user : null));

const isPhotoPost = computed(() => Boolean(props.article.is_photo_post));

const displayLabel = computed(() => props.article.label || props.article.title || "Фотопост");

const formatCount = (value) => new Intl.NumberFormat("ru-RU").format(value ?? 0);

const pluralize = (n, [one, few, many]) => {
    const mod10 = n % 10;
    const mod100 = n % 100;
    if (mod10 === 1 && mod100 !== 11) return one;
    if (mod10 >= 2 && mod10 <= 4 && (mod100 < 12 || mod100 > 14)) return few;
    return many;
};

const pluralViews = (n) => pluralize(n ?? 0, ["просмотр", "просмотра", "просмотров"]);
const pluralComments = (n) => pluralize(n ?? 0, ["комментарий", "комментария", "комментариев"]);
</script>
