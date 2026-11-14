// resources/js/Support/themes.js
// Палитры проекта: id должен совпадать с data-theme в app.css и со списком
// известных тем в app.blade.php (он ставит тему до первой отрисовки).
export const themes = [
    { id: "paper", label: "Бумага", swatch: "#e6e1d7", dark: false },
    { id: "light", label: "Светлая", swatch: "#f5f1e8", dark: false },
    { id: "cupcake", label: "Cupcake", swatch: "#efeae6", dark: false },
    { id: "corporate", label: "Corporate", swatch: "#e8e8e8", dark: false },
    { id: "lofi", label: "Lofi", swatch: "#f2f2f2", dark: false },
    { id: "garden", label: "Garden", swatch: "#d4d2d2", dark: false },
    { id: "retro", label: "Retro", swatch: "#e4d8b4", dark: false },
    { id: "winter", label: "Winter", swatch: "#f2f7ff", dark: false },
    { id: "toner", label: "Тонер", swatch: "#121110", dark: true },
    { id: "dark", label: "Тёмная", swatch: "#151515", dark: true },
    { id: "dracula", label: "Dracula", swatch: "#232530", dark: true },
    { id: "business", label: "Business", swatch: "#1c1c1c", dark: true },
    { id: "night", label: "Night", swatch: "#0c1425", dark: true },
    { id: "forest", label: "Forest", swatch: "#140f0f", dark: true },
    { id: "coffee", label: "Coffee", swatch: "#1c131b", dark: true },
    { id: "dim", label: "Dim", swatch: "#242933", dark: true },
];

export const themeGroups = [
    { label: "Светлые", items: themes.filter((theme) => !theme.dark) },
    { label: "Тёмные", items: themes.filter((theme) => theme.dark) },
];

/**
 * Применяет палитру к документу и запоминает выбор.
 * Возвращает id применённой темы — вызывающий код держит его в своём состоянии.
 */
export const applyTheme = (id) => {
    const theme = themes.find((item) => item.id === id) ?? themes[0];
    const html = document.documentElement;

    html.setAttribute("data-theme", theme.id);
    html.setAttribute("data-mode", theme.dark ? "dark" : "light");
    html.style.colorScheme = theme.dark ? "dark" : "light";
    localStorage.setItem("theme", theme.id);

    return theme.id;
};

export const savedThemeId = () => {
    const saved = localStorage.getItem("theme");
    return themes.some((item) => item.id === saved) ? saved : null;
};

export const currentThemeId = () => document.documentElement.getAttribute("data-theme") || "paper";
