<!-- resources/js/Components/LikeButton.vue -->
<template>
    <!-- Лайк: в ленте — «пилюля» с подложкой, как reaction-item на DTF -->
    <button
        @click="toggleLike"
        :disabled="loading"
        :class="[
            'inline-flex items-center gap-1 transition-colors',
            pill ? 'ui-pill' : 'text-[0.9375rem]',
            liked ? (pill ? 'ui-pill--active' : 'text-accent') : 'text-content hover:text-accent',
        ]"
        :aria-pressed="liked"
        :aria-label="liked ? 'Убрать лайк' : 'Поставить лайк'"
    >
        <svg
            class="h-5 w-5 shrink-0 transition-transform duration-300"
            :class="{ 'scale-110': liked, 'scale-100': !liked }"
            :fill="liked ? 'currentColor' : 'none'"
            stroke="currentColor"
            stroke-width="1.5"
            viewBox="0 0 24 24"
            aria-hidden="true"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"
            />
        </svg>
        <span class="num">{{ likesCount }}</span>
        <span v-if="label" class="hidden sm:inline">{{ label }}</span>
    </button>
</template>

<script setup>
import { ref, computed } from "vue";
import { router, usePage } from "@inertiajs/vue3";

const page = usePage();

const props = defineProps({
    article: {
        type: Object,
        required: true,
    },
    initialLiked: {
        type: Boolean,
        default: false,
    },
    initialLikesCount: {
        type: Number,
        default: 0,
    },
    // В ленте лайк рисуется «пилюлей» с подложкой (как reaction-item на DTF)
    pill: {
        type: Boolean,
        default: false,
    },
    // Подпись рядом с числом: «12 лайков», «1 лайк». На мобильных скрывается.
    label: {
        type: String,
        default: "",
    },
});

const liked = ref(props.initialLiked);
const likesCount = ref(props.initialLikesCount);
const loading = ref(false);

const authUser = computed(() => page.props.auth.user);

const toggleLike = async () => {
    if (!authUser.value) {
        router.visit(route("login"));
        return;
    }

    if (loading.value) return;

    loading.value = true;

    try {
        const response = await fetch(route("articles.like.toggle", props.article.slug), {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.getAttribute("content"),
            },
            body: JSON.stringify({}),
        });

        if (response.status === 401) {
            router.visit(route("login"));
            return;
        }

        if (response.ok) {
            const data = await response.json();
            liked.value = data.liked;
            likesCount.value = data.likes_count;
        }
    } catch (error) {
        console.error("Ошибка:", error);
    } finally {
        loading.value = false;
    }
};
</script>
