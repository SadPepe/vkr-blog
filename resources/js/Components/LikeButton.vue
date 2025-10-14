<!-- resources/js/Components/LikeButton.vue -->
<template>
    <button
        @click="toggleLike"
        :disabled="loading"
        :class="[
            'flex items-center space-x-2 px-4 py-2 rounded-lg border transition-all duration-300',
            liked 
                ? 'bg-red-50 border-red-200 text-red-600 hover:bg-red-100 dark:bg-red-900/20 dark:border-red-800 dark:text-red-400'
                : 'bg-white border-gray-300 text-gray-600 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400'
        ]"
    >
        <svg 
            class="w-5 h-5 transition-transform duration-300" 
            :class="{ 'scale-110': liked, 'scale-100': !liked }"
            fill="currentColor" 
            viewBox="0 0 20 20"
        >
            <path 
                v-if="liked"
                fill-rule="evenodd" 
                d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" 
                clip-rule="evenodd"
            />
            <path 
                v-else
                fill-rule="evenodd" 
                d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" 
                clip-rule="evenodd"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
            />
        </svg>
        <span class="font-medium">{{ likesCount }}</span>
    </button>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

const page = usePage();

const props = defineProps({
    article: {
        type: Object,
        required: true
    },
    initialLiked: {
        type: Boolean,
        default: false
    },
    initialLikesCount: {
        type: Number,
        default: 0
    }
});

const liked = ref(props.initialLiked);
const likesCount = ref(props.initialLikesCount);
const loading = ref(false);

const authUser = computed(() => page.props.auth.user);

const toggleLike = async () => {
    if (!authUser.value) {
        // Перенаправляем на страницу входа
        router.visit(route('login'));
        return;
    }

    if (loading.value) return;

    loading.value = true;

    try {
        const response = await fetch(route('articles.like.toggle', props.article.slug), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({})
        });

        if (response.ok) {
            const data = await response.json();
            liked.value = data.liked;
            likesCount.value = data.likes_count;
        } else {
            console.error('Ошибка при обновлении лайка');
        }
    } catch (error) {
        console.error('Ошибка:', error);
    } finally {
        loading.value = false;
    }
};

// Загружаем статус лайка при монтировании
onMounted(async () => {
    if (authUser.value) {
        try {
            const response = await fetch(route('articles.like.status', props.article.slug));
            if (response.ok) {
                const data = await response.json();
                liked.value = data.liked;
                likesCount.value = data.likes_count;
            }
        } catch (error) {
            console.error('Ошибка при загрузке статуса лайка:', error);
        }
    }
});
</script>