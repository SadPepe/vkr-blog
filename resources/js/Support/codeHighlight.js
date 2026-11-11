/**
 * Оформление блоков кода.
 *
 * Язык берём только из явной разметки (`<code class="language-php">`), которую
 * ставит селект в редакторе. Автодетект намеренно не включаем: на коротких
 * фрагментах он чаще ошибается, чем помогает, и грузит ленту лишней работой.
 *
 * После подсветки блок получает шапку с названием языка и кнопкой копирования.
 */
import hljs from "highlight.js/lib/core";
import bash from "highlight.js/lib/languages/bash";
import css from "highlight.js/lib/languages/css";
import javascript from "highlight.js/lib/languages/javascript";
import json from "highlight.js/lib/languages/json";
import php from "highlight.js/lib/languages/php";
import sql from "highlight.js/lib/languages/sql";
import typescript from "highlight.js/lib/languages/typescript";
import xml from "highlight.js/lib/languages/xml";
import { CODE_LANGUAGES } from "@/Support/codeLanguages";

const GRAMMARS = { bash, css, javascript, json, php, sql, typescript, xml };

const ALIASES = {
    js: "javascript",
    ts: "typescript",
    html: "xml",
    htm: "xml",
    svg: "xml",
    sh: "bash",
    shell: "bash",
    zsh: "bash",
};

// hljs нужен для статьи и ленты: язык берём из class="language-…"
for (const [name, grammar] of Object.entries(GRAMMARS)) {
    hljs.registerLanguage(name, grammar);
}

const languageLabel = (className) => {
    const match = /(?:^|\s)language-([\w-]+)/.exec(className);
    if (!match) return "Код";

    const value = ALIASES[match[1]] ?? match[1];

    return CODE_LANGUAGES.find((item) => item.value === value)?.label ?? match[1].toUpperCase();
};

/**
 * Подсвечивает блоки внутри root. Уже обработанные пропускает, чтобы
 * повторный вызов после перерисовки не портил DOM.
 */
const highlightWithin = (root) => {
    let highlighted = 0;

    root.querySelectorAll("pre code").forEach((block) => {
        if (block.dataset.highlighted) return;

        const match = /(?:^|\s)language-([\w-]+)/.exec(block.className);
        if (!match) return;

        const language = ALIASES[match[1]] ?? match[1];
        if (!GRAMMARS[language]) return;

        try {
            block.innerHTML = hljs.highlight(block.textContent ?? "", {
                language,
                ignoreIllegals: true,
            }).value;
            block.dataset.highlighted = language;
            highlighted += 1;
        } catch {
            // Битая разметка или неизвестный язык — блок остаётся монохромным
        }
    });

    return highlighted;
};

const copyText = async (text, button) => {
    let resetTimer;

    const reset = () => {
        button.textContent = "Скопировать";
        delete button.dataset.copied;
    };

    const flash = (text, copied) => {
        window.clearTimeout(resetTimer);
        button.textContent = text;

        if (copied) {
            button.dataset.copied = "true";
        } else {
            delete button.dataset.copied;
        }

        resetTimer = window.setTimeout(reset, 2200);
    };

    try {
        await navigator.clipboard.writeText(text);
        flash("Скопировано", true);
        return;
    } catch {
        // Старые браузеры и http: clipboard API недоступен — копируем через textarea
    }

    const area = document.createElement("textarea");
    area.value = text;
    area.setAttribute("readonly", "");
    area.style.position = "fixed";
    area.style.top = "-1000px";
    area.style.opacity = "0";
    document.body.append(area);
    area.select();

    let copied = false;

    try {
        copied = document.execCommand("copy");
    } catch {
        copied = false;
    } finally {
        area.remove();
    }

    flash(copied ? "Скопировано" : "Не удалось", copied);
};

const createBar = (code) => {
    const bar = document.createElement("div");
    bar.className = "code-block__bar";

    const label = document.createElement("span");
    label.className = "code-block__lang";
    label.textContent = languageLabel(code.className);

    const button = document.createElement("button");
    button.type = "button";
    button.className = "code-block__copy";
    button.textContent = "Скопировать";
    button.setAttribute("aria-live", "polite");
    button.addEventListener("click", () => copyText(code.textContent ?? "", button));

    bar.append(label, button);

    return bar;
};

/**
 * Оборачивает каждый <pre> в панель с шапкой и подсвечивает код.
 * Идемпотентно: повторный вызов на том же DOM ничего не дублирует.
 */
export function enhanceCodeBlocks(root) {
    if (!root) return 0;

    root.querySelectorAll("pre").forEach((pre) => {
        if (pre.closest(".code-block")) return;

        const code = pre.querySelector("code");
        if (!code) return;

        const wrapper = document.createElement("div");
        wrapper.className = "code-block";
        pre.replaceWith(wrapper);
        wrapper.append(createBar(code), pre);
    });

    return highlightWithin(root);
}
