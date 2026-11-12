<template>
    <div class="relative max-w-4xl mx-auto dark:bg-transparent">
        <!-- Черновик: при правке статьи не подменяем контент, а предлагаем восстановить -->
        <div
            v-if="pendingDraft || restoredDraft"
            class="mb-4 flex items-center justify-between gap-3 rounded-md border border-amber-300 bg-amber-50 px-4 py-3 dark:border-amber-500/30 dark:bg-amber-500/10"
        >
            <div class="flex items-center gap-2 text-sm text-amber-800 dark:text-amber-200">
                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>
                    <template v-if="pendingDraft">
                        Найден несохранённый черновик{{ pendingDraft.savedAt ? ` от ${formatSavedTime(pendingDraft.savedAt)}` : "" }}
                    </template>
                    <template v-else>
                        Восстановлен черновик{{ restoredDraft.savedAt ? ` от ${formatSavedTime(restoredDraft.savedAt)}` : "" }}
                    </template>
                </span>
            </div>
            <div class="flex shrink-0 items-center gap-2">
                <button
                    v-if="pendingDraft"
                    type="button"
                    class="rounded-md px-3 py-1.5 text-xs font-semibold text-amber-900 transition-colors hover:bg-amber-100 dark:text-amber-200 dark:hover:bg-amber-500/20"
                    @click="restorePendingDraft"
                >
                    Восстановить
                </button>
                <button
                    type="button"
                    class="rounded-md px-3 py-1.5 text-xs font-medium text-amber-800 transition-colors hover:bg-amber-100 dark:text-amber-300 dark:hover:bg-amber-500/20"
                    @click="pendingDraft ? discardPendingDraft() : discardRestoredDraft()"
                >
                    {{ pendingDraft ? "Удалить" : "Отменить" }}
                </button>
            </div>
        </div>

        <!-- Ошибка загрузки/ссылки -->
        <div
            v-if="editorError"
            class="mb-4 flex items-start justify-between gap-3 rounded-md border border-red-300 bg-red-50 px-4 py-3 dark:border-red-500/30 dark:bg-dark:hover:bg-red-500/10"
            role="alert"
        >
            <p class="text-sm text-red-800 dark:text-red-200">{{ editorError }}</p>
            <button
                type="button"
                class="rounded p-0.5 text-red-700 transition-colors hover:bg-red-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500 dark:text-red-300 red-500/20"
                aria-label="Скрыть сообщение"
                @click="editorError = ''"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- ФОРМА -->
        <div
            class="border rounded-md mb-6 bg-surface shadow-sm hover:shadow-md transition-shadow duration-300"
        >
            <input
                v-model="title"
                aria-label="Заголовок (необязательно)"
                placeholder="Заголовок (необязательно — можно просто текст)"
                class="mb-6 w-full rounded-t-md border-b border-line bg-surface-muted px-6 py-4 text-xl font-semibold text-content placeholder:text-muted focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/20 transition-colors duration-200"
            />
            <div class="p-6 border-t border-line">
                <div class="w-full">
                    <label class="text-lg font-semibold text-content mb-3 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                            />
                        </svg>
                        Категория
                    </label>
                    <select
                        v-model="selectedCategory"
                        class="w-full p-3 border border-line-strong rounded-md focus:ring-2 focus:ring-accent focus:border-accent transition-all duration-200 appearance-none bg-surface-muted bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTYiIGhlaWdodD0iMTYiIHZpZXdCb3g9IjAgMCAxNiAxNiIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTQgNkw4IDEwTDEyIDYiIHN0cm9rZT0iIzljYTBhZiIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiIHN0cm9rZS1saW5lam9pbj0icm91bmQiLz4KPC9zdmc+')] bg-no-repeat bg-[center_right_1rem]"
                    >
                        <option disabled :value="null" class="text-muted">Выберите категорию</option>
                        <option
                            v-for="categoryOption in categoryOptions"
                            :key="categoryOption.slug"
                            :value="categoryOption.slug"
                            class=""
                        >
                            {{ categoryOption.name }}
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <!-- ПАНЕЛЬ ИНСТРУМЕНТОВ -->
        <div
            v-if="editor"
            ref="toolbarRef"
            class="editor-toolbar bg-surface flex flex-nowrap items-center overflow-x-auto rounded-md border mb-6 p-2 shadow-sm sticky top-0 z-10 backdrop-blur-sm bg-surface/95 [&>*]:shrink-0 md:flex-wrap md:overflow-visible md:p-3"
            :style="toolbarStyle"
        >
            <!-- Группа форматирования текста -->
            <div class="flex items-center border-r border-line pr-3 mr-3">
                <button
                    type="button"
                    @click="editor.chain().focus().toggleBold().run()"
                    :disabled="!editor.can().chain().focus().toggleBold().run()"
                    :class="[ editor.isActive('bold') ? 'bg-accent-soft text-accent ' : 'hover:bg-surface-muted text-content', 'p-2 rounded-md', ]"
                    class="tooltip"
                    data-tooltip="Жирный (Ctrl+B)"
                >
                    <BoldIcon class="w-5 h-5" />
                </button>
                <button
                    type="button"
                    @click="editor.chain().focus().toggleItalic().run()"
                    :disabled="
                        !editor.can().chain().focus().toggleItalic().run()
                    "
                    :class="[ editor.isActive('italic') ? 'bg-accent-soft text-accent ' : 'hover:bg-surface-muted text-content', 'p-2 rounded-md', ]"
                    class="tooltip"
                    data-tooltip="Курсив (Ctrl+I)"
                >
                    <ItalicIcon class="w-5 h-5" />
                </button>
                <button
                    type="button"
                    @click="editor.chain().focus().toggleUnderline().run()"
                    :class="[ editor.isActive('underline') ? 'bg-accent-soft text-accent ' : 'hover:bg-surface-muted text-content', 'p-2 rounded-md', ]"
                    class="tooltip"
                    data-tooltip="Подчеркивание"
                >
                    <UnderlineIcon class="w-5 h-5" />
                </button>
            </div>

            <!-- Группа заголовков -->
            <div class="flex items-center border-r border-line pr-3 mr-3">
                <button
                    type="button"
                    @click="editor.chain().focus().toggleHeading({ level: 1 }).run()"
                    :class="[ editor.isActive('heading', { level: 1 }) ? 'bg-accent-soft text-accent ' : 'hover:bg-surface-muted text-content', 'px-3 py-2 rounded-md font-bold', ]"
                    class="tooltip"
                    data-tooltip="Заголовок 1"
                >
                    H1
                </button>
                <button
                    type="button"
                    @click="editor.chain().focus().toggleHeading({ level: 2 }).run()"
                    :class="[ editor.isActive('heading', { level: 2 }) ? 'bg-accent-soft text-accent ' : 'hover:bg-surface-muted text-content', 'px-3 py-2 rounded-md font-semibold', ]"
                    class="tooltip"
                    data-tooltip="Заголовок 2"
                >
                    H2
                </button>
                <button
                    type="button"
                    @click="editor.chain().focus().toggleHeading({ level: 3 }).run()"
                    :class="[ editor.isActive('heading', { level: 3 }) ? 'bg-accent-soft text-accent ' : 'hover:bg-surface-muted text-content', 'px-3 py-2 rounded-md font-semibold', ]"
                    class="tooltip"
                    data-tooltip="Заголовок 3"
                >
                    H3
                </button>
            </div>

            <!-- Группа выравнивания -->
            <div class="flex items-center border-r border-line pr-3 mr-3">
                <button
                    type="button"
                    @click="editor.chain().focus().toggleTextAlign('left').run()"
                    :class="[ editor.isActive({ textAlign: 'left' }) ? 'bg-accent-soft text-accent ' : 'hover:bg-surface-muted text-content', 'p-2 rounded-md', ]"
                    class="tooltip"
                    data-tooltip="Выровнять по левому краю"
                >
                    <AlignLeftIcon class="w-5 h-5" />
                </button>
                <button
                    type="button"
                    @click="editor.chain().focus().toggleTextAlign('center').run()"
                    :class="[ editor.isActive({ textAlign: 'center' }) ? 'bg-accent-soft text-accent ' : 'hover:bg-surface-muted text-content', 'p-2 rounded-md', ]"
                    class="tooltip"
                    data-tooltip="Выровнять по центру"
                >
                    <AlignCenterIcon class="w-5 h-5" />
                </button>
                <button
                    type="button"
                    @click="editor.chain().focus().toggleTextAlign('right').run()"
                    :class="[ editor.isActive({ textAlign: 'right' }) ? 'bg-accent-soft text-accent ' : 'hover:bg-surface-muted text-content', 'p-2 rounded-md', ]"
                    class="tooltip"
                    data-tooltip="Выровнять по правому краю"
                >
                    <AligRightIcon class="w-5 h-5" />
                </button>
                <button
                    type="button"
                    @click="editor.chain().focus().toggleTextAlign('justify').run()"
                    :class="[ editor.isActive({ textAlign: 'justify' }) ? 'bg-accent-soft text-accent ' : 'hover:bg-surface-muted text-content', 'p-2 rounded-md', ]"
                    class="tooltip"
                    data-tooltip="Выровнять по ширине"
                >
                    <AlignJustifyIcon class="w-5 h-5" />
                </button>
            </div>

            <!-- Группа списков -->
            <div class="flex items-center border-r border-line pr-3 mr-3">
                <button
                    type="button"
                    @click="editor.chain().focus().toggleBulletList().run()"
                    :class="[ editor.isActive('bulletList') ? 'bg-accent-soft text-accent ' : 'hover:bg-surface-muted text-content', 'p-2 rounded-md', ]"
                    class="tooltip"
                    data-tooltip="Маркированный список"
                >
                    <BulletListIcon class="w-5 h-5" />
                </button>
                <button
                    type="button"
                    @click="editor.chain().focus().toggleOrderedList().run()"
                    :class="[ editor.isActive('orderedList') ? 'bg-accent-soft text-accent ' : 'hover:bg-surface-muted text-content', 'p-2 rounded-md', ]"
                    class="tooltip"
                    data-tooltip="Нумерованный список"
                >
                    <OrderedListIcon class="w-5 h-5" />
                </button>
            </div>

            <!-- Группа дополнительных элементов -->
            <div class="flex items-center gap-1">
                <div class="relative">
                    <button
                        type="button"
                        :class="[mentionOpen ? 'bg-accent-soft text-accent' : 'hover:bg-surface-muted text-content', 'p-2 rounded-md']"
                        class="tooltip"
                        data-tooltip="Упомянуть пользователя"
                        @click="toggleMentionPicker"
                    >
                        <AtIcon class="w-5 h-5" />
                    </button>

                    <div
                        v-if="mentionOpen"
                        class="absolute left-0 top-full z-30 mt-2 w-[min(22rem,80vw)] overflow-hidden rounded-[14px] border border-line bg-surface shadow-lg"
                    >
                        <div class="border-b border-line p-2">
                            <input
                                ref="mentionInput"
                                v-model="mentionQuery"
                                type="text"
                                placeholder="Имя или @username"
                                aria-label="Найти пользователя для упоминания"
                                class="h-10 w-full rounded-[10px] border border-line-strong bg-surface px-3 text-sm text-content placeholder:text-muted focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/20"
                                @input="searchMentions"
                                @keydown.esc="mentionOpen = false"
                            />
                        </div>
                        <p v-if="mentionLoading" class="px-4 py-3 text-sm text-muted">Ищу людей…</p>
                        <p v-else-if="mentionQuery && !mentionResults.length" class="px-4 py-3 text-sm text-muted">
                            Никого не нашли
                        </p>
                        <ul v-else-if="mentionResults.length" class="max-h-64 overflow-y-auto py-1">
                            <li v-for="user in mentionResults" :key="user.id">
                                <button
                                    type="button"
                                    class="flex w-full items-center gap-3 px-3 py-2 text-left transition-colors hover:bg-surface-muted"
                                    @click="insertMention(user)"
                                >
                                    <UserAvatar :user="user" :size="30" />
                                    <span class="min-w-0">
                                        <span class="block truncate text-[0.9375rem] font-medium text-content">{{ user.name }}</span>
                                        <span class="block truncate text-[0.8125rem] text-muted">@{{ user.username }}</span>
                                    </span>
                                </button>
                            </li>
                        </ul>
                        <p v-else class="px-4 py-3 text-sm text-muted">Начните вводить имя или логин.</p>
                    </div>
                </div>

                <button
                    type="button"
                    @click="setLink"
                    :class="[ editor.isActive('link') ? 'bg-accent-soft text-accent ' : 'hover:bg-surface-muted text-content', 'p-2 rounded-md', ]"
                    class="tooltip"
                    data-tooltip="Добавить ссылку"
                >
                    <LinkIcon class="w-5 h-5" />
                </button>
                <button
                    type="button"
                    @click="editor.chain().focus().toggleBlockquote().run()"
                    :class="[ editor.isActive('blockquote') ? 'bg-accent-soft text-accent ' : 'hover:bg-surface-muted text-content', 'p-2 rounded-md', ]"
                    class="tooltip"
                    data-tooltip="Цитата"
                >
                    <BlockquoteIcon class="w-5 h-5" />
                </button>
                <button
                    type="button"
                    @click="editor.chain().focus().toggleCodeBlock().run()"
                    :class="[ editor.isActive('codeBlock') ? 'bg-accent-soft text-accent ' : 'hover:bg-surface-muted text-content', 'p-2 rounded-md', ]"
                    class="tooltip"
                    data-tooltip="Блок кода"
                >
                    <CodeIcon class="w-5 h-5" />
                </button>
                <select
                    v-model="codeLanguage"
                    :disabled="!inCodeBlock"
                    aria-label="Язык блока кода"
                    class="ml-1 max-w-[8.5rem] rounded-md border border-line bg-surface px-2 py-1.5 text-xs text-content transition-colors focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/20 disabled:cursor-not-allowed disabled:opacity-45"
                    @change="setCodeLanguage"
                >
                    <option value="">Без подсветки</option>
                    <option v-for="language in codeLanguages" :key="language.value" :value="language.value">
                        {{ language.label }}
                    </option>
                </select>
                <button
                    type="button"
                    @click="editor.chain().focus().setHorizontalRule().run()"
                    :class="'hover:bg-surface-muted text-content p-2 rounded-md'"
                    class="tooltip"
                    data-tooltip="Горизонтальная линия"
                >
                    <HorizontalRuleIcon class="w-5 h-5" />
                </button>
                <button
                    type="button"
                    @click="addImage"
                    :class="'hover:bg-surface-muted text-content p-2 rounded-md'"
                    class="tooltip"
                    data-tooltip="Добавить изображение"
                >
                    <ImageIcon class="w-5 h-5" />
                </button>
                <button
                    type="button"
                    @click="addGallery"
                    :class="'hover:bg-surface-muted text-content p-2 rounded-md'"
                    class="tooltip"
                    data-tooltip="Карусель фото"
                >
                    <CarouselIcon class="w-5 h-5" />
                </button>
                <button
                    type="button"
                    @click="openVideoModal"
                    :class="'hover:bg-surface-muted text-content p-2 rounded-md'"
                    class="tooltip"
                    data-tooltip="Вставить видео"
                >
                    <MoviePlayOutlineIcon class="w-5 h-5" />
                </button>
            </div>

            <span class="ml-auto hidden text-[11px] font-normal text-muted md:inline">
                Черновик сохраняется автоматически (Ctrl+S)
            </span>
        </div>

        <!-- Панель стала fixed при открытой клавиатуре — держим её место в потоке -->
        <div v-if="toolbarSpacerHeight" :style="{ height: `${toolbarSpacerHeight}px` }" aria-hidden="true"></div>

        <!-- BUBBLE МЕНЮ -->
        <bubble-menu
            v-if="editor"
            :editor="editor"
            :should-show="showBubbleMenu"
            :tippy-options="{ duration: 100, maxWidth: 'none' }"
            class="bubble-menu"
        >
            <div
                class="flex items-center bg-surface rounded-md shadow-lg border border-line p-2 backdrop-blur-sm bg-surface/95"
            >
                <button
                    type="button"
                    @click="editor.chain().focus().toggleBold().run()"
                    :class="[ editor.isActive('bold') ? 'bg-accent-soft text-accent ' : 'hover:bg-surface-muted text-content', 'p-2 rounded-md transition-all duration-200', ]"
                >
                    <BoldIcon class="w-4 h-4" />
                </button>
                <button
                    type="button"
                    @click="editor.chain().focus().toggleItalic().run()"
                    :class="[ editor.isActive('italic') ? 'bg-accent-soft text-accent ' : 'hover:bg-surface-muted text-content', 'p-2 rounded-md transition-all duration-200', ]"
                >
                    <ItalicIcon class="w-4 h-4" />
                </button>
                <button
                    type="button"
                    @click="editor.chain().focus().toggleUnderline().run()"
                    :class="[ editor.isActive('underline') ? 'bg-accent-soft text-accent ' : 'hover:bg-surface-muted text-content', 'p-2 rounded-md transition-all duration-200', ]"
                >
                    <UnderlineIcon class="w-4 h-4" />
                </button>
                <div class="w-px h-6 bg-surface-muted mx-1"></div>
                <button
                    type="button"
                    @click="setLink"
                    :class="[ editor.isActive('link') ? 'bg-accent-soft text-accent ' : 'hover:bg-surface-muted text-content', 'p-2 rounded-md transition-all duration-200', ]"
                >
                    <LinkIcon class="w-4 h-4" />
                </button>
            </div>
        </bubble-menu>

        <!-- РЕДАКТОР -->
        <div class="relative">
            <editor-content :editor="editor" class="editor-content" />
            <div v-if="editor && !hasContent" class="absolute top-4 left-4 pointer-events-none text-muted text-lg">
                Начните писать вашу статью...
            </div>
        </div>

        <!-- Вставка внешнего видео -->
        <Modal :show="videoModalOpen" max-width="md" centered blur @close="closeVideoModal">
            <form class="p-6" @submit.prevent="insertVideo">
                <h2 class="text-xl font-semibold text-content">Вставить видео</h2>
                <p class="mt-2 text-sm leading-relaxed text-muted">
                    Вставьте ссылку на видео YouTube, Rutube или VK Video. Видео останется на внешней площадке.
                </p>

                <label for="video-embed-url" class="mt-5 block text-sm font-medium text-content">Ссылка на видео</label>
                <input
                    id="video-embed-url"
                    ref="videoInput"
                    v-model="videoUrl"
                    type="url"
                    inputmode="url"
                    autocomplete="off"
                    placeholder="https://youtube.com/watch?v=..."
                    class="mt-2 h-11 w-full rounded-md border border-line-strong bg-surface px-3 text-sm text-content placeholder:text-muted focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/20"
                    @input="videoError = ''"
                />
                <p v-if="videoError" class="mt-2 text-sm text-red-600 dark:text-red-400" role="alert">{{ videoError }}</p>
                <p class="mt-2 text-xs text-muted">Поддерживаются YouTube, Rutube и VK Video.</p>

                <div class="mt-6 flex justify-end gap-2.5">
                    <button
                        type="button"
                        class="rounded border border-line-strong bg-surface px-4 py-2 text-sm font-semibold text-content transition-colors hover:bg-surface-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2"
                        @click="closeVideoModal"
                    >
                        Отмена
                    </button>
                    <button
                        type="submit"
                        class="rounded bg-accent px-4 py-2 text-sm font-semibold text-accent-contrast shadow-sm transition-colors hover:bg-accent-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2"
                    >
                        Вставить
                    </button>
                </div>
            </form>
        </Modal>
    </div>
</template>

<script setup>
import { BubbleMenu, Editor, EditorContent } from "@tiptap/vue-3";
import StarterKit from "@tiptap/starter-kit";
import { CODE_LANGUAGES } from "@/Support/codeLanguages";
import Image from "@tiptap/extension-image";
import ImageCarousel from "@/Utilities/imageCarouselNode";
import VideoEmbed from "@/Utilities/videoEmbedNode";
import { CAROUSEL_IMAGE_TYPES, MAX_CAROUSEL_IMAGES } from "@/Support/imageCarouselConfig";
import { parseVideoUrl } from "@/Support/videoEmbed";
import Heading from "@tiptap/extension-heading";
import Underline from "@tiptap/extension-underline";
import Paragraph from "@tiptap/extension-paragraph";
import Link from "@tiptap/extension-link";
import TextAlign from "@tiptap/extension-text-align";
import BoldIcon from "vue-material-design-icons/FormatBold.vue";
import ItalicIcon from "vue-material-design-icons/FormatItalic.vue";
import UnderlineIcon from "vue-material-design-icons/FormatUnderline.vue";
import BlockquoteIcon from "vue-material-design-icons/FormatQuoteClose.vue";
import BulletListIcon from "vue-material-design-icons/FormatListBulleted.vue";
import OrderedListIcon from "vue-material-design-icons/FormatListNumbered.vue";
import CodeIcon from "vue-material-design-icons/CodeTags.vue";
import HorizontalRuleIcon from "vue-material-design-icons/Minus.vue";
import AlignCenterIcon from "vue-material-design-icons/FormatAlignCenter.vue";
import AlignJustifyIcon from "vue-material-design-icons/FormatAlignJustify.vue";
import AlignLeftIcon from "vue-material-design-icons/FormatAlignLeft.vue";
import AligRightIcon from "vue-material-design-icons/FormatAlignRight.vue";
import ImageIcon from "vue-material-design-icons/Image.vue";
import CarouselIcon from "vue-material-design-icons/ViewCarousel.vue";
import MoviePlayOutlineIcon from "vue-material-design-icons/MoviePlayOutline.vue";
import LinkIcon from "vue-material-design-icons/LinkVariant.vue";
import AtIcon from "vue-material-design-icons/At.vue";
import UserAvatar from "@/Components/UserAvatar.vue";
import Modal from "@/Components/Modal.vue";
import { nextTick, onMounted, onBeforeUnmount, ref, watch } from "vue";
import { MAX_DRAFT_SIZE, readDraft, removeDraft, writeDraft } from "@/Utilities/drafts";

const props = defineProps({
    initialTitle: String,
    initialContent: String,
    initialCardCategory: String,
    draftKey: {
        type: String,
        default: "default",
    },
});

const emit = defineEmits(["save"]);

// Реактивные переменные
const title = ref(props.initialTitle || "");
const editor = ref(null);
const selectedCategory = ref(props.initialCardCategory || "");

// Язык блока кода: селект живёт рядом с кнопкой и активен только внутри блока
const codeLanguage = ref("");
const inCodeBlock = ref(false);
const codeLanguages = CODE_LANGUAGES;

const syncCodeLanguage = () => {
    if (!editor.value) return;

    inCodeBlock.value = editor.value.isActive("codeBlock");
    codeLanguage.value = editor.value.getAttributes("codeBlock").language || "";
};

const setCodeLanguage = () => {
    if (!editor.value?.isActive("codeBlock")) return;

    editor.value.chain().focus().updateAttributes("codeBlock", { language: codeLanguage.value || null }).run();
};

const hasContent = ref(false);
const isDirty = ref(false);
const restoredDraft = ref(null);
const pendingDraft = ref(null);
// Черновики пишем только после инициализации: иначе установка полей при монтировании
// сама создаёт «черновик» поверх сохранённой статьи.
let draftTracking = false;
const editorError = ref("");
let editorErrorTimer = null;

// Вставка упоминания через поиск: в HTML попадает обычная ссылка с классом mention
const mentionOpen = ref(false);
const mentionQuery = ref("");
const mentionResults = ref([]);
const mentionLoading = ref(false);
const mentionInput = ref(null);
let mentionTimer = null;

// Вставка видео с внешних площадок
const videoModalOpen = ref(false);
const videoUrl = ref("");
const videoError = ref("");
const videoInput = ref(null);

const showError = (message) => {
    editorError.value = message;
    clearTimeout(editorErrorTimer);
    editorErrorTimer = setTimeout(() => {
        editorError.value = "";
    }, 6000);
};

function escapeRegExp(string) {
    return string.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
}

function debounce(ms, fn) {
    let timer;
    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => fn(...args), ms);
    };
}

// ---------- Черновики (LocalStorage) ----------
const loadDraft = () => readDraft(props.draftKey);

const clearDraft = () => {
    removeDraft(props.draftKey);
    isDirty.value = false;
    restoredDraft.value = null;
};

const currentSnapshot = () =>
    JSON.stringify({
        title: title.value || "",
        category: selectedCategory.value || "",
        content: editor.value ? editor.value.getHTML() : props.initialContent || "",
    });

const initialSnapshot = () =>
    JSON.stringify({
        title: props.initialTitle || "",
        category: props.initialCardCategory || "",
        content: props.initialContent || "",
    });

const saveDraft = debounce(700, () => {
    if (!editor.value) return;

    if (currentSnapshot() === initialSnapshot()) {
        clearDraft();
        return;
    }

    const html = editor.value.getHTML();
    if (html.length > MAX_DRAFT_SIZE) {
        // Не пишем огромный контент в localStorage (может выпасть квота),
        // но продолжаем держать его в памяти редактора.
        return;
    }

    writeDraft(props.draftKey, {
        title: title.value,
        category: selectedCategory.value,
        content: html,
        savedAt: Date.now(),
    });
});

const queueDraftSave = () => {
    if (!editor.value || !draftTracking) return;
    isDirty.value = true;
    saveDraft();
};

// Полный сброс: удаляем сохранённый черновик и возвращаем поля к исходным
// (для новой статьи — к пустым, для редактирования — к сохранённой версии).
const resetDraft = () => {
    title.value = props.initialTitle || "";
    selectedCategory.value = props.initialCardCategory || "";
    editor.value?.commands.setContent(props.initialContent || "", false);
    syncHasContent();
    clearDraft();
};

const discardRestoredDraft = () => {
    resetDraft();
};

// Черновик при правке статьи: контент статьи не трогаем, пока пользователь не согласится
const restorePendingDraft = () => {
    if (!pendingDraft.value || !editor.value) return;

    title.value = pendingDraft.value.title || "";
    selectedCategory.value = pendingDraft.value.category || "";
    editor.value.commands.setContent(pendingDraft.value.content || "", false);
    syncHasContent();

    restoredDraft.value = { savedAt: pendingDraft.value.savedAt };
    pendingDraft.value = null;
    isDirty.value = true;
};

const discardPendingDraft = () => {
    removeDraft(props.draftKey);
    pendingDraft.value = null;
};

const formatSavedTime = (ts) =>
    new Date(ts).toLocaleTimeString("ru-RU", {
        hour: "2-digit",
        minute: "2-digit",
    });

// Следим за полями — любое изменение помечаем «изменено» и сохраняем черновик
watch([title, selectedCategory], () => {
    queueDraftSave();
});

// ---------- Инициализация редактора ----------
const syncHasContent = () => {
    if (!editor.value) {
        hasContent.value = false;
        return;
    }

    const html = editor.value.getHTML();
    hasContent.value =
        editor.value.state.doc.textContent.trim().length > 0 ||
        html.includes("<img") ||
        html.includes("video-embed");
};

const handleKeyDown = (_view, event) => {
    if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === "s") {
        event.preventDefault();
        if (!isDirty.value) return true;
        emit("save");
        return true;
    }
    return false;
};

onMounted(() => {
    const draft = loadDraft();
    const draftContent = draft?.content || "";
    // Правка существующей статьи: источник правды — сохранённый текст,
    // черновик лишь предлагаем восстановить кнопкой.
    const editingExisting = !!(props.initialContent || "").trim();
    const restoredContent = editingExisting ? props.initialContent || "" : draftContent || props.initialContent || "";

    if (editingExisting) {
        if (draftContent && draftContent !== (props.initialContent || "")) {
            pendingDraft.value = {
                savedAt: draft.savedAt || null,
                title: draft.title,
                category: draft.category,
                content: draftContent,
            };
        }
    } else if (draftContent) {
        restoredDraft.value = { savedAt: draft.savedAt || Date.now() };
        isDirty.value = true;
    }

    editor.value = new Editor({
        extensions: [
            StarterKit,
            // StarterKit уже включает заголовки; переопределяем конфигурацию.
            Heading.configure({
                levels: [1, 2, 3],
                HTMLAttributes: {
                    class: "heading text-content",
                },
            }),
            Paragraph.configure({
                HTMLAttributes: {
                    class: "text-content font-[Inter] leading-relaxed text-base",
                },
            }),
            Underline,
            Link.configure({
                openOnClick: true,
                defaultProtocol: "https",
                protocols: ["http", "https", "mailto", "tel", "#"],
                HTMLAttributes: {
                    target: "_blank",
                    rel: "noopener noreferrer",
                    class: "text-accent hover:text-accent-hover dark:hover:text-accent cursor-pointer underline",
                },
            }),
            TextAlign.configure({
                types: ["heading", "paragraph"],
            }),
            Image.configure({
                HTMLAttributes: {
                    class: "my-image rounded-md shadow-md max-w-full h-auto my-4 mx-auto",
                },
                allowBase64: true,
            }),
            ImageCarousel,
            VideoEmbed,
        ],
        content: restoredContent,
        editorProps: {
            attributes: {
                class: "prose prose-lg dark:prose-invert min-h-[600px] max-w-none mx-auto focus:outline-none p-6 border-2 border-dashed border-line  rounded-md bg-surface  shadow-sm hover:border-accent transition-colors duration-300",
            },
            handleKeyDown,
            handleDrop: (view, event, _slice, moved) => handleImageDrop(view, event, moved),
            handlePaste: (_view, event) => handleImagePaste(event),
        },
    });

    // Восстанавливаем значения полей: при правке — из статьи, при создании — из черновика
    title.value = editingExisting
        ? props.initialTitle || ""
        : (draft?.title !== undefined ? draft.title : "") || props.initialTitle || "";
    selectedCategory.value = editingExisting
        ? props.initialCardCategory || ""
        : (draft?.category !== undefined ? draft.category : "") || props.initialCardCategory || "";

    editor.value.on("update", () => {
        syncHasContent();
        syncCodeLanguage();
        queueDraftSave();
    });

    editor.value.on("selectionUpdate", syncCodeLanguage);
    syncCodeLanguage();

    // Упрощаем вставку из Word/PDF: сбрасываем скрытое форматирование,
    // оставляя только осмысленные структуры (заголовки, списки т.п.)
    editor.value.on("paste", (_event, _slice, marksInRange) => {
        // Tiptap контролирует пастинг через schema — Word-мусор уже отсекается.
        // Здесь ничего не делаем, но оставляем обработчик для будущих проверок.
        void marksInRange;
    });

    syncHasContent();

    // Установка полей при монтировании — не повод писать черновик
    nextTick(() => {
        draftTracking = true;
    });

    const handleBeforeUnload = (e) => {
        if (isDirty.value && currentSnapshot() !== initialSnapshot()) {
            e.preventDefault();
            e.returnValue = "";
        }
    };
    window.addEventListener("beforeunload", handleBeforeUnload);
    window.__tiptapBeforeUnload = handleBeforeUnload;

    // Панель редактора держим в видимой области, когда открыта клавиатура
    syncToolbarPosition();
    window.visualViewport?.addEventListener("resize", syncToolbarPosition);
    window.visualViewport?.addEventListener("scroll", syncToolbarPosition);
    window.addEventListener("resize", syncToolbarPosition);
    window.addEventListener("orientationchange", syncToolbarPosition);
});

// Панель появляется вместе с редактором — привязываемся к ней, как только он готов
watch(editor, (instance) => {
    if (instance) {
        nextTick(syncToolbarPosition);
    }
});

// Очистка
onBeforeUnmount(() => {
    window.visualViewport?.removeEventListener("resize", syncToolbarPosition);
    window.visualViewport?.removeEventListener("scroll", syncToolbarPosition);
    window.removeEventListener("resize", syncToolbarPosition);
    window.removeEventListener("orientationchange", syncToolbarPosition);
    toolbarResizeObserver?.disconnect();
    toolbarResizeObserver = null;

    const handler = window.__tiptapBeforeUnload;
    if (handler) {
        window.removeEventListener("beforeunload", handler);
        delete window.__tiptapBeforeUnload;
    }
    editor.value?.destroy();
});

const categoryOptions = ref([
    { name: "Игры", slug: "games" },
    { name: "Красота и стиль", slug: "beauty" },
    { name: "Еда", slug: "food" },
    { name: "Наука", slug: "science" },
    { name: "Спорт", slug: "sport" },
    { name: "Технологии", slug: "tech" },
    { name: "Путешествия", slug: "travel" },
    { name: "Искусство", slug: "art" },
]);

// На тач-устройствах всплывающее меню накладывается на системное меню
// выделения («Копировать/Вырезать»), а форматирование доступно в верхней панели.
const showBubbleMenu = () => {
    if (typeof window === "undefined" || typeof window.matchMedia !== "function") {
        return true;
    }

    return !window.matchMedia("(pointer: coarse)").matches;
};

const isCoarsePointer = () =>
    typeof window !== "undefined" &&
    typeof window.matchMedia === "function" &&
    window.matchMedia("(pointer: coarse)").matches;

// Клавиатура на телефоне уменьшает visual viewport, а не layout: sticky-панель
// остаётся выше видимой области. Пока клавиатура открыта, переносим панель
// в fixed и привязываем к верху экрана.
const toolbarRef = ref(null);
const toolbarStyle = ref({});
const toolbarSpacerHeight = ref(0);

const KEYBOARD_GAP = 80;

let toolbarResizeObserver = null;

const observeToolbar = () => {
    if (toolbarResizeObserver || typeof ResizeObserver === "undefined" || !toolbarRef.value) {
        return;
    }

    toolbarResizeObserver = new ResizeObserver(() => syncToolbarPosition());
    toolbarResizeObserver.observe(toolbarRef.value);
};

const syncToolbarPosition = () => {
    const element = toolbarRef.value;
    const viewport = typeof window !== "undefined" ? window.visualViewport : null;

    if (!element || !viewport || !isCoarsePointer()) {
        toolbarStyle.value = {};
        toolbarSpacerHeight.value = 0;
        return;
    }

    observeToolbar();

    const keyboardOpen = viewport.height < window.innerHeight - KEYBOARD_GAP || viewport.offsetTop > 0;

    if (!keyboardOpen) {
        toolbarStyle.value = {};
        toolbarSpacerHeight.value = 0;
        return;
    }

    // Высота панели + её mb-6 (1.5rem), чтобы контент не прыгал под панель
    toolbarSpacerHeight.value = Math.round(element.offsetHeight + 24);

    // Панель на всю ширину экрана: скругления и боковые границы тут лишние.
    // top: 0 — браузеры сами держат fixed у верха видимой части экрана.
    toolbarStyle.value = {
        position: "fixed",
        top: "0px",
        left: "0px",
        right: "0px",
        zIndex: 50,
        marginBottom: "0px",
        borderRadius: "0px",
        borderLeftWidth: "0px",
        borderRightWidth: "0px",
    };
};

const toggleMentionPicker = async () => {
    mentionOpen.value = !mentionOpen.value;

    if (!mentionOpen.value) return;

    mentionQuery.value = "";
    mentionResults.value = [];
    await nextTick();
    mentionInput.value?.focus();
};

const searchMentions = () => {
    clearTimeout(mentionTimer);
    const query = mentionQuery.value.trim();

    if (!query) {
        mentionResults.value = [];
        mentionLoading.value = false;
        return;
    }

    mentionTimer = setTimeout(async () => {
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
    }, 180);
};

const insertMention = (user) => {
    if (!editor.value) return;

    editor.value
        .chain()
        .focus()
        .insertContent(
            `<a href="${route("authors.byUsername", user.username)}" class="mention">@${user.username}</a> `,
        )
        .run();

    mentionOpen.value = false;
    mentionQuery.value = "";
    mentionResults.value = [];
};

const setLink = () => {
    const previousUrl = editor.value.getAttributes("link").href || "";
    const url = window.prompt("Введите URL ссылки:", previousUrl);

    if (url === null) {
        return;
    }

    if (url === "") {
        editor.value.chain().focus().extendMarkRange("link").unsetLink().run();
        return;
    }

    // Блокируем опасные схемы (javascript:, data:, vbscript:)
    const trimmed = url.trim();
    if (/^(javascript|vbscript|data):/i.test(trimmed)) {
        showError("Недопустимый тип ссылки.");
        return;
    }

    // Нормализуем URL: схемы (http(s):, mailto:, tel: и т.п.),
    // относительные пути якоря оставляем как есть.
    // Голый домен без схемы получает https://.
    const finalUrl =
        /^[a-z][a-z0-9+.\-]*:/i.test(trimmed) ||
        trimmed.startsWith("/") ||
        trimmed.startsWith("#") ||
        trimmed.startsWith("?") ||
        trimmed.startsWith("//")
            ? trimmed
            : `https://${trimmed}`;

    editor.value.chain().focus().extendMarkRange("link").setLink({ href: finalUrl }).run();
};

const MAX_IMAGE_SIZE = 2 * 1024 * 1024;
const ALLOWED_IMAGE_TYPES = CAROUSEL_IMAGE_TYPES;

// Новые фото живут в редакторе как blob: и уходят на сервер при сохранении
const imageUrlsFromFiles = (files) => {
    const urls = [];

    for (const file of files) {
        if (!ALLOWED_IMAGE_TYPES.includes(file.type)) {
            showError("Поддерживаются JPG, PNG или WebP");
            continue;
        }

        if (file.size > MAX_IMAGE_SIZE) {
            showError("Максимальный размер файла - 2MB");
            continue;
        }

        urls.push(URL.createObjectURL(file));
    }

    return urls;
};

const imageFilesFrom = (dataTransfer) => [...(dataTransfer?.files || [])].filter((file) => file.type.startsWith("image/"));

const insertImageUrls = (urls, pos = null) => {
    if (!urls.length || !editor.value) return;

    const content = urls.map((src) => ({ type: "image", attrs: { src } }));
    const chain = editor.value.chain().focus();

    (pos === null ? chain.insertContent(content) : chain.insertContentAt(pos, content)).run();
};

// Добавление изображения в content
const addImage = () => {
    const input = document.createElement("input");
    input.type = "file";
    input.accept = "image/*";

    input.onchange = (e) => {
        insertImageUrls(imageUrlsFromFiles([...(e.target.files || [])]));
    };

    input.click();
};

// Карусель: два и больше фото, максимум 8
const addGallery = () => {
    const input = document.createElement("input");
    input.type = "file";
    input.accept = "image/*";
    input.multiple = true;

    input.onchange = (e) => {
        const files = [...(e.target.files || [])];

        if (files.length < 2) {
            showError("Для карусели нужно минимум два фото");
            return;
        }

        if (files.length > MAX_CAROUSEL_IMAGES) {
            showError(`В карусель можно добавить не больше ${MAX_CAROUSEL_IMAGES} фото`);
            return;
        }

        const srcs = imageUrlsFromFiles(files);
        if (srcs.length < 2) return;

        editor.value.chain().focus().insertContent({ type: "imageCarousel", attrs: { srcs } }).run();
    };

    input.click();
};

const openVideoModal = async () => {
    videoModalOpen.value = true;
    videoUrl.value = "";
    videoError.value = "";
    await nextTick();
    videoInput.value?.focus();
};

const closeVideoModal = () => {
    videoModalOpen.value = false;
    videoUrl.value = "";
    videoError.value = "";
};

const insertVideo = () => {
    if (!editor.value) return;

    const parsed = parseVideoUrl(videoUrl.value);

    if (!parsed.ok) {
        videoError.value = parsed.error;
        return;
    }

    editor.value
        .chain()
        .focus()
        .insertContent({
            type: "videoEmbed",
            attrs: {
                provider: parsed.provider,
                videoId: parsed.videoId,
                hash: parsed.hash,
            },
        })
        .run();

    closeVideoModal();
};

// Фото можно перетащить или вставить из буфера; внутреннее перемещение узлов не трогаем
const handleImageDrop = (view, event, moved) => {
    if (moved) return false;

    const files = imageFilesFrom(event.dataTransfer);
    if (!files.length) return false;

    event.preventDefault();

    const coords = view.posAtCoords({ left: event.clientX, top: event.clientY });
    insertImageUrls(imageUrlsFromFiles(files), coords?.pos ?? null);

    return true;
};

const handleImagePaste = (event) => {
    const files = imageFilesFrom(event.clipboardData);
    if (!files.length) return false;

    event.preventDefault();
    insertImageUrls(imageUrlsFromFiles(files));

    return true;
};

const prepareArticleData = async () => {
    if (!editor.value) throw new Error("Editor not ready");

    // Клиентская валидация: пустой пост (ни текста, ни картинки) не отправляем
    let content = editor.value.getHTML();
    if (!editor.value.state.doc.textContent.trim() && !content.includes("<img") && !content.includes("video-embed")) {
        return { error: "Добавьте текст, изображение или видео — пустой пост опубликовать нельзя." };
    }

    // Находим изображения как blob: (новые) и data: (base64, например вставки)
    const assetSrcs = [...(content.matchAll(/src="((?:blob:[^"]+|data:image[^"]+))"/g) || [])]
        .map((m) => m[1])
        .filter((v, i, arr) => arr.indexOf(v) === i);

    if (assetSrcs.length > 0) {
        const replacements = [];

        for (const assetUrl of assetSrcs) {
            try {
                const response = await fetch(assetUrl);
                const blob = await response.blob();
                const ext = (blob.type.split("/")[1] || "png").split(";")[0];
                const formData = new FormData();
                formData.append("image", blob, `image-${Date.now()}-${Math.floor(Math.random() * 1e5)}.${ext}`);

                const { data } = await axios.post("/upload-image", formData);
                replacements.push({ assetUrl, serverUrl: data.url });
            } catch (err) {
                console.error("Не удалось загрузить изображение:", assetUrl, err);
            }
        }

        replacements.forEach(({ assetUrl, serverUrl }) => {
            content = content.replace(new RegExp(escapeRegExp(assetUrl), "g"), serverUrl);
        });
    }

    // Создаем FormData для отправки
    const formData = new FormData();
    formData.append("title", title.value.trim());
    formData.append("content", content);

    if (selectedCategory.value) {
        formData.append("category_slug", selectedCategory.value);
    }

    return {
        formData,
        assetUrls: assetSrcs,
        error: null,
    };
};

// Метод для очистки blob-ссылок
const cleanupBlobUrls = (assetUrls) => {
    (assetUrls || []).forEach((url) => {
        if (typeof url === "string" && url.startsWith("blob:")) {
            URL.revokeObjectURL(url);
        }
    });
};

defineExpose({
    prepareArticleData,
    cleanupBlobUrls,
    clearDraft,
    resetDraft,
    title,
    selectedCategory,
    isDirty,
});
</script>

<style scoped>
.tooltip {
    position: relative;
}

.tooltip::before {
    content: attr(data-tooltip);
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    background: rgb(var(--c-content));
    color: rgb(var(--c-page));
    padding: 0.5rem 0.75rem;
    border-radius: 0.375rem;
    font-size: 0.75rem;
    white-space: nowrap;
    opacity: 0;
    visibility: hidden;
    transition: all 0.2s ease-in-out;
    z-index: 1000;
}

.tooltip::after {
    content: "";
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    border: 5px solid transparent;
    border-top-color: rgb(var(--c-content));
    opacity: 0;
    visibility: hidden;
    transition: all 0.2s ease-in-out;
}

.tooltip:hover::before,
.tooltip:hover::after {
    opacity: 1;
    visibility: visible;
    bottom: calc(100% + 5px);
}
</style>

<style>
/* Стили редактора */
.prose {
    max-width: none !important;
}

.prose h1.heading {
    line-height: 1.2;
    text-wrap: pretty;
    font-size: 2.1rem; /* 40px */
    font-weight: 700; /* Bold */
    line-height: 1.2; /* 48px */
    margin-bottom: 1.5rem;
}

.prose h2.heading {
    text-wrap: pretty;
    font-size: 1.875rem; /* 30px */
    font-weight: 600; /* Semi-bold */
    margin: 0.8rem 0 1rem;
}

.prose h3.heading {
    text-wrap: pretty;
    font-size: 1.5rem; /* 24px */
    font-weight: 600; /* Semi-bold */
    margin: 0.8rem 0 1rem;
}

[data-mode="dark"] .prose h2.heading {
    color: rgb(231 229 228 / var(--tw-text-opacity, 1));
}

[data-mode="dark"] .prose h3.heading {
    color: rgb(231 229 228 / var(--tw-text-opacity, 1));
}

.prose p {
    line-height: 1.7;
    margin: 0.4rem 0;
}

.prose ul,
.prose ol {
    margin: 1.1rem 0;
    padding-inline-start: 1.5rem;
    list-style-position: outside;
    color: rgb(var(--c-content));
}

.prose li {
    margin: 0.35rem 0;
}

.prose li > p {
    margin: 0.25rem 0;
}

.prose li > ul,
.prose li > ol {
    margin: 0.5rem 0 0.5rem 0.25rem;
    padding-inline-start: 1.4rem;
}

.prose ul ul {
    list-style-type: circle;
}

.prose ul ul ul {
    list-style-type: square;
}

.prose ol ol {
    list-style-type: lower-alpha;
}

.prose ol ol ol {
    list-style-type: lower-roman;
}

.prose ol {
    list-style: decimal;
}

.prose ul {
    list-style: disc;
}

.prose blockquote {
    border-left: 5px solid rgb(var(--c-line-strong));
    padding-left: 1.5rem;
    margin: 1.5rem 0;
    font-style: italic;
    background-color: rgb(var(--c-accent-soft));
    border-radius: 0.3rem;
}

.prose pre {
    background: rgb(var(--c-surface-muted));
    border-radius: 0.75rem;
    color: rgb(var(--c-content));
    font-family: ui-monospace, SFMono-Regular, "SF Mono", Menlo, Consolas, "Liberation Mono", monospace;
    font-size: 0.875rem;
    line-height: 1.65;
    margin: 1.5rem 0;
    padding: 0.95rem 1.15rem;
    overflow-x: auto;
    border: 1px solid rgb(var(--c-line));
}

.prose code {
    background: rgb(var(--c-surface-muted));
    border: 1px solid rgb(var(--c-line));
    color: rgb(var(--c-content));
    padding: 0.12em 0.4em;
    border-radius: 0.375rem;
    font-size: 0.875em;
}

.prose pre code {
    background: none;
    border: none;
    color: inherit;
    padding: 0;
    border-radius: 0;
}

.prose hr {
    border: none;
    border-top: 2px solid #e5e7eb;
    margin: 2rem 0;
}

[data-mode="dark"] .prose hr {
    border-top-color: rgb(var(--c-page));
}

.prose img {
    display: block;
    margin: 1.5rem auto;
    max-width: 100%;
    height: auto;
}

.prose figure img {
    margin-block: 0;
}

.prose .my-image {
    box-shadow: 0 4px 6px -1px rgb(0 / 0.1);
    transition: transform 0.3s ease;
}

/* Панель инструментов на телефоне — одна строка с горизонтальной прокруткой */
.editor-toolbar {
    scrollbar-width: none;
    -ms-overflow-style: none;
    overscroll-behavior-x: contain;
}

.editor-toolbar::-webkit-scrollbar {
    display: none;
}

/* Bubble menu стили */
.bubble-menu {
    animation: slideDown 0.2s ease-out;
}

/* На тач-экранах системное меню выделения перекрывается панелью —
   прячем её, форматирование остаётся в верхней панели редактора */
@media (pointer: coarse) {
    .bubble-menu {
        display: none !important;
    }
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Фокус состояния */
.ProseMirror:focus {
    outline: none;
    border-color: rgb(var(--c-accent)) !important;
    box-shadow: 0 3px rgb(var(--c-accent) / 0.1);
}
</style>
