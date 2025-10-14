<template>
    <div class="fixed inset-x-0 top-0 z-30 mx-auto w-full max-w-screen-xl">
        <div
            class="border border-gray-100 bg-white/80 dark:bg-[#202020] dark:border-0 py-3 shadow backdrop-blur-lg md:top-2 xl:rounded-3xl"
        >
            <div class="px-4">
                <div class="flex items-center justify-between">
                    <!-- Логотип -->
                    <a
                        href="/"
                        aria-label="home"
                        class="inline-flex dark:text-stone-300 items-center font-medium text-gray-700"
                    >
                        МОЙ.БЛОГ
                    </a>

                    <!-- Десктопное меню -->
                    <div
                        class="hidden sm:flex sm:items-center sm:justify-center sm:gap-5"
                    >
                        <form
                            @submit.prevent="performSearch"
                            class="flex items-center justify-center p-2"
                        >
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Поиск..."
                                class="w-full border-0 rounded-md px-2 py-1 bg-transparent focus:ring-0"
                            />
                            <button
                                type="submit"
                                class="transition duration-200 hover:scale-125"
                            >
                                <SearchIcon
                                    class="dark:text-stone-300"
                                ></SearchIcon>
                            </button>
                        </form>
                    </div>
<div class="flex">
                     <button
                            @click="toggleTheme"
                            class="p-2 mt-1 h-9 rounded-lg text-slate-700 dark:text-slate-300 transition-all duration-200"
                            :title="
                                isDark
                                    ? 'Переключить на светлую тему'
                                    : 'Переключить на темную тему'
                            "
                        >
                            <svg
                                v-if="isDark"
                                class="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"
                                />
                            </svg>
                            <svg
                                v-else
                                class="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"
                                />
                            </svg>
                        </button>

                    <!-- Десктопные кнопки авторизации -->
                    <div
                        v-if="!$page.props.auth.user"
                        class="hidden sm:flex scrollbar-hide overflow-x-auto items-center justify-end gap-3"
                    >
                        <a
                            class="dark:bg-[#303030] dark:text-stone-300 dark:hover:bg-[#404040] items-center justify-center rounded-xl bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm transition-all duration-150 hover:bg-gray-50 sm:inline-flex"
                            :href="route('login')"
                            >Войти</a
                        >
                        <a
                            class="dark:bg-[#303030] dark:text-stone-300 dark:hover:bg-[#404040] inline-flex items-center justify-center rounded-xl bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm transition-all duration-150 hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"
                            :href="route('register')"
                            >Регистрация</a
                        >
                    </div>

                    <!-- Десктопный профиль -->

                    <div
                        class="hidden sm:flex space-x-3 relative"
                        v-if="$page.props.auth.user"
                    >
                       

                        <button
                            @click="toggleMenu"
                            class="flex items-center focus:outline-none"
                        >
                            <div
                                class="w-12 h-12 rounded-full overflow-hidden hover:scale-110 transition-all"
                            >
                                <img
                                    src="/storage/avatars/altAvat.png"
                                    alt="User Avatar"
                                    class="w-full h-full object-cover"
                                />
                            </div>
                        </button>

                        <transition
                            enter-active-class="transition ease-out duration-100"
                            enter-from-class="transform opacity-0 scale-95"
                            enter-to-class="transform opacity-100 scale-100"
                            leave-active-class="transition ease-in duration-75"
                            leave-from-class="transform opacity-100 scale-100"
                            leave-to-class="transform opacity-0 scale-95"
                        >
                            <div
                                v-show="isOpen"
                                @click.away="closeMenu"
                                ref="dropdown"
                                class="absolute dark:bg-[#202020] right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50"
                            >
                                <a
                                    href="#"
                                    class="block px-4 py-2 text-sm text-gray-700 dark:text-stone-300 dark:hover:bg-[#353535] hover:bg-gray-100"
                                    @click.prevent="
                                        router.get(route('profile'))
                                    "
                                >
                                    Профиль
                                </a>
                                <a
                                    href="#"
                                    class="block px-4 py-2 text-sm text-gray-700 dark:text-stone-300 dark:hover:bg-[#353535] hover:bg-gray-100"
                                    @click.prevent="
                                        router.get(route('profile.edit'))
                                    "
                                >
                                    Настройки
                                </a>
                                <a
                                    href="#"
                                    class="block px-4 py-2 text-sm dark:text-stone-300 dark:hover:bg-[#353535] text-gray-700 hover:bg-gray-100"
                                    @click.prevent="logout"
                                >
                                    Выйти
                                </a>
                            </div>
                        </transition>
                    </div>
                    </div>

                    <!-- Бургер-меню для мобильных -->
                    <div class="sm:hidden flex items-center gap-2">
                        <!-- Бургер-кнопка -->
                        <button
                            @click="toggleMobileMenu"
                            class="p-2 rounded-md text-gray-700 dark:text-stone-300 hover:bg-gray-100 dark:hover:bg-[#303030] focus:outline-none"
                        >
                            <svg
                                class="w-6 h-6"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    :d="
                                        isMobileMenuOpen
                                            ? 'M6 18L18 6M6 6l12 12'
                                            : 'M4 6h16M4 12h16M4 18h16'
                                    "
                                />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Поисковая строка для мобильных -->
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
                            class="flex items-center"
                        >
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Поиск..."
                                class="flex-1 border border-gray-300 dark:border-gray-600 rounded-l-md px-3 py-2 bg-white dark:bg-[#303030] dark:text-stone-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                            <button
                                type="submit"
                                class="bg-blue-600 text-white px-4 py-2 rounded-r-md hover:bg-blue-700 transition-colors"
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
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 -translate-y-4"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-4"
        >
            <div
                v-show="isMobileMenuOpen"
                class="sm:hidden bg-white/95 dark:bg-[#202020]/95 backdrop-blur-lg border-t border-gray-200 dark:border-gray-700 shadow-lg"
            >
                <div class="px-4 py-4 space-y-4">
                    <!-- Поиск в мобильном меню (альтернативный вариант) -->
                    <div class="sm:hidden">
                        <form
                            @submit.prevent="performSearchMobile"
                            class="flex items-center"
                        >
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Поиск..."
                                class="flex-1 border border-gray-300 dark:border-gray-600 rounded-l-md px-3 py-2 bg-white dark:bg-[#303030] dark:text-stone-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                            <button
                                type="submit"
                                class="bg-blue-600 text-white px-4 py-2 rounded-r-md hover:bg-blue-700 transition-colors"
                            >
                                <SearchIcon class="text-white"></SearchIcon>
                            </button>
                        </form>
                    </div>

                    <!-- Кнопки авторизации для мобильных -->
                    <div
                        v-if="!$page.props.auth.user"
                        class="flex flex-col space-y-3"
                    >
                        <a
                            class="dark:bg-[#303030] dark:text-stone-300 dark:hover:bg-[#404040] items-center justify-center rounded-xl bg-white px-4 py-3 text-sm font-semibold text-gray-900 shadow-sm transition-all duration-150 hover:bg-gray-50 text-center border border-gray-200 dark:border-gray-600"
                            :href="route('login')"
                            @click="closeMobileMenu"
                        >
                            Войти
                        </a>
                        <a
                            class="dark:bg-[#303030] dark:text-stone-300 dark:hover:bg-[#404040] inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition-all duration-150 hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 text-center"
                            :href="route('register')"
                            @click="closeMobileMenu"
                        >
                            Регистрация
                        </a>
                    </div>

                    <button 
            @click="toggleTheme(), closeMobileMenu()"
            class="flex items-center w-full p-3 text-left text-sm text-slate-700 dark:text-slate-200 rounded-lg"
        >
            <svg v-if="isDark" class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            <svg v-else class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
            </svg>
            {{ isDark ? 'Светлая тема' : 'Темная тема' }}
        </button>
                    <!-- Профиль для мобильных -->
                    <div v-if="$page.props.auth.user" class="">
                        <a
                            href="#"
                            class="block text-sm text-gray-700 dark:text-stone-300 dark:hover:bg-[#353535] hover:bg-gray-100 rounded-lg transition-colors"
                            @click.prevent="goToProfile"
                            ><div
                                class="flex items-center space-x-3 p-2 border border-gray-200 dark:border-gray-600 rounded-xl"
                            >
                                <div
                                    class="w-10 h-10 rounded-full overflow-hidden"
                                >
                                    <img
                                        src="/storage/avatars/altAvat.png"
                                        alt="User Avatar"
                                        class="w-full h-full object-cover"
                                    />
                                </div>
                                <div
                                    class="text-sm font-medium dark:text-stone-300"
                                >
                                    Профиль
                                </div>
                            </div></a
                        >

                        <a
                            href="#"
                            class="block px-4 py-3 text-sm text-gray-700 dark:text-stone-300 dark:hover:bg-[#353535] hover:bg-gray-100 rounded-lg transition-colors"
                            @click.prevent="goToSettings"
                        >
                            Настройки
                        </a>
                        <a
                            href="#"
                            class="block px-4 py-3 text-sm dark:text-stone-300 dark:hover:bg-[#353535] text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                            @click.prevent="logoutMobile"
                        >
                            Выйти
                        </a>
                    </div>
                </div>
            </div>
        </transition>

        <CategoryCarousel />
    </div>
</template>

<script setup>
import { onClickOutside } from "@vueuse/core";
import { router } from "@inertiajs/vue3";
import SearchIcon from "vue-material-design-icons/Magnify.vue";
import { ref, watch, onMounted } from "vue";
import CategoryCarousel from "@/Components/CategoryCarousel.vue";

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
});

const toggleTheme = () => {
    if (isDark.value) {
        setTheme("light");
    } else {
        setTheme("dark");
    }
};

if (typeof window !== "undefined") {
    window
        .matchMedia("(prefers-color-scheme: dark)")
        .addEventListener("change", (e) => {
            if (!localStorage.getItem("theme")) {
                if (e.matches) {
                    setTheme("dark");
                } else {
                    setTheme("light");
                }
            }
        });
}

// Следим за изменениями системных предпочтений
if (typeof window !== "undefined") {
    window
        .matchMedia("(prefers-color-scheme: dark)")
        .addEventListener("change", (e) => {
            if (!localStorage.getItem("theme")) {
                if (e.matches) {
                    enableDarkMode();
                } else {
                    disableDarkMode();
                }
            }
        });
}

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
