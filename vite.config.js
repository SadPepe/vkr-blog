import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import os from 'node:os';

/**
 * LAN-адрес машины — чтобы открывать сайт с телефона в той же Wi-Fi.
 *
 * Vite слушает все интерфейсы (0.0.0.0), но в `public/hot` Laravel пишет адрес
 * dev-сервера: если там окажется localhost/[::1], телефон не загрузит
 * `/@vite/client` и ассеты, и страница останется пустой. Поэтому в hot
 * попадает LAN-адрес. Переопределяется переменной VITE_DEV_HOST
 * (другая сеть, tailscale, домен вместо IP).
 */
function devHost() {
    if (process.env.VITE_DEV_HOST) {
        return process.env.VITE_DEV_HOST;
    }

    for (const addresses of Object.values(os.networkInterfaces())) {
        for (const address of addresses ?? []) {
            if (address.family === 'IPv4' && !address.internal) {
                return address.address;
            }
        }
    }

    return 'localhost';
}

export default defineConfig(() => {
    const host = devHost();

    return {
        plugins: [
            laravel({
                input: 'resources/js/app.js',
                refresh: true,
            }),
            vue({
                template: {
                    transformAssetUrls: {
                        base: null,
                        includeAbsolute: false,
                    },
                },
            }),
        ],
        server: {
            host: '0.0.0.0',
            port: 5173,
            strictPort: true,
            hmr: { host },
            // Телефон открывает страницу с `:8001`, а модули тянет с `:5173` —
            // это кросс-домен. По умолчанию Vite 6 разрешает CORS только для
            // localhost, поэтому на телефоне не грузится ни один ESM-модуль и
            // страница навсегда остаётся на скелетоне.
            cors: true,
        },
    };
});
