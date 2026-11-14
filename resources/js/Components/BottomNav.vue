<!-- resources/js/Components/BottomNav.vue -->
<template>
    <!-- Нижняя навигация на телефоне: две кнопки, монограмма «ЧП» по центру, ещё две.
         Рубрики и «Написать» — то, до чего в бургере было два тапа. Тема живёт иконкой
         в шапке, авторы — в бургере. Работает до 1024px: на планшете нижняя панель тоже
         удобнее меню, с 1024px появляется обычная навигация в шапке. -->
    <nav class="bottom-nav lg:hidden" aria-label="Основная навигация">
        <button
            ref="categoriesButton"
            type="button"
            class="bottom-nav__item"
            :class="{ 'bottom-nav__item--active': categoriesOpen || isCurrent('articlesByCategory') }"
            aria-haspopup="dialog"
            :aria-expanded="categoriesOpen"
            @click="categoriesOpen = !categoriesOpen"
        >
            <svg fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"
                />
            </svg>
            <span>Рубрики</span>
        </button>

        <Link
            :href="route('articles.search')"
            class="bottom-nav__item"
            :class="{ 'bottom-nav__item--active': isCurrent('articles.search') }"
            :aria-current="isCurrent('articles.search') ? 'page' : undefined"
        >
            <svg fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
            </svg>
            <span>Поиск</span>
        </Link>

        <!-- Центр: монограмма на круге, приподнятом над панелью -->
        <Link :href="route('home')" class="bottom-nav__logo" aria-label="Челны подземка — на главную">
            <span aria-hidden="true">ЧП</span>
        </Link>

        <Link
            :href="createPath"
            class="bottom-nav__item"
            :class="{ 'bottom-nav__item--active': isCurrent('create') }"
            :aria-current="isCurrent('create') ? 'page' : undefined"
        >
            <svg fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zM19.5 14.25v4.125A2.625 2.625 0 0116.875 21H5.625A2.625 2.625 0 013 18.375V7.125A2.625 2.625 0 015.625 4.5h4.125"
                />
            </svg>
            <span>Написать</span>
        </Link>

        <Link
            :href="authPath"
            class="bottom-nav__item"
            :class="{ 'bottom-nav__item--active': isCurrent(authPathName) }"
            :aria-current="isCurrent(authPathName) ? 'page' : undefined"
        >
            <svg fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"
                />
            </svg>
            <span>{{ signedIn ? "Профиль" : "Войти" }}</span>
        </Link>
    </nav>

    <!-- Шторка рубрик над панелью: категории из общих props, по одному тапу -->
    <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="translate-y-3 opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="translate-y-3 opacity-0"
    >
        <div v-if="categoriesOpen" ref="sheet" class="bottom-sheet" role="dialog" aria-label="Рубрики">
            <div class="flex items-center justify-between">
                <p class="text-[0.6875rem] font-semibold uppercase tracking-[0.08em] text-muted">Рубрики</p>
                <button type="button" class="bottom-sheet__close" aria-label="Закрыть" @click="categoriesOpen = false">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="mt-3 grid grid-cols-2 gap-x-3">
                <Link
                    v-for="category in categories"
                    :key="category.slug"
                    :href="route('articlesByCategory', category.slug)"
                    class="theme-choice"
                    @click="categoriesOpen = false"
                >
                    <span class="min-w-0 flex-1 truncate">{{ category.name }}</span>
                    <span v-if="category.articles_count" class="num text-[0.75rem] text-muted">{{ category.articles_count }}</span>
                </Link>
            </div>
        </div>
    </Transition>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import { onClickOutside } from "@vueuse/core";
const page = usePage();

const signedIn = computed(() => Boolean(page.props.auth?.user));
const categories = computed(() => page.props.categories ?? []);

// Гостю и профиль, и «Написать» ведут на вход: не обещаем страницы, куда не пустят
const authPath = computed(() => (signedIn.value ? route("profile") : route("login")));
const authPathName = computed(() => (signedIn.value ? "profile" : "login"));
const createPath = computed(() => (signedIn.value ? route("create") : route("login")));

// Шторка рубрик: открывается кнопкой, закрывается выбором, Esc и кликом мимо
const categoriesOpen = ref(false);
const sheet = ref(null);
const categoriesButton = ref(null);

onClickOutside(sheet, () => (categoriesOpen.value = false), { ignore: [categoriesButton] });

const onKeydown = (event) => {
    if (event.key === "Escape") categoriesOpen.value = false;
};

onMounted(() => window.addEventListener("keydown", onKeydown));
onBeforeUnmount(() => window.removeEventListener("keydown", onKeydown));

// Подсветка текущего раздела: Ziggy понимает маски, как в NavLink.vue
const isCurrent = (pattern) => {
    try {
        return route().current(pattern);
    } catch {
        return false;
    }
};
</script>
