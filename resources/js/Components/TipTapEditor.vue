<template>
    <div class="relative max-w-4xl mx-auto dark:bg-transparent">
        <!-- Баннер восстановленного черновика -->
        <div
            v-if="restoredDraft"
            class="mb-4 flex items-center justify-between gap-3 rounded-md border border-amber-300 bg-amber-50 px-4 py-3 dark:border-amber-500/30 dark:bg-amber-500/10"
        >
            <div class="flex items-center gap-2 text-sm text-amber-800 dark:text-amber-200">
                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span> Восстановлен черновик{{ restoredDraft.savedAt ? ` от ${formatSavedTime(restoredDraft.savedAt)}` : "" }} </span>
            </div>
            <button
                type="button"
                @click="discardRestoredDraft"
                class="rounded-md px-3 py-1.5 text-xs font-medium text-amber-800 transition-colors hover:bg-amber-100 dark:text-amber-300 amber-500/20"
            >
                Отменить
            </button>
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
            class="bg-surface flex flex-wrap items-center rounded-md border mb-6 p-3 shadow-sm sticky top-24 z-10 backdrop-blur-sm bg-surface/95"
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
            </div>

            <span class="ml-auto hidden text-[11px] font-normal text-muted md:inline">
                Черновик сохраняется автоматически (Ctrl+S)
            </span>
        </div>

        <!-- BUBBLE МЕНЮ -->
        <bubble-menu :editor="editor" :tippy-options="{ duration: 100, maxWidth: 'none' }" v-if="editor" class="bubble-menu">
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
    </div>
</template>

<script setup>
import { BubbleMenu, Editor, EditorContent } from "@tiptap/vue-3";
import StarterKit from "@tiptap/starter-kit";
import Image from "@tiptap/extension-image";
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
import LinkIcon from "vue-material-design-icons/LinkVariant.vue";
import { onMounted, onBeforeUnmount, ref, watch } from "vue";
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

const hasContent = ref(false);
const isDirty = ref(false);
const restoredDraft = ref(null);
const editorError = ref("");
let editorErrorTimer = null;

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
    if (!editor.value) return;
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
    hasContent.value = !!editor.value && editor.value.state.doc.textContent.trim().length > 0;
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
    const restoredContent = draft?.content || props.initialContent || "";

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
        ],
        content: restoredContent,
        editorProps: {
            attributes: {
                class: "prose prose-lg dark:prose-invert min-h-[600px] max-w-none mx-auto focus:outline-none p-6 border-2 border-dashed border-line  rounded-md bg-surface  shadow-sm hover:border-accent transition-colors duration-300",
            },
            handleKeyDown,
        },
    });

    // Восстанавливаем значения полей
    title.value = (draft?.title !== undefined ? draft.title : "") || props.initialTitle || "";
    selectedCategory.value = (draft?.category !== undefined ? draft.category : "") || props.initialCardCategory || "";

    if (draft?.content) {
        restoredDraft.value = { savedAt: draft.savedAt || Date.now() };
        isDirty.value = true;
    }

    editor.value.on("update", () => {
        syncHasContent();
        queueDraftSave();
    });

    // Упрощаем вставку из Word/PDF: сбрасываем скрытое форматирование,
    // оставляя только осмысленные структуры (заголовки, списки т.п.)
    editor.value.on("paste", (_event, _slice, marksInRange) => {
        // Tiptap контролирует пастинг через schema — Word-мусор уже отсекается.
        // Здесь ничего не делаем, но оставляем обработчик для будущих проверок.
        void marksInRange;
    });

    syncHasContent();

    const handleBeforeUnload = (e) => {
        if (isDirty.value && currentSnapshot() !== initialSnapshot()) {
            e.preventDefault();
            e.returnValue = "";
        }
    };
    window.addEventListener("beforeunload", handleBeforeUnload);
    window.__tiptapBeforeUnload = handleBeforeUnload;
});

// Очистка
onBeforeUnmount(() => {
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

// Добавление изображения в content
const addImage = () => {
    const input = document.createElement("input");
    input.type = "file";
    input.accept = "image/*";

    input.onchange = async (e) => {
        const file = e.target.files[0];
        if (!file) return;

        if (file.size > 2 * 1024 * 1024) {
            showError("Максимальный размер файла - 2MB");
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

    // Клиентская валидация: пустой пост (ни текста, ни картинки) не отправляем
    let content = editor.value.getHTML();
    if (!editor.value.state.doc.textContent.trim() && !content.includes("<img")) {
        return { error: "Добавьте текст или изображение — пустой пост опубликовать нельзя." };
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
    font-family: "JetBrains Mono", monospace;
    margin: 1.5rem 0;
    padding: 1.5rem;
    overflow-x: auto;
    border: 1px solid rgb(var(--c-line));
}

.prose code {
    background: rgb(var(--c-surface-muted));
    color: rgb(var(--c-accent));
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
    font-size: 0.875rem;
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
    border-color: rgb(var(--c-accent)) !important;
    box-shadow: 0 3px rgb(var(--c-accent) / 0.1);
}
</style>
