<template>
    <div class="mx-auto w-full max-w-[44rem]">
        <!-- Черновик: при правке статьи не подменяем контент, а предлагаем восстановить -->
        <div
            v-if="pendingDraft || restoredDraft"
            class="mx-5 mt-4 flex flex-wrap items-start justify-between gap-3 rounded-xl bg-accent-soft px-4 py-3 sm:mx-8 sm:mt-6"
        >
            <div class="flex min-w-0 items-start gap-2.5">
                <ClockOutlineIcon :size="16" class="mt-0.5 shrink-0 text-accent" />
                <p class="min-w-0 text-sm leading-relaxed text-content">
                    <template v-if="pendingDraft">
                        Найден несохранённый черновик{{ pendingDraft.savedAt ? ` от ${formatSavedTime(pendingDraft.savedAt)}` : "" }}
                    </template>
                    <template v-else>
                        Восстановлен черновик{{ restoredDraft.savedAt ? ` от ${formatSavedTime(restoredDraft.savedAt)}` : "" }}
                    </template>
                </p>
            </div>
            <div class="flex shrink-0 items-center gap-1">
                <button
                    v-if="pendingDraft"
                    type="button"
                    class="min-h-9 px-3 text-[13px] font-medium text-accent transition-colors hover:text-accent-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent"
                    @click="restorePendingDraft"
                >
                    Восстановить
                </button>
                <button
                    type="button"
                    class="min-h-9 px-3 text-[13px] font-medium text-muted transition-colors hover:text-content focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent"
                    @click="pendingDraft ? discardPendingDraft() : discardRestoredDraft()"
                >
                    {{ pendingDraft ? "Удалить" : "Отменить" }}
                </button>
            </div>
        </div>

        <!-- Ошибка загрузки/ссылки -->
        <div
            v-if="editorError"
            class="mx-5 mt-4 flex items-start justify-between gap-3 rounded-xl bg-surface-muted px-4 py-3 sm:mx-8"
            role="alert"
        >
            <p class="min-w-0 text-sm leading-relaxed text-content">{{ editorError }}</p>
            <button
                type="button"
                class="inline-flex h-8 w-8 shrink-0 items-center justify-center text-muted transition-colors hover:text-content focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent"
                aria-label="Скрыть сообщение"
                @click="editorError = ''"
            >
                <CloseIcon :size="18" />
            </button>
        </div>

        <!-- Лист: тулбар, полотно, подвал -->
        <section class="mt-4 sm:mt-6" aria-label="Редактор статьи">
            <!-- Тулбар: десктоп -->
            <div class="editor-toolbar sticky top-0 z-20 hidden bg-page md:block"
                role="toolbar"
                aria-label="Форматирование">
                <div class="flex h-11 items-center px-8">
                    <div class="relative shrink-0">
                        <button
                            type="button"
                            class="editor-btn flex h-9 items-center gap-1 px-1.5 text-sm font-medium"
                            aria-haspopup="menu"
                            :aria-expanded="styleMenuOpen"
                            aria-label="Стиль абзаца"
                            @click="styleMenuOpen = !styleMenuOpen"
                        >
                            <span class="text-[15px] font-semibold leading-none lg:hidden">Aa</span>
                            <span class="hidden max-w-[6rem] truncate text-[13px] lg:inline">{{ currentBlockLabel }}</span>
                            <ChevronDownIcon :size="14" class="text-muted" />
                        </button>
                        <div
                            v-if="styleMenuOpen"
                            class="absolute left-0 top-full z-40 mt-1 w-60 rounded-xl border border-line bg-surface p-1"
                            role="menu"
                            aria-label="Стиль абзаца"
                        >
                            <button
                                v-for="style in blockStyles"
                                :key="style.key"
                                type="button"
                                role="menuitemradio"
                                :aria-checked="editor && style.isActive(editor)"
                                class="flex min-h-10 w-full items-center justify-between gap-3 rounded-lg px-3 text-left text-sm transition-colors hover:bg-surface-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-accent"
                                :class="editor && style.isActive(editor) ? 'bg-accent-soft text-accent' : 'text-content'"
                                @click="applyBlockStyle(style)"
                            >
                                <span>{{ style.label }}</span>
                                <CheckIcon v-if="editor && style.isActive(editor)" :size="16" />
                            </button>
                        </div>
                        <div
                            v-if="styleMenuOpen"
                            class="fixed inset-0 z-30"
                            aria-hidden="true"
                            @click="styleMenuOpen = false"
                        ></div>
                    </div>

                    <span class="mx-1 h-5 w-px shrink-0 bg-line-strong" aria-hidden="true"></span>

                    <button
                        v-for="tool in inlineTools"
                        :key="tool.key"
                        type="button"
                        class="editor-btn h-8 w-8"
                        :aria-label="tool.label"
                        :aria-pressed="tool.isActive ? toolActive(tool) : undefined"
                        :disabled="toolDisabled(tool)"
                        @click="tool.run()"
                    >
                        <component :is="tool.icon" :size="18" />
                    </button>

                    <span class="mx-1 h-5 w-px shrink-0 bg-line-strong" aria-hidden="true"></span>

                    <button
                        v-for="tool in blockTools"
                        :key="tool.key"
                        type="button"
                        class="editor-btn h-8 w-8"
                        :aria-label="tool.label"
                        :aria-pressed="tool.isActive ? toolActive(tool) : undefined"
                        @click="tool.run()"
                    >
                        <component :is="tool.icon" :size="18" />
                    </button>

                    <select
                        v-if="inCodeBlock"
                        v-model="codeLanguage"
                        aria-label="Язык блока кода"
                        class="ml-1 h-8 max-w-[8rem] rounded-lg border border-line bg-surface px-1.5 text-xs text-content focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/20"
                        @change="setCodeLanguage"
                    >
                        <option value="">Без подсветки</option>
                        <option v-for="language in codeLanguages" :key="language.value" :value="language.value">
                            {{ language.label }}
                        </option>
                    </select>

                    <span class="mx-1 h-5 w-px shrink-0 bg-line-strong" aria-hidden="true"></span>

                    <button
                        v-for="tool in insertTools"
                        :key="tool.key"
                        type="button"
                        class="editor-btn h-8 w-8"
                        :aria-label="tool.label"
                        @click="tool.run()"
                    >
                        <component :is="tool.icon" :size="18" />
                    </button>
                </div>
            </div>

            <!-- Тулбар: телефон. Один ряд, без горизонтальной прокрутки -->
            <div
                ref="toolbarRef"
                class="editor-toolbar sticky top-0 z-30 flex h-12 items-center gap-0.5 bg-page px-2 md:hidden"
                role="toolbar"
                aria-label="Форматирование"
                :style="toolbarStyle"
            >
                <div class="relative">
                    <button
                        type="button"
                        class="editor-btn h-11 w-11 text-[15px] font-semibold"
                        aria-haspopup="menu"
                        :aria-expanded="styleMenuOpen"
                        aria-label="Стиль абзаца"
                        @click="styleMenuOpen = !styleMenuOpen"
                    >
                        Aa
                    </button>
                    <div
                        v-if="styleMenuOpen"
                        class="absolute left-0 top-full z-40 mt-1 w-60 rounded-xl border border-line bg-surface p-1"
                        role="menu"
                        aria-label="Стиль абзаца"
                    >
                        <button
                            v-for="style in blockStyles"
                            :key="style.key"
                            type="button"
                            role="menuitemradio"
                            :aria-checked="editor && style.isActive(editor)"
                            class="flex min-h-11 w-full items-center justify-between gap-3 rounded-lg px-3 text-left text-sm transition-colors hover:bg-surface-muted"
                            :class="editor && style.isActive(editor) ? 'bg-accent-soft text-accent' : 'text-content'"
                            @click="applyBlockStyle(style)"
                        >
                            <span>{{ style.label }}</span>
                            <CheckIcon v-if="editor && style.isActive(editor)" :size="16" />
                        </button>
                    </div>
                    <div
                        v-if="styleMenuOpen"
                        class="fixed inset-0 z-30"
                        aria-hidden="true"
                        @click="styleMenuOpen = false"
                    ></div>
                </div>

                <span class="mx-1 h-6 w-px shrink-0 bg-line-strong" aria-hidden="true"></span>

                <button
                    v-for="tool in mobileBarTools"
                    :key="tool.key"
                    type="button"
                    class="editor-btn h-11 w-11"
                    :aria-label="tool.label"
                    :aria-pressed="tool.isActive ? toolActive(tool) : undefined"
                    @click="tool.run()"
                >
                    <component :is="tool.icon" :size="21" />
                </button>

                <button
                    type="button"
                    class="editor-btn ml-auto h-11 w-11"
                    aria-label="Все инструменты"
                    aria-haspopup="dialog"
                    :aria-expanded="sheetOpen"
                    aria-controls="editor-tools"
                    @click="openSheet"
                >
                    <DotsHorizontalIcon :size="22" />
                </button>
            </div>

            <!-- Панель стала fixed при открытой клавиатуре — держим её место в потоке -->
            <div v-if="toolbarSpacerHeight" :style="{ height: `${toolbarSpacerHeight}px` }" aria-hidden="true"></div>

        <!-- Заголовок и категория: без карточек и рамок -->
        <div class="px-5 pt-5 sm:px-8 sm:pt-6">
            <div class="flex flex-wrap items-center gap-x-5 gap-y-2">
                <label for="article-category" class="text-[13px] font-medium text-muted">Категория</label>
                <div class="relative min-w-0">
                    <select
                        id="article-category"
                        v-model="selectedCategory"
                        class="min-h-10 w-full max-w-[14rem] appearance-none truncate rounded-lg border border-line bg-surface bg-none py-1 pl-3 pr-8 text-sm text-content transition-colors hover:border-line-strong focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/20"
                    >
                        <option value="">Без категории</option>
                        <option v-for="categoryOption in categoryOptions" :key="categoryOption.slug" :value="categoryOption.slug">
                            {{ categoryOption.name }}
                        </option>
                    </select>
                    <ChevronDownIcon :size="16" class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 text-muted" />
                </div>
            </div>

            <label for="article-title" class="sr-only">Заголовок статьи (необязательно)</label>
            <textarea
                id="article-title"
                ref="titleRef"
                v-model="title"
                rows="1"
                placeholder="Заголовок"
                class="mt-4 block w-full resize-none overflow-hidden border-0 bg-transparent p-0 font-display text-[clamp(1.5rem,4.4vw,2.5rem)] font-bold leading-[1.08] tracking-[-0.02em] text-content placeholder:text-muted focus:outline-none focus:ring-0"
                @input="onTitleInput"
                @keydown.enter.prevent="focusEditor"
            ></textarea>
        </div>


            <!-- Полотно -->
            <div class="relative">
                <editor-content :editor="editor" />
            </div>

            <!-- Подвал: объём и состояние черновика -->
            <div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-1 px-5 py-3 text-[13px] leading-tight text-muted sm:px-8">
                <p class="flex items-center gap-2">
                    <span>{{ wordCount }} {{ wordForm }}</span>
                    <span class="text-line-strong" aria-hidden="true">·</span>
                    <span>{{ readingMinutes }} мин чтения</span>
                </p>
                <p class="flex items-center gap-1.5">
                    <span
                        class="h-1.5 w-1.5 shrink-0 rounded-full"
                        :class="isDirty ? 'bg-accent' : 'bg-line-strong'"
                        aria-hidden="true"
                    ></span>
                    {{ draftStatus }}
                </p>
            </div>
        </section>

        <!-- BUBBLE МЕНЮ -->
        <bubble-menu
            v-if="editor"
            :editor="editor"
            :should-show="showBubbleMenu"
            :update-delay="120"
            :tippy-options="{ duration: 100, maxWidth: 'none', placement: 'top-start', offset: [0, 10] }"
            class="bubble-menu"
        >
            <div class="flex items-center gap-0.5 rounded-xl border border-line bg-surface p-1">
                <button
                    v-for="tool in bubbleTools"
                    :key="tool.key"
                    type="button"
                    class="editor-btn h-8 w-8"
                    :aria-label="tool.label"
                    :aria-pressed="toolActive(tool)"
                    @click="tool.run()"
                >
                    <component :is="tool.icon" :size="17" />
                </button>
            </div>
        </bubble-menu>

        <!-- Панель инструментов: телефон -->
        <div
            v-if="sheetOpen"
            class="fixed inset-0 z-40 bg-black/50 md:hidden"
            aria-hidden="true"
            @click="closeSheet"
        ></div>
        <div
            v-if="sheetOpen"
            id="editor-tools"
            ref="sheetRef"
            class="editor-sheet fixed inset-x-3 bottom-[calc(env(safe-area-inset-bottom)+0.75rem)] z-50 max-h-[92dvh] overflow-y-auto overscroll-contain rounded-2xl bg-surface pb-4 outline-none md:hidden"
            tabindex="-1"
            role="dialog"
            aria-modal="true"
            aria-label="Инструменты редактора"
        >
            <div class="flex items-center justify-between px-4 pb-1 pt-3">
                <p class="text-sm font-medium text-content">Инструменты</p>
                <button
                    type="button"
                    class="editor-btn h-10 w-10"
                    aria-label="Закрыть"
                    @click="closeSheet"
                >
                    <CloseIcon :size="20" />
                </button>
            </div>

            <div v-for="group in sheetGroups" :key="group.title" class="px-4">
                <p class="pb-1.5 pt-2 text-[13px] text-muted">{{ group.title }}</p>
                <div class="grid grid-cols-2 gap-1.5">
                    <button
                        v-for="tool in group.items"
                        :key="tool.key"
                        type="button"
                        class="editor-tile flex min-h-[56px] flex-col items-center justify-center gap-1 rounded-xl border border-line px-2 py-2 text-[12px] leading-tight"
                        :aria-label="tool.label"
                        :aria-pressed="tool.isActive ? toolActive(tool) : undefined"
                        @click="runFromSheet(tool)"
                    >
                        <component :is="tool.icon" :size="22" />
                        <span class="text-center">{{ tool.label }}</span>
                    </button>
                </div>
            </div>

            <div v-if="inCodeBlock" class="px-4 pb-2 pt-3">
                <label for="code-language" class="text-[13px] text-muted">Язык кода</label>
                <select
                    id="code-language"
                    v-model="codeLanguage"
                    class="mt-1.5 h-11 w-full rounded-lg border border-line bg-surface px-2 text-sm text-content focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/20"
                    @change="setCodeLanguage"
                >
                    <option value="">Без подсветки</option>
                    <option v-for="language in codeLanguages" :key="language.value" :value="language.value">
                        {{ language.label }}
                    </option>
                </select>
            </div>
        </div>

        <!-- Упоминание пользователя -->
        <div
            v-if="mentionOpen"
            class="fixed inset-0 z-50 flex items-start justify-center bg-black/50 px-4 pt-[10vh]"
            @click.self="mentionOpen = false"
        >
            <div class="w-full max-w-sm rounded-2xl border border-line bg-surface" role="dialog" aria-modal="true" aria-label="Упомянуть пользователя">
                <div class="flex items-center justify-between px-4 pt-3">
                    <p class="text-sm font-medium text-content">Упомянуть</p>
                    <button
                        type="button"
                        class="editor-btn h-9 w-9"
                        aria-label="Закрыть"
                        @click="mentionOpen = false"
                    >
                        <CloseIcon :size="18" />
                    </button>
                </div>
                <div class="border-b border-line p-3">
                    <input
                        ref="mentionInput"
                        v-model="mentionQuery"
                        type="text"
                        placeholder="Имя или @username"
                        aria-label="Найти пользователя для упоминания"
                        class="h-11 w-full rounded-lg border border-line-strong bg-surface px-3 text-sm text-content placeholder:text-muted focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/20"
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
                            class="flex min-h-11 w-full items-center gap-3 rounded-lg px-3 py-2 text-left transition-colors hover:bg-surface-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-accent"
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

        <!-- Вставка внешнего видео -->
        <Modal :show="videoModalOpen" max-width="md" centered @close="closeVideoModal">
            <form class="p-6" @submit.prevent="insertVideo">
                <h2 class="text-lg font-semibold text-content">Вставить видео</h2>
                <p class="mt-2 text-sm leading-relaxed text-muted">
                    Вставьте ссылку на видео Rutube или VK Video. Видео останется на внешней площадке.
                </p>

                <label for="video-embed-url" class="mt-5 block text-[13px] font-medium text-muted">Ссылка на видео</label>
                <input
                    id="video-embed-url"
                    ref="videoInput"
                    v-model="videoUrl"
                    type="url"
                    inputmode="url"
                    autocomplete="off"
                    placeholder="https://rutube.ru/video/..."
                    class="mt-2 h-11 w-full rounded-lg border border-line-strong bg-surface px-3 text-sm text-content placeholder:text-muted focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/20"
                    @input="videoError = ''"
                />
                <p v-if="videoError" class="mt-2 text-sm text-accent" role="alert">{{ videoError }}</p>
                <p class="mt-2 text-xs text-muted">Поддерживаются Rutube и VK Video.</p>

                <div class="mt-6 flex justify-end gap-2">
                    <button
                        type="button"
                        class="h-10 rounded-lg border border-line-strong px-4 text-sm font-medium text-content transition-colors hover:bg-surface-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2"
                        @click="closeVideoModal"
                    >
                        Отмена
                    </button>
                    <button
                        type="submit"
                        class="h-10 rounded-lg bg-accent px-4 text-sm font-medium text-accent-contrast transition-colors hover:bg-accent-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2"
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
import Placeholder from "@tiptap/extension-placeholder";
import BoldIcon from "vue-material-design-icons/FormatBold.vue";
import ItalicIcon from "vue-material-design-icons/FormatItalic.vue";
import UnderlineIcon from "vue-material-design-icons/FormatUnderline.vue";
import BlockquoteIcon from "vue-material-design-icons/FormatQuoteClose.vue";
import BulletListIcon from "vue-material-design-icons/FormatListBulleted.vue";
import OrderedListIcon from "vue-material-design-icons/FormatListNumbered.vue";
import CodeIcon from "vue-material-design-icons/CodeTags.vue";
import CodeBlockIcon from "vue-material-design-icons/CodeBraces.vue";
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
import StrikethroughIcon from "vue-material-design-icons/FormatStrikethrough.vue";
import CheckIcon from "vue-material-design-icons/Check.vue";
import ChevronDownIcon from "vue-material-design-icons/ChevronDown.vue";
import CloseIcon from "vue-material-design-icons/Close.vue";
import DotsHorizontalIcon from "vue-material-design-icons/DotsHorizontal.vue";
import ClockOutlineIcon from "vue-material-design-icons/ClockOutline.vue";
import UserAvatar from "@/Components/UserAvatar.vue";
import Modal from "@/Components/Modal.vue";
import { computed, nextTick, onMounted, onBeforeUnmount, ref, watch } from "vue";
import {
    createDraftImageUrl,
    deleteDraftImages,
    extractDraftImageIds,
    hydrateDraftImages,
    MAX_DRAFT_SIZE,
    readDraft,
    removeDraft,
    serializeDraftImages,
    writeDraft,
} from "@/Utilities/drafts";

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
const titleRef = ref(null);

// Заголовок — многострочное поле: длинный текст переносится, а не уезжает
// за край. Высоту подгоняем под содержимое (textarea + scrollHeight).
const syncTitleHeight = () => {
    const element = titleRef.value;
    if (!element) return;

    element.style.height = "0px";
    element.style.height = `${element.scrollHeight}px`;
};

const onTitleInput = () => {
    // В заголовке не бывает абзацев — при вставке многострочного текста
    // склеиваем строки в одну.
    if (title.value.includes("\n")) {
        title.value = title.value.replace(/\s*\n+\s*/g, " ");
    }

    nextTick(syncTitleHeight);
};

watch(title, () => nextTick(syncTitleHeight));
const editor = ref(null);
const selectedCategory = ref(props.initialCardCategory || "");

// Состояние панелей и статус автосохранения
const styleMenuOpen = ref(false);
const sheetOpen = ref(false);
const lastSavedAt = ref(null);
const wordCount = ref(0);

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
    // removeDraft удалит картинки, уже записанные в сохранённый черновик.
    // Отдельно чистим изображения, которые появились в редакторе после
    // последнего autosave и ещё не попали в localStorage.
    const unsavedImageIds = extractDraftImageIds(serializeDraftImages(editor.value?.getHTML() || ""));
    if (unsavedImageIds.length) deleteDraftImages(unsavedImageIds);

    removeDraft(props.draftKey);
    isDirty.value = false;
    lastSavedAt.value = null;
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

    const html = serializeDraftImages(editor.value.getHTML());
    if (html.length > MAX_DRAFT_SIZE) {
        // Не пишем огромный контент в localStorage (может выпасть квота),
        // но продолжаем держать его в памяти редактора.
        return;
    }

    // Удалённые из редактора фото больше не нужны в IndexedDB.
    const previousDraft = loadDraft();
    const previousIds = new Set(extractDraftImageIds(previousDraft?.content || ""));
    const currentIds = new Set(extractDraftImageIds(html));
    const removedIds = [...previousIds].filter((id) => !currentIds.has(id));
    if (removedIds.length) deleteDraftImages(removedIds);

    const savedAt = Date.now();
    writeDraft(props.draftKey, {
        title: title.value,
        category: selectedCategory.value,
        content: html,
        savedAt,
    });
    lastSavedAt.value = savedAt;
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
    syncWordCount();
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
    syncWordCount();

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
const syncWordCount = () => {
    if (!editor.value) {
        wordCount.value = 0;
        return;
    }

    // Объём текста для подвала: слова по пробелам, как в редакторах
    const trimmed = (editor.value.state.doc.textContent || "").trim();
    wordCount.value = trimmed ? trimmed.split(/\s+/).length : 0;
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

onMounted(async () => {
    const draft = loadDraft();
    const storedDraftContent = draft?.content || "";
    // Черновик мог быть сохранён со служебными draft-image:<id>: достаём
    // настоящие Blob из IndexedDB и снова превращаем их в blob:-URL.
    const draftContent = storedDraftContent ? await hydrateDraftImages(storedDraftContent) : "";
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
                    class: "text-content leading-relaxed",
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
                    class: "my-image",
                },
                allowBase64: true,
            }),
            ImageCarousel,
            VideoEmbed,
            Placeholder.configure({
                placeholder: "Начните писать…",
                emptyEditorClass: "is-editor-empty",
            }),
        ],
        content: restoredContent,
        editorProps: {
            attributes: {
                class: "article-body editor-body focus:outline-none",
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
        syncWordCount();
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

    syncWordCount();

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

// Всплывающее меню нужно только для выделенного фрагмента. Раньше оно
// показывалось при любом состоянии выделения и висело на пустом курсоре,
// закрывая строку, в которую печатают.
const showBubbleMenu = ({ editor, state }) => {
    if (typeof window === "undefined") return false;

    // На тач-устройствах меню накладывается на системное меню выделения
    // («Копировать/Вырезать»), а форматирование доступно в верхней панели.
    if (typeof window.matchMedia === "function" && window.matchMedia("(pointer: coarse)").matches) {
        return false;
    }

    // В блоке кода форматировать нечего
    if (editor?.isActive("codeBlock")) return false;

    return Boolean(state?.selection && !state.selection.empty);
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

    // Высота панели: при fixed она выходит из потока, место держим распоркой
    toolbarSpacerHeight.value = Math.round(element.offsetHeight);

    // Панель на всю ширину экрана: скругления и боковые границы тут лишние.
    // top привязываем к началу visual viewport — иначе на iOS панель уезжает
    // под адресную строку или за клавиатуру.
    toolbarStyle.value = {
        position: "fixed",
        top: `${Math.max(0, viewport.offsetTop)}px`,
        left: "0px",
        right: "0px",
        zIndex: 30,
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

// Исходник до сжатия; после сжатия — не больше серверного лимита /upload-image
const MAX_SOURCE_IMAGE_SIZE = 20 * 1024 * 1024;
const MAX_UPLOAD_IMAGE_SIZE = 10 * 1024 * 1024;
const EDITOR_IMAGE_MAX_SIDE = 2400;
const EDITOR_IMAGE_TARGET_MB = 2.5;
const ALLOWED_IMAGE_TYPES = CAROUSEL_IMAGE_TYPES;

const megabytes = (bytes) => Math.round(bytes / (1024 * 1024));

// Сжимаем в браузере до отправки: сервер всё равно пережмёт фото в WebP,
// но платить за это трафиком и памятью PHP незачем. Модуль тянем лениво.
const compressBeforeUpload = async (file) => {
    try {
        const { default: imageCompression } = await import("browser-image-compression");

        return await imageCompression(file, {
            maxWidthOrHeight: EDITOR_IMAGE_MAX_SIDE,
            maxSizeMB: EDITOR_IMAGE_TARGET_MB,
            initialQuality: 0.82,
            useWebWorker: true,
        });
    } catch (err) {
        console.error("Не удалось сжать фото, отправляем как есть:", file.name, err);
        return file;
    }
};

// Новые фото живут в редакторе как blob: и уходят на сервер при сохранении
const imageUrlsFromFiles = async (files) => {
    const urls = [];

    for (const file of files) {
        if (!ALLOWED_IMAGE_TYPES.includes(file.type)) {
            showError("Поддерживаются JPG, PNG или WebP");
            continue;
        }

        if (file.size > MAX_SOURCE_IMAGE_SIZE) {
            showError(`Файл больше ${megabytes(MAX_SOURCE_IMAGE_SIZE)} МБ — выберите фото поменьше`);
            continue;
        }

        const prepared = await compressBeforeUpload(file);

        if (prepared.size > MAX_UPLOAD_IMAGE_SIZE) {
            showError(`Даже после сжатия фото больше ${megabytes(MAX_UPLOAD_IMAGE_SIZE)} МБ — выберите файл поменьше`);
            continue;
        }

        urls.push(createDraftImageUrl(prepared));
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

    input.onchange = async (e) => {
        insertImageUrls(await imageUrlsFromFiles([...(e.target.files || [])]));
    };

    input.click();
};

// Карусель: два и больше фото, максимум 8
const addGallery = () => {
    const input = document.createElement("input");
    input.type = "file";
    input.accept = "image/*";
    input.multiple = true;

    input.onchange = async (e) => {
        const files = [...(e.target.files || [])];

        if (files.length < 2) {
            showError("Для карусели нужно минимум два фото");
            return;
        }

        if (files.length > MAX_CAROUSEL_IMAGES) {
            showError(`В карусель можно добавить не больше ${MAX_CAROUSEL_IMAGES} фото`);
            return;
        }

        const srcs = await imageUrlsFromFiles(files);

        // Часть фото могла не пройти проверку размера — карусель из одного кадра не нужна
        if (srcs.length < 2) {
            showError("Для карусели нужно минимум два фото");
            return;
        }

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
    imageUrlsFromFiles(files).then((urls) => insertImageUrls(urls, coords?.pos ?? null));

    return true;
};

const handleImagePaste = (event) => {
    const files = imageFilesFrom(event.clipboardData);
    if (!files.length) return false;

    event.preventDefault();
    imageUrlsFromFiles(files).then((urls) => insertImageUrls(urls));

    return true;
};

// Уже отправленные на сервер фото: повторное сохранение после ошибки не дублирует файлы
const uploadedAssets = new Map();

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
        const failed = [];

        for (const assetUrl of assetSrcs) {
            try {
                // Уже загруженное фото не отправляем заново при повторном сохранении
                let serverUrl = uploadedAssets.get(assetUrl);

                if (!serverUrl) {
                    const response = await fetch(assetUrl);
                    const blob = await response.blob();
                    const ext = (blob.type.split("/")[1] || "png").split(";")[0];
                    const formData = new FormData();
                    formData.append("image", blob, `image-${Date.now()}-${Math.floor(Math.random() * 1e5)}.${ext}`);

                    const { data } = await axios.post("/upload-image", formData);
                    serverUrl = data.url;
                    uploadedAssets.set(assetUrl, serverUrl);
                }

                replacements.push({ assetUrl, serverUrl });
            } catch (err) {
                console.error("Не удалось загрузить изображение:", assetUrl, err);
                failed.push(assetUrl);
            }
        }

        // Не сохраняем статью с blob:-ссылками: после перезагрузки они мертвы
        if (failed.length > 0) {
            return {
                error: `Не удалось загрузить фото (${failed.length}). Проверьте соединение и попробуйте сохранить ещё раз — уже загруженные не отправятся повторно.`,
            };
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

// ---------- Панель инструментов ----------
// Кнопки описываются данными: одна конфигурация кормит десктопный тулбар,
// телефонный ряд и шторку «Ещё». Активность — из состояния выделения Tiptap.
const editorChain = () => editor.value?.chain().focus();

const blockStyles = [
    {
        key: "paragraph",
        label: "Обычный текст",
        shortLabel: "Текст",
        run: () => editorChain()?.setParagraph().run(),
        isActive: (instance) => instance.isActive("paragraph"),
    },
    {
        key: "h2",
        label: "Заголовок",
        run: () => editorChain()?.toggleHeading({ level: 2 }).run(),
        isActive: (instance) => instance.isActive("heading", { level: 2 }),
    },
    {
        key: "h3",
        label: "Подзаголовок",
        run: () => editorChain()?.toggleHeading({ level: 3 }).run(),
        isActive: (instance) => instance.isActive("heading", { level: 3 }),
    },
    {
        key: "quote",
        label: "Цитата",
        run: () => editorChain()?.toggleBlockquote().run(),
        isActive: (instance) => instance.isActive("blockquote"),
    },
    {
        key: "codeBlock",
        label: "Блок кода",
        run: () => editorChain()?.toggleCodeBlock().run(),
        isActive: (instance) => instance.isActive("codeBlock"),
    },
];

const inlineTools = [
    {
        key: "bold",
        label: "Жирный",
        icon: BoldIcon,
        run: () => editorChain()?.toggleBold().run(),
        isActive: (instance) => instance.isActive("bold"),
        can: (instance) => instance.can().chain().focus().toggleBold().run(),
    },
    {
        key: "italic",
        label: "Курсив",
        icon: ItalicIcon,
        run: () => editorChain()?.toggleItalic().run(),
        isActive: (instance) => instance.isActive("italic"),
        can: (instance) => instance.can().chain().focus().toggleItalic().run(),
    },
    {
        key: "underline",
        label: "Подчёркнутый",
        icon: UnderlineIcon,
        run: () => editorChain()?.toggleUnderline().run(),
        isActive: (instance) => instance.isActive("underline"),
    },
    {
        key: "strike",
        label: "Зачёркнутый",
        icon: StrikethroughIcon,
        run: () => editorChain()?.toggleStrike().run(),
        isActive: (instance) => instance.isActive("strike"),
    },
    {
        key: "code",
        label: "Моноширинный",
        icon: CodeIcon,
        run: () => editorChain()?.toggleCode().run(),
        isActive: (instance) => instance.isActive("code"),
    },
];

const blockTools = [
    {
        key: "bulletList",
        label: "Список",
        icon: BulletListIcon,
        run: () => editorChain()?.toggleBulletList().run(),
        isActive: (instance) => instance.isActive("bulletList"),
    },
    {
        key: "orderedList",
        label: "Нумерация",
        icon: OrderedListIcon,
        run: () => editorChain()?.toggleOrderedList().run(),
        isActive: (instance) => instance.isActive("orderedList"),
    },
    {
        key: "blockquote",
        label: "Цитата",
        icon: BlockquoteIcon,
        run: () => editorChain()?.toggleBlockquote().run(),
        isActive: (instance) => instance.isActive("blockquote"),
    },
    {
        key: "codeBlock",
        label: "Блок кода",
        icon: CodeBlockIcon,
        run: () => editorChain()?.toggleCodeBlock().run(),
        isActive: (instance) => instance.isActive("codeBlock"),
    },
    {
        key: "hr",
        label: "Линия",
        icon: HorizontalRuleIcon,
        run: () => editorChain()?.setHorizontalRule().run(),
    },
];

const alignTools = [
    {
        key: "alignLeft",
        label: "По левому краю",
        icon: AlignLeftIcon,
        run: () => editorChain()?.toggleTextAlign("left").run(),
        isActive: (instance) => instance.isActive({ textAlign: "left" }),
    },
    {
        key: "alignCenter",
        label: "По центру",
        icon: AlignCenterIcon,
        run: () => editorChain()?.toggleTextAlign("center").run(),
        isActive: (instance) => instance.isActive({ textAlign: "center" }),
    },
    {
        key: "alignRight",
        label: "По правому краю",
        icon: AligRightIcon,
        run: () => editorChain()?.toggleTextAlign("right").run(),
        isActive: (instance) => instance.isActive({ textAlign: "right" }),
    },
    {
        key: "alignJustify",
        label: "По ширине",
        icon: AlignJustifyIcon,
        run: () => editorChain()?.toggleTextAlign("justify").run(),
        isActive: (instance) => instance.isActive({ textAlign: "justify" }),
    },
];

const insertTools = [
    {
        key: "link",
        label: "Ссылка",
        icon: LinkIcon,
        run: setLink,
        isActive: (instance) => instance.isActive("link"),
    },
    {
        key: "mention",
        label: "Упомянуть",
        icon: AtIcon,
        run: toggleMentionPicker,
    },
    {
        key: "image",
        label: "Фото",
        icon: ImageIcon,
        run: addImage,
    },
    {
        key: "gallery",
        label: "Карусель",
        icon: CarouselIcon,
        run: addGallery,
    },
    {
        key: "video",
        label: "Видео",
        icon: MoviePlayOutlineIcon,
        run: openVideoModal,
    },
];

const toolByKey = (key) =>
    [...inlineTools, ...blockTools, ...alignTools, ...insertTools].find((tool) => tool.key === key);

// Телефонный ряд: только самое частое, остальное — в шторке
const mobileBarTools = [
    toolByKey("bold"),
    toolByKey("italic"),
    toolByKey("link"),
    toolByKey("image"),
].filter(Boolean);

// Меню выделения на десктопе
const bubbleTools = [
    toolByKey("bold"),
    toolByKey("italic"),
    toolByKey("underline"),
    toolByKey("link"),
].filter(Boolean);

const sheetGroups = [
    {
        title: "Текст",
        items: [toolByKey("underline"), toolByKey("strike"), toolByKey("code"), toolByKey("codeBlock")],
    },
    {
        title: "Блоки",
        items: [toolByKey("bulletList"), toolByKey("orderedList"), toolByKey("blockquote"), toolByKey("hr")],
    },
    {
        title: "Выравнивание",
        items: alignTools,
    },
    {
        title: "Вставка",
        items: [toolByKey("image"), toolByKey("gallery"), toolByKey("video"), toolByKey("mention")],
    },
].filter((group) => group.items.every(Boolean));

const toolActive = (tool) => (editor.value && tool.isActive ? tool.isActive(editor.value) : false);
const toolDisabled = (tool) => (editor.value && tool.can ? !tool.can(editor.value) : false);

const currentBlockLabel = computed(() => {
    if (!editor.value) return "Текст";

    const current = blockStyles.find((style) => style.isActive(editor.value));

    return current ? current.shortLabel || current.label : "Текст";
});

// Состояние черновика: в подвале и точкой в телефонной панели
const draftStatus = computed(() => {
    if (!isDirty.value) return "Без изменений";
    if (!lastSavedAt.value) return "Сохраняю…";

    return `Сохранено в ${formatSavedTime(lastSavedAt.value)}`;
});

const wordForm = computed(() => {
    const mod100 = wordCount.value % 100;
    const mod10 = wordCount.value % 10;

    if (mod100 > 10 && mod100 < 20) return "слов";
    if (mod10 === 1) return "слово";
    if (mod10 >= 2 && mod10 <= 4) return "слова";

    return "слов";
});

const readingMinutes = computed(() => Math.max(1, Math.ceil(wordCount.value / 180)));

const applyBlockStyle = (style) => {
    styleMenuOpen.value = false;
    style.run();
};

const sheetRef = ref(null);

const openSheet = () => {
    styleMenuOpen.value = false;
    sheetOpen.value = true;
    nextTick(() => sheetRef.value?.focus());
};

const closeSheet = () => {
    sheetOpen.value = false;
    // Возвращаем фокус на кнопку «···», а не в текст: иначе на телефоне
    // закрытие шторки тапом по фону снова поднимает клавиатуру.
    nextTick(() => toolbarRef.value?.querySelector('[aria-controls="editor-tools"]')?.focus());
};

const runFromSheet = (tool) => {
    sheetOpen.value = false;
    tool.run();
};

const focusEditor = () => editor.value?.commands.focus();

watch(sheetOpen, (open) => {
    document.body.style.overflow = open ? "hidden" : "";
});

const handleGlobalKeydown = (event) => {
    if (event.key !== "Escape") return;

    if (styleMenuOpen.value) styleMenuOpen.value = false;
    if (mentionOpen.value) mentionOpen.value = false;
    if (sheetOpen.value) closeSheet();
};

onMounted(() => {
    document.addEventListener("keydown", handleGlobalKeydown);
    nextTick(syncTitleHeight);
    // Unbounded грузится позже первого рендера: до него заголовок занимает
    // меньше строк, и высота, посчитанная сразу, обрезала бы хвост.
    document.fonts?.ready.then(() => nextTick(syncTitleHeight));
    window.addEventListener("resize", syncTitleHeight);
});

onBeforeUnmount(() => {
    document.removeEventListener("keydown", handleGlobalKeydown);
    window.removeEventListener("resize", syncTitleHeight);
    document.body.style.overflow = "";
});

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

<style>
/* Полотно редактора: та же типографика, что у опубликованной статьи */
.editor-body {
    min-height: 55vh;
    max-width: none;
    padding: 1rem 1.25rem 2rem;
    outline: none;
}

@media (min-width: 640px) {
    .editor-body {
        padding: 1.25rem 2rem 3rem;
    }
}

@media (min-width: 1024px) {
    .editor-body {
        min-height: 60vh;
        padding: 1.5rem 2rem 4rem;
    }
}

.editor-body:focus,
.editor-body:focus-visible {
    outline: none;
}

/* Пустой редактор: подсказка в первой строке, а не поверх полотна */
.editor-body p.is-editor-empty:first-child::before {
    content: attr(data-placeholder);
    float: left;
    height: 0;
    color: rgb(var(--c-muted));
    pointer-events: none;
}

.editor-body > :first-child {
    margin-top: 0;
}

.editor-body .ProseMirror-selectednode {
    outline: 2px solid rgb(var(--c-accent));
    outline-offset: 2px;
}

/* Кнопки панелей: состояния из aria, чтобы не дублировать классы в разметке */
.editor-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.625rem;
    color: rgb(var(--c-content));
    background: transparent;
    transition: background-color 0.15s ease, color 0.15s ease;
}

.editor-btn:hover {
    background: rgb(var(--c-surface));
}

.editor-btn[aria-pressed="true"] {
    background: rgb(var(--c-accent-soft));
    color: rgb(var(--c-accent));
}

.editor-btn:focus-visible,
.editor-tile:focus-visible {
    outline: 2px solid rgb(var(--c-accent));
    outline-offset: -2px;
}

.editor-btn:disabled {
    opacity: 0.4;
    pointer-events: none;
}

.editor-tile {
    color: rgb(var(--c-content));
    background: transparent;
    transition: background-color 0.15s ease, color 0.15s ease;
}

.editor-tile:hover {
    background: rgb(var(--c-surface-muted));
}

.editor-tile[aria-pressed="true"] {
    background: rgb(var(--c-accent-soft));
    color: rgb(var(--c-accent));
}

.editor-tile[aria-pressed="true"]:hover {
    background: rgb(var(--c-accent-soft));
}

/* Шторка инструментов выезжает снизу; движение уважает prefers-reduced-motion */
.editor-sheet {
    animation: sheet-up 0.18s ease-out;
}

/* Глобальное правило фокуса в app.css рисует акцентную обводку у [tabindex];
   для шторки она лишняя — фокус нужен только для скринридера. */
.editor-sheet:focus,
.editor-sheet:focus-visible {
    outline: none;
}

@keyframes sheet-up {
    from {
        transform: translateY(100%);
    }
    to {
        transform: translateY(0);
    }
}

@media (prefers-reduced-motion: reduce) {
    .editor-sheet {
        animation: none;
    }
}

/* Меню выделения */
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
        transform: translateY(-6px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (prefers-reduced-motion: reduce) {
    .bubble-menu {
        animation: none;
    }
}
</style>
