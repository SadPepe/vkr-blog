<!-- resources/js/Components/Comments/CommentItem.vue -->
<template>
    <div
        class="rounded-md border border-line bg-surface p-4 shadow-sm transition-colors duration-200 hover:border-line-strong dark:border-white/[0.06] dark:bg-surface/[0.03] hover:border-line"
    >
        <!-- Шапка: аватар + имя + время -->
        <div class="flex items-center justify-between gap-3">
            <div class="flex min-w-0 items-center gap-2.5">
                <UserAvatar :user="comment.user" :size="38" class="shrink-0" />

                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-x-2">
                        <span class="font-semibold leading-tight text-content">
                            {{ comment.user.name }}
                        </span>
                        <span
                            v-if="comment.user.id === articleAuthorId"
                            class="rounded-md bg-accent-soft px-2 py-0.5 text-[11px] font-medium text-accent"
                        >
                            Автор
                        </span>
                    </div>
                    <span class="text-xs leading-tight text-muted">
                        {{ formatDate(comment.created_at) }}
                    </span>
                </div>
            </div>

            <!-- Редактировать / удалить -->
            <div class="flex shrink-0 items-center gap-0.5">
                <button
                    v-if="canEdit(comment)"
                    @click="openForm('edit')"
                    class="rounded-md p-1.5 text-muted transition-colors hover:bg-surface-muted hover:text-accent dark:hover:text-accent"
                    :aria-label="editing ? 'Закрыть редактирование' : 'Редактировать комментарий'"
                    :title="editing ? 'Закрыть редактирование' : 'Редактировать'"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                        />
                    </svg>
                </button>
                <button
                    v-if="canDelete(comment)"
                    @click="$emit('delete', comment.id)"
                    class="rounded-md p-1.5 text-muted transition-colors hover:bg-surface-muted hover:text-red-600 dark:hover:text-red-400"
                    aria-label="Удалить комментарий"
                    title="Удалить"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                        />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Содержимое -->
        <div class="mt-3">
            <p v-if="!editing" class="whitespace-pre-wrap text-[15px] leading-relaxed text-content">
                {{ comment.content }}
            </p>

            <!-- Форма редактирования -->
            <form v-else @submit.prevent="submitEdit" class="mt-1">
                <textarea
                    v-model="editText"
                    rows="2"
                    class="w-full resize-none rounded-md border border-line bg-surface-muted px-3.5 py-2.5 text-sm text-content outline-none transition-all focus:border-accent focus:ring-2 focus:ring-accent/20"
                    :class="{ 'border-red-500': editErrors.content }"
                />
                <InputError :message="editErrors.content" />
                <div class="mt-2 flex justify-end gap-2">
                    <button
                        type="button"
                        @click="closeForm"
                        class="rounded-full px-3 py-1.5 text-sm text-muted transition-colors hover:bg-surface-muted"
                    >
                        Отмена
                    </button>
                    <button
                        type="submit"
                        :disabled="!editText.trim() || loading"
                        class="rounded-md bg-accent px-4 py-1.5 text-sm font-medium text-accent-contrast transition-colors hover:bg-accent-hover disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        {{ loading ? "Сохранение..." : "Сохранить" }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Панель действий -->
        <div class="mt-3 flex items-center gap-1.5">
            <button
                @click="toggleLike"
                :class="[ liked ? 'bg-red-50 text-red-500 dark:bg-dark:hover:bg-red-500/10 dark:text-red-400' : 'text-muted hover:bg-red-50 hover:text-red-500 dark:hover:bg-red-500/10 dark:hover:text-red-400', 'inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-sm font-medium transition-all duration-150 active:scale-95', ]"
                :aria-label="liked ? 'Убрать лайк' : 'Лайкнуть комментарий'"
                :title="liked ? 'Убрать лайк' : 'Лайкнуть'"
            >
                <svg
                    class="h-[18px] w-[18px]"
                    :class="liked ? 'fill-current' : 'fill-none'"
                    stroke="currentColor"
                    stroke-width="2"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
                    />
                </svg>
                <span>{{ likesCount }}</span>
            </button>

            <button
                v-if="authUser"
                @click="openForm('reply')"
                :class="[ replying ? 'bg-accent-soft text-accent ' : 'text-muted hover:bg-surface-muted hover:text-accent dark:hover:text-accent', 'inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-sm font-medium transition-colors duration-150', ]"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                </svg>
                {{ replying ? "Закрыть" : "Ответить" }}
            </button>
        </div>

        <!-- Форма ответа -->
        <form v-if="replying" @submit.prevent="submitReply" class="mt-3 rounded-md bg-surface-muted/70 p-3">
            <textarea
                v-model="replyText"
                rows="2"
                :placeholder="`Ответить ${comment.user.name}...`"
                class="w-full resize-none rounded-md border border-line bg-surface-muted px-3 py-2.5 text-sm text-content outline-none transition-all focus:border-accent focus:ring-2 focus:ring-accent/20"
                :class="{ 'border-red-500': replyErrors.content }"
            />
            <InputError :message="replyErrors.content" />
            <div class="mt-2 flex justify-end gap-2">
                <button
                    type="button"
                    @click="closeForm"
                    class="rounded-full px-3 py-1.5 text-sm text-muted transition-colors hover:bg-surface-muted"
                >
                    Отмена
                </button>
                <button
                    type="submit"
                    :disabled="!replyText.trim() || loading"
                    class="rounded-md bg-accent px-4 py-1.5 text-sm font-medium text-accent-contrast transition-colors hover:bg-accent-hover disabled:cursor-not-allowed disabled:opacity-50"
                >
                    {{ loading ? "Отправка..." : "Ответить" }}
                </button>
            </div>
        </form>

        <!-- Ответы: ветка -->
        <div
            v-if="comment.replies && comment.replies.length"
            class="mt-4 space-y-3 border-l-2 border-line dark:border-white/[0.06]"
            :class="depth === 0 ? 'ml-2 pl-4 sm:ml-4 sm:pl-5' : 'pl-3'"
        >
            <CommentItem
                v-for="reply in comment.replies"
                :key="reply.id"
                :comment="reply"
                :article-author-id="articleAuthorId"
                :article-slug="articleSlug"
                :depth="depth + 1"
                @delete="$emit('delete', reply.id)"
            />
        </div>
    </div>
</template>

<script setup>
import { computed, ref } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import InputError from "@/Components/InputError.vue";
import UserAvatar from "@/Components/UserAvatar.vue";

const page = usePage();

const props = defineProps({
    comment: {
        type: Object,
        required: true,
    },
    articleAuthorId: {
        type: Number,
        required: true,
    },
    articleSlug: {
        type: String,
        required: true,
    },
    depth: {
        type: Number,
        default: 0,
    },
});

const emit = defineEmits(["delete"]);

const authUser = computed(() => page.props.auth.user);

const liked = ref(!!props.comment.is_liked);
const likesCount = ref(props.comment.likes_count || 0);
const likeLoading = ref(false);

const formMode = ref(null); // 'reply' | 'edit' | null
const replyText = ref("");
const editText = ref("");
const loading = ref(false);
const replyErrors = ref({});
const editErrors = ref({});

const replying = computed(() => formMode.value === "reply");
const editing = computed(() => formMode.value === "edit");

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString("ru-RU", {
        year: "numeric",
        month: "long",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};

const canEdit = (comment) => {
    return authUser.value && authUser.value.id === comment.user.id;
};

const canDelete = (comment) => {
    if (!authUser.value) return false;
    return authUser.value.id === comment.user.id || authUser.value.id === props.articleAuthorId;
};

const toggleLike = async () => {
    if (!authUser.value) {
        router.visit(route("login"));
        return;
    }

    if (likeLoading.value) return;
    likeLoading.value = true;

    try {
        const response = await fetch(route("comments.like.toggle", props.comment.id), {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-XSRF-TOKEN": decodeURIComponent(document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || ""),
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
        console.error("Ошибка при лайке комментария:", error);
    } finally {
        likeLoading.value = false;
    }
};

const openForm = (mode) => {
    if (formMode.value === mode) {
        closeForm();
        return;
    }
    formMode.value = mode;
    if (mode === "reply") {
        replyText.value = "";
        replyErrors.value = {};
    } else {
        editText.value = props.comment.content;
        editErrors.value = {};
    }
};

const closeForm = () => {
    formMode.value = null;
};

const submitReply = async () => {
    loading.value = true;
    replyErrors.value = {};

    try {
        await router.post(
            route("comments.store", props.articleSlug),
            {
                content: replyText.value,
                parent_id: props.comment.id,
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    replyText.value = "";
                    formMode.value = null;
                },
                onError: (errors) => {
                    replyErrors.value = errors;
                },
            },
        );
    } finally {
        loading.value = false;
    }
};

const submitEdit = async () => {
    loading.value = true;
    editErrors.value = {};

    try {
        await router.put(
            route("comments.update", props.comment.id),
            {
                content: editText.value,
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    formMode.value = null;
                },
                onError: (errors) => {
                    editErrors.value = errors;
                },
            },
        );
    } finally {
        loading.value = false;
    }
};
</script>
