<!-- resources/js/Components/Comments/CommentInput.vue -->
<template>
    <div>
        <!-- Низкая полоска: рамка, поле в одну строку, стрелка появляется с вводом -->
        <div class="flex items-stretch border border-line-strong bg-surface transition-colors focus-within:border-accent">
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
                class="flex h-12 shrink-0 items-center justify-center self-end overflow-hidden bg-accent text-accent-contrast transition-all duration-150 hover:bg-accent-hover disabled:pointer-events-none"
                :class="canSubmit ? 'w-12 opacity-100' : 'w-0 opacity-0'"
            >
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 12h15m0 0l-6-6m6 6l-6 6" />
                </svg>
            </button>
        </div>

        <input ref="fileInput" type="file" accept="image/*" class="sr-only" @change="onFileChange" />

        <!-- Одно фото на комментарий: превью до отправки, сжатие уже в браузере -->
        <div v-if="previewSrc" class="mt-2 flex items-center gap-3 border border-line bg-surface p-2">
            <img :src="previewSrc" alt="Выбранное фото" class="h-14 w-14 shrink-0 object-cover" />
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
};

// Enter отправляет через ту же форму, что и стрелка; Shift + Enter — перенос строки.
// Живое значение поля читаем из DOM: ввод и Enter могут прийти до обновления пропса.
const onEnter = (event) => {
    if (event.isComposing || event.shiftKey) return;
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
