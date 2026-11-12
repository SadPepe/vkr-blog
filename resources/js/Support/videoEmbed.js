// Встраивание видео с внешних площадок.
//
// В статье хранится только безопасная обёртка:
// <div class="video-embed" data-video-provider="rutube" data-video-id="..."></div>
// Сам iframe создаётся на клиенте по белому списку провайдеров. Это не даёт
// авторам вставлять произвольные iframe и скрипты через HTML статьи.

export const VIDEO_PROVIDER_LABELS = {
    rutube: "Rutube",
    vk: "VK Video",
};

const RUTUBE_ID_PATTERN = /^[A-Za-z0-9]{16,64}$/;
const VK_VIDEO_ID_PATTERN = /^-?\d+_\d+$/;
const VK_HASH_PATTERN = /^[A-Za-z0-9_-]+$/;

export const videoProviderLabel = (provider) => VIDEO_PROVIDER_LABELS[provider] || "Видео";

const invalid = (error) => ({ ok: false, error });

const parseHttpUrl = (value) => {
    const raw = String(value || "").trim();
    if (!raw) return null;

    try {
        return new URL(raw);
    } catch {
        try {
            return new URL(`https://${raw}`);
        } catch {
            return null;
        }
    }
};

export const parseVideoUrl = (input) => {
    let raw = String(input || "").trim();

    if (!raw) {
        return invalid("Вставьте ссылку на видео.");
    }

    // Пользователь может вставить не только ссылку, но и готовый iframe-код.
    const iframeSrc = raw.match(/<iframe\b[^>]*\bsrc\s*=\s*["']([^"']+)["']/i);
    if (iframeSrc) {
        raw = iframeSrc[1].replace(/&amp;/g, "&");
    }

    const url = parseHttpUrl(raw);
    if (!url || !["http:", "https:"].includes(url.protocol)) {
        return invalid("Вставьте корректную ссылку на видео.");
    }

    const host = url.hostname.toLowerCase().replace(/^www\./, "");
    const path = url.pathname.replace(/\/+$/, "");

    // Rutube: страница видео и embed-ссылка.
    if (host === "rutube.ru") {
        const match = path.match(/^\/(?:video|play\/embed)\/([^/]+)/);
        const videoId = match?.[1] || "";

        if (RUTUBE_ID_PATTERN.test(videoId)) {
            return { ok: true, provider: "rutube", videoId, hash: "" };
        }
    }

    // VK Video: страница видео или embed-ссылка video_ext.php.
    if (["vk.com", "m.vk.com", "vk.ru", "vkvideo.ru", "m.vkvideo.ru"].includes(host)) {
        let oid = url.searchParams.get("oid") || "";
        let id = url.searchParams.get("id") || "";
        const hash = url.searchParams.get("hash") || "";

        if (!oid || !id) {
            const fromPath = path.match(/\/video(-?\d+)_(\d+)/);
            const fromZ = (url.searchParams.get("z") || "").match(/^video(-?\d+)_(\d+)$/);
            const match = fromPath || fromZ;

            if (match) {
                oid = match[1];
                id = match[2];
            }
        }

        const videoId = `${oid}_${id}`;
        if (VK_VIDEO_ID_PATTERN.test(videoId) && (!hash || VK_HASH_PATTERN.test(hash))) {
            return { ok: true, provider: "vk", videoId, hash };
        }
    }

    return invalid("Поддерживаются ссылки Rutube и VK Video.");
};

export const buildVideoEmbedUrl = ({ provider, videoId, hash = "" } = {}) => {
    const normalizedProvider = String(provider || "").toLowerCase();
    const normalizedId = String(videoId || "");
    const normalizedHash = String(hash || "");

    if (normalizedProvider === "rutube" && RUTUBE_ID_PATTERN.test(normalizedId)) {
        return `https://rutube.ru/play/embed/${encodeURIComponent(normalizedId)}/`;
    }

    if (normalizedProvider === "vk" && VK_VIDEO_ID_PATTERN.test(normalizedId)) {
        const [oid, id] = normalizedId.split("_");
        const params = new URLSearchParams({ oid, id, hd: "2" });

        if (normalizedHash && VK_HASH_PATTERN.test(normalizedHash)) {
            params.set("hash", normalizedHash);
        }

        return `https://vk.com/video_ext.php?${params.toString()}`;
    }

    return "";
};

const PLAY_ICON = '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M9 5.5v13l10-6.5-10-6.5z" /></svg>';

/** Карточка «смотреть»: до нажатия на странице нет ни одного запроса к провайдеру. */
const createFacade = ({ provider, src, wrapper }) => {
    const label = videoProviderLabel(provider);

    const button = document.createElement("button");
    button.type = "button";
    button.className = "video-embed__facade";
    button.setAttribute("aria-label", `Смотреть видео ${label}`);

    const play = document.createElement("span");
    play.className = "video-embed__play";
    play.innerHTML = PLAY_ICON;

    const providerName = document.createElement("span");
    providerName.className = "video-embed__provider";
    providerName.textContent = label;

    const hint = document.createElement("span");
    hint.className = "video-embed__hint";
    hint.textContent = `Плеер ${label} загрузится после нажатия и получит ваш IP-адрес и свои файлы cookie.`;

    button.append(play, providerName, hint);
    button.addEventListener("click", () => loadPlayer({ wrapper, src, provider }), { once: true });

    return button;
};

const loadPlayer = ({ wrapper, src, provider }) => {
    const iframe = document.createElement("iframe");
    iframe.src = src;
    iframe.title = `Видео ${videoProviderLabel(provider)}`;
    iframe.allow = "accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share";
    iframe.allowFullscreen = true;
    iframe.referrerPolicy = "no-referrer";

    wrapper.classList.remove("video-embed--facade");
    wrapper.classList.add("video-embed--ready");
    wrapper.replaceChildren(iframe);
};

/**
 * Заменяет сохранённые обёртки на карточку «смотреть»; iframe появляется
 * только по нажатию читателя. Повторный вызов безопасен: уже загруженные
 * плееры и готовые карточки пропускаются.
 */
export const enhanceVideoEmbeds = (root) => {
    if (!root) return 0;

    let enhanced = 0;

    root.querySelectorAll(".video-embed[data-video-provider][data-video-id]").forEach((wrapper) => {
        if (wrapper.querySelector("iframe") || wrapper.querySelector(".video-embed__facade")) return;

        const provider = wrapper.dataset.videoProvider || "";
        const videoId = wrapper.dataset.videoId || "";
        const hash = wrapper.dataset.videoHash || "";
        const src = buildVideoEmbedUrl({ provider, videoId, hash });

        if (!src) {
            wrapper.classList.add("video-embed--invalid");
            wrapper.textContent = "Не удалось загрузить видео.";
            return;
        }

        wrapper.classList.add("video-embed--facade");
        wrapper.replaceChildren(createFacade({ provider, src, wrapper }));
        enhanced += 1;
    });

    return enhanced;
};
