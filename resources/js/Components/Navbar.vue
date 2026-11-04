<template>
    <div
        class="fixed inset-x-2 top-2 z-30 rounded-md border border-line bg-surface/80 shadow-lg shadow-black/5 backdrop-blur-xl supports-[backdrop-filter]:bg-surface/70 dark:supports-[backdrop-filter]:bg-[#1c1c1c]/60 md:top-2.5"
    >
        <div class="relative z-20 border-b border-line">
            <div class="mx-auto w-full max-w-[1400px] px-4 py-3.5 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between gap-4">
                    <!-- Логотип -->
                    <Link
                        :href="route('home')"
                        aria-label="На главную"
                        class="group inline-flex shrink-0 items-center gap-2.5 rounded text-lg font-bold tracking-tight text-content focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2 focus-visible:ring-offset-page dark:text-white"
                    >
                        <span
                            class="flex h-9 w-9 items-center justify-center rounded-md bg-gradient-to-br from-accent to-accent-2 text-accent-contrast shadow-md"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487z"
                                />
                            </svg>
                        </span>
                        <span class="text-content dark:text-white">
                            МОЙ.<span class="bg-accent hover:bg-accent-hover bg-clip-text text-transparent">БЛОГ</span>
                        </span>
                    </Link>

                    <!-- Десктопный поиск -->
                    <div class="hidden flex-1 sm:flex sm:items-center sm:justify-center">
                        <form
                            @submit.prevent="performSearch"
                            class="group flex w-full max-w-xs items-center gap-2 rounded border border-line bg-surface-muted/80 px-4 py-2 transition-all duration-200 focus-within:border-accent focus-within:bg-surface focus-within:ring-2 focus-within:ring-accent/20 dark:focus-within:bg-surface/10 lg:max-w-sm"
                        >
                            <SearchIcon class="h-4 w-4 shrink-0 text-muted transition-colors duration-200 group-focus-within:text-accent" />
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Поиск по блогу..."
                                aria-label="Поиск по блогу"
                                class="w-full border-0 bg-transparent p-0 text-sm text-content placeholder:text-muted focus:outline-none focus:ring-0"
                            />
                            <button
                                type="submit"
                                aria-label="Найти"
                                class="shrink-0 rounded p-1 text-muted transition-all duration-200 hover:text-accent focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent dark:hover:text-accent"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M12 5l7 7-7 7" />
                                </svg>
                            </button>
                        </form>
                    </div>

                    <!-- Правая панель действий -->
                    <div class="flex shrink-0 items-center gap-2">
                        <!-- Выбор темы оформления -->
                        <div class="relative" ref="themeDropdown">
                            <button
                                type="button"
                                @click="isThemeOpen = !isThemeOpen"
                                class="inline-flex h-9 w-9 items-center justify-center rounded text-muted transition-colors hover:bg-surface-muted hover:text-accent focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2 focus-visible:ring-offset-page"
                                title="Оформление"
                                aria-label="Выбрать тему оформления"
                                aria-haspopup="true"
                                :aria-expanded="isThemeOpen"
                            >
                                <svg
                                    class="h-5 w-5"
                                    fill="currentColor"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path
                                        d="M12 3a9 9 0 0 0 0 18c.83 0 1.5-.67 1.5-1.5 0-.39-.15-.74-.39-1.01-.23-.26-.38-.61-.38-.99 0-.83.67-1.5 1.5-1.5H16c2.76 0 5-2.24 5-5 0-4.42-4.03-8-9-8zm-5.5 9c-.83 0-1.5-.67-1.5-1.5S5.67 9 6.5 9 8 9.67 8 10.5 7.33 12 6.5 12zm3-4C8.67 8 8 7.33 8 6.5S8.67 5 9.5 5s1.5.67 1.5 1.5S10.33 8 9.5 8zm5 0c-.83 0-1.5-.67-1.5-1.5S13.67 5 14.5 5s1.5.67 1.5 1.5S15.33 8 14.5 8zm3 4c-.83 0-1.5-.67-1.5-1.5S16.67 9 17.5 9s1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"
                                    />
                                </svg>
                            </button>

                            <transition
                                enter-active-class="transition ease-out duration-100"
                                enter-from-class="transform opacity-0 scale-95 -translate-y-2"
                                enter-to-class="transform opacity-100 scale-100 translate-y-0"
                                leave-active-class="transition ease-in duration-75"
                                leave-from-class="transform opacity-100 scale-100 translate-y-0"
                                leave-to-class="transform opacity-0 scale-95 -translate-y-2"
                            >
                                <div
                                    v-show="isThemeOpen"
                                    class="absolute right-0 top-full z-30 mt-2 grid max-h-[70vh] w-[min(92vw,24rem)] grid-cols-2 gap-1 overflow-y-auto rounded-md border border-line bg-surface p-1.5 shadow-xl"
                                >
                                    <div v-for="group in themeGroups" :key="group.label" class="min-w-0">
                                        <p class="px-2 pb-1 pt-1.5 text-[10px] font-semibold uppercase tracking-[0.14em] text-muted">
                                            {{ group.label }}
                                        </p>

                                        <button
                                            v-for="theme in group.items"
                                            :key="theme.id"
                                            type="button"
                                            @click="applyTheme(theme.id)"
                                            class="flex w-full items-center gap-2 rounded px-2 py-1.5 text-left text-[13px] font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent"
                                            :class="
                                                activeTheme === theme.id
                                                    ? 'bg-accent-soft text-accent'
                                                    : 'text-content hover:bg-surface-muted'
                                            "
                                        >
                                            <span
                                                class="h-3 w-3 shrink-0 rounded-full border border-line"
                                                :style="{ backgroundColor: theme.swatch }"
                                            />
                                            <span class="min-w-0 flex-1 truncate">{{ theme.label }}</span>
                                            <svg
                                                v-if="activeTheme === theme.id"
                                                class="h-3.5 w-3.5 shrink-0"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2.5"
                                                viewBox="0 0 24 24"
                                                aria-hidden="true"
                                            >
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </transition>
                        </div>

                        <!-- Десктопные кнопки авторизации -->
                        <div v-if="!$page.props.auth.user" class="hidden sm:flex items-center gap-2.5">
                            <a
                                :href="route('login')"
                                class="inline-flex items-center justify-center rounded border border-line bg-surface px-4 py-2 text-sm font-semibold text-content shadow-sm transition-all duration-200 hover:border-accent hover:text-accent hover:shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2 focus-visible:ring-offset-page dark:hover:border-accent dark:hover:text-accent"
                            >
                                Войти
                            </a>
                            <a
                                :href="route('register')"
                                class="inline-flex items-center justify-center rounded bg-accent hover:bg-accent-hover px-4 py-2 text-sm font-semibold text-accent-contrast shadow-md transition-all duration-200 hover:shadow-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2 focus-visible:ring-offset-page"
                            >
                                Регистрация
                            </a>
                        </div>

                        <!-- Десктопный профиль -->
                        <div v-if="$page.props.auth.user" class="relative hidden sm:flex">
                            <button
                                @click="toggleMenu"
                                class="rounded-full p-0.5 ring-2 ring-transparent transition-all duration-200 hover:ring-accent/60 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2 focus-visible:ring-offset-page"
                                aria-label="Меню профиля"
                                :aria-expanded="isOpen"
                            >
                                <UserAvatar
                                    :user="$page.props.auth.user"
                                    :size="36"
                                    class="transition-transform duration-200 hover:scale-105"
                                />
                            </button>

                            <transition
                                enter-active-class="transition ease-out duration-100"
                                enter-from-class="transform opacity-0 scale-95 -translate-y-2"
                                enter-to-class="transform opacity-100 scale-100 translate-y-0"
                                leave-active-class="transition ease-in duration-75"
                                leave-from-class="transform opacity-100 scale-100 translate-y-0"
                                leave-to-class="transform opacity-0 scale-95 -translate-y-2"
                            >
                                <div
                                    v-show="isOpen"
                                    @click.away="closeMenu"
                                    ref="dropdown"
                                    class="absolute right-0 top-full mt-3 w-56 overflow-hidden rounded-md border border-line bg-surface p-1.5 shadow-xl shadow-black/10 ring-1 ring-black/5"
                                >
                                    <a
                                        href="#"
                                        class="flex items-center rounded px-3.5 py-2.5 text-sm font-medium text-content transition-colors duration-150 hover:bg-surface-muted hover:text-content focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent dark:hover:text-white"
                                        @click.prevent="router.get(route('profile'))"
                                    >
                                        Профиль
                                    </a>
                                    <a
                                        href="#"
                                        class="flex items-center rounded px-3.5 py-2.5 text-sm font-medium text-content transition-colors duration-150 hover:bg-surface-muted hover:text-content focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent dark:hover:text-white"
                                        @click.prevent="router.get(route('profile.edit'))"
                                    >
                                        Настройки
                                    </a>
                                    <div class="my-1.5 h-px bg-surface-muted dark:bg-surface/10"></div>
                                    <a
                                        href="#"
                                        class="flex items-center rounded px-3.5 py-2.5 text-sm font-medium text-red-600 transition-colors duration-150 hover:bg-red-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500 dark:text-red-400 dark:hover:bg-red-500/10"
                                        @click.prevent="logout"
                                    >
                                        Выйти
                                    </a>
                                </div>
                            </transition>
                        </div>

                        <!-- Поиск для мобильных -->
                        <button
                            @click="toggleSearch"
                            type="button"
                            class="inline-flex h-9 w-9 items-center justify-center rounded text-muted transition-all duration-200 hover:bg-surface-muted hover:text-accent focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2 focus-visible:ring-offset-page sm:hidden dark:hover:text-accent"
                            :aria-label="isSearchOpen ? 'Скрыть поиск' : 'Открыть поиск'"
                            :aria-expanded="isSearchOpen"
                        >
                            <svg
                                v-if="!isSearchOpen"
                                class="h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
                            </svg>
                            <svg
                                v-else
                                class="h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>

                        <!-- Бургер-кнопка для мобильных -->
                        <button
                            @click="toggleMobileMenu"
                            class="inline-flex h-9 w-9 items-center justify-center rounded text-content transition-all duration-200 hover:bg-surface-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent sm:hidden"
                            aria-label="Меню"
                            :aria-expanded="isMobileMenuOpen"
                        >
                            <svg
                                class="h-6 w-6 transition-transform duration-200"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    :d="isMobileMenuOpen ? 'M6 18L18 6M6 6l12 12' : 'M4 6h16M4 12h16M4 18h16'"
                                />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Поисковая строка для мобильных (в шапке) -->
                <transition
                    enter-active-class="transition ease-out duration-200"
                    enter-from-class="opacity-0 -translate-y-2"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition ease-in duration-150"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 -translate-y-2"
                >
                    <div v-show="isSearchOpen" class="sm:hidden mt-3">
                        <form
                            @submit.prevent="performSearch"
                            class="flex items-center gap-2 rounded border border-line bg-surface-muted px-3 py-2 transition-all duration-200 focus-within:border-accent focus-within:ring-2 focus-within:ring-accent/20"
                        >
                            <input
                                ref="mobileSearchInput"
                                v-model="searchQuery"
                                type="text"
                                placeholder="Поиск по блогу..."
                                aria-label="Поиск по блогу"
                                class="flex-1 border-0 bg-transparent p-0 text-sm text-content placeholder:text-muted focus:outline-none focus:ring-0"
                            />
                            <button
                                type="submit"
                                aria-label="Найти"
                                class="rounded bg-accent hover:bg-accent-hover px-4 py-1.5 text-sm font-semibold text-accent-contrast shadow-sm transition-all duration-200 hover:shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2 focus-visible:ring-offset-page"
                            >
                                Найти
                            </button>
                        </form>
                    </div>
                </transition>
            </div>
        </div>

        <!-- Мобильное меню -->
        <transition
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="opacity-0 -translate-y-3"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition-all duration-200 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-3"
        >
            <div
                v-show="isMobileMenuOpen"
                class="sm:hidden mt-2 overflow-hidden rounded-md border border-line bg-surface/95 p-4 shadow-xl shadow-black/10 backdrop-blur-xl dark:bg-[#202020]/95"
            >
                <div class="space-y-3">
                    <!-- Кнопки авторизации для мобильных -->
                    <div v-if="!$page.props.auth.user" class="grid gap-2.5">
                        <a
                            :href="route('login')"
                            @click="closeMobileMenu"
                            class="inline-flex w-full items-center justify-center rounded border border-line bg-surface px-4 py-3 text-sm font-semibold text-content shadow-sm transition-all duration-200 hover:border-accent hover:text-accent focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2 focus-visible:ring-offset-page dark:hover:border-accent dark:hover:text-accent"
                        >
                            Войти
                        </a>
                        <a
                            :href="route('register')"
                            @click="closeMobileMenu"
                            class="inline-flex w-full items-center justify-center rounded bg-accent hover:bg-accent-hover px-4 py-3 text-sm font-semibold text-accent-contrast shadow-md transition-all duration-200 hover:shadow-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2 focus-visible:ring-offset-page"
                        >
                            Регистрация
                        </a>
                    </div>

                    <!-- Профиль для мобильных -->
                    <div v-if="$page.props.auth.user" class="space-y-1">
                        <a
                            href="#"
                            @click.prevent="goToProfile"
                            class="flex items-center gap-3 rounded border border-line p-2.5 transition-colors duration-150 hover:border-accent hover:bg-accent-soft/50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent dark:hover:border-accent"
                        >
                            <UserAvatar :user="$page.props.auth.user" :size="40" />
                            <div class="text-sm font-medium text-content">Профиль</div>
                        </a>

                        <a
                            href="#"
                            @click.prevent="goToSettings"
                            class="flex items-center rounded px-4 py-3 text-sm font-medium text-content transition-colors duration-150 hover:bg-surface-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent"
                        >
                            Настройки
                        </a>
                        <a
                            href="#"
                            @click.prevent="logoutMobile"
                            class="flex items-center rounded px-4 py-3 text-sm font-medium text-red-600 transition-colors duration-150 hover:bg-red-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500 dark:text-red-400 dark:hover:bg-red-500/10"
                        >
                            Выйти
                        </a>
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>

<script setup>
import { onClickOutside } from "@vueuse/core";
import { Link, router, usePage } from "@inertiajs/vue3";
import SearchIcon from "vue-material-design-icons/Magnify.vue";
import { nextTick, onBeforeUnmount, onMounted, ref, watch } from "vue";
import UserAvatar from "@/Components/UserAvatar.vue";

const props = defineProps({
    searchQuery: String,
});

const page = usePage();

const queryFromUrl = (url) => new URLSearchParams((url || "").split("?")[1] || "").get("query") || "";

const searchQuery = ref(props.searchQuery ?? queryFromUrl(page.url));
const isOpen = ref(false);
const isMobileMenuOpen = ref(false);
const isSearchOpen = ref(false);
const dropdown = ref(null);
const mobileSearchInput = ref(null);
const themes = [
    { id: "light", label: "Светлая", swatch: "#f5f1e8", dark: false },
    { id: "cupcake", label: "Cupcake", swatch: "#efeae6", dark: false },
    { id: "corporate", label: "Corporate", swatch: "#e8e8e8", dark: false },
    { id: "lofi", label: "Lofi", swatch: "#f2f2f2", dark: false },
    { id: "garden", label: "Garden", swatch: "#d4d2d2", dark: false },
    { id: "retro", label: "Retro", swatch: "#e4d8b4", dark: false },
    { id: "winter", label: "Winter", swatch: "#f2f7ff", dark: false },
    { id: "dark", label: "Тёмная", swatch: "#151515", dark: true },
    { id: "dracula", label: "Dracula", swatch: "#232530", dark: true },
    { id: "business", label: "Business", swatch: "#1c1c1c", dark: true },
    { id: "night", label: "Night", swatch: "#0c1425", dark: true },
    { id: "forest", label: "Forest", swatch: "#140f0f", dark: true },
    { id: "coffee", label: "Coffee", swatch: "#1c131b", dark: true },
    { id: "dim", label: "Dim", swatch: "#242933", dark: true },
];

const themeGroups = [
    { label: "Светлые", items: themes.filter((theme) => !theme.dark) },
    { label: "Тёмные", items: themes.filter((theme) => theme.dark) },
];

const isThemeOpen = ref(false);
const activeTheme = ref("light");
const themeDropdown = ref(null);

const applyTheme = (id) => {
    const theme = themes.find((item) => item.id === id) ?? themes[0];
    const html = document.documentElement;

    html.setAttribute("data-theme", theme.id);
    html.setAttribute("data-mode", theme.dark ? "dark" : "light");
    html.style.colorScheme = theme.dark ? "dark" : "light";
    localStorage.setItem("theme", theme.id);

    activeTheme.value = theme.id;
    isThemeOpen.value = false;
};

const savedThemeId = () => {
    const saved = localStorage.getItem("theme");
    return themes.some((item) => item.id === saved) ? saved : null;
};

onMounted(() => {
    const initial = savedThemeId() ?? (window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light");

    const theme = themes.find((item) => item.id === initial) ?? themes[0];

    document.documentElement.setAttribute("data-theme", theme.id);
    document.documentElement.setAttribute("data-mode", theme.dark ? "dark" : "light");
    document.documentElement.style.colorScheme = theme.dark ? "dark" : "light";
    activeTheme.value = theme.id;

    window.addEventListener("resize", handleResize);
    window.addEventListener("keydown", handleEscape);
});

onBeforeUnmount(() => {
    window.removeEventListener("resize", handleResize);
    window.removeEventListener("keydown", handleEscape);
});

onClickOutside(themeDropdown, () => {
    isThemeOpen.value = false;
});

const performSearch = () => {
    if (searchQuery.value.trim()) {
        router.get(route("articles.search"), { query: searchQuery.value });
    } else {
        router.visit(route("home"));
    }
    closeMobileMenu();
    closeSearch();
};

const toggleSearch = async () => {
    isSearchOpen.value = !isSearchOpen.value;
    if (isSearchOpen.value) {
        isMobileMenuOpen.value = false;
        await nextTick();
        mobileSearchInput.value?.focus();
    }
};

const closeSearch = () => {
    isSearchOpen.value = false;
};

const toggleMobileMenu = () => {
    isMobileMenuOpen.value = !isMobileMenuOpen.value;
    if (isMobileMenuOpen.value) {
        isSearchOpen.value = false;
    }
};

const closeMobileMenu = () => {
    isMobileMenuOpen.value = false;
};

onClickOutside(dropdown, () => {
    isOpen.value = false;
});

const toggleMenu = () => {
    isOpen.value = !isOpen.value;
};

const goToProfile = () => {
    router.get(route("profile"));
    closeMobileMenu();
};

const goToSettings = () => {
    router.get(route("profile.edit"));
    closeMobileMenu();
};

const logout = () => {
    router.post(route("logout"));
};

const logoutMobile = () => {
    router.post(route("logout"));
    closeMobileMenu();
};

// Держим поле поиска в актуальном состоянии при переходах между страницами
watch(
    () => page.url,
    (url) => {
        searchQuery.value = queryFromUrl(url);
    },
);

// Закрытие мобильных панелей при расширении окна до десктопа
const handleResize = () => {
    if (window.innerWidth >= 640) {
        closeMobileMenu();
        closeSearch();
    }
};

const handleEscape = (event) => {
    if (event.key === "Escape") {
        closeMobileMenu();
        closeSearch();
        isOpen.value = false;
        isThemeOpen.value = false;
    }
};
</script>
