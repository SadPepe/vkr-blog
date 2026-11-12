<!-- resources/js/Components/Comments/CommentSection.vue -->
<template>
    <section id="comments" class="mt-8 scroll-mt-24 lg:mt-10" aria-labelledby="comments-title">
        <!-- Заголовок: название и число комментариев, без линий и пилюль -->
        <div class="flex flex-wrap items-baseline gap-x-4 gap-y-1">
            <h2 id="comments-title" class="type-h2 text-content">Обсуждение</h2>
            <span v-if="comments.length" class="num text-[0.9375rem] text-muted">
                {{ comments.length }} {{ plural(comments.length, ["комментарий", "комментария", "комментариев"]) }}
                <template v-if="repliesCount"> · {{ repliesCount }} {{ plural(repliesCount, ["ответ", "ответа", "ответов"]) }} </template>
            </span>
        </div>

        <!-- Форма — низкая полоска с рамкой; стрелка отправки появляется с вводом -->
        <form v-if="authUser" class="mt-8" @submit.prevent="submitComment">
            <CommentInput
                id="new-comment"
                v-model="newComment"
                v-model:image="newImage"
                label="Ваш комментарий"
                placeholder="Коментарий"
                submit-label="Отправить комментарий"
                :loading="loading"
                :error="errors.content"
                :image-error="errors.image"
                :hint="hint"
            />
        </form>

        <div v-else class="mt-8">
            <p class="text-[1.0625rem] text-muted">
                Чтобы присоединиться к обсуждению,
                <Link :href="route('login')" class="text-accent transition-colors hover:text-accent-hover">войдите</Link>
                или
                <Link :href="route('register')" class="text-accent transition-colors hover:text-accent-hover">зарегистрируйтесь</Link>.
            </p>
        </div>

        <!-- Список комментариев: без панелей, ритм задают отступы -->
        <ol v-if="comments.length" class="mt-10 space-y-9" aria-label="Комментарии">
            <li v-for="comment in comments" :key="comment.id">
                <CommentItem :comment="comment" :article-author-id="articleAuthorId" :article-slug="articleSlug" @delete="deleteComment" />
            </li>
        </ol>
        <p v-else class="mt-8 text-[1.0625rem] text-muted">Пока никто не высказался. Начните обсуждение — первый комментарий задаёт тон.</p>

        <ConfirmDialog
            :show="pendingDeleteId !== null"
            title="Удалить комментарий?"
            text="Комментарий и все ответы на него исчезнут безвозвратно."
            @confirm="confirmDelete"
            @cancel="pendingDeleteId = null"
        />
    </section>
</template>

<script setup>
import { computed, nextTick, reactive, ref } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import CommentInput from "@/Components/Comments/CommentInput.vue";
import CommentItem from "@/Components/Comments/CommentItem.vue";
import ConfirmDialog from "@/Components/ConfirmDialog.vue";
import UserAvatar from "@/Components/UserAvatar.vue";

const MAX_LENGTH = 1000;

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
const newImage = ref(null);
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
// В счётчике статьи — только верхний уровень; ответы показываем отдельным числом
const repliesCount = computed(() =>
    props.comments.reduce((total, comment) => total + (comment.replies || []).filter((reply) => !reply.hidden_at).length, 0),
);
const remaining = computed(() => MAX_LENGTH - newComment.value.length);
// Счётчик появляется только у предела, чтобы полоска оставалась низкой
const hint = computed(() =>
    remaining.value <= 200 ? `${remaining.value} ${plural(remaining.value, ["символ", "символа", "символов"])} осталось` : "",
);
// Новые комментарии показываются сверху — свежий оказывается сразу под формой
const submitComment = async () => {
    if ((!newComment.value.trim() && !newImage.value) || loading.value) return;

    loading.value = true;
    errors.content = "";
    errors.image = "";

    const countBefore = props.comments.length;
    const payload = { content: newComment.value };
    if (newImage.value) payload.image = newImage.value;

    try {
        await router.post(route("comments.store", props.articleSlug), payload, {
            preserveScroll: true,
            forceFormData: !!newImage.value,
            onSuccess: async () => {
                newComment.value = "";
                newImage.value = null;

                await nextTick();

                // Страховка: если список не обновился, дотягиваем страницу вручную
                if (props.comments.length === countBefore) {
                    router.reload({ only: ["article"], preserveScroll: true });
                }
            },
            onError: (err) => {
                if (err.content) {
                    errors.content = err.content[0];
                }
                if (err.image) {
                    errors.image = err.image[0];
                }
            },
        });
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
