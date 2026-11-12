<!-- resources/js/Components/Comments/CommentInput.vue -->
<template>
    <div>
        <!-- Низкая полоска: рамка, поле в одну строку, стрелка появляется с вводом -->
        <div class="relative">
        <div class="flex items-stretch overflow-hidden rounded-[14px] border border-line-strong bg-surface transition-colors focus-within:border-accent">
            <label :for="id" class="sr-only">{{ label }}</label>
            <textarea
                :id="id"
                ref="field"
                :value="modelValue"
                rows="1"
                :maxlength="maxlength"
                :placeholder="placeholder"
                class="min-h-12 w-full resize-none border-0 bg-transparent px-4 py-3 text-[1.0625rem] leading-[1.45] text-content outline-none placeholder:text-muted focus:ring-0"
                @input="onInput"
                @keydown.enter="onEnter"
                @keydown.esc="closeMentions"
            />
            <button
                type="button"
                class="flex h-12 w-10 shrink-0 items-center justify-center self-end transition-colors disabled:opacity-40"
                :class="hasPhoto ? 'text-accent' : 'text-muted hover:text-accent'"
                :aria-label="hasPhoto ? 'Фото прикреплено' : 'Прикрепить фото'"
                :title="hasPhoto ? 'Фото прикреплено' : 'Прикрепить фото'"
                :disabled="processing"
                @click="fileInput?.click()"
            >
                <svg v-if="!processing" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13"
                    />
                </svg>
                <span v-else class="text-[0.75rem]">…</span>
            </button>
            <button
                type="submit"
                :disabled="!canSubmit"
                :aria-label="submitLabel"
                :title="submitTitle"
                class="mr-1.5 flex h-9 shrink-0 items-center justify-center self-center overflow-hidden rounded-full bg-accent text-accent-contrast transition-all duration-150 hover:bg-accent-hover disabled:pointer-events-none"
                :class="canSubmit ? 'w-9 opacity-100' : 'mr-0 w-0 opacity-0'"
            >
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 12h15m0 0l-6-6m6 6l-6 6" />
                </svg>
            </button>
        </div>

        <!-- Подсказки @username: поиск по имени и логину -->
        <div
            v-if="mentionOpen && (mentionLoading || mentionResults.length || mentionQuery)"
            class="absolute left-0 right-0 top-full z-30 mt-2 overflow-hidden rounded-[14px] border border-line bg-surface shadow-lg"
        >
            <p v-if="mentionLoading" class="px-4 py-3 text-sm text-muted">Ищу людей…</p>
            <p v-else-if="!mentionResults.length" class="px-4 py-3 text-sm text-muted">Никого не нашли</p>
            <ul v-else class="max-h-64 overflow-y-auto py-1">
                <li v-for="user in mentionResults" :key="user.id">
                    <button
                        type="button"
                        class="flex w-full items-center gap-3 px-3 py-2 text-left transition-colors hover:bg-surface-muted"
                        @mousedown.prevent="selectMention(user)"
                    >
                        <UserAvatar :user="user" :size="28" />
                        <span class="min-w-0">
                            <span class="block truncate text-[0.9375rem] font-medium text-content">{{ user.name }}</span>
                            <span class="block truncate text-[0.8125rem] text-muted">@{{ user.username }}</span>
                        </span>
                    </button>
                </li>
            </ul>
        </div>
        </div>

        <input ref="fileInput" type="file" accept="image/*" class="sr-only" @change="onFileChange" />

        <!-- Одно фото на комментарий: превью до отправки, сжатие уже в браузере -->
        <div v-if="previewSrc" class="mt-2 flex items-center gap-3 rounded-[14px] border border-line bg-surface p-2">
            <img :src="previewSrc" alt="Выбранное фото" class="h-14 w-14 shrink-0 rounded-lg object-cover" />
            <p class="min-w-0 flex-1 truncate text-[0.875rem] text-muted">
                {{ processing ? "Готовим фото…" : fileName || "Фото из комментария" }}
            </p>
            <button
                type="button"
                class="shrink-0 text-[0.8125rem] text-muted transition-colors hover:text-accent"
                @click="removePhoto"
            >
                Убрать
            </button>
        </div>
        <button
            v-else-if="removingExisting && existingImageUrl"
            type="button"
            class="mt-2 text-[0.8125rem] text-muted transition-colors hover:text-accent"
            @click="$emit('update:removeImage', false)"
        >
            Вернуть фото
        </button>

        <div
            v-if="error || imageError || hint || cancelable"
            class="mt-2 flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1"
        >
            <InputError :message="error || imageError" />
            <div class="ml-auto flex flex-wrap items-baseline gap-x-4 gap-y-1">
                <button
                    v-if="cancelable"
                    type="button"
                    class="text-[0.9375rem] text-muted transition-colors hover:text-accent"
                    @click="$emit('cancel')"
                >
                    Отмена
                </button>
                <p v-if="hint" class="num text-[0.8125rem] text-muted">{{ hint }}</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, nextTick, ref, watch } from "vue";
import imageCompression from "browser-image-compression";
import InputError from "@/Components/InputError.vue";
import UserAvatar from "@/Components/UserAvatar.vue";

const props = defineProps({
    id: {
        type: String,
        required: true,
    },
    modelValue: {
        type: String,
        default: "",
    },
    label: {
        type: String,
        default: "Текст комментария",
    },
    placeholder: {
        type: String,
        default: "",
    },
    submitLabel: {
        type: String,
        default: "Отправить",
    },
    submitTitle: {
        type: String,
        default: "Отправить — Enter, новая строка — Shift + Enter",
    },
    loading: {
        type: Boolean,
        default: false,
    },
    error: {
        type: String,
        default: "",
    },
    hint: {
        type: String,
        default: "",
    },
    cancelable: {
        type: Boolean,
        default: false,
    },
    maxlength: {
        type: Number,
        default: 1000,
    },
    // Новый файл (File | null) и уже загруженное фото комментария при правке
    image: {
        type: Object,
        default: null,
    },
    existingImageUrl: {
        type: String,
        default: "",
    },
    removeImage: {
        type: Boolean,
        default: false,
    },
    imageError: {
        type: String,
        default: "",
    },
});

const emit = defineEmits(["update:modelValue", "update:image", "update:removeImage", "cancel"]);

const field = ref(null);
const fileInput = ref(null);
const processing = ref(false);
const localPreview = ref(null);
const fileName = ref("");

// Подсказки упоминаний: @username ищется по имени и логину
const mentionOpen = ref(false);
const mentionQuery = ref("");
const mentionStart = ref(-1);
const mentionResults = ref([]);
const mentionLoading = ref(false);
let mentionTimer = null;

const hasPhoto = computed(() => !!props.image);
const removingExisting = computed(() => !!props.removeImage && !props.image);
// Превью: новое фото важнее уже сохранённого
const previewSrc = computed(() => localPreview.value || (props.existingImageUrl && !props.removeImage ? props.existingImageUrl : ""));
const canSubmit = computed(
    () => (!!props.modelValue.trim() || hasPhoto.value || !!previewSrc.value) && !props.loading && !processing.value,
);

const clearSelection = () => {
    if (localPreview.value) URL.revokeObjectURL(localPreview.value);
    localPreview.value = null;
    fileName.value = "";
    if (fileInput.value) fileInput.value.value = "";
};

// Фото сжимаем до отправки: на сервер уходит лёгкий файл, итоговое WebP собирает ImageStore
const onFileChange = async (event) => {
    const file = event.target.files?.[0] ?? null;
    if (!file) return;

    processing.value = true;

    // Превью показываем сразу, сжатие идёт в фоне: выбор фото виден мгновенно
    if (localPreview.value) URL.revokeObjectURL(localPreview.value);
    localPreview.value = URL.createObjectURL(file);
    fileName.value = file.name;

    try {
        const compressed = await imageCompression(file, {
            maxWidthOrHeight: 1600,
            maxSizeMB: 1.5,
            useWebWorker: true,
            fileType: "image/webp",
        }).catch(() => file);

        emit("update:image", compressed);
        emit("update:removeImage", false);
    } finally {
        processing.value = false;
    }
};

const removePhoto = () => {
    if (props.image) {
        clearSelection();
        emit("update:image", null);
        return;
    }

    emit("update:removeImage", true);
};

// Родитель очищает файл после успешной отправки — убираем и превью
watch(
    () => props.image,
    (value) => {
        if (!value) clearSelection();
    },
);

// Поле растёт под многострочный текст, но не выше семи строк
const resize = () => {
    const el = field.value;
    if (!el) return;
    el.style.height = "auto";
    el.style.height = `${Math.min(el.scrollHeight, 160)}px`;
};

const onInput = (event) => {
    emit("update:modelValue", event.target.value);
    nextTick(resize);
    detectMention();
};

const closeMentions = () => {
    mentionOpen.value = false;
    mentionQuery.value = "";
    mentionStart.value = -1;
    mentionResults.value = [];
    mentionLoading.value = false;
    clearTimeout(mentionTimer);
};

const searchMentions = async (query) => {
    if (!query) {
        mentionResults.value = [];
        return;
    }

    mentionLoading.value = true;

    try {
        const response = await fetch(route("mentions.search", { q: query }), {
            headers: { Accept: "application/json" },
        });

        mentionResults.value = response.ok ? await response.json() : [];
    } catch {
        mentionResults.value = [];
    } finally {
        mentionLoading.value = false;
    }
};

const detectMention = () => {
    const el = field.value;
    if (!el) return;

    const before = el.value.slice(0, el.selectionStart ?? 0);
    const match = before.match(/(?:^|[\s(])@([a-z0-9_]{1,32})$/i);

    if (!match) {
        closeMentions();
        return;
    }

    mentionStart.value = before.lastIndexOf("@");
    mentionQuery.value = match[1];
    mentionOpen.value = true;
    clearTimeout(mentionTimer);
    mentionTimer = setTimeout(() => searchMentions(match[1]), 180);
};

const selectMention = (user) => {
    const el = field.value;
    if (!el || mentionStart.value < 0) return;

    const before = el.value.slice(0, mentionStart.value);
    const after = el.value.slice(el.selectionStart ?? el.value.length);
    const inserted = `@${user.username} `;
    const nextValue = before + inserted + after;
    const nextCaret = before.length + inserted.length;

    closeMentions();
    emit("update:modelValue", nextValue);

    nextTick(() => {
        el.value = nextValue;
        el.setSelectionRange(nextCaret, nextCaret);
        el.focus();
        resize();
    });
};

// Enter отправляет через ту же форму, что и стрелка; Shift + Enter — перенос строки.
// Живое значение поля читаем из DOM: ввод и Enter могут прийти до обновления пропса.
const onEnter = (event) => {
    if (event.isComposing || event.shiftKey) return;

    // Если открыты подсказки, Enter выбирает первого человека, а не отправляет комментарий
    if (mentionOpen.value && mentionResults.value.length) {
        event.preventDefault();
        selectMention(mentionResults.value[0]);
        return;
    }

    event.preventDefault();

    const el = field.value;
    if (!canSubmit.value) return;
    el?.form?.requestSubmit();
};

const focus = () => field.value?.focus();

watch(
    () => props.modelValue,
    (value) => {
        if (!value) {
            nextTick(() => {
                if (field.value) field.value.style.height = "";
            });
        }
    },
);

defineExpose({ focus });
</script>
