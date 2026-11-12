<template>
    <template v-for="(part, index) in parts" :key="index">
        <Link
            v-if="part.type === 'mention'"
            :href="route('authors.byUsername', part.username)"
            class="mention"
        >@{{ part.username }}</Link>
        <a
            v-else-if="part.type === 'link'"
            :href="part.href"
            target="_blank"
            rel="noopener noreferrer"
            class="break-words text-accent underline underline-offset-2 transition-colors hover:text-accent-hover"
        >{{ part.text }}</a>
        <template v-else>{{ part.text }}</template>
    </template>
</template>

<script setup>
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";

const props = defineProps({
    text: {
        type: String,
        default: "",
    },
    linkify: {
        type: Boolean,
        default: true,
    },
});

const MENTION_PATTERN = /(?<![\p{L}\p{N}_@])@([a-z0-9_]{3,32})(?![\p{L}\p{N}_])/giu;
const URL_PATTERN = /(?:https?:\/\/|www\.)[^\s<>"']+/giu;

const pushMentions = (text, result) => {
    let lastIndex = 0;

    for (const match of text.matchAll(MENTION_PATTERN)) {
        if (match.index > lastIndex) {
            result.push({ type: "text", text: text.slice(lastIndex, match.index) });
        }

        result.push({ type: "mention", username: match[1] });
        lastIndex = match.index + match[0].length;
    }

    if (lastIndex < text.length) {
        result.push({ type: "text", text: text.slice(lastIndex) });
    }
};

const splitTrailingPunctuation = (rawUrl) => {
    let url = rawUrl;
    let trailing = "";

    while (url.length > 0) {
        const lastChar = url.at(-1);

        if (/[.,!?;:]/.test(lastChar)) {
            trailing = lastChar + trailing;
            url = url.slice(0, -1);
            continue;
        }

        // Закрывающую скобку оставляем в ссылке, только если она парная.
        if (lastChar === ")" && (url.match(/\(/g)?.length ?? 0) < (url.match(/\)/g)?.length ?? 0)) {
            trailing = lastChar + trailing;
            url = url.slice(0, -1);
            continue;
        }

        break;
    }

    return { url, trailing };
};

const isHttpUrl = (url) => {
    try {
        const parsed = new URL(url);
        return parsed.protocol === "http:" || parsed.protocol === "https:";
    } catch {
        return false;
    }
};

const parts = computed(() => {
    const text = props.text ?? "";

    if (!props.linkify) {
        const result = [];
        pushMentions(text, result);

        return result.length ? result : [{ type: "text", text }];
    }

    const result = [];
    let lastIndex = 0;

    for (const match of text.matchAll(URL_PATTERN)) {
        pushMentions(text.slice(lastIndex, match.index), result);

        const { url: visibleUrl, trailing } = splitTrailingPunctuation(match[0]);
        const href = visibleUrl.startsWith("www.") ? `https://${visibleUrl}` : visibleUrl;

        if (isHttpUrl(href)) {
            result.push({ type: "link", text: visibleUrl, href });

            if (trailing) {
                result.push({ type: "text", text: trailing });
            }
        } else {
            result.push({ type: "text", text: match[0] });
        }

        lastIndex = match.index + match[0].length;
    }

    pushMentions(text.slice(lastIndex), result);

    return result.length ? result : [{ type: "text", text }];
});
</script>
