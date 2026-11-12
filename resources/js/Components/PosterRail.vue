<!-- resources/js/Components/PosterRail.vue -->
<template>
    <!-- Рейл открытый: без панели и отдельной плашки заголовка.
         Блоки разделены линиями, как и лента. -->
    <section>
        <div class="flex items-baseline justify-between gap-4 border-b border-line pb-3">
            <h2 class="type-h3 text-content">{{ title }}</h2>
            <slot name="action" />
        </div>
        <ul class="mt-1">
            <li v-for="article in articles" :key="article.id">
                <RailPost :article="article" :show-author="showAuthor" />
            </li>
        </ul>
    </section>
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
