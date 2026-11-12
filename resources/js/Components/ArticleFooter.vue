<!-- Блок под постом на телефоне и планшете: автор, ссылки, метрики и действия.
     С lg то же самое живёт в рейле справа, поэтому здесь компонент скрыт. -->
<template>
    <div class="mt-12 border-t border-line-strong pt-5 lg:hidden">
        <div class="flex flex-wrap items-center gap-x-3 gap-y-3">
            <div class="flex min-w-0 items-center gap-2.5 sm:gap-3">
                <UserAvatar :user="article.user" :size="36" class="hidden sm:block" />
                <UserAvatar :user="article.user" :size="24" class="sm:hidden" />
                <div class="min-w-0">
                    <Link
                        :href="authorUrl"
                        class="block truncate text-[0.9375rem] font-medium text-content transition-colors hover:text-accent sm:text-[1.0625rem]"
                    >
                        {{ article.user.name }}
                    </Link>
                    <!-- «Все статьи автора» — только когда есть вторая строка (вариант 1) -->
                    <Link
                        :href="authorUrl"
                        class="hidden text-[0.8125rem] text-accent transition-colors hover:text-accent-hover sm:inline-block"
                    >
                        Все статьи автора
                    </Link>
                </div>
            </div>

            <div class="ml-auto">
                <ArticleShare :url="shareUrl" :title="shareTitle" />
            </div>
        </div>

        <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-2">
            <ArticleStats
                :article="article"
                :views-count="article.views_count"
                :initial-liked="article.is_liked"
                :initial-likes-count="article.likes_count"
                :comments-count="commentsCount"
            />

            <Link
                v-if="isOwner"
                :href="route('articles.edit', { article: article.slug })"
                class="ui-pill ui-pill--quiet ml-auto min-h-9 px-2 text-[0.8125rem]"
            >
                Редактировать
            </Link>
            <button
                v-else-if="canReport"
                type="button"
                class="ui-pill ui-pill--quiet ml-auto min-h-9 px-2 text-[0.8125rem]"
                @click="$emit('report')"
            >
                Пожаловаться
            </button>
        </div>
    </div>
</template>

<script setup>
import { computed } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import ArticleShare from "@/Components/ArticleShare.vue";
import ArticleStats from "@/Components/ArticleStats.vue";
import UserAvatar from "@/Components/UserAvatar.vue";

const props = defineProps({
    article: {
        type: Object,
        required: true,
    },
    authorUrl: {
        type: String,
        required: true,
    },
    shareUrl: {
        type: String,
        required: true,
    },
    shareTitle: {
        type: String,
        default: "",
    },
    isOwner: {
        type: Boolean,
        default: false,
    },
    commentsCount: {
        type: Number,
        default: 0,
    },
});

defineEmits(["report"]);

const page = usePage();
const canReport = computed(() => !props.isOwner && Boolean(page.props.auth?.user));
</script>
