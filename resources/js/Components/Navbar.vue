<template>
    <header class="relative z-30 w-full bg-page">
        <div class="page-shell">
            <div class="site-header-grid py-4 lg:py-5">
                <!-- Логотип: знак подземного перехода + название, второе слово акцентом -->
                <Link
                    :href="route('home')"
                    class="site-header-logo inline-flex shrink-0 items-center gap-2 focus-visible:outline-none"
                    aria-label="Челны подземка — на главную"
                >
                    <svg
                        class="h-6 w-6 shrink-0 text-accent lg:h-7 lg:w-7"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2.2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <path d="M4 21V10.5a8 8 0 0 1 16 0V21" />
                        <path d="M8 21v-4.5h4V12h4" />
                    </svg>
                    <span
                        class="font-sans text-[1.0625rem] font-bold leading-none tracking-[-0.02em] text-content sm:text-[1.25rem] lg:text-[1.5rem]"
                        >Челны <span class="text-accent">подземка</span></span
                    >
                </Link>

                <!-- Категории строкой текстовых ссылок -->
                <nav class="site-header-cats hidden min-w-0 lg:block" aria-label="Категории">
                    <ul class="flex flex-wrap items-center gap-x-5 gap-y-1">
                        <li>
                            <Link
                                :href="route('authors.index')"
                                class="whitespace-nowrap text-[1.0625rem] leading-6 transition-colors hover:text-accent"
                                :class="isAuthorsPage ? 'text-accent underline decoration-2 underline-offset-[7px]' : 'text-muted'"
                            >
                                Авторы
                            </Link>
                        </li>
                        <li v-for="category in categories" :key="category.id">
                            <Link
                                :href="route('articlesByCategory', category.slug)"
                                class="whitespace-nowrap text-[1.0625rem] leading-6 transition-colors hover:text-accent"
                                :class="
                                    activeCategory === category.slug
                                        ? 'text-accent underline decoration-2 underline-offset-[7px]'
                                        : 'text-muted'
                                "
                            >
                                {{ category.name }}
                            </Link>
                        </li>
                    </ul>
                </nav>

                <!-- Поиск: поле с линией снизу -->
                <form
                    class="site-header-search hidden lg:block lg:max-w-[26rem] lg:justify-self-start"
                    role="search"
                    @submit.prevent="performSearch"
                >
                    <div class="flex items-center gap-2 border-b border-line pb-1.5 focus-within:border-accent">
                        <SearchIcon class="h-4 w-4 shrink-0 text-muted" />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Поиск по блогу"
                            aria-label="Поиск по блогу"
                            class="w-full border-0 bg-transparent p-0 text-[1.0625rem] text-content placeholder:text-muted focus:outline-none focus:ring-0"
                        />
                    </div>
                </form>

                <div class="site-header-actions flex shrink-0 items-center gap-0.5 lg:gap-3">
                    <!-- Тема оформления -->
                    <div ref="themeDropdown" class="theme-menu">
                        <button
                            type="button"
                            class="inline-flex h-11 w-11 items-center justify-center text-muted transition-colors hover:text-accent focus-visible:outline-none lg:h-10 lg:w-10"
                            title="Оформление"
                            aria-label="Выбрать тему оформления"
                            aria-haspopup="true"
                            :aria-expanded="isThemeOpen"
                            @click="isThemeOpen = !isThemeOpen"
                        >
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path
                                    d="M12 3a9 9 0 0 0 0 18c.83 0 1.5-.67 1.5-1.5 0-.39-.15-.74-.39-1.01-.23-.26-.38-.61-.38-.99 0-.83.67-1.5 1.5-1.5H16c2.76 0 5-2.24 5-5 0-4.42-4.03-8-9-8zm-5.5 9c-.83 0-1.5-.67-1.5-1.5S5.67 9 6.5 9 8 9.67 8 10.5 7.33 12 6.5 12zm3-4C8.67 8 8 7.33 8 6.5S8.67 5 9.5 5s1.5.67 1.5 1.5S10.33 8 9.5 8zm5 0c-.83 0-1.5-.67-1.5-1.5S13.67 5 14.5 5s1.5.67 1.5 1.5S15.33 8 14.5 8zm3 4c-.83 0-1.5-.67-1.5-1.5S16.67 9 17.5 9s1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"
                                />
                            </svg>
                        </button>

                        <transition
                            enter-active-class="transition ease-out duration-100"
                            enter-from-class="opacity-0 -translate-y-1"
                            enter-to-class="opacity-100 translate-y-0"
                            leave-active-class="transition ease-in duration-75"
                            leave-from-class="opacity-100 translate-y-0"
                            leave-to-class="opacity-0 -translate-y-1"
                        >
                            <!-- На узких экранах панель привязана к шапке (иначе уезжает за левый край),
                                 на lg — к самой кнопке, чтобы открываться под ней -->
                            <div
                                v-show="isThemeOpen"
                                class="theme-menu__panel absolute right-5 top-full z-30 mt-2 grid max-h-[70vh] w-[min(92vw,23rem)] grid-cols-2 gap-x-4 gap-y-3 overflow-y-auto bg-surface p-4 shadow-lg sm:right-8"
                            >
                                <div v-for="group in themeGroups" :key="group.label" class="min-w-0">
                                    <p class="mb-1 text-sm text-muted">{{ group.label }}</p>
                                    <button
                                        v-for="theme in group.items"
                                        :key="theme.id"
                                        type="button"
                                        class="flex w-full items-center gap-2 py-1.5 pl-2 pr-1.5 text-left text-sm transition-colors"
                                        :class="
                                            activeTheme === theme.id ? 'bg-accent-soft text-accent' : 'text-content hover:bg-surface-muted'
                                        "
                                        @click="applyTheme(theme.id)"
                                    >
                                        <span class="h-3 w-3 shrink-0" :style="{ backgroundColor: theme.swatch }" />
                                        <span class="min-w-0 flex-1 truncate">{{ theme.label }}</span>
                                    </button>
                                </div>
                            </div>
                        </transition>
                    </div>

                    <!-- Гость -->
                    <template v-if="!$page.props.auth.user">
                        <a
                            :href="route('login')"
                            class="hidden text-[1.0625rem] text-muted transition-colors hover:text-content lg:inline-flex"
                        >
                            Войти
                        </a>
                        <a
                            :href="route('register')"
                            class="hidden items-center justify-center bg-accent px-2.5 py-2 text-sm font-semibold text-accent-contrast transition-colors hover:bg-accent-hover lg:inline-flex lg:px-4 lg:text-sm"
                        >
                            Регистрация
                        </a>
                    </template>

                    <!-- Авторизован: создать пост иконкой, профиль — в меню аватара -->
                    <template v-else>
                        <Link
                            :href="route('create')"
                            class="hidden h-11 w-11 items-center justify-center text-accent transition-colors hover:text-accent-hover focus-visible:outline-none lg:inline-flex lg:h-10 lg:w-10"
                            aria-label="Написать статью"
                            title="Написать статью"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" />
                            </svg>
                        </Link>
                    <div class="relative hidden lg:block">
                        <button
                            class="flex items-center focus-visible:outline-none"
                            aria-label="Меню профиля"
                            :aria-expanded="isOpen"
                            @click="toggleMenu"
                        >
                            <UserAvatar :user="$page.props.auth.user" :size="34" />
                        </button>

                        <transition
                            enter-active-class="transition ease-out duration-100"
                            enter-from-class="opacity-0 -translate-y-1"
                            enter-to-class="opacity-100 translate-y-0"
                            leave-active-class="transition ease-in duration-75"
                            leave-from-class="opacity-100 translate-y-0"
                            leave-to-class="opacity-0 -translate-y-1"
                        >
                            <div v-show="isOpen" ref="dropdown" class="absolute right-0 top-full z-30 mt-2 w-56 bg-surface p-2 shadow-lg">
                                <a
                                    href="#"
                                    class="flex items-center px-3 py-2.5 text-[1.0625rem] text-content transition-colors hover:bg-surface-muted"
                                    @click.prevent="goToProfile"
                                >
                                    Профиль
                                </a>
                                <a
                                    href="#"
                                    class="flex items-center px-3 py-2.5 text-[1.0625rem] text-content transition-colors hover:bg-surface-muted"
                                    @click.prevent="goToSettings"
                                >
                                    Редактировать профиль
                                </a>
                                <Link
                                    v-if="page.props.auth?.can?.moderate"
                                    :href="route('filament.admin.pages.dashboard')"
                                    class="mt-1 flex items-center px-3 py-2.5 text-[1.0625rem] font-medium text-content transition-colors hover:bg-surface-muted"
                                >
                                    Админка
                                </Link>
                                <a
                                    href="#"
                                    class="mt-1 flex items-center px-3 py-2.5 text-[1.0625rem] text-accent transition-colors hover:bg-accent-soft"
                                    @click.prevent="logout"
                                >
                                    Выйти
                                </a>
                            </div>
                        </transition>
                    </div>
                    </template>

                    <!-- Поиск на планшете -->
                    <button
                        type="button"
                        class="hidden h-11 w-11 items-center justify-center text-muted transition-colors hover:text-accent focus-visible:outline-none sm:inline-flex lg:hidden"
                        :aria-label="isSearchOpen ? 'Скрыть поиск' : 'Открыть поиск'"
                        :aria-expanded="isSearchOpen"
                        @click="toggleSearch"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                            <path
                                v-if="!isSearchOpen"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"
                            />
                            <path v-else stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                    <!-- Бургер -->
                    <button
                        type="button"
                        class="inline-flex h-11 w-11 items-center justify-center text-content transition-colors hover:text-accent focus-visible:outline-none lg:hidden"
                        aria-label="Меню"
                        :aria-expanded="isMobileMenuOpen"
                        @click="toggleMobileMenu"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.75"
                                :d="isMobileMenuOpen ? 'M6 18L18 6M6 6l12 12' : 'M4 6h16M4 12h16M4 18h16'"
                            />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Поиск на планшете: строка под шапкой -->
        <div v-show="isSearchOpen" class="page-shell pb-4 sm:block lg:hidden">
            <form role="search" @submit.prevent="performSearch">
                <div class="flex items-center gap-2 border-b border-line pb-2 focus-within:border-accent">
                    <SearchIcon class="h-4 w-4 shrink-0 text-muted" />
                    <input
                        ref="mobileSearchInput"
                        v-model="searchQuery"
                        type="text"
                        placeholder="Поиск по блогу"
                        aria-label="Поиск по блогу"
                        class="w-full border-0 bg-transparent p-0 text-[1.0625rem] text-content placeholder:text-muted focus:outline-none focus:ring-0"
                    />
                </div>
            </form>
        </div>

        <!-- Мобильное меню: полноэкранная панель на фоне page, без блюра -->
        <transition
            enter-active-class="transition-opacity duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-show="isMobileMenuOpen" class="fixed inset-0 z-50 flex flex-col bg-page lg:hidden">
                <div class="page-shell flex items-center justify-between py-4">
                    <Link
                        :href="route('home')"
                        class="inline-flex shrink-0 items-center gap-2 focus-visible:outline-none"
                        aria-label="Челны подземка — на главную"
                        @click="closeMobileMenu"
                    >
                        <svg
                            class="h-6 w-6 shrink-0 text-accent"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2.2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            viewBox="0 0 24 24"
                            aria-hidden="true"
                        >
                            <path d="M4 21V10.5a8 8 0 0 1 16 0V21" />
                            <path d="M8 21v-4.5h4V12h4" />
                        </svg>
                        <span class="text-[1.0625rem] font-bold leading-none tracking-[-0.02em] text-content"
                            >Челны <span class="text-accent">подземка</span></span
                        >
                    </Link>
                    <button
                        type="button"
                        class="inline-flex h-9 w-9 items-center justify-center text-content transition-colors hover:text-accent focus-visible:outline-none"
                        aria-label="Закрыть меню"
                        @click="closeMobileMenu"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="page-shell flex-1 overflow-y-auto pb-12">
                    <form role="search" class="pb-6" @submit.prevent="performSearch">
                        <div class="flex items-center gap-2 border-b border-line pb-2 focus-within:border-accent">
                            <SearchIcon class="h-4 w-4 shrink-0 text-muted" />
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Поиск по блогу"
                                aria-label="Поиск по блогу"
                                class="w-full border-0 bg-transparent p-0 text-[1.0625rem] text-content placeholder:text-muted focus:outline-none focus:ring-0"
                            />
                        </div>
                    </form>

                    <nav aria-label="Авторы и категории">
                        <ul class="flex flex-col gap-1">
                            <li>
                                <Link
                                    :href="route('authors.index')"
                                    class="block py-1.5 text-[1.25rem] font-medium leading-snug transition-colors hover:text-accent"
                                    :class="isAuthorsPage ? 'text-accent' : 'text-content'"
                                    @click="closeMobileMenu"
                                >
                                    Авторы
                                </Link>
                            </li>
                            <li v-for="category in categories" :key="category.id">
                                <Link
                                    :href="route('articlesByCategory', category.slug)"
                                    class="block py-1.5 text-[1.25rem] font-medium leading-snug transition-colors hover:text-accent"
                                    :class="activeCategory === category.slug ? 'text-accent' : 'text-content'"
                                    @click="closeMobileMenu"
                                >
                                    {{ category.name }}
                                </Link>
                            </li>
                        </ul>
                    </nav>

                    <div v-if="!$page.props.auth.user" class="mt-8 flex flex-col items-start gap-4">
                        <a
                            :href="route('register')"
                            class="inline-flex w-full items-center justify-center bg-accent px-4 py-3 text-[1.0625rem] font-semibold text-accent-contrast transition-colors hover:bg-accent-hover"
                        >
                            Регистрация
                        </a>
                        <a :href="route('login')" class="text-[1.0625rem] text-muted transition-colors hover:text-content"> Войти </a>
                    </div>

                    <div v-else class="mt-8">
                        <a href="#" class="flex items-center gap-3 py-2" @click.prevent="goToProfile">
                            <UserAvatar :user="$page.props.auth.user" :size="40" />
                            <span class="text-[1.0625rem] font-medium text-content">Профиль</span>
                        </a>
                        <Link
                            :href="route('create')"
                            class="mt-1 flex items-center gap-2 py-2 text-[1.0625rem] text-content"
                            @click="closeMobileMenu"
                        >
                            Написать статью
                        </Link>
                        <a href="#" class="mt-1 block py-2 text-[1.0625rem] text-content" @click.prevent="goToSettings">
                            Редактировать профиль
                        </a>
                        <Link
                            v-if="page.props.auth?.can?.moderate"
                            :href="route('filament.admin.pages.dashboard')"
                            class="mt-1 block py-2 text-[1.0625rem] font-medium text-content"
                            @click="closeMobileMenu"
                        >
                            Админка
                        </Link>
                        <a href="#" class="mt-1 block py-2 text-[1.0625rem] text-accent" @click.prevent="logoutMobile"> Выйти </a>
                    </div>
                </div>
            </div>
        </transition>
    </header>
</template>
<script setup>
import { onClickOutside } from "@vueuse/core";
import { Link, router, usePage } from "@inertiajs/vue3";
import SearchIcon from "vue-material-design-icons/Magnify.vue";
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from "vue";
import UserAvatar from "@/Components/UserAvatar.vue";

const props = defineProps({
    searchQuery: String,
});

const page = usePage();

const categories = computed(() => page.props.categories ?? []);

const activeCategory = computed(() => {
    const path = (page.url || "").split("?")[0];
    return path.startsWith("/categories/") ? decodeURIComponent(path.replace("/categories/", "")) : null;
});

const isAuthorsPage = computed(() => (page.url || "").split("?")[0].startsWith("/authors"));


const queryFromUrl = (url) => new URLSearchParams((url || "").split("?")[1] || "").get("query") || "";

const searchQuery = ref(props.searchQuery ?? queryFromUrl(page.url));
const isOpen = ref(false);
const isMobileMenuOpen = ref(false);
const isSearchOpen = ref(false);
const dropdown = ref(null);
const mobileSearchInput = ref(null);
const themes = [
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
    // Новым посетителям — «Бумага»; выбор пользователя важнее системной схемы.
    const initial = savedThemeId() ?? "paper";

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

// Мобильная панель занимает экран — фон под ней не прокручивается
watch(isMobileMenuOpen, (open) => {
    document.body.style.overflow = open ? "hidden" : "";
});

onBeforeUnmount(() => {
    document.body.style.overflow = "";
});

// Закрытие мобильных панелей при расширении окна до десктопа
const handleResize = () => {
    if (window.innerWidth >= 1024) {
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
