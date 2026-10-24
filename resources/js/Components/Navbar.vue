<template>
    <div class="fixed inset-x-0 top-0 z-30 mx-auto w-full max-w-screen-xl border border-gray-100/80 bg-white/80 shadow-lg shadow-slate-900/5 backdrop-blur-xl supports-[backdrop-filter]:bg-white/70 dark:border-white/5 dark:bg-[#202020]/80 dark:supports-[backdrop-filter]:bg-[#1c1c1c]/60 md:top-2.5 xl:rounded-md">
        <div
            class="relative z-20 border-b border-gray-100/80 dark:border-white/5">
            <div class="px-4 py-3.5 sm:px-6">
                <div class="flex items-center justify-between gap-4">
                    <!-- Логотип -->
                    <a href="/" aria-label="На главную"
                        class="group inline-flex shrink-0 items-center gap-2.5 rounded text-lg font-bold tracking-tight text-gray-900 transition-all duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:text-white dark:focus-visible:ring-offset-[#202020]">
                        <span
                            class="flex h-9 w-9 items-center justify-center rounded-md bg-gradient-to-br from-emerald-600 to-teal-600 text-white shadow-md shadow-emerald-500/30 transition-transform duration-200 group-hover:rotate-6 group-hover:scale-110">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487z" />
                            </svg>
                        </span>
                        <span class="text-gray-900 dark:text-white">
                            МОЙ.<span
                                class="bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent dark:from-emerald-400 dark:to-teal-400">БЛОГ</span>
                        </span>
                    </a>

                    <!-- Десктопный поиск -->
                    <div class="hidden flex-1 sm:flex sm:items-center sm:justify-center">
                        <form @submit.prevent="performSearch"
                            class="group flex w-full max-w-xs items-center gap-2 rounded border border-gray-200/80 bg-gray-50/80 px-4 py-2 transition-all duration-200 focus-within:border-emerald-400 focus-within:bg-[#FAF8F4] focus-within:ring-2 focus-within:ring-emerald-500/20 dark:border-white/10 dark:bg-white/5 dark:focus-within:bg-white/10 lg:max-w-sm">
                            <SearchIcon
                                class="h-4 w-4 shrink-0 text-gray-400 transition-colors duration-200 group-focus-within:text-emerald-500 dark:text-stone-400 dark:group-focus-within:text-emerald-300" />
                            <input v-model="searchQuery" type="text" placeholder="Поиск по блогу..."
                                class="w-full border-0 bg-transparent p-0 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-0 dark:text-stone-200 dark:placeholder-stone-500" />
                            <button type="submit" aria-label="Найти"
                                class="shrink-0 rounded p-1 text-gray-400 transition-all duration-200 hover:scale-110 hover:text-emerald-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 dark:text-stone-400 dark:hover:text-emerald-300">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M12 5l7 7-7 7" />
                                </svg>
                            </button>
                        </form>
                    </div>

                    <!-- Правая панель действий -->
                    <div class="flex shrink-0 items-center gap-2">
                        <!-- Переключатель темы -->
                        <button @click="toggleTheme"
                            class="inline-flex h-9 w-9 items-center justify-center rounded text-slate-600 transition-all duration-200 hover:scale-110 hover:bg-slate-100 hover:text-emerald-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:text-stone-300 dark:hover:bg-white/10 dark:hover:text-emerald-300 dark:focus-visible:ring-offset-[#202020]"
                            :title="isDark ? 'Переключить на светлую тему' : 'Переключить на темную тему'"
                            :aria-label="isDark ? 'Переключить на светлую тему' : 'Переключить на темную тему'">
                            <svg v-if="isDark" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                            </svg>
                        </button>

                        <!-- Десктопные кнопки авторизации -->
                        <div v-if="!$page.props.auth.user" class="hidden sm:flex items-center gap-2.5">
                            <a :href="route('login')"
                                class="inline-flex items-center justify-center rounded border border-gray-200 bg-[#FAF8F4] px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-emerald-300 hover:text-emerald-600 hover:shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:border-white/10 dark:bg-white/5 dark:text-stone-200 dark:hover:border-emerald-400/40 dark:hover:text-emerald-300 dark:focus-visible:ring-offset-[#202020]">
                                Войти
                            </a>
                            <a :href="route('register')"
                                class="inline-flex items-center justify-center rounded bg-gradient-to-r from-emerald-600 to-teal-600 px-4 py-2 text-sm font-semibold text-white shadow-md shadow-emerald-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg hover:shadow-emerald-500/30 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-offset-[#202020]">
                                Регистрация
                            </a>
                        </div>

                        <!-- Десктопный профиль -->
                        <div v-if="$page.props.auth.user" class="relative hidden sm:flex">
                            <button @click="toggleMenu"
                                class="rounded-full p-0.5 ring-2 ring-transparent transition-all duration-200 hover:ring-emerald-500/60 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-offset-[#202020]"
                                aria-label="Меню профиля" aria-expanded="isOpen">
                                <UserAvatar :user="$page.props.auth.user" :size="36"
                                    class="transition-transform duration-200 hover:scale-105" />
                            </button>

                            <transition enter-active-class="transition ease-out duration-100"
                                enter-from-class="transform opacity-0 scale-95 -translate-y-2"
                                enter-to-class="transform opacity-100 scale-100 translate-y-0"
                                leave-active-class="transition ease-in duration-75"
                                leave-from-class="transform opacity-100 scale-100 translate-y-0"
                                leave-to-class="transform opacity-0 scale-95 -translate-y-2">
                                <div v-show="isOpen" @click.away="closeMenu" ref="dropdown"
                                    class="absolute right-0 top-full mt-3 w-56 overflow-hidden rounded-md border border-gray-100 bg-[#FAF8F4] p-1.5 shadow-xl shadow-slate-900/10 ring-1 ring-black/5 dark:border-white/10 dark:bg-[#252525] dark:ring-white/5">
                                    <a href="#"
                                        class="flex items-center rounded px-3.5 py-2.5 text-sm font-medium text-gray-700 transition-colors duration-150 hover:bg-gray-100 hover:text-gray-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 dark:text-stone-200 dark:hover:bg-white/10 dark:hover:text-white"
                                        @click.prevent="router.get(route('profile'))">
                                        Профиль
                                    </a>
                                    <a href="#"
                                        class="flex items-center rounded px-3.5 py-2.5 text-sm font-medium text-gray-700 transition-colors duration-150 hover:bg-gray-100 hover:text-gray-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 dark:text-stone-200 dark:hover:bg-white/10 dark:hover:text-white"
                                        @click.prevent="router.get(route('profile.edit'))">
                                        Настройки
                                    </a>
                                    <div class="my-1.5 h-px bg-gray-100 dark:bg-white/10"></div>
                                    <a href="#"
                                        class="flex items-center rounded px-3.5 py-2.5 text-sm font-medium text-red-600 transition-colors duration-150 hover:bg-red-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500 dark:text-red-400 dark:hover:bg-red-500/10"
                                        @click.prevent="logout">
                                        Выйти
                                    </a>
                                </div>
                            </transition>
                        </div>

                        <!-- Бургер-кнопка для мобильных -->
                        <button @click="toggleMobileMenu"
                            class="inline-flex h-9 w-9 items-center justify-center rounded text-gray-700 transition-all duration-200 hover:scale-110 hover:bg-slate-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 sm:hidden dark:text-stone-300 dark:hover:bg-white/10"
                            aria-label="Открыть меню" aria-expanded="isMobileMenuOpen">
                            <svg class="h-6 w-6 transition-transform duration-200" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="isMobileMenuOpen
                                        ? 'M6 18L18 6M6 6l12 12'
                                        : 'M4 6h16M4 12h16M4 18h16'
                                    " />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Поисковая строка для мобильных (в шапке) -->
                <transition enter-active-class="transition ease-out duration-200"
                    enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition ease-in duration-150" leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 -translate-y-2">
                    <div v-show="isSearchOpen" class="sm:hidden mt-3">
                        <form @submit.prevent="performSearch"
class="flex items-center gap-2 rounded border border-gray-200 bg-gray-50 px-3 py-2 transition-all duration-200 focus-within:border-emerald-400 focus-within:ring-2 focus-within:ring-emerald-500/20 dark:border-white/10 dark:bg-white/5">
                            <input v-model="searchQuery" type="text" placeholder="Поиск по блогу..."
                                class="flex-1 border-0 bg-transparent p-0 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-0 dark:text-stone-200 dark:placeholder-stone-500" />
                            <button type="submit" aria-label="Найти"
                                class="rounded bg-gradient-to-r from-emerald-600 to-teal-600 px-4 py-1.5 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-offset-[#202020]">
                                Найти
                            </button>
                        </form>
                    </div>
                </transition>
            </div>
        </div>

        <!-- Мобильное меню -->
        <transition enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="opacity-0 -translate-y-3" enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition-all duration-200 ease-in" leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-3">
            <div v-show="isMobileMenuOpen"
                class="sm:hidden mt-2 overflow-hidden rounded-md border border-gray-100 bg-white/95 p-4 shadow-xl shadow-slate-900/10 backdrop-blur-xl dark:border-white/10 dark:bg-[#202020]/95">
                <div class="space-y-3">
                    <!-- Поиск в мобильном меню -->
                    <form @submit.prevent="performSearchMobile"
                        class="flex items-center gap-2 rounded border border-gray-200 bg-gray-50 px-3 py-2 transition-all duration-200 focus-within:border-emerald-400 focus-within:ring-2 focus-within:ring-emerald-500/20 dark:border-white/10 dark:bg-white/5">
                        <SearchIcon
                            class="h-4 w-4 shrink-0 text-gray-400 transition-colors duration-200 group-focus-within:text-emerald-500 dark:text-stone-400" />
                        <input v-model="searchQuery" type="text" placeholder="Поиск по блогу..."
                            class="flex-1 border-0 bg-transparent p-0 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-0 dark:text-stone-200 dark:placeholder-stone-500" />
                        <button type="submit" aria-label="Найти"
                            class="shrink-0 rounded p-1 text-emerald-600 transition-all duration-200 hover:scale-110 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 dark:text-emerald-300">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M12 5l7 7-7 7" />
                            </svg>
                        </button>
                    </form>

                    <!-- Кнопки авторизации для мобильных -->
                    <div v-if="!$page.props.auth.user" class="grid gap-2.5">
                        <a :href="route('login')" @click="closeMobileMenu"
                            class="inline-flex w-full items-center justify-center rounded border border-gray-200 bg-[#FAF8F4] px-4 py-3 text-sm font-semibold text-gray-700 shadow-sm transition-all duration-200 hover:border-emerald-300 hover:text-emerald-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:border-white/10 dark:bg-white/5 dark:text-stone-200 dark:hover:border-emerald-400/40 dark:hover:text-emerald-300 dark:focus-visible:ring-offset-[#202020]">
                            Войти
                        </a>
                        <a :href="route('register')" @click="closeMobileMenu"
                            class="inline-flex w-full items-center justify-center rounded bg-gradient-to-r from-emerald-600 to-teal-600 px-4 py-3 text-sm font-semibold text-white shadow-md shadow-emerald-500/25 transition-all duration-200 hover:shadow-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-offset-[#202020]">
                            Регистрация
                        </a>
                    </div>

                    <!-- Переключатель темы для мобильных -->
                    <button @click="toggleTheme(), closeMobileMenu()"
                        class="flex w-full items-center rounded p-3 text-left text-sm font-medium text-slate-700 transition-all duration-150 hover:bg-slate-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 dark:text-stone-200 dark:hover:bg-white/10">
                        <svg v-if="isDark" class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <svg v-else class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                        {{ isDark ? 'Светлая тема' : 'Темная тема' }}
                    </button>

                    <!-- Профиль для мобильных -->
                    <div v-if="$page.props.auth.user" class="space-y-1">
                        <a href="#" @click.prevent="goToProfile"
                            class="flex items-center gap-3 rounded border border-gray-200 p-2.5 transition-colors duration-150 hover:border-emerald-200 hover:bg-emerald-50/50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 dark:border-white/10 dark:hover:border-emerald-400/40 dark:hover:bg-emerald-500/10">
                            <UserAvatar :user="$page.props.auth.user" :size="40" />
                            <div class="text-sm font-medium text-gray-900 dark:text-stone-100">
                                Профиль
                            </div>
                        </a>

                        <a href="#" @click.prevent="goToSettings"
                            class="flex items-center rounded px-4 py-3 text-sm font-medium text-gray-700 transition-colors duration-150 hover:bg-slate-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 dark:text-stone-200 dark:hover:bg-white/10">
                            Настройки
                        </a>
                        <a href="#" @click.prevent="logoutMobile"
                            class="flex items-center rounded px-4 py-3 text-sm font-medium text-red-600 transition-colors duration-150 hover:bg-red-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500 dark:text-red-400 dark:hover:bg-red-500/10">
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
import { router } from "@inertiajs/vue3";
import SearchIcon from "vue-material-design-icons/Magnify.vue";
import { ref, watch, onMounted } from "vue";
import UserAvatar from "@/Components/UserAvatar.vue";

const props = defineProps({
    searchQuery: String,
});

const searchQuery = ref("");
const isOpen = ref(false);
const isMobileMenuOpen = ref(false);
const isSearchOpen = ref(false);
const dropdown = ref(null);
const isDark = ref(false);

const setTheme = (theme) => {
    const html = document.documentElement;

    if (theme === "dark") {
        html.setAttribute("data-theme", "dark");
        localStorage.setItem("theme", "dark");
        isDark.value = true;
    } else {
        html.removeAttribute("data-theme");
        localStorage.setItem("theme", "light");
        isDark.value = false;
    }
};

onMounted(() => {
    const savedTheme = localStorage.getItem("theme");
    const systemPrefersDark = window.matchMedia(
        "(prefers-color-scheme: dark)"
    ).matches;

    if (savedTheme === "dark" || (!savedTheme && systemPrefersDark)) {
        setTheme("dark");
    } else {
        setTheme("light");
    }

    // Следим за изменениями системных предпочтений
    window
        .matchMedia("(prefers-color-scheme: dark)")
        .addEventListener("change", (e) => {
            if (!localStorage.getItem("theme")) {
                setTheme(e.matches ? "dark" : "light");
            }
        });
});

const toggleTheme = () => {
    if (isDark.value) {
        setTheme("light");
    } else {
        setTheme("dark");
    }
};

const performSearch = () => {
    if (searchQuery.value.trim()) {
        router.get(route("articles.search"), {
            query: searchQuery.value,
        });
    } else {
        router.visit(route("home"));
    }
    closeMobileMenu();
    closeSearch();
};

const performSearchMobile = () => {
    performSearch();
    closeMobileMenu();
};

const toggleSearch = () => {
    isSearchOpen.value = !isSearchOpen.value;
    if (isSearchOpen.value) {
        isMobileMenuOpen.value = false;
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

// Закрытие меню при изменении размера окна на десктопный
watch(
    () => window.innerWidth,
    (newWidth) => {
        if (newWidth >= 640) {
            closeMobileMenu();
            closeSearch();
        }
    }
);

// Закрытие меню при нажатии Escape
const handleEscape = (event) => {
    if (event.key === "Escape") {
        closeMobileMenu();
        closeSearch();
    }
};

if (typeof window !== "undefined") {
    window.addEventListener("keydown", handleEscape);
}
</script>