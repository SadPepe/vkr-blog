import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
        "./resources/js/**/*.vue",
    ],
    // Классы, которые собираются в JS (Support/videoEmbed.js, фасад видео):
    // в .vue-файлах их нет, и Tailwind вырезает правила из @layer components.
    safelist: [
        "video-embed__facade",
        "video-embed__play",
        "video-embed__provider",
        "video-embed__hint",
        "video-embed--facade",
        "video-embed--ready",
        "video-embed--invalid",
    ],
    // Тёмные темы включаются атрибутом data-mode="dark" (data-theme отвечает за палитру)
    darkMode: ["class", '[data-mode="dark"]'],
    theme: {
        extend: {
            fontFamily: {
                sans: ['"Golos Text"', ...defaultTheme.fontFamily.sans],
                display: ["Unbounded", ...defaultTheme.fontFamily.sans],
            },
            colors: {
                page: "rgb(var(--c-page) / <alpha-value>)",
                surface: {
                    DEFAULT: "rgb(var(--c-surface) / <alpha-value>)",
                    muted: "rgb(var(--c-surface-muted) / <alpha-value>)",
                },
                line: {
                    DEFAULT: "rgb(var(--c-line) / <alpha-value>)",
                    strong: "rgb(var(--c-line-strong) / <alpha-value>)",
                },
                content: "rgb(var(--c-content) / <alpha-value>)",
                muted: "rgb(var(--c-muted) / <alpha-value>)",
                accent: {
                    DEFAULT: "rgb(var(--c-accent) / <alpha-value>)",
                    hover: "rgb(var(--c-accent-hover) / <alpha-value>)",
                    soft: "rgb(var(--c-accent-soft) / <alpha-value>)",
                    contrast: "rgb(var(--c-accent-contrast) / <alpha-value>)",
                    2: "rgb(var(--c-accent-2) / <alpha-value>)",
                },
            },
        },
    },

    plugins: [forms],
};
