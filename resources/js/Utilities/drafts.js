// Черновики статей хранятся только в localStorage браузера.
// Ключ вида blog_draft_article-create или blog_draft_article-<slug>.
//
// Фото нельзя хранить в localStorage как blob:-ссылки: они живут только до
// перезагрузки документа. Поэтому сами файлы складываем в IndexedDB, а в HTML
// черновика пишем служебную ссылку draft-image:<id>. При восстановлении
// достаём Blob и снова превращаем его в blob:-URL для редактора.

export const DRAFT_PREFIX = "blog_draft_";
export const MAX_DRAFT_SIZE = 400 * 1024; // 400KB — меньше лимита localStorage
export const CREATE_DRAFT_KEY = "article-create";
export const DRAFTS_UPDATED_EVENT = "drafts:updated";
export const DRAFT_IMAGE_PREFIX = "draft-image:";

const DRAFT_IMAGE_DB = "blog-drafts-images";
const DRAFT_IMAGE_STORE = "images";
const blobImageIds = new Map();
let draftImageDbPromise = null;

export const draftStorageKey = (key) => DRAFT_PREFIX + (key || "default");

export const notifyDraftsUpdated = () => {
    try {
        window.dispatchEvent(new CustomEvent(DRAFTS_UPDATED_EVENT));
    } catch {
        /* окно недоступно */
    }
};

const randomId = () => {
    if (typeof crypto !== "undefined" && typeof crypto.randomUUID === "function") {
        return crypto.randomUUID();
    }

    return `${Date.now()}-${Math.random().toString(36).slice(2)}`;
};

const openDraftImageDb = () => {
    if (typeof indexedDB === "undefined") return Promise.resolve(null);
    if (draftImageDbPromise) return draftImageDbPromise;

    draftImageDbPromise = new Promise((resolve) => {
        try {
            const request = indexedDB.open(DRAFT_IMAGE_DB, 1);
            request.onupgradeneeded = () => {
                request.result.createObjectStore(DRAFT_IMAGE_STORE, { keyPath: "id" });
            };
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => resolve(null);
        } catch {
            resolve(null);
        }
    });

    return draftImageDbPromise;
};

const withDraftImageStore = async (mode, callback) => {
    const db = await openDraftImageDb();
    if (!db) return null;

    return new Promise((resolve) => {
        try {
            const transaction = db.transaction(DRAFT_IMAGE_STORE, mode);
            const store = transaction.objectStore(DRAFT_IMAGE_STORE);
            const request = callback(store);
            request.onsuccess = () => resolve(request.result ?? null);
            request.onerror = () => resolve(null);
        } catch {
            resolve(null);
        }
    });
};

export const putDraftImage = (id, blob) =>
    withDraftImageStore("readwrite", (store) => store.put({ id, blob }));

export const getDraftImage = (id) => withDraftImageStore("readonly", (store) => store.get(id));

export const deleteDraftImages = async (ids) => {
    const uniqueIds = [...new Set((ids || []).filter(Boolean))];
    if (!uniqueIds.length) return;

    await Promise.all(uniqueIds.map((id) => withDraftImageStore("readwrite", (store) => store.delete(id))));

    for (const [url, id] of blobImageIds.entries()) {
        if (uniqueIds.includes(id)) blobImageIds.delete(url);
    }
};

// Создаём blob:-URL и запоминаем соответствие файла и служебного id.
// Запись в IndexedDB стартует сразу, чтобы к моменту autosave черновика
// изображение уже было доступно после перезагрузки.
export const createDraftImageUrl = (file) => {
    const url = URL.createObjectURL(file);
    const id = randomId();

    blobImageIds.set(url, id);
    putDraftImage(id, file);

    return url;
};

export const registerDraftImageUrl = (url, id) => {
    if (url && id) blobImageIds.set(url, id);
};

export const draftImageIdForUrl = (url) => blobImageIds.get(url) || null;

export const extractDraftImageIds = (html) =>
    [...String(html || "").matchAll(/draft-image:([^"']+)/g)].map((match) => match[1]);

// Перед записью в localStorage заменяем нестабильные blob:-URL на id из IndexedDB.
export const serializeDraftImages = (html) =>
    String(html || "").replace(/src="(blob:[^"]+)"/g, (match, url) => {
        const id = draftImageIdForUrl(url);
        return id ? `src="${DRAFT_IMAGE_PREFIX}${id}"` : match;
    });

// После чтения черновика возвращаем настоящие blob:-URL для редактора.
export const hydrateDraftImages = async (html) => {
    let result = String(html || "");
    const ids = [...new Set(extractDraftImageIds(result))];

    for (const id of ids) {
        const record = await getDraftImage(id);
        if (!record?.blob) continue;

        const url = URL.createObjectURL(record.blob);
        registerDraftImageUrl(url, id);
        result = result.split(`${DRAFT_IMAGE_PREFIX}${id}`).join(url);
    }

    return result;
};

export const readDraft = (key) => {
    try {
        const raw = localStorage.getItem(draftStorageKey(key));
        if (!raw) return null;
        const data = JSON.parse(raw);
        if (!data || typeof data !== "object") return null;
        return data;
    } catch {
        return null;
    }
};

export const writeDraft = (key, data) => {
    try {
        localStorage.setItem(draftStorageKey(key), JSON.stringify(data));
        notifyDraftsUpdated();
        return true;
    } catch {
        // Квота переполнена — черновик не сохраняется, но редактор жив.
        return false;
    }
};

export const removeDraft = (key) => {
    const draft = readDraft(key);
    if (draft?.content) {
        deleteDraftImages(extractDraftImageIds(draft.content));
    }

    try {
        localStorage.removeItem(draftStorageKey(key));
    } catch {
        /* localStorage недоступен */
    }
    notifyDraftsUpdated();
};

/**
 * Список всех черновиков, свежие сверху.
 * @returns {Array<{key: string, isNew: boolean, slug: string|null, title: string, excerpt: string, savedAt: number|null, contentLength: number}>}
 */
export const listDrafts = () => {
    const drafts = [];

    try {
        for (let i = 0; i < localStorage.length; i += 1) {
            const storageKey = localStorage.key(i);
            if (!storageKey || !storageKey.startsWith(DRAFT_PREFIX)) continue;

            const key = storageKey.slice(DRAFT_PREFIX.length);
            const data = readDraft(key);
            if (!data) continue;

            const isNew = key === CREATE_DRAFT_KEY;
            drafts.push({
                key,
                isNew,
                slug: !isNew && key.startsWith("article-") ? key.slice("article-".length) : null,
                title: typeof data.title === "string" ? data.title.trim() : "",
                excerpt: typeof data.excerpt === "string" ? data.excerpt.trim() : "",
                savedAt: Number(data.savedAt) || null,
                contentLength: typeof data.content === "string" ? data.content.length : 0,
            });
        }
    } catch {
        return [];
    }

    return drafts.sort((a, b) => (b.savedAt || 0) - (a.savedAt || 0));
};

export const formatDraftTime = (timestamp) => {
    if (!timestamp) return "без даты";

    const date = new Date(timestamp);
    if (Number.isNaN(date.getTime())) return "без даты";

    const now = new Date();
    const time = date.toLocaleTimeString("ru-RU", { hour: "2-digit", minute: "2-digit" });

    if (date.toDateString() === now.toDateString()) return `сегодня, ${time}`;

    const yesterday = new Date(now);
    yesterday.setDate(now.getDate() - 1);
    if (date.toDateString() === yesterday.toDateString()) return `вчера, ${time}`;

    return `${date.toLocaleDateString("ru-RU", { day: "2-digit", month: "short" })}, ${time}`;
};

export const draftTypeLabel = (draft) => (draft.isNew ? "Новая статья" : `Редактирование · /${draft.slug}`);
