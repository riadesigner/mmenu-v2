self.addEventListener('install', event => {
    console.log('Service Worker installed');
});

self.addEventListener('activate', event => {
    console.log('Service Worker activated');
});

self.addEventListener('push', event => {

    console.log('Push event received');

    const data = event.data ? event.data.json() : {};

    const options = {
        body: data.body || '',
        icon: '/icons/icon-192.png',
        badge: '/icons/icon-192.png',
        data: {
            url: data.url || '/'
        }
    };

    event.waitUntil(
        self.registration.showNotification(
            data.title || 'Новое сообщение',
            options
        )
    );

});
    // это наверх в push для теста
    // event.waitUntil(
    //     self.registration.showNotification(
    //         'ТЕСТ УВЕДОМЛЕНИЯ',
    //         {
    //             body: 'Если ты это видишь — всё работает',
    //             icon: '/icon-192.png'
    //         }
    //     )
    // );

self.addEventListener('notificationclick', event => {
    event.notification.close();
    const url = event.notification.data.url;
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true })
        .then(clientList => {

            for (const client of clientList) {
                if (client.url.includes(url) && 'focus' in client) {
                    return client.focus();
                }
            }

            if (clients.openWindow) {
                return clients.openWindow(url);
            }
        })
    );
});