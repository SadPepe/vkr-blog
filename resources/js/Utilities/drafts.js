// Черновики статей хранятся только в localStorage браузера.
// Ключ вида blog_draft_article-create или blog_draft_article-<slug>.

export const DRAFT_PREFIX = "blog_draft_";
export const MAX_DRAFT_SIZE = 400 * 1024; // 400KB — меньше лимита localStorage
export const CREATE_DRAFT_KEY = "article-create";
export const DRAFTS_UPDATED_EVENT = "drafts:updated";

export const draftStorageKey = (key) => DRAFT_PREFIX + (key || "default");

export const notifyDraftsUpdated = () => {
    try {
        window.dispatchEvent(new CustomEvent(DRAFTS_UPDATED_EVENT));
    } catch {
        /* окно недоступно */
    }
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
