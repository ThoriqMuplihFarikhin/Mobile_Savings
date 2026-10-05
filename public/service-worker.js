const NAMA_CACHE = 'aset-statis-v1';

const AWALAN_STATIS = [
    '/build/',
    '/manifest.webmanifest',
    '/icon-',
    '/favicon.',
    '/apple-touch-icon',
];

self.addEventListener('install', () => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches
            .keys()
            .then((kunci) =>
                Promise.all(
                    kunci
                        .filter((kunciLama) => kunciLama !== NAMA_CACHE)
                        .map((kunciLama) => caches.delete(kunciLama))
                )
            )
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const permintaan = event.request;

    if (permintaan.method !== 'GET') {
        return;
    }

    const url = new URL(permintaan.url);

    if (url.origin !== self.location.origin) {
        return;
    }

    const statis = AWALAN_STATIS.some((awalan) => url.pathname.startsWith(awalan))
        || /\.(?:png|svg|ico|webmanifest)$/.test(url.pathname);

    if (!statis) {
        return;
    }

    event.respondWith(
        caches.open(NAMA_CACHE).then(async (cache) => {
            const tersimpan = await cache.match(permintaan);

            if (tersimpan) {
                return tersimpan;
            }

            const respons = await fetch(permintaan);

            if (respons.ok) {
                cache.put(permintaan, respons.clone());
            }

            return respons;
        })
    );
});
