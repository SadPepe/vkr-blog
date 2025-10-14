<!-- resources/js/Components/Comments/CommentItem.vue -->
<template>
    <div class="border-b border-gray-200 dark:border-stone-700 pb-6 last:border-b-0">
        <div class="flex space-x-3">
            <!-- Аватар -->
            <img
                :src="comment.user.avatar_url || '/storage/avatars/altAvat.png'"
                :alt="comment.user.name"
                class="w-10 h-10 rounded-full flex-shrink-0"
            />
            
            <div class="flex-1 min-w-0">
                <!-- Заголовок комментария -->
                <div class="flex items-center justify-between mb-2">
                    <div>
                        <span class="font-semibold text-gray-900 dark:text-stone-300">
                            {{ comment.user.name }}
                        </span>
                        <span class="text-sm text-gray-500 dark:text-stone-500 ml-2">
                            {{ formatDate(comment.created_at) }}
                        </span>
                        <span 
                            v-if="comment.user.id === articleAuthorId"
                            class="ml-2 px-2 py-1 text-xs bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 rounded-full"
                        >
                            Автор
                        </span>
                    </div>
                    
                    <!-- Действия -->
                    <div v-if="canEdit(comment)" class="flex space-x-2">
                        <button
                            @click="$emit('edit', comment)"
                            class="text-gray-500 hover:text-blue-600 transition-colors"
                            title="Редактировать"
                        >
                            ✏️
                        </button>
                        <button
                            @click="$emit('delete', comment.id)"
                            class="text-gray-500 hover:text-red-600 transition-colors"
                            title="Удалить"
                        >
                            🗑️
                        </button>
                    </div>
                </div>

                <!-- Содержимое комментария -->
                <p class="text-gray-700 dark:text-stone-300 whitespace-pre-wrap">
                    {{ comment.content }}
                </p>

                <!-- Действия -->
                <div class="flex items-center space-x-4 mt-3">
                    <button
                        v-if="authUser"
                        @click="$emit('reply', comment)"
                        class="text-sm text-gray-500 hover:text-blue-600 transition-colors"
                    >
                        Ответить
                    </button>
                </div>

                <!-- Ответы -->
                <div v-if="comment.replies && comment.replies.length" class="mt-4 ml-6 space-y-4 border-l-2 border-gray-200 dark:border-stone-700 pl-4">
                    <CommentItem
                        v-for="reply in comment.replies"
                        :key="reply.id"
                        :comment="reply"
                        :article-author-id="articleAuthorId"
                        @reply="$emit('reply', reply)"
                        @edit="$emit('edit', reply)"
                        @delete="$emit('delete', reply.id)"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();

const props = defineProps({
    comment: {
        type: Object,
        required: true
    },
    articleAuthorId: {
        type: Number,
        required: true
    }
});

const emit = defineEmits(['reply', 'edit', 'delete']);

// Получаем авторизованного пользователя
const authUser = computed(() => page.props.auth.user);

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('ru-RU', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const canEdit = (comment) => {
    return authUser.value && authUser.value.id === comment.user.id;
};
</script>