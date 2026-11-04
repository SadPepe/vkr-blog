<template>
    <footer
        lang="ru"
        class="mt-12 border-t border-line bg-surface"
    >
        <div class="mx-auto max-w-[1400px] px-4 py-12 sm:px-6 lg:px-8">
            <div class="grid gap-10 md:grid-cols-2 lg:grid-cols-4">
                <!-- О блоге -->
                <div class="lg:col-span-2">
                    <Link
                        href="/"
                        aria-label="На главную"
                        class="inline-flex items-center gap-2.5 rounded-md text-lg font-bold tracking-tight text-content focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent dark:text-white"
                    >
                        <span
                            class="flex h-8 w-8 items-center justify-center rounded-md bg-gradient-to-br from-accent to-accent-2 text-accent-contrast shadow-md"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487z"
                                />
                            </svg>
                        </span>
                        <span>
                            МОЙ.<span
                                class="bg-accent hover:bg-accent-hover bg-clip-text text-transparent"
                                >БЛОГ</span
                            >
                        </span>
                    </Link>
                    <p class="mt-4 max-w-md text-sm text-muted">
                        Площадка, где каждый может поделиться знаниями, опытом и историями. Пишите статьи о путешествиях, технологиях, науке
                        и всём, что вас вдохновляет.
                    </p>
                </div>

                <!-- Разделы -->
                <div>
                    <p class="mb-4 text-base font-bold text-content">Разделы</p>
                    <ul class="space-y-2 text-sm">
                        <li>
                            <Link
                                href="/"
                                class="rounded text-muted transition-colors hover:text-accent focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2 dark:hover:text-accent"
                            >
                                Главная
                            </Link>
                        </li>
                        <li>
                            <Link
                                href="/search"
                                class="rounded text-muted transition-colors hover:text-accent focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2 dark:hover:text-accent"
                            >
                                Поиск
                            </Link>
                        </li>
                        <li v-if="$page.props.auth.user">
                            <Link
                                href="/create"
                                class="rounded text-muted transition-colors hover:text-accent focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2 dark:hover:text-accent"
                            >
                                Создать статью
                            </Link>
                        </li>
                        <li v-else>
                            <Link
                                href="/register"
                                class="rounded text-muted transition-colors hover:text-accent focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2 dark:hover:text-accent"
                            >
                                Регистрация
                            </Link>
                        </li>
                    </ul>
                </div>

                <!-- Соцсети -->
                <div>
                    <p class="mb-4 text-base font-bold text-content">Мы в сети</p>
                    <div class="flex items-center space-x-3">
                        <a
                            href="#"
                            aria-label="Telegram"
                            class="rounded text-muted transition-colors hover:text-accent focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2 dark:hover:text-accent"
                        >
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
                                <path
                                    d="M11.944 0A12 12 0 000 12a12 12 0 0012 12 12 12 0 0012-12A12 12 0 0012 0a12 12 0 00-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 01.171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"
                                />
                            </svg>
                        </a>
                        <a
                            href="#"
                            aria-label="ВКонтакте"
                            class="rounded text-muted transition-colors hover:text-accent focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2 dark:hover:text-accent"
                        >
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
                                <path
                                    d="M19.915 8.268c.11-.372 0-.634-.53-.634h-1.74c-.448 0-.653.233-.765.49 0 0-.886 2.129-2.15 3.514-.412.407-.603.539-.832.539-.11 0-.29-.133-.29-.51V8.268c0-.447-.13-.634-.51-.634h-2.73c-.289 0-.447.204-.447.397 0 .412.63.507.696 1.665v2.512c0 .446-.08.528-.27.528-.613 0-2.083-2.205-2.955-4.727-.168-.501-.345-.667-.797-.667H7.35c-.501 0-.601.237-.601.49 0 .473.619 2.951 2.881 6.202 1.507 2.14 3.672 3.312 5.645 3.312 1.18 0 1.326-.26 1.326-.708v-1.63c0-.497.107-.596.461-.596.262 0 .712.133 1.76 1.16 1.26 1.25 1.469 1.81 2.15 1.81h1.74c.501 0 .746-.26.603-.742-.155-.49-.748-1.21-1.52-2.05-.387-.446-.973-1.017-1.144-1.25-.26-.338-.186-.491 0-.791.003-.04.87-1.25 1.332-2.06z"
                                />
                            </svg>
                        </a>
                        <a
                            href="#"
                            aria-label="YouTube"
                            class="rounded text-muted transition-colors hover:text-accent focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2 dark:hover:text-accent"
                        >
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
                                <path
                                    d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"
                                />
                            </svg>
                        </a>
                    </div>
                    <p class="mt-4 text-sm text-muted">Подписывайтесь, чтобы не пропускать новые статьи.</p>
                </div>
            </div>

            <div class="mt-10 flex flex-col justify-between gap-4 border-t border-line pt-6 sm:flex-row">
                <p class="text-sm text-muted">© {{ year }} МОЙ.БЛОГ. Все права защищены.</p>
                <p class="text-sm text-muted">Сделано с любовью к текстам</p>
            </div>
        </div>
    </footer>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";

const year = new Date().getFullYear();
</script>
