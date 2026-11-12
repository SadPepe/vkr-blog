<template>
    <footer class="mt-24 bg-surface">
        <div class="page-shell pb-10 pt-16 lg:pt-20">
            <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4 lg:gap-12">
                <div class="lg:col-span-2">
                    <p class="type-h2 max-w-[22ch] text-content">Пространство для текстов, фотографий и заметок.</p>
                    <p class="mt-4 max-w-[46ch] text-muted">
                        У каждого свой профиль, своя обложка и своя тема из шестнадцати. Статьи, категории, комментарии и поиск — всё на
                        месте.
                    </p>
                    <a
                        v-if="!$page.props.auth.user"
                        :href="route('register')"
                        class="ui-pill ui-pill--solid mt-8 px-5"
                    >
                        Регистрация
                    </a>
                    <Link
                        v-else
                        :href="route('create')"
                        class="ui-pill ui-pill--solid mt-8 px-5"
                    >
                        Написать статью
                    </Link>
                </div>

                <div>
                    <p class="text-sm text-muted">Разделы</p>
                    <ul class="mt-4 space-y-2.5">
                        <li>
                            <Link :href="route('home')" class="text-muted transition-colors hover:text-accent"> Главная </Link>
                        </li>
                        <li>
                            <Link :href="route('authors.index')" class="text-muted transition-colors hover:text-accent"> Авторы </Link>
                        </li>
                        <li>
                            <Link :href="route('articles.search')" class="text-muted transition-colors hover:text-accent"> Поиск </Link>
                        </li>
                        <li v-if="$page.props.auth.user">
                            <Link :href="route('create')" class="text-muted transition-colors hover:text-accent"> Создать статью </Link>
                        </li>
                        <li v-else>
                            <Link :href="route('register')" class="text-muted transition-colors hover:text-accent"> Регистрация </Link>
                        </li>
                    </ul>
                </div>

                <div>
                    <p class="text-sm text-muted">Мы в сети</p>
                    <div class="mt-4 flex items-center gap-4">
                        <a href="#" aria-label="ВКонтакте" class="text-muted transition-colors hover:text-accent">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path
                                    d="M19.915 8.268c.11-.372 0-.634-.53-.634h-1.74c-.448 0-.653.233-.765.49 0 0-.886 2.129-2.15 3.514-.412.407-.603.539-.832.539-.11 0-.29-.133-.29-.51V8.268c0-.447-.13-.634-.51-.634h-2.73c-.289 0-.447.204-.447.397 0 .412.63.507.696 1.665v2.512c0 .446-.08.528-.27.528-.613 0-2.083-2.205-2.955-4.727-.168-.501-.345-.667-.797-.667H7.35c-.501 0-.601.237-.601.49 0 .473.619 2.951 2.881 6.202 1.507 2.14 3.672 3.312 5.645 3.312 1.18 0 1.326-.26 1.326-.708v-1.63c0-.497.107-.596.461-.596.262 0 .712.133 1.76 1.16 1.26 1.25 1.469 1.81 2.15 1.81h1.74c.501 0 .746-.26.603-.742-.155-.49-.748-1.21-1.52-2.05-.387-.446-.973-1.017-1.144-1.25-.26-.338-.186-.491 0-.791.003-.04.87-1.25 1.332-2.06z"
                                />
                            </svg>
                        </a>
                    </div>
                    <p class="mt-4 max-w-[28ch] text-sm text-muted">Подписывайтесь, чтобы не пропускать новые статьи.</p>
                </div>
            </div>

            <!-- Название возвращается во всю ширину: футер — закрывающая полоса, а не карта сайта -->
            <div class="mt-16">
                <p class="type-display-xl text-content" aria-hidden="true">Челны <span class="text-accent">подземка</span></p>
                <div class="mt-6 flex flex-col gap-2 text-sm text-muted sm:flex-row sm:items-center sm:justify-between">
                    <p>© {{ year }} Челны подземка. Все права защищены.</p>
                    <p>Сделано с любовью к текстам</p>
                </div>

                <div class="mt-6 flex flex-wrap gap-x-5 gap-y-2 border-t border-line pt-5 text-sm">
                    <Link :href="route('legal.privacy')" class="text-muted transition-colors hover:text-accent">
                        Политика обработки ПД
                    </Link>
                    <Link :href="route('legal.terms')" class="text-muted transition-colors hover:text-accent">
                        Пользовательское соглашение
                    </Link>
                    <Link :href="route('legal.consent')" class="text-muted transition-colors hover:text-accent">
                        Согласие на обработку
                    </Link>
                    <Link :href="route('legal.distribution')" class="text-muted transition-colors hover:text-accent">
                        Согласие на распространение
                    </Link>
                    <Link :href="route('legal.copyright')" class="text-muted transition-colors hover:text-accent"> Правообладателям </Link>
                    <a :href="'mailto:' + $page.props.legal.email" class="text-muted transition-colors hover:text-accent">
                        {{ $page.props.legal.email }}
                    </a>
                </div>
            </div>
        </div>
    </footer>
</template>
<script setup>
import { Link } from "@inertiajs/vue3";

const year = new Date().getFullYear();
</script>
