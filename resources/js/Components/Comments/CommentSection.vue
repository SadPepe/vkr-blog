<!-- resources/js/Components/Comments/CommentSection.vue -->
<template>
    <div class="mt-12 border-t pt-8">
        <h3 class="text-2xl font-bold text-gray-800 dark:text-stone-300 mb-6">
            Комментарии ({{ comments.length }})
        </h3>

        <!-- Форма добавления комментария -->
        <div v-if="authUser" class="mb-8">
            <form @submit.prevent="submitComment" class="space-y-4">
                <textarea
                    v-model="newComment"
                    placeholder="Напишите ваш комментарий..."
                    rows="4"
                    class="w-full px-4 py-3 border border-gray-300 dark:border-stone-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-[#1a1a1a] dark:text-stone-300 resize-none"
                    :class="{ 'border-red-500': errors.content }"
                ></textarea>
                <InputError :message="errors.content" />
                <div class="flex justify-end">
                    <button
                        type="submit"
                        :disabled="!newComment.trim() || loading"
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        <span v-if="loading">Отправка...</span>
                        <span v-else>Оставить комментарий</span>
                    </button>
                </div>
            </form>
        </div>
        <div v-else class="mb-8 p-4 bg-gray-50 dark:bg-stone-800 rounded-lg text-center">
            <p class="text-gray-600 dark:text-stone-400">
                <a :href="route('login')" class="text-blue-600 hover:underline">Войдите</a>, чтобы оставить комментарий
            </p>
        </div>

        <!-- Список комментариев -->
        <div v-if="comments.length" class="space-y-6">
            <CommentItem
                v-for="comment in comments"
                :key="comment.id"
                :comment="comment"
                :article-author-id="articleAuthorId"
                @reply="startReply"
                @edit="startEdit"
                @delete="deleteComment"
            />
        </div>
        <div v-else class="text-center py-8 text-gray-500 dark:text-stone-400">
            Пока нет комментариев. Будьте первым!
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import CommentItem from '@/Components/Comments/CommentItem.vue';
import InputError from '@/Components/InputError.vue';

const page = usePage();

const props = defineProps({
    comments: {
        type: Array,
        required: true,
        default: () => []
    },
    article: {
        type: Object,
        required: true
    }
});

const newComment = ref('');
const loading = ref(false);
const errors = reactive({});

// Получаем авторизованного пользователя
const authUser = computed(() => page.props.auth.user);
const articleAuthorId = computed(() => props.article.user.id);

const submitComment = async () => {
    loading.value = true;
    errors.content = '';

    try {
        await router.post(route('comments.store', props.article.slug), {
            content: newComment.value
        }, {
            preserveScroll: true,
            onSuccess: () => {
                newComment.value = '';
            },
            onError: (err) => {
                if (err.content) {
                    errors.content = err.content[0];
                }
            }
        });
    } finally {
        loading.value = false;
    }
};

const startReply = (comment) => {
    // Пока просто устанавливаем текст с упоминанием
    newComment.value = `@${comment.user.name} `;
};

const startEdit = (comment) => {
    // Логика редактирования (можно реализовать позже)
    console.log('Edit:', comment);
};

const deleteComment = async (commentId) => {
    if (!confirm('Удалить комментарий?')) return;

    await router.delete(route('comments.destroy', commentId), {
        preserveScroll: true
    });
};
</script>