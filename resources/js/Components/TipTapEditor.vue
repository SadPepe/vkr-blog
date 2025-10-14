<template>
    <div class="relative max-w-4xl mx-auto dark:bg-transparent">
        <!-- ФОРМА -->
        <div
            class="border rounded-2xl dark:border-stone-700 mb-6 bg-white dark:bg-[#1a1a1a] shadow-sm hover:shadow-md transition-shadow duration-300"
        >
            <input
                v-model="title"
                placeholder="Введите заголовок статьи..."
                class="dark:bg-[#2a2a2a] dark:border-stone-600 dark:text-stone-300 mb-6 w-full px-6 py-4 text-xl font-semibold border-b border-gray-200 rounded-t-2xl focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-colors duration-200"
            />
            <div class="mb-6 px-6">
                <label
                    class="text-lg font-semibold text-gray-800 mb-3 dark:text-stone-200 flex items-center"
                >
                    <svg
                        class="w-5 h-5 mr-2 text-gray-600 dark:text-stone-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>
                    Краткое описание
                </label>
                <textarea
                    v-model="excerpt"
                    placeholder="Опишите суть статьи в нескольких предложениях..."
                    class="dark:bg-[#2a2a2a] dark:border-stone-600 dark:text-stone-300 w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-colors duration-200 resize-none"
                    maxlength="200"
                    rows="3"
                ></textarea>
                <div class="flex justify-between items-center mt-2">
                    <span class="text-xs text-gray-500 dark:text-stone-400"
                        >Будет отображаться в карточке статьи</span
                    >
                    <span
                        class="text-xs font-medium"
                        :class="
                            excerpt.length >= 190
                                ? 'text-red-500'
                                : 'text-gray-500 dark:text-stone-400'
                        "
                    >
                        {{ excerpt.length }}/200
                    </span>
                </div>
            </div>
            <div
                class="flex justify-between p-6 border-t border-gray-100 dark:border-stone-700"
            >
                <div class="w-[55%]">
                    <label
                        class="text-lg font-semibold text-gray-800 mb-3 dark:text-stone-200 flex items-center"
                    >
                        <svg
                            class="w-5 h-5 mr-2 text-gray-600 dark:text-stone-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                            />
                        </svg>
                        Изображение для карточки
                    </label>
                    <div v-if="previewImage" class="mb-4 relative group">
                        <img
                            :src="previewImage"
                            alt="Предпросмотр"
                            class="w-full max-h-[300px] object-cover rounded-lg shadow-md transition-transform duration-300"
                        />
                        <div
                            class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all duration-300 rounded-lg flex items-center justify-center"
                        >
                            <button
                                @click="removeCardImage"
                                class="opacity-0 group-hover:opacity-100 bg-red-500 text-white p-2 rounded-full transform scale-0 group-hover:scale-100 transition-all duration-300"
                            >
                                <svg
                                    class="w-4 h-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
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
                    <div class="flex gap-3">
                        <button
                            type="button"
                            @click="$refs.cardImageInput.click()"
                            class="flex items-center px-4 py-2 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white rounded-lg text-sm font-medium transition-all duration-200 shadow-md hover:shadow-lg"
                        >
                            <svg
                                class="w-4 h-4 mr-2"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"
                                />
                            </svg>
                            {{ previewImage ? "Заменить" : "Загрузить" }}
                        </button>
                        <input
                            type="file"
                            ref="cardImageInput"
                            accept="image/*"
                            @change="handleCardImageUpload"
                            class="hidden"
                        />
                    </div>
                </div>
                <div class="w-[40%]">
                    <label
                        class="text-lg font-semibold text-gray-800 mb-3 dark:text-stone-200 flex items-center"
                    >
                        <svg
                            class="w-5 h-5 mr-2 text-gray-600 dark:text-stone-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
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
                        class="w-full p-3 border border-gray-300 dark:border-stone-600 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:text-stone-300 dark:bg-[#2a2a2a] transition-all duration-200 appearance-none bg-white bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTYiIGhlaWdodD0iMTYiIHZpZXdCb3g9IjAgMCAxNiAxNiIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTQgNkw4IDEwTDEyIDYiIHN0cm9rZT0iIzljYTBhZiIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiIHN0cm9rZS1saW5lam9pbj0icm91bmQiLz4KPC9zdmc+')] bg-no-repeat bg-[center_right_1rem]"
                    >
                        <option disabled :value="null" class="text-gray-400">
                            Выберите категорию
                        </option>
                        <option
                            v-for="categoryOption in categoryOptions"
                            :key="categoryOption.slug"
                            :selected="
                                categoryOption.slug === InitialCategorySlug
                            "
                            :value="categoryOption.slug"
                            class="dark:bg-[#2a2a2a] dark:text-stone-300"
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
            class="dark:bg-[#1a1a1a] bg-white flex flex-wrap items-center rounded-2xl border dark:border-stone-700 mb-6 p-3 shadow-sm sticky top-[145px] z-10 backdrop-blur-sm bg-white/95 dark:bg-[#1a1a1a]/95"
        >
            <!-- Группа форматирования текста -->
            <div
                class="flex items-center border-r border-gray-200 dark:border-stone-600 pr-3 mr-3"
            >
                <button
                    @click="editor.chain().focus().toggleBold().run()"
                    :disabled="!editor.can().chain().focus().toggleBold().run()"
                    :class="[
                        editor.isActive('bold')
                            ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300'
                            : 'hover:bg-gray-100 dark:hover:bg-stone-700 text-gray-700 dark:text-stone-300',
                        'p-2 rounded-lg transition-all duration-200 transform hover:scale-105',
                    ]"
                    class="tooltip"
                    data-tooltip="Жирный (Ctrl+B)"
                >
                    <BoldIcon class="w-5 h-5" />
                </button>
                <button
                    @click="editor.chain().focus().toggleItalic().run()"
                    :disabled="
                        !editor.can().chain().focus().toggleItalic().run()
                    "
                    :class="[
                        editor.isActive('italic')
                            ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300'
                            : 'hover:bg-gray-100 dark:hover:bg-stone-700 text-gray-700 dark:text-stone-300',
                        'p-2 rounded-lg transition-all duration-200 transform hover:scale-105',
                    ]"
                    class="tooltip"
                    data-tooltip="Курсив (Ctrl+I)"
                >
                    <ItalicIcon class="w-5 h-5" />
                </button>
                <button
                    @click="editor.chain().focus().toggleUnderline().run()"
                    :class="[
                        editor.isActive('underline')
                            ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300'
                            : 'hover:bg-gray-100 dark:hover:bg-stone-700 text-gray-700 dark:text-stone-300',
                        'p-2 rounded-lg transition-all duration-200 transform hover:scale-105',
                    ]"
                    class="tooltip"
                    data-tooltip="Подчеркивание"
                >
                    <UnderlineIcon class="w-5 h-5" />
                </button>
            </div>

            <!-- Группа заголовков -->
            <div
                class="flex items-center border-r border-gray-200 dark:border-stone-600 pr-3 mr-3"
            >
                <button
                    @click="
                        editor.chain().focus().toggleHeading({ level: 1 }).run()
                    "
                    :class="[
                        editor.isActive('heading', { level: 1 })
                            ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300'
                            : 'hover:bg-gray-100 dark:hover:bg-stone-700 text-gray-700 dark:text-stone-300',
                        'px-3 py-2 rounded-lg font-bold transition-all duration-200 transform hover:scale-105',
                    ]"
                    class="tooltip"
                    data-tooltip="Заголовок 1"
                >
                    H1
                </button>
                <button
                    @click="
                        editor.chain().focus().toggleHeading({ level: 2 }).run()
                    "
                    :class="[
                        editor.isActive('heading', { level: 2 })
                            ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300'
                            : 'hover:bg-gray-100 dark:hover:bg-stone-700 text-gray-700 dark:text-stone-300',
                        'px-3 py-2 rounded-lg font-semibold transition-all duration-200 transform hover:scale-105',
                    ]"
                    class="tooltip"
                    data-tooltip="Заголовок 2"
                >
                    H2
                </button>
                <button
                    @click="
                        editor.chain().focus().toggleHeading({ level: 3 }).run()
                    "
                    :class="[
                        editor.isActive('heading', { level: 3 })
                            ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300'
                            : 'hover:bg-gray-100 dark:hover:bg-stone-700 text-gray-700 dark:text-stone-300',
                        'px-3 py-2 rounded-lg font-semibold transition-all duration-200 transform hover:scale-105',
                    ]"
                    class="tooltip"
                    data-tooltip="Заголовок 3"
                >
                    H3
                </button>
            </div>

            <!-- Группа выравнивания -->
            <div
                class="flex items-center border-r border-gray-200 dark:border-stone-600 pr-3 mr-3"
            >
                <button
                    @click="
                        editor.chain().focus().toggleTextAlign('left').run()
                    "
                    :class="[
                        editor.isActive({ textAlign: 'left' })
                            ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300'
                            : 'hover:bg-gray-100 dark:hover:bg-stone-700 text-gray-700 dark:text-stone-300',
                        'p-2 rounded-lg transition-all duration-200 transform hover:scale-105',
                    ]"
                    class="tooltip"
                    data-tooltip="Выровнять по левому краю"
                >
                    <AlignLeftIcon class="w-5 h-5" />
                </button>
                <button
                    @click="
                        editor.chain().focus().toggleTextAlign('center').run()
                    "
                    :class="[
                        editor.isActive({ textAlign: 'center' })
                            ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300'
                            : 'hover:bg-gray-100 dark:hover:bg-stone-700 text-gray-700 dark:text-stone-300',
                        'p-2 rounded-lg transition-all duration-200 transform hover:scale-105',
                    ]"
                    class="tooltip"
                    data-tooltip="Выровнять по центру"
                >
                    <AlignCenterIcon class="w-5 h-5" />
                </button>
                <button
                    @click="
                        editor.chain().focus().toggleTextAlign('right').run()
                    "
                    :class="[
                        editor.isActive({ textAlign: 'right' })
                            ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300'
                            : 'hover:bg-gray-100 dark:hover:bg-stone-700 text-gray-700 dark:text-stone-300',
                        'p-2 rounded-lg transition-all duration-200 transform hover:scale-105',
                    ]"
                    class="tooltip"
                    data-tooltip="Выровнять по правому краю"
                >
                    <AligRightIcon class="w-5 h-5" />
                </button>
                <button
                    @click="
                        editor.chain().focus().toggleTextAlign('justify').run()
                    "
                    :class="[
                        editor.isActive({ textAlign: 'justify' })
                            ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300'
                            : 'hover:bg-gray-100 dark:hover:bg-stone-700 text-gray-700 dark:text-stone-300',
                        'p-2 rounded-lg transition-all duration-200 transform hover:scale-105',
                    ]"
                    class="tooltip"
                    data-tooltip="Выровнять по ширине"
                >
                    <AlignJustifyIcon class="w-5 h-5" />
                </button>
            </div>

            <!-- Группа списков -->
            <div
                class="flex items-center border-r border-gray-200 dark:border-stone-600 pr-3 mr-3"
            >
                <button
                    @click="editor.chain().focus().toggleBulletList().run()"
                    :class="[
                        editor.isActive('bulletList')
                            ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300'
                            : 'hover:bg-gray-100 dark:hover:bg-stone-700 text-gray-700 dark:text-stone-300',
                        'p-2 rounded-lg transition-all duration-200 transform hover:scale-105',
                    ]"
                    class="tooltip"
                    data-tooltip="Маркированный список"
                >
                    <BulletListIcon class="w-5 h-5" />
                </button>
                <button
                    @click="editor.chain().focus().toggleOrderedList().run()"
                    :class="[
                        editor.isActive('orderedList')
                            ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300'
                            : 'hover:bg-gray-100 dark:hover:bg-stone-700 text-gray-700 dark:text-stone-300',
                        'p-2 rounded-lg transition-all duration-200 transform hover:scale-105',
                    ]"
                    class="tooltip"
                    data-tooltip="Нумерованный список"
                >
                    <OrderedListIcon class="w-5 h-5" />
                </button>
            </div>

            <!-- Группа дополнительных элементов -->
            <div class="flex items-center gap-1">
                <button
                    @click="setLink"
                    :class="[
                        editor.isActive('link')
                            ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300'
                            : 'hover:bg-gray-100 dark:hover:bg-stone-700 text-gray-700 dark:text-stone-300',
                        'p-2 rounded-lg transition-all duration-200 transform hover:scale-105',
                    ]"
                    class="tooltip"
                    data-tooltip="Добавить ссылку"
                >
                    <LinkIcon class="w-5 h-5" />
                </button>
                <button
                    @click="editor.chain().focus().toggleBlockquote().run()"
                    :class="[
                        editor.isActive('blockquote')
                            ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300'
                            : 'hover:bg-gray-100 dark:hover:bg-stone-700 text-gray-700 dark:text-stone-300',
                        'p-2 rounded-lg transition-all duration-200 transform hover:scale-105',
                    ]"
                    class="tooltip"
                    data-tooltip="Цитата"
                >
                    <BlockquoteIcon class="w-5 h-5" />
                </button>
                <button
                    @click="editor.chain().focus().toggleCodeBlock().run()"
                    :class="[
                        editor.isActive('codeBlock')
                            ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300'
                            : 'hover:bg-gray-100 dark:hover:bg-stone-700 text-gray-700 dark:text-stone-300',
                        'p-2 rounded-lg transition-all duration-200 transform hover:scale-105',
                    ]"
                    class="tooltip"
                    data-tooltip="Блок кода"
                >
                    <CodeIcon class="w-5 h-5" />
                </button>
                <button
                    @click="editor.chain().focus().setHorizontalRule().run()"
                    :class="'hover:bg-gray-100 dark:hover:bg-stone-700 text-gray-700 dark:text-stone-300 p-2 rounded-lg transition-all duration-200 transform hover:scale-105'"
                    class="tooltip"
                    data-tooltip="Горизонтальная линия"
                >
                    <HorizontalRuleIcon class="w-5 h-5" />
                </button>
                <button
                    @click="addImage"
                    :class="'hover:bg-gray-100 dark:hover:bg-stone-700 text-gray-700 dark:text-stone-300 p-2 rounded-lg transition-all duration-200 transform hover:scale-105'"
                    class="tooltip"
                    data-tooltip="Добавить изображение"
                >
                    <ImageIcon class="w-5 h-5" />
                </button>
            </div>
        </div>

        <!-- BUBBLE МЕНЮ -->
        <bubble-menu
            :editor="editor"
            :tippy-options="{ duration: 100, maxWidth: 'none' }"
            v-if="editor"
            class="bubble-menu"
        >
            <div
                class="flex items-center bg-white dark:bg-stone-800 rounded-xl shadow-lg border border-gray-200 dark:border-stone-600 p-2 backdrop-blur-sm bg-white/95 dark:bg-stone-800/95"
            >
                <button
                    @click="editor.chain().focus().toggleBold().run()"
                    :class="[
                        editor.isActive('bold')
                            ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300'
                            : 'hover:bg-gray-100 dark:hover:bg-stone-700 text-gray-700 dark:text-stone-300',
                        'p-2 rounded-lg transition-all duration-200',
                    ]"
                >
                    <BoldIcon class="w-4 h-4" />
                </button>
                <button
                    @click="editor.chain().focus().toggleItalic().run()"
                    :class="[
                        editor.isActive('italic')
                            ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300'
                            : 'hover:bg-gray-100 dark:hover:bg-stone-700 text-gray-700 dark:text-stone-300',
                        'p-2 rounded-lg transition-all duration-200',
                    ]"
                >
                    <ItalicIcon class="w-4 h-4" />
                </button>
                <button
                    @click="editor.chain().focus().toggleUnderline().run()"
                    :class="[
                        editor.isActive('underline')
                            ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300'
                            : 'hover:bg-gray-100 dark:hover:bg-stone-700 text-gray-700 dark:text-stone-300',
                        'p-2 rounded-lg transition-all duration-200',
                    ]"
                >
                    <UnderlineIcon class="w-4 h-4" />
                </button>
                <div class="w-px h-6 bg-gray-200 dark:bg-stone-600 mx-1"></div>
                <button
                    @click="setLink"
                    :class="[
                        editor.isActive('link')
                            ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300'
                            : 'hover:bg-gray-100 dark:hover:bg-stone-700 text-gray-700 dark:text-stone-300',
                        'p-2 rounded-lg transition-all duration-200',
                    ]"
                >
                    <LinkIcon class="w-4 h-4" />
                </button>
            </div>
        </bubble-menu>

        <!-- РЕДАКТОР -->
        <div class="relative">
            <editor-content :editor="editor" class="editor-content" />
            <div
                v-if="!editor?.getHTML()"
                class="absolute top-4 left-4 pointer-events-none text-gray-400 dark:text-stone-500 text-lg"
            >
                Начните писать вашу статью...
            </div>
        </div>
    </div>
</template>

<script setup>
import { BubbleMenu, Editor, EditorContent } from "@tiptap/vue-3";
import StarterKit from "@tiptap/starter-kit";
import Image from "@tiptap/extension-image";
import Heading from "@tiptap/extension-heading";
import BulletList from "@tiptap/extension-bullet-list";
import OrderedList from "@tiptap/extension-ordered-list";
import Underline from "@tiptap/extension-underline";
import Paragraph from "@tiptap/extension-paragraph";
import Blockquote from "@tiptap/extension-blockquote";
import HorizontalRule from "@tiptap/extension-horizontal-rule";
import HardBreak from "@tiptap/extension-hard-break";
import Link from "@tiptap/extension-link";
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
import LinkIcon from "vue-material-design-icons/LinkVariant.vue";
import TextAlign from "@tiptap/extension-text-align";
import { onMounted, onBeforeUnmount, ref, watch } from "vue";

const props = defineProps({
    initialTitle: String,
    initialContent: String,
    initialExcerpt: String,
    initialCardImage: String,
    initialCardCategory: String,
});

const emit = defineEmits(["save"]);

// Реактивные переменные
const title = ref(props.initialTitle || "");

const editor = ref(null);
const excerpt = ref(props.initialExcerpt || "");
const InitialCategorySlug = ref(props.initialCardCategory || "");
const cardImageFile = ref(null);
const previewImage = ref(props.initialCardImage || "");
const cardImageInput = ref(null);
const selectedCategory = ref(null);

function escapeRegExp(string) {
    return string.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
}

// Инициализация редактора
onMounted(() => {
    editor.value = new Editor({
        extensions: [
            StarterKit,
            Heading.configure({
                levels: [1, 2, 3],
                HTMLAttributes: {
                    class: "heading text-stone-800 dark:text-stone-200",
                },
            }),
            Link.configure({
                openOnClick: true,
                defaultProtocol: "https",
                HTMLAttributes: {
                    class: "text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 cursor-pointer underline",
                },
            }),
            BulletList,
            BubbleMenu,
            OrderedList,
            Underline,
            Paragraph.configure({
                HTMLAttributes: {
                    class: "text-stone-800 dark:text-stone-200 font-[Inter] leading-relaxed text-base",
                },
            }),
            Blockquote,
            TextAlign.configure({
                types: ["heading", "paragraph"],
            }),
            HorizontalRule,
            Image.configure({
                HTMLAttributes: {
                    class: "my-image rounded-md shadow-md max-w-full h-auto my-4 mx-auto",
                },
                allowBase64: true,
            }),
            HardBreak.configure({
                HTMLAttributes: {
                    class: "hover:bg-gray-200 rounded",
                },
            }),
        ],

        content: props.initialContent || "",
        editorProps: {
            attributes: {
                class: "prose prose-lg dark:prose-invert min-h-[600px] max-w-none mx-auto focus:outline-none p-6 border-2 border-dashed border-gray-200 dark:border-stone-700 rounded-2xl bg-white dark:bg-[#1a1a1a] shadow-sm hover:border-indigo-300 dark:hover:border-indigo-600 transition-colors duration-300",
            },
        },
    });
    selectedCategory.value = props.initialCardCategory || "uncategorized";
    title.value = props.initialTitle || "";
    excerpt.value = props.initialExcerpt || "";
});

// Очистка
onBeforeUnmount(() => {
    editor.value?.destroy();
});

const categoryOptions = ref([
    { name: "Без категории", slug: "uncategorized" },
    { name: "Игры", slug: "games" },
    { name: "Красота и стиль", slug: "beauty" },
    { name: "Еда", slug: "food" },
    { name: "Наука", slug: "science" },
    { name: "Спорт", slug: "sport" },
    { name: "Технологии", slug: "tech" },
    { name: "Путешествия", slug: "travel" },
    { name: "Искусство", slug: "art" },
]);

const setLink = () => {
    const previousUrl = editor.value.getAttributes("link").href;
    const url = window.prompt("Введите URL ссылки:", previousUrl);

    if (url === null) {
        return;
    }

    if (url === "") {
        editor.value.chain().focus().extendMarkRange("link").unsetLink().run();
        return;
    }

    const finalUrl = url.match(/^https?:\/\//) ? url : `https://${url}`;

    editor.value
        .chain()
        .focus()
        .extendMarkRange("link")
        .setLink({ href: finalUrl })
        .run();
};

//Загрузка превью
const handleCardImageUpload = (e) => {
    const file = e.target.files[0];
    if (!file) return;

    // Проверка типа файла
    if (!file.type.match("image.*")) {
        alert("Пожалуйста, выберите изображение");
        return;
    }

    // Проверка размера (например, до 2MB)
    if (file.size > 2 * 1024 * 1024) {
        alert("Максимальный размер файла - 2MB");
        return;
    }

    cardImageFile.value = file;
    previewImage.value = URL.createObjectURL(file);
};

// Удаление изображения
const removeCardImage = () => {
    cardImageFile.value = null;
    previewImage.value = "";
    cardImageInput.value.value = "";
};

// Добавление изображения в content
const addImage = () => {
    const input = document.createElement("input");
    input.type = "file";
    input.accept = "image/*";

    input.onchange = async (e) => {
        const file = e.target.files[0];
        if (!file) return;

        if (file.size > 2 * 1024 * 1024) {
            alert("Максимальный размер файла - 2MB");
            return;
        }

        // Создаем временный URL для предпросмотра
        const blobUrl = URL.createObjectURL(file);

        // Вставляем изображение в редактор
        editor.value.chain().focus().setImage({ src: blobUrl }).run();
    };

    input.click();
};

const prepareArticleData = async () => {
    if (!editor.value) throw new Error("Editor not ready");

    let content = editor.value.getHTML();
    const blobUrls = [...content.matchAll(/src="(blob:[^"]+)"/g)];

    // Загружаем картинки из редактора, отправляем на сервер и возвращаем путь
    if (blobUrls.length > 0) {
        const uploadResults = await Promise.all(
            blobUrls.map(async ([_, blobUrl]) => {
                const response = await fetch(blobUrl);
                const blob = await response.blob();
                const formData = new FormData();
                formData.append(
                    "image",
                    blob,
                    `image-${Date.now()}.${blob.type.split("/")[1] || "png"}`
                );

                const { data } = await axios.post("/upload-image", formData);
                return { blobUrl, serverUrl: data.url };
            })
        );

        uploadResults.forEach(({ blobUrl, serverUrl }) => {
            content = content.replace(
                new RegExp(escapeRegExp(blobUrl), "g"),
                serverUrl
            );
        });
    }

    // 4. Создаем FormData для отправки
    const formData = new FormData();
    formData.append("title", title.value);
    formData.append("content", content);
    formData.append("excerpt", excerpt.value);

    if (selectedCategory.value) {
        formData.append("category_slug", selectedCategory.value);
    }

    if (cardImageFile.value) {
        formData.append("card_image", cardImageFile.value);
    }

    return {
        formData,
        blobUrls,
    };
};

// Метод для очистки blob-ссылок
const cleanupBlobUrls = (blobUrls) => {
    if (blobUrls && blobUrls.length > 0) {
        blobUrls.forEach(([_, blobUrl]) => {
            URL.revokeObjectURL(blobUrl);
        });
    }
};

defineExpose({
    prepareArticleData,
    cleanupBlobUrls,
    title,
    excerpt,
    cardImageFile,
    previewImage,
    selectedCategory,
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
    background: #1f2937;
    color: white;
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
    border-top-color: #1f2937;
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

.dark .tooltip::after {
    border-top-color: #374151;
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



.dark .prose h2.heading {
    color: rgb(231 229 228 / var(--tw-text-opacity, 1));
}

.dark .prose h3.heading {
    color: rgb(231 229 228 / var(--tw-text-opacity, 1));
}

.prose p {
    line-height: 1.7;
    margin: 0.4rem 0;
}

.prose ul,
.prose ol {
    padding: 0 1rem;
    margin: 1.25rem 1rem 1.25rem 0.4rem;
    color: rgb(231 229 228 / var(--tw-text-opacity, 1));

    li p {
        margin-top: 0.25em;
        margin-bottom: 0.25em;
    }
}

.prose ol {
    list-style: decimal;
}

.prose ul {
    list-style: disc;
}

.prose blockquote {
    border-left: 5px solid #444;
    padding-left: 1.5rem;
    margin: 1.5rem 0;
    font-style: italic;
    background-color:rgba(172, 164, 253, 0.2);
    border-radius: 0.3rem;
}

.dark .prose blockquote {
    border-left-color: #4b5563;
    color: #9ca3af;
}

.prose pre {
    background: #1f2937;
    border-radius: 0.75rem;
    color: #e5e7eb;
    font-family: "JetBrains Mono", monospace;
    margin: 1.5rem 0;
    padding: 1.5rem;
    overflow-x: auto;
    border: 1px solid #374151;
}

.prose code {
    background: #f3f4f6;
    color: #dc2626;
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
    font-size: 0.875rem;
}

.dark .prose code {
    background: #374151;
    color: #f87171;
}

.prose pre code {
    background: none;
    color: inherit;
    padding: 0;
}

.prose hr {
    border: none;
    border-top: 2px solid #e5e7eb;
    margin: 2rem 0;
}

.dark .prose hr {
    border-top-color: #374151;
}

.prose .my-image {
    box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    transition: transform 0.3s ease;
}

/* Bubble menu стили */
.bubble-menu {
    animation: slideDown 0.2s ease-out;
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
    border-color: #8b5cf6 !important;
    box-shadow: 0 0 0 3px rgb(139 92 246 / 0.1);
}

.dark .ProseMirror:focus {
    border-color: #a78bfa !important;
    box-shadow: 0 0 0 3px rgb(167 139 250 / 0.2);
}
</style>
