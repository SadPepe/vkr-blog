<!-- resources/js/Components/PosterRail.vue -->
<template>
    <!-- Афиша целиком: панель с плашкой заголовка + список строк RailPost.
         Один и тот же блок на главной, на статье и в профиле — меняется
         только подпись раздела и то, нужна ли строка автора. -->
    <div class="border border-line bg-surface">
        <h2 class="border-b border-line bg-surface-muted px-4 py-2.5 text-[0.9375rem] font-semibold text-content">
            {{ title }}
        </h2>
        <ul class="divide-y divide-line">
            <li v-for="article in articles" :key="article.id">
                <RailPost :article="article" :show-author="showAuthor" class="px-4" />
            </li>
        </ul>
    </div>
</template>

<script setup>
import RailPost from "@/Components/RailPost.vue";

defineProps({
    articles: {
        type: Array,
        default: () => [],
    },
    title: {
        type: String,
        default: "Афиша",
    },
    // В профиле автора строка автора не нужна: и так его страница
    showAuthor: {
        type: Boolean,
        default: true,
    },
});
</script>
