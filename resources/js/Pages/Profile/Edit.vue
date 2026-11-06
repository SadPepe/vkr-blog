<template>
    <Layout>
        <Head title="Настройки профиля" />

        <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-bold tracking-tight text-content">Настройки профиля</h1>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Данные профиля -->
                <section class="rounded-md border border-line bg-surface p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-content">Данные профиля</h2>
                    <p class="mt-1 text-sm text-muted">
                        Имя и рассказ о себе видят читатели. Логин нужен только для входа, почта — для восстановления пароля.
                    </p>

                    <div class="mt-5 space-y-4">
                        <div>
                            <InputLabel for="name" value="Имя" />
                            <TextInput
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="mt-1.5 block w-full"
                                required
                                autofocus
                                autocomplete="name"
                            />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>

                        <div>
                            <p class="text-sm font-medium text-content">Логин</p>
                            <p class="mt-1.5 text-sm text-content">{{ user.username }}</p>
                            <p class="mt-1 text-xs text-muted">Используется для входа, другим не показывается.</p>
                        </div>

                        <div>
                            <InputLabel for="email" value="Почта (необязательно)" />
                            <TextInput id="email" v-model="form.email" type="email" class="mt-1.5 block w-full" autocomplete="email" />
                            <p class="mt-1 text-xs text-muted">Нужна для восстановления пароля. Можно оставить пустой.</p>
                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>

                        <div>
                            <InputLabel for="about" value="Обо мне" />
                            <TextArea id="about" v-model="form.about" rows="4" class="mt-1.5 block w-full" autocomplete="about" />
                            <InputError class="mt-2" :message="form.errors.about" />
                        </div>
                    </div>
                </section>

                <!-- Ссылки автора -->
                <section class="rounded-md border border-line bg-surface p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-content">Ссылки</h2>
                    <p class="mt-1 text-sm text-muted">
                        Сайт, Telegram, VK, портфолио — до пяти ссылок. Они появятся в профиле в блоке над «Об&nbsp;авторе». Название можно
                        не заполнять: тогда подставится домен.
                    </p>

                    <div class="mt-5 space-y-3">
                        <div v-for="(link, index) in form.links" :key="index" class="flex flex-col gap-2 sm:flex-row sm:items-start">
                            <TextInput
                                :id="`link-label-${index}`"
                                v-model="link.label"
                                type="text"
                                class="sm:w-44"
                                placeholder="Название, например Telegram"
                                aria-label="Название ссылки"
                                autocomplete="off"
                            />
                            <div class="min-w-0 flex-1">
                                <TextInput
                                    :id="`link-url-${index}`"
                                    v-model="link.url"
                                    type="text"
                                    class="w-full"
                                    placeholder="t.me/username или https://site.ru"
                                    aria-label="Адрес ссылки"
                                    autocomplete="off"
                                />
                                <InputError class="mt-1.5" :message="form.errors[`links.${index}.url`]" />
                            </div>
                            <button
                                type="button"
                                class="inline-flex h-10 w-10 items-center justify-center text-muted transition-colors hover:text-accent focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent"
                                :aria-label="`Убрать ссылку ${index + 1}`"
                                title="Убрать ссылку"
                                @click="removeLink(index)"
                            >
                                <svg
                                    class="h-4 w-4"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.6"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <InputError :message="form.errors.links" />

                        <button
                            v-if="form.links.length < 5"
                            type="button"
                            class="inline-flex min-h-11 items-center text-sm text-accent transition-colors hover:text-accent-hover"
                            @click="addLink"
                        >
                            Добавить ссылку
                        </button>
                    </div>
                </section>

                <!-- Оформление профиля -->
                <section class="rounded-md border border-line bg-surface p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-content">Оформление профиля</h2>
                    <p class="mt-1 text-sm text-muted">
                        Обложка украсит шапку профиля, фон — поля вокруг него. Фон автоматически приглушается и размывается, чтобы текст
                        оставался читаемым при любом изображении. Подойдут JPG, PNG или WebP до 5 МБ.
                    </p>

                    <div class="mt-5 grid gap-6 sm:grid-cols-2">
                        <!-- Аватар -->
                        <div class="sm:col-span-2">
                            <p class="text-sm font-medium text-content">Аватар</p>

                            <div class="mt-2 flex flex-wrap items-center gap-4">
                                <div class="h-20 w-20 shrink-0 overflow-hidden rounded-full ring-1 ring-line">
                                    <img v-if="avatarShown" :src="avatarShown" alt="Аватар профиля" class="h-full w-full object-cover" />
                                    <span
                                        v-else
                                        class="flex h-full w-full items-center justify-center bg-surface-muted text-lg font-semibold text-muted"
                                    >
                                        {{ avatarInitial }}
                                    </span>
                                </div>

                                <div class="flex flex-wrap items-center gap-2">
                                    <label
                                        for="avatar_image"
                                        class="inline-flex cursor-pointer items-center justify-center rounded-md border border-line-strong px-3 py-1.5 text-sm font-medium text-content transition-colors hover:bg-surface-muted focus-within:ring-2 focus-within:ring-accent focus-within:ring-offset-2 focus-within:ring-offset-surface"
                                        :class="{ 'pointer-events-none opacity-60': avatarProcessing }"
                                    >
                                        {{ avatarShown ? "Заменить" : "Загрузить" }}
                                    </label>
                                    <input
                                        id="avatar_image"
                                        ref="avatarInput"
                                        type="file"
                                        accept="image/jpeg,image/png,image/webp"
                                        class="sr-only"
                                        @change="onAvatarChange"
                                    />
                                    <button
                                        v-if="avatarShown"
                                        type="button"
                                        class="rounded-md px-2.5 py-1.5 text-sm font-medium text-red-600 transition-colors hover:bg-surface-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500 dark:text-red-400"
                                        @click="removeAvatar"
                                    >
                                        Удалить
                                    </button>

                                    <span v-if="avatarProcessing" class="text-sm text-muted">Готовим фото…</span>
                                </div>
                            </div>

                            <p class="mt-1.5 text-xs text-muted">Квадратное фото до 2 МБ — сожмём до 512 px автоматически.</p>

                            <InputError class="mt-1" :message="form.errors.avatar_image" />
                        </div>

                        <!-- Обложка -->
                        <div>
                            <p class="text-sm font-medium text-content">Фото в шапке профиля</p>

                            <div class="mt-2 overflow-hidden rounded-md border border-line bg-surface-muted">
                                <div class="relative aspect-[16/9] w-full">
                                    <img v-if="coverShown" :src="coverShown" alt="Обложка профиля" class="h-full w-full object-cover" />
                                    <div
                                        v-else
                                        class="flex h-full w-full items-center justify-center bg-gradient-to-r from-accent to-accent-2 px-3 text-center text-xs font-medium text-accent-contrast"
                                    >
                                        Без обложки
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3 flex flex-wrap items-center gap-2">
                                <label
                                    for="cover_image"
                                    class="inline-flex cursor-pointer items-center justify-center rounded-md border border-line-strong px-3 py-1.5 text-sm font-medium text-content transition-colors hover:bg-surface-muted focus-within:ring-2 focus-within:ring-accent focus-within:ring-offset-2 focus-within:ring-offset-surface"
                                >
                                    {{ coverShown ? "Заменить" : "Загрузить" }}
                                </label>
                                <input
                                    id="cover_image"
                                    ref="coverInput"
                                    type="file"
                                    accept="image/jpeg,image/png,image/webp"
                                    class="sr-only"
                                    @change="onCoverChange"
                                />
                                <button
                                    v-if="coverShown"
                                    type="button"
                                    class="rounded-md px-2.5 py-1.5 text-sm font-medium text-red-600 transition-colors hover:bg-surface-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500 dark:text-red-400"
                                    @click="removeCover"
                                >
                                    Удалить
                                </button>
                            </div>

                            <InputError class="mt-2" :message="form.errors.cover_image" />
                        </div>

                        <!-- Фон -->
                        <div>
                            <p class="text-sm font-medium text-content">Фон страницы профиля</p>

                            <div class="mt-2 overflow-hidden rounded-md border border-line bg-surface-muted">
                                <div class="relative aspect-[16/9] w-full">
                                    <img
                                        v-if="backgroundShown"
                                        :src="backgroundShown"
                                        alt="Фон профиля"
                                        class="h-full w-full object-cover"
                                    />
                                    <div
                                        v-else
                                        class="flex h-full w-full items-center justify-center bg-page px-3 text-center text-xs font-medium text-muted"
                                    >
                                        Фон темы по умолчанию
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3 flex flex-wrap items-center gap-2">
                                <label
                                    for="background_image"
                                    class="inline-flex cursor-pointer items-center justify-center rounded-md border border-line-strong px-3 py-1.5 text-sm font-medium text-content transition-colors hover:bg-surface-muted focus-within:ring-2 focus-within:ring-accent focus-within:ring-offset-2 focus-within:ring-offset-surface"
                                >
                                    {{ backgroundShown ? "Заменить" : "Загрузить" }}
                                </label>
                                <input
                                    id="background_image"
                                    ref="backgroundInput"
                                    type="file"
                                    accept="image/jpeg,image/png,image/webp"
                                    class="sr-only"
                                    @change="onBackgroundChange"
                                />
                                <button
                                    v-if="backgroundShown"
                                    type="button"
                                    class="rounded-md px-2.5 py-1.5 text-sm font-medium text-red-600 transition-colors hover:bg-surface-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500 dark:text-red-400"
                                    @click="removeBackground"
                                >
                                    Удалить
                                </button>
                            </div>

                            <InputError class="mt-2" :message="form.errors.background_image" />
                        </div>

                        <!-- Размытие фона -->
                        <div class="sm:col-span-2">
                            <div class="flex items-center justify-between gap-3">
                                <label for="background_blur" class="text-sm font-medium text-content"> Размытие фона </label>
                                <span class="text-sm tabular-nums text-muted">{{ form.background_blur }} px</span>
                            </div>

                            <input
                                id="background_blur"
                                v-model.number="form.background_blur"
                                type="range"
                                min="0"
                                max="24"
                                step="1"
                                class="mt-2 w-full accent-accent"
                                :disabled="!backgroundShown"
                            />

                            <div class="mt-1 flex items-center justify-between text-xs text-muted">
                                <span>0 — чётко</span>
                                <span>24 — мягко</span>
                            </div>

                            <p class="mt-1.5 text-xs text-muted">
                                Читаемость от размытия не зависит: тексты и карточки всегда лежат на непрозрачных панелях. Ползунок меняет
                                только то, насколько заметен фон.
                            </p>

                            <InputError class="mt-1" :message="form.errors.background_blur" />
                        </div>
                    </div>
                </section>

                <div class="flex items-center gap-4">
                    <PrimaryButton :disabled="form.processing">Сохранить</PrimaryButton>

                    <span v-if="form.processing && form.progress" class="text-sm text-muted">
                        Загрузка {{ form.progress.percentage }}%
                    </span>

                    <Transition
                        enter-active-class="transition ease-in-out"
                        enter-from-class="opacity-0"
                        leave-active-class="transition ease-in-out"
                        leave-to-class="opacity-0"
                    >
                        <p v-if="form.recentlySuccessful" class="text-sm text-accent">Сохранено</p>
                    </Transition>
                </div>
            </form>

            <!-- Смена пароля -->
            <section class="mt-6 rounded-md border border-line bg-surface p-6 shadow-sm">
                <UpdatePasswordForm />
            </section>

            <!-- Удаление аккаунта -->
            <section class="mt-6 rounded-md border border-line bg-surface p-6 shadow-sm">
                <DeleteUserForm />
            </section>
        </div>
    </Layout>
</template>

<script setup>
import { computed, ref } from "vue";
import { Head, useForm } from "@inertiajs/vue3";
import imageCompression from "browser-image-compression";
import Layout from "@/Layouts/Layout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextArea from "@/Components/TextArea.vue";
import TextInput from "@/Components/TextInput.vue";
import DeleteUserForm from "@/Pages/Profile/Partials/DeleteUserForm.vue";
import UpdatePasswordForm from "@/Pages/Profile/Partials/UpdatePasswordForm.vue";

const props = defineProps({
    mustVerifyEmail: Boolean,
    status: String,
    user: {
        type: Object,
        required: true,
    },
});

const addLink = () => {
    if (form.links.length < 5) form.links.push({ label: "", url: "" });
};

const removeLink = (index) => {
    form.links.splice(index, 1);
};

const avatarInput = ref(null);
const coverInput = ref(null);
const backgroundInput = ref(null);
const avatarObjectUrl = ref(null);
const coverObjectUrl = ref(null);
const backgroundObjectUrl = ref(null);
const avatarProcessing = ref(false);

const form = useForm({
    name: props.user.name,
    email: props.user.email ?? "",
    about: props.user.about || "",
    background_blur: props.user.background_blur ?? 12,
    links: (props.user.links ?? []).map((link) => ({ label: link.label ?? "", url: link.url ?? "" })),
    avatar_image: null,
    cover_image: null,
    background_image: null,
    remove_avatar_image: false,
    remove_cover_image: false,
    remove_background_image: false,
});

const avatarShown = computed(() => {
    if (form.avatar_image) return avatarObjectUrl.value;
    return form.remove_avatar_image ? null : props.user.avatar_url;
});

const avatarInitial = computed(() => (props.user.name || "?").trim().charAt(0).toUpperCase());

const coverShown = computed(() => {
    if (form.cover_image) return coverObjectUrl.value;
    return form.remove_cover_image ? null : props.user.cover_url;
});

const backgroundShown = computed(() => {
    if (form.background_image) return backgroundObjectUrl.value;
    return form.remove_background_image ? null : props.user.background_url;
});

const previewUrl = (file, current) => {
    if (current) URL.revokeObjectURL(current);
    return file ? URL.createObjectURL(file) : null;
};

const onAvatarChange = async (event) => {
    const file = event.target.files?.[0] ?? null;

    if (!file) return;

    avatarProcessing.value = true;

    try {
        const compressed = await imageCompression(file, {
            maxWidthOrHeight: 512,
            maxSizeMB: 0.5,
            useWebWorker: true,
            fileType: "image/jpeg",
        });

        form.avatar_image = compressed;
        form.remove_avatar_image = false;
        avatarObjectUrl.value = previewUrl(compressed, avatarObjectUrl.value);
    } finally {
        avatarProcessing.value = false;
    }
};

const onCoverChange = (event) => {
    const file = event.target.files?.[0] ?? null;
    form.cover_image = file;
    form.remove_cover_image = false;
    coverObjectUrl.value = previewUrl(file, coverObjectUrl.value);
};

const onBackgroundChange = (event) => {
    const file = event.target.files?.[0] ?? null;
    form.background_image = file;
    form.remove_background_image = false;
    backgroundObjectUrl.value = previewUrl(file, backgroundObjectUrl.value);
};

const removeAvatar = () => {
    form.avatar_image = null;
    form.remove_avatar_image = true;
    avatarObjectUrl.value = previewUrl(null, avatarObjectUrl.value);
    if (avatarInput.value) avatarInput.value.value = "";
};

const removeCover = () => {
    form.cover_image = null;
    form.remove_cover_image = true;
    coverObjectUrl.value = previewUrl(null, coverObjectUrl.value);
    if (coverInput.value) coverInput.value.value = "";
};

const removeBackground = () => {
    form.background_image = null;
    form.remove_background_image = true;
    backgroundObjectUrl.value = previewUrl(null, backgroundObjectUrl.value);
    if (backgroundInput.value) backgroundInput.value.value = "";
};

const submit = () => {
    form.transform((data) => {
        // Multipart-тело PHP разбирает только у POST, поэтому метод подменяем вручную.
        const payload = { ...data, _method: "PUT" };

        // Не отправляем пустые файлы и «false»-флаги: правило boolean их не примет.
        ["avatar_image", "cover_image", "background_image"].forEach((field) => {
            if (!payload[field]) delete payload[field];
        });
        ["remove_avatar_image", "remove_cover_image", "remove_background_image"].forEach((field) => {
            if (!payload[field]) delete payload[field];
        });

        return payload;
    }).post(route("profile.update"), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            avatarObjectUrl.value = previewUrl(null, avatarObjectUrl.value);
            coverObjectUrl.value = previewUrl(null, coverObjectUrl.value);
            backgroundObjectUrl.value = previewUrl(null, backgroundObjectUrl.value);
            if (avatarInput.value) avatarInput.value.value = "";
            if (coverInput.value) coverInput.value.value = "";
            if (backgroundInput.value) backgroundInput.value.value = "";
            form.avatar_image = null;
            form.cover_image = null;
            form.background_image = null;
            form.remove_avatar_image = false;
            form.remove_cover_image = false;
            form.remove_background_image = false;
        },
    });
};
</script>
