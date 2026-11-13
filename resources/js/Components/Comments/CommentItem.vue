<!-- resources/js/Components/Comments/CommentItem.vue -->
<template>
    <article :id="`comment-${comment.id}`" class="scroll-mt-24">
        <div class="flex items-start gap-3">
            <UserAvatar :user="comment.user" :size="36" class="mt-0.5 shrink-0" />

            <div class="min-w-0 flex-1">
                <!-- Имя, дата и правка: имя слева, дата и служебные иконки — справа -->
                <div class="flex min-h-6 items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-baseline gap-x-2 gap-y-0.5">
                            <Link
                                :href="route('authors.show', comment.user.id)"
                                class="text-[0.9375rem] font-semibold leading-snug text-content transition-colors hover:text-accent"
                            >
                                {{ comment.user.name }}
                            </Link>
                            <span v-if="isArticleAuthor" class="text-[0.8125rem] font-medium text-accent">автор</span>
                            <time :datetime="comment.created_at" class="num text-[0.875rem] text-muted">
                                {{ formatDate(comment.created_at) }}
                            </time>
                        </div>
                    </div>

                    <div class="flex shrink-0 items-center gap-2">
                        <div v-if="canEdit || canDelete" class="flex items-center gap-1">
                            <button
                                v-if="canEdit && !editing"
                                type="button"
                                class="inline-flex h-6 w-6 items-center justify-center text-muted transition-colors hover:text-accent"
                                aria-label="Изменить комментарий"
                                title="Изменить"
                                @click="openForm('edit')"
                            >
                                <svg
                                    class="h-4 w-4"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.75"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487z"
                                    />
                                </svg>
                            </button>

                            <button
                                v-if="canDelete"
                                type="button"
                                class="inline-flex h-6 w-6 items-center justify-center text-muted transition-colors hover:text-accent"
                                aria-label="Удалить комментарий"
                                title="Удалить"
                                @click="$emit('delete', comment.id)"
                            >
                                <svg
                                    class="h-4 w-4"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.75"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Текст комментария -->
                <p v-if="!editing && comment.content" class="comment-content mt-0.5 whitespace-pre-wrap break-words text-[1.0625rem] leading-[1.6] text-content">
                    <MentionText :text="comment.content" />
                </p>

                <!-- Прикреплённое фото: клик открывает лайтбокс -->
                <button
                    v-if="!editing && comment.image_url"
                    type="button"
                    class="mt-3 block max-w-full cursor-zoom-in"
                    :aria-label="`Открыть фото комментария ${comment.user.name}`"
                    @click="lightboxOpen = true"
                >
                    <img
                        :src="comment.image_url"
                        :alt="`Фото к комментарию от ${comment.user.name}`"
                        decoding="async"
                        class="max-h-80 w-auto max-w-full rounded-xl border border-line object-cover"
                    />
                </button>

                <!-- Правка: та же полоска, что и у нового комментария -->
                <form v-else-if="editing" class="mt-1" @submit.prevent="submitEdit">
                    <CommentInput
                        :id="`edit-${comment.id}`"
                        ref="editInput"
                        v-model="editText"
                        v-model:image="editImage"
                        v-model:remove-image="editRemoveImage"
                        :existing-image-url="comment.image_url || ''"
                        label="Изменить комментарий"
                        submit-label="Сохранить изменения"
                        submit-title="Сохранить — Enter, новая строка — Shift + Enter"
                        :loading="loading"
                        :error="editErrors.content"
                        :image-error="editErrors.image"
                        cancelable
                        @cancel="closeForm"
                    />
                </form>
            </div>
        </div>

        <div class="pl-12">
            <!-- Строка действий: лайк, ответ, жалоба -->
            <div class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-1 text-[0.9375rem] sm:gap-x-4">
                <button
                    type="button"
                    class="ui-pill min-h-9 px-3 text-[0.875rem]"
                    :class="liked ? 'ui-pill--active' : 'ui-pill--quiet'"
                    :aria-label="liked ? 'Убрать лайк' : 'Лайкнуть комментарий'"
                    :aria-pressed="liked"
                    @click="toggleLike"
                >
                    <svg
                        class="h-[1.125rem] w-[1.125rem]"
                        :class="liked ? 'fill-current' : 'fill-none'"
                        stroke="currentColor"
                        stroke-width="1.75"
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
                        />
                    </svg>
                    <span class="num font-medium">{{ likesCount }}</span>
                </button>

                <button
                    v-if="authUser && !replying"
                    type="button"
                    class="ui-pill ui-pill--quiet min-h-9 px-3 text-[0.875rem]"
                    @click="openForm('reply')"
                >
                    Ответить
                </button>

                <button
                    v-if="authUser && !isOwn"
                    type="button"
                    class="ui-pill ui-pill--quiet h-9 w-9 min-h-9 px-0"
                    aria-label="Пожаловаться на комментарий"
                    title="Пожаловаться"
                    @click="reportOpen = true"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 21V4m0 0h11.5l-1.6 3.2a1 1 0 000 .9L14.5 11H3" />
                    </svg>
                </button>
            </div>

            <ReportDialog :show="reportOpen" type="comment" :id="comment.id" @close="reportOpen = false" />

            <ImageLightbox
                :show="lightboxOpen"
                :src="comment.image_url || ''"
                :alt="`Фото к комментарию от ${comment.user.name}`"
                @close="lightboxOpen = false"
            />

            <!-- Ответ: та же полоска, под комментарием -->
            <form v-if="replying" class="mt-3" @submit.prevent="submitReply">
                <CommentInput
                    :id="`reply-${comment.id}`"
                    ref="replyInput"
                    v-model="replyText"
                    v-model:image="replyImage"
                    :label="`Ответ на комментарий ${comment.user.name}`"
                    :placeholder="`Ответить ${comment.user.name}…`"
                    submit-label="Отправить ответ"
                    :loading="loading"
                    :error="replyErrors.content"
                    :image-error="replyErrors.image"
                    cancelable
                    @cancel="closeForm"
                />
            </form>

            <!-- Ветка ответов: одна линия, отступ от тела, без вложенных панелей -->
            <div v-if="visibleReplies.length" class="mt-5 space-y-7" :class="repliesClass">
                <CommentItem
                    v-for="reply in visibleReplies"
                    :key="reply.id"
                    :comment="reply"
                    :article-author-id="articleAuthorId"
                    :article-slug="articleSlug"
                    :depth="depth + 1"
                    @delete="$emit('delete', $event)"
                />
            </div>
        </div>
    </article>
</template>

<script setup>
import { computed, nextTick, ref } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import CommentInput from "@/Components/Comments/CommentInput.vue";
import UserAvatar from "@/Components/UserAvatar.vue";
import ImageLightbox from "@/Components/ImageLightbox.vue";
import ReportDialog from "@/Components/ReportDialog.vue";
import MentionText from "@/Components/MentionText.vue";

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
const isOwn = computed(() => authUser.value?.id === props.comment.user.id);
const isArticleAuthor = computed(() => props.comment.user.id === props.articleAuthorId);
const canEdit = computed(() => isOwn.value);
const canDelete = computed(() => isOwn.value || authUser.value?.id === props.articleAuthorId);

const visibleReplies = computed(() => (props.comment.replies || []).filter((reply) => !reply.hidden_at));

// Ветка — одна линия и один отступ; глубже второго уровня не сужаем текст на телефоне
// Аватар ответа встаёт на одну вертикаль с началом текста родителя:
// ветку сдвигаем влево на ширину аватара с отступом (36 + 12), линия уходит в жёлоб.
// Линия ветки жмётся к ответу: 12px от аватара, а сам аватар — на вертикали текста родителя
const repliesClass = computed(() => (props.depth === 0 ? "-ml-3.5 border-l-2 border-line pl-3" : ""));

const reportOpen = ref(false);
const lightboxOpen = ref(false);

const liked = ref(!!props.comment.is_liked);
const likesCount = ref(props.comment.likes_count || 0);
const likeLoading = ref(false);

const formMode = ref(null); // 'reply' | 'edit' | null
const replyText = ref("");
const editText = ref("");
const replyImage = ref(null);
const editImage = ref(null);
const editRemoveImage = ref(false);
const loading = ref(false);
const replyErrors = ref({});
const editErrors = ref({});
const replyInput = ref(null);
const editInput = ref(null);

const replying = computed(() => formMode.value === "reply");
const editing = computed(() => formMode.value === "edit");

const months = ["января", "февраля", "марта", "апреля", "мая", "июня", "июля", "августа", "сентября", "октября", "ноября", "декабря"];

const formatDate = (dateString) => {
    const date = new Date(dateString);
    if (Number.isNaN(date.getTime())) return "";

    const time = date.toLocaleTimeString("ru-RU", { hour: "2-digit", minute: "2-digit" });
    const year = date.getFullYear() === new Date().getFullYear() ? "" : ` ${date.getFullYear()}`;

    return `${date.getDate()} ${months[date.getMonth()]}${year}, ${time}`;
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

const openForm = async (mode) => {
    if (formMode.value === mode) {
        closeForm();
        return;
    }

    formMode.value = mode;

    if (mode === "reply") {
        replyText.value = "";
        replyImage.value = null;
        replyErrors.value = {};
        await nextTick();
        replyInput.value?.focus();
    } else {
        editText.value = props.comment.content;
        editImage.value = null;
        editRemoveImage.value = false;
        editErrors.value = {};
        await nextTick();
        editInput.value?.focus();
    }
};

const closeForm = () => {
    formMode.value = null;
};

const toErrors = (errors) => ({
    content: errors.content?.[0] ?? "",
    image: errors.image?.[0] ?? "",
});

const submitReply = () => {
    if ((!replyText.value.trim() && !replyImage.value) || loading.value) return;

    // loading держим до onFinish: router.post() не возвращает Promise запроса.
    loading.value = true;
    replyErrors.value = {};

    const payload = {
        content: replyText.value,
        parent_id: props.comment.id,
    };
    if (replyImage.value) payload.image = replyImage.value;

    try {
        router.post(route("comments.store", props.articleSlug), payload, {
            preserveScroll: true,
            forceFormData: !!replyImage.value,
            onSuccess: () => {
                replyText.value = "";
                replyImage.value = null;
                formMode.value = null;
            },
            onError: (errors) => {
                replyErrors.value = toErrors(errors);
            },
            onFinish: () => {
                loading.value = false;
            },
        });
    } catch (error) {
        loading.value = false;
        console.error("Не удалось отправить ответ:", error);
    }
};

const submitEdit = () => {
    const hasPhoto = !!editImage.value || (!!props.comment.image_url && !editRemoveImage.value);

    if ((!editText.value.trim() && !hasPhoto) || loading.value) return;

    loading.value = true;
    editErrors.value = {};

    const payload = { content: editText.value };
    if (editImage.value) {
        payload.image = editImage.value;
    } else if (editRemoveImage.value) {
        payload.remove_image = true;
    }

    try {
        router.put(route("comments.update", props.comment.id), payload, {
            preserveScroll: true,
            forceFormData: !!editImage.value,
            onSuccess: () => {
                editImage.value = null;
                editRemoveImage.value = false;
                formMode.value = null;
            },
            onError: (errors) => {
                editErrors.value = toErrors(errors);
            },
            onFinish: () => {
                loading.value = false;
            },
        });
    } catch (error) {
        loading.value = false;
        console.error("Не удалось сохранить комментарий:", error);
    }
};
</script>
