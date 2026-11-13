<!-- resources/js/Components/Editor/ImageCarouselNodeView.vue -->
<template>
    <NodeViewWrapper
        class="editor-carousel"
        :class="{ 'is-selected': selected }"
        contenteditable="false"
        data-testid="image-carousel-editor"
    >
        <!-- Горизонтальная лента: кадры в ряд, «+» последней плиткой -->
        <div ref="strip" class="editor-carousel__strip" role="group" aria-label="Карусель фотографий">
            <figure v-for="(src, index) in srcs" :key="`${src}-${index}`" class="editor-carousel__frame">
                <img :src="src" :alt="`Фото ${index + 1}`" draggable="false" class="editor-carousel__photo" />
                <button
                    type="button"
                    class="editor-carousel__remove"
                    :aria-label="`Убрать фото ${index + 1}`"
                    title="Убрать фото"
                    @click="removeAt(index)"
                >
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </figure>

            <button
                v-if="srcs.length < max"
                type="button"
                class="editor-carousel__add"
                aria-label="Добавить фото в карусель"
                title="Добавить фото"
                @click="pick"
            >
                <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7h14" />
                </svg>
                <span>Добавить фото</span>
            </button>
        </div>

        <input ref="input" type="file" accept="image/*" multiple style="display: none" tabindex="-1" @change="onFiles" />

        <!-- Служебная строка: сколько кадров, прокрутка, удаление всей карусели -->
        <div class="editor-carousel__bar">
            <span class="num">{{ srcs.length }} фото</span>
            <div class="editor-carousel__actions">
                <button
                    type="button"
                    class="editor-carousel__nav"
                    aria-label="Прокрутить ленту назад"
                    title="Назад"
                    @click="scrollBy(-1)"
                >
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <button
                    type="button"
                    class="editor-carousel__nav"
                    aria-label="Прокрутить ленту вперёд"
                    title="Вперёд"
                    @click="scrollBy(1)"
                >
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
                <button
                    type="button"
                    class="editor-carousel__nav editor-carousel__nav--danger"
                    aria-label="Удалить карусель"
                    title="Удалить карусель"
                    @click="deleteNode"
                >
                    <svg fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                        />
                    </svg>
                </button>
            </div>
        </div>

        <p v-if="notice" class="editor-carousel__notice">{{ notice }}</p>
    </NodeViewWrapper>
</template>

<script setup>
import { computed, onBeforeUnmount, ref } from "vue";
import { NodeViewWrapper } from "@tiptap/vue-3";
import { CAROUSEL_IMAGE_TYPES, MAX_CAROUSEL_IMAGES } from "@/Support/imageCarouselConfig";
import { createDraftImageUrl } from "@/Utilities/drafts";

const props = defineProps({
    editor: { type: Object, default: null },
    node: { type: Object, required: true },
    decorations: { type: Array, default: () => [] },
    selected: { type: Boolean, default: false },
    extension: { type: Object, default: null },
    getPos: { type: Function, default: null },
    updateAttributes: { type: Function, required: true },
    deleteNode: { type: Function, required: true },
});

const max = MAX_CAROUSEL_IMAGES;
const srcs = computed(() => props.node.attrs.srcs || []);
const strip = ref(null);
const input = ref(null);
const notice = ref("");
let noticeTimer = null;

const flash = (text) => {
    notice.value = text;
    clearTimeout(noticeTimer);
    noticeTimer = setTimeout(() => {
        notice.value = "";
    }, 4000);
};

const pick = () => input.value?.click();

// Новые кадры живут в редакторе как blob: и загружаются на сервер при сохранении
const onFiles = (event) => {
    const files = [...(event.target.files || [])].filter((file) => CAROUSEL_IMAGE_TYPES.includes(file.type));
    event.target.value = "";

    if (!files.length) {
        flash("Поддерживаются JPG, PNG или WebP");
        return;
    }

    const room = max - srcs.value.length;
    if (room <= 0) {
        flash(`В карусели максимум ${max} фото`);
        return;
    }

    if (files.length > room) {
        flash(`В карусель можно добавить не больше ${max} фото`);
    }

    const urls = files.slice(0, room).map((file) => createDraftImageUrl(file));
    props.updateAttributes({ srcs: [...srcs.value, ...urls] });
};

const removeAt = (index) => {
    const next = srcs.value.filter((_, i) => i !== index);

    if (!next.length) {
        props.deleteNode();
        return;
    }

    props.updateAttributes({ srcs: next });
};

const scrollBy = (direction) => {
    const el = strip.value;
    if (!el) return;

    const behavior = window.matchMedia("(prefers-reduced-motion: reduce)").matches ? "auto" : "smooth";
    el.scrollBy({ left: direction * Math.max(el.clientWidth * 0.8, 200), behavior });
};

onBeforeUnmount(() => clearTimeout(noticeTimer));
</script>
