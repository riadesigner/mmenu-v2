// Регистрируем Service Worker при загрузке страницы (без запроса разрешения!)
async function initServiceWorker() {
    if (!('serviceWorker' in navigator)) {
        console.log('SW not supported');
        return false;
    }
    
    try {
        const registration = await navigator.serviceWorker.register(`${GLB_APP_URL}/webcart/sw.js`);
        console.log('Service Worker registered successfully');
        return registration;
    } catch (error) {
        console.error('Service Worker registration failed:', error);
        return false;
    }
}

// Вызываем при загрузке страницы
document.addEventListener('DOMContentLoaded', () => {
    initServiceWorker();

    // HTML кнопка для вызова
    const btn = document.getElementById('enablePushBtn');
    console.log('btn', btn);
    document.getElementById('enablePushBtn')?.addEventListener('click', () => {
        console.log('Clicked enablePushBtn 1');
        registerPush('BLBMhtX4Q6rQrZ8j_JepZMQWZ36r3hMHL-6Iq5sFe8jE7ebF9sVwY89v_0iawLMkYh0WckmcSeAmUdQwm_v46V0');
    });
        
});

// Функция подписки - вызываем ТОЛЬКО по клику
async function registerPush(vapidPublicKey) {
    // Проверяем Service Worker
    if (!('serviceWorker' in navigator)) {
        console.log('SW not supported');
        alert('Уведомления не поддерживаются');
        return;
    }
    
     console.log('TEST-A');


    // 1. СНАЧАЛА регистрируем Service Worker
            console.log('Registering Service Worker...');
            let registration = await navigator.serviceWorker.register(`${GLB_APP_URL}/webcart/sw.js`);
            console.log('Service Worker registered:', registration);
            
            // 2. Ждем, пока Service Worker активируется
            // (можно использовать ready или ждать события activate)
            await waitForServiceWorkerReady(registration);
            
            // 3. Теперь запрашиваем разрешение
            const permission = await Notification.requestPermission();
            if (permission !== 'granted') {
                alert('Разрешите уведомления');
                return;
            }

            console.log('TEST0 - Permission:', permission);    

            // Получаем существующую подписку
            let subscriptionAlready = await registration.pushManager.getSubscription();
            console.log('TEST2 - Got subscription:', subscriptionAlready);
            
            if (subscriptionAlready) {
                console.log('Подписка на уведомления уже есть');
                // Если подписка уже есть, можно не создавать новую
                alert('Уведомления уже включены');
                return;
            }

            // 5. Создаем новую подписку
            const subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(vapidPublicKey)
            });
            
            console.log('New subscription created:', subscription);
            
            // 6. Отправляем на сервер
            // const response = await fetch('/api-save-subscription', {
            //     method: 'POST',
            //     headers: { 'Content-Type': 'application/json' },
            //     body: JSON.stringify(subscription)
            // });
            
            // const text = await response.text();
            // console.log("Server response:", text);
            // alert('Уведомления успешно включены!');


    console.log('STOP2')
    return;

    



    

    

    
    console.log('TEST3 - Subscription created:', subscription);
    
    // Отправляем на сервер
    try {
        const response = await fetch('/api-save-subscription', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(subscription)
        });
        
        const text = await response.text();
        console.log("Server response:", text);
        
        if (response.ok) {
            alert('Уведомления успешно включены!');
        } else {
            alert('Ошибка при сохранении подписки');
        }
    } catch (error) {
        console.error('Failed to save subscription:', error);
        alert('Ошибка соединения с сервером');
    }
    
    console.log('Push registered');
}

// Вспомогательная функция
function urlBase64ToUint8Array(base64String) {
    // Очищаем строку от пробелов, переносов строк и других лишних символов
    base64String = base64String.trim();
    
    // Удаляем возможные префиксы типа '-----BEGIN PUBLIC KEY-----'
    if (base64String.includes('BEGIN PUBLIC KEY')) {
        base64String = base64String
            .replace(/-----BEGIN PUBLIC KEY-----/, '')
            .replace(/-----END PUBLIC KEY-----/, '')
            .replace(/\s/g, '');
    }
    
    // Проверяем, что строка не пустая
    if (!base64String) {
        console.error('VAPID key is empty');
        throw new Error('VAPID public key is empty');
    }
    
    console.log('Original VAPID key:', base64String);
    
    try {
        // Добавляем padding если нужно
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding)
            .replace(/-/g, '+')
            .replace(/_/g, '/');
        
        console.log('Formatted base64:', base64);
        
        const rawData = atob(base64);
        return Uint8Array.from([...rawData].map(char => char.charCodeAt(0)));
    } catch (error) {
        console.error('Failed to decode VAPID key:', error);
        console.error('Problematic string:', base64String);
        throw new Error('Invalid VAPID public key format');
    }
}


// Вспомогательная функция для ожидания готовности Service Worker
async function waitForServiceWorkerReady(registration) {
    return new Promise((resolve, reject) => {
        // Проверяем, если уже активен
        if (registration.active) {
            console.log('Service Worker already active');
            resolve(registration);
            return;
        }
        
        // Ждем событие activate
        const serviceWorker = registration.installing || registration.waiting;
        if (serviceWorker) {
            console.log('Waiting for Service Worker to activate...');
            serviceWorker.addEventListener('statechange', (event) => {
                console.log('Service Worker state:', event.target.state);
                if (event.target.state === 'activated') {
                    console.log('Service Worker activated');
                    resolve(registration);
                }
            });
        } else {
            // Если нет ни installing, ни waiting, ждем ready
            navigator.serviceWorker.ready.then(resolve).catch(reject);
        }
        
        // Таймаут на всякий случай
        setTimeout(() => {
            reject(new Error('Service Worker activation timeout'));
        }, 10000);
    });
}
