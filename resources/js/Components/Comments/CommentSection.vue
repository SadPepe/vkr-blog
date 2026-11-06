<!-- resources/js/Components/Comments/CommentSection.vue -->
<template>
    <div class="mt-14">
        <!-- Заголовок: название и счётчик в одну строку, без линий и пилюль -->
        <div class="mt-16 flex items-baseline gap-3">
            <h2 class="type-h2 text-content">Комментарии</h2>
            <span class="num text-[1.0625rem] text-muted">{{ comments.length }}</span>
        </div>

        <!-- Форма добавления комментария -->
        <div v-if="authUser" class="mt-8">
            <form @submit.prevent="submitComment">
                <div class="flex items-start gap-3">
                    <UserAvatar :user="authUser" :size="40" class="mt-1 shrink-0" />
                    <div class="min-w-0 flex-1">
                        <textarea
                            v-model="newComment"
                            placeholder="Поделитесь мыслями"
                            rows="3"
                            class="w-full resize-none border-0 border-b border-line bg-transparent px-0 py-2 text-[1.0625rem] text-content outline-none transition-colors placeholder:text-muted focus:border-accent focus:ring-0"
                            :class="{ 'border-accent': errors.content }"
                        />
                        <InputError :message="errors.content" class="mt-2" />
                    </div>
                </div>
                <div class="mt-4 flex justify-end">
                    <button
                        type="submit"
                        :disabled="!newComment.trim() || loading"
                        class="inline-flex min-h-11 items-center bg-accent px-5 py-2.5 text-sm font-semibold text-accent-contrast transition-colors hover:bg-accent-hover disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <span>{{ loading ? "Отправка…" : "Оставить комментарий" }}</span>
                    </button>
                </div>
            </form>
        </div>
        <p v-else class="mt-6 text-muted">
            <Link :href="route('login')" class="text-accent transition-colors hover:text-accent-hover">Войдите</Link>, чтобы оставить
            комментарий
        </p>

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
        <p v-else class="mt-8 text-muted">Пока нет комментариев. Будьте первыми.</p>

        <ConfirmDialog
            :show="pendingDeleteId !== null"
            title="Удалить комментарий?"
            text="Комментарий и все ответы на него исчезнут безвозвратно."
            @confirm="confirmDelete"
            @cancel="pendingDeleteId = null"
        />
    </div>
</template>

<script setup>
import { ref, reactive, computed } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import CommentItem from "@/Components/Comments/CommentItem.vue";
import ConfirmDialog from "@/Components/ConfirmDialog.vue";
import InputError from "@/Components/InputError.vue";
import UserAvatar from "@/Components/UserAvatar.vue";

const page = usePage();

const props = defineProps({
    comments: {
        type: Array,
        required: true,
        default: () => [],
    },
    article: {
        type: Object,
        required: true,
    },
    articleSlug: {
        type: String,
        required: true,
    },
});

const newComment = ref("");
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
    errors.content = "";

    try {
        await router.post(
            route("comments.store", props.articleSlug),
            {
                content: newComment.value,
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    newComment.value = "";
                },
                onError: (err) => {
                    if (err.content) {
                        errors.content = err.content[0];
                    }
                },
            },
        );
    } finally {
        loading.value = false;
    }
};

const pendingDeleteId = ref(null);

const deleteComment = (commentId) => {
    pendingDeleteId.value = commentId;
};

const confirmDelete = async () => {
    const id = pendingDeleteId.value;
    pendingDeleteId.value = null;

    if (!id) return;

    await router.delete(route("comments.destroy", id), {
        preserveScroll: true,
    });
};
</script>
