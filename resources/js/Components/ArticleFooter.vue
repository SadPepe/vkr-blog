<!-- Действия под постом: лайк и «поделиться» одной строкой на всех ширинах.
     Автор, просмотры и правка переехали над постом, в строку автора. -->
<template>
    <div class="mt-10 flex flex-wrap items-center gap-2 border-t border-line-strong pt-5">
        <LikeButton
            :article="article"
            :initial-liked="article.is_liked"
            :initial-likes-count="article.likes_count"
            :label="pluralLikes"
            pill
            class="min-h-11 px-3 sm:min-h-10"
        />

        <ArticleShare :url="shareUrl" :title="shareTitle" />
    </div>
</template>

<script setup>
import { computed } from "vue";
import ArticleShare from "@/Components/ArticleShare.vue";
import LikeButton from "@/Components/LikeButton.vue";

const props = defineProps({
    article: {
        type: Object,
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
});

const pluralize = (n, forms) => {
    const count = Math.abs(n ?? 0) % 100;
    const last = count % 10;
    if (count > 10 && count < 20) return forms[2];
    if (last > 1 && last < 5) return forms[1];
    if (last === 1) return forms[0];
    return forms[2];
};

const pluralLikes = computed(() => pluralize(props.article.likes_count, ["лайк", "лайка", "лайков"]));
</script>
