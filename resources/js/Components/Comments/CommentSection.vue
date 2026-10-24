<!-- resources/js/Components/Comments/CommentSection.vue -->
<template>
    <div class="mt-14">
        <!-- Лаконичный разделитель: рассеивающиеся линии с иконкой по центру -->
        <div class="flex items-center gap-4">
            <span
                class="h-px flex-1 bg-gradient-to-r from-transparent via-gray-200 to-gray-100 dark:via-stone-500/25 dark:to-transparent"
            ></span>
            <span
                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-gray-200 bg-[#FAF8F4] text-emerald-600 shadow-sm dark:border-white/10 dark:bg-white/5 dark:text-emerald-400"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
            </span>
            <span
                class="h-px flex-1 bg-gradient-to-l from-transparent via-gray-200 to-gray-100 dark:via-stone-500/25 dark:to-transparent"
            ></span>
        </div>

        <!-- Заголовок: акцентная полоса + название + счётчик -->
        <div class="mb-7 mt-7 flex items-center gap-3">
            <span class="h-6 w-1.5 rounded-full bg-gradient-to-b from-emerald-500 to-teal-500"></span>
            <h3 class="text-xl font-bold text-gray-900 dark:text-stone-200">
                Комментарии
            </h3>
            <span
                class="rounded-md bg-emerald-100 px-2.5 py-0.5 text-sm font-semibold text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300"
            >
                {{ comments.length }}
            </span>
            <span class="hidden text-xs text-gray-400 sm:inline dark:text-stone-500">
                {{ plural(comments.length, ['обсуждение', 'обсуждения', 'обсуждений']) }}
            </span>
        </div>

        <!-- Форма добавления комментария -->
        <div v-if="authUser" class="mb-8">
            <form
                @submit.prevent="submitComment"
                class="rounded-md border border-gray-200/80 bg-[#FAF8F4]/80 p-4 transition-colors focus-within:border-emerald-300 dark:border-white/[0.07] dark:bg-white/[0.03] dark:focus-within:border-emerald-500/40"
            >
                <div class="flex items-start gap-3">
                    <UserAvatar :user="authUser" :size="40" class="mt-1 shrink-0" />
                    <textarea
                        v-model="newComment"
                        placeholder="Поделитесь мыслями..."
                        rows="3"
                        class="w-full resize-none rounded border border-gray-200 bg-[#FAF8F4] px-3.5 py-2.5 text-sm text-gray-800 outline-none transition-all placeholder:text-gray-400 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-500/20 dark:border-white/10 dark:bg-white/5 dark:text-stone-200 dark:placeholder-stone-500"
                        :class="{ 'border-red-500': errors.content }"
                    />
                </div>
                <InputError :message="errors.content" class="mt-2" />
                <div class="mt-3 flex justify-end">
                    <button
                        type="submit"
                        :disabled="!newComment.trim() || loading"
                        class="inline-flex items-center gap-2 rounded-md bg-gradient-to-r from-emerald-600 to-teal-600 px-5 py-2 text-sm font-semibold text-white shadow-md shadow-emerald-500/20 transition-all duration-200 hover:shadow-lg disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <svg v-if="!loading" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                        <span>{{ loading ? 'Отправка...' : 'Оставить комментарий' }}</span>
                    </button>
                </div>
            </form>
        </div>
        <div
            v-else
            class="mb-8 rounded-2xl border border-gray-200/80 bg-gray-50 px-5 py-4 text-center text-sm text-gray-600 dark:border-white/[0.07] dark:bg-white/[0.03] dark:text-stone-400"
        >
            <Link :href="route('login')" class="font-semibold text-emerald-600 transition-colors hover:text-emerald-500 dark:text-emerald-400 dark:hover:text-emerald-300">Войдите</Link>,
            чтобы оставить комментарий
        </div>

        <!-- Список комментариев -->
        <div v-if="comments.length" class="space-y-4">
            <CommentItem
                v-for="comment in comments"
                :key="comment.id"
                :comment="comment"
                :article-author-id="articleAuthorId"
                :article-slug="articleSlug"
                @delete="deleteComment"
            />
        </div>
        <div v-else class="rounded-2xl border border-dashed border-gray-200 py-10 text-center dark:border-white/10">
            <p class="text-sm text-gray-500 dark:text-stone-400">
                Пока нет комментариев. Будьте первыми!
            </p>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import CommentItem from '@/Components/Comments/CommentItem.vue';
import InputError from '@/Components/InputError.vue';
import UserAvatar from '@/Components/UserAvatar.vue';

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
    },
    articleSlug: {
        type: String,
        required: true
    }
});

const newComment = ref('');
const loading = ref(false);
const errors = reactive({});

const plural = (count, [one, few, many]) => {
    const mod10 = count % 10;
    const mod100 = count % 100;

    if (mod10 === 1 && mod100 !== 11) return one;
    if (mod10 >= 2 && mod10 <= 4 && (mod100 < 10 || mod100 >= 20)) return few;
    return many;
};

const authUser = computed(() => page.props.auth.user);
const articleAuthorId = computed(() => props.article.user.id);

const submitComment = async () => {
    loading.value = true;
    errors.content = '';

    try {
        await router.post(route('comments.store', props.articleSlug), {
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

const deleteComment = async (commentId) => {
    if (!confirm('Удалить комментарий?')) return;

    await router.delete(route('comments.destroy', commentId), {
        preserveScroll: true
    });
};
</script>