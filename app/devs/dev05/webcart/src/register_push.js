export const RegisterPush = {
    init:function(vapidPublicKey){
        this.vapidPublicKey = vapidPublicKey;
        this.SW_PATH = `${GLB_APP_URL}/webcart/sw.js`;
        console.log('RegisterPush init!');
        this.regPush();        

    },
    initServiceWorker: async function() {
        // проверяем доступность Service Worker
        if (!('serviceWorker' in navigator)) {
            console.log('SW not supported');
            return false;
        }
        // регистрируем Service Worker
        try {
            const registration = await navigator.serviceWorker.register(this.SW_PATH);
            console.log('Service Worker registered successfully');
            return registration;
        } catch (error) {
            console.error('Service Worker registration failed:', error);
            return false;
        }
    },
    regPush: async function() {
        
        // 1. Регистрируем Service Worker
        const registration = await this.initServiceWorker();
        if(!registration) return;
        
        // 2. Ждем, пока Service Worker активируется
        // (можно использовать ready или ждать события activate)
        await this.waitForServiceWorkerReady(registration);
        
        // 3. Теперь запрашиваем разрешение
        const permission = await Notification.requestPermission();
        if (permission !== 'granted') {
            alert('Разрешите уведомления');
            return;
        }                

        // Получаем существующую подписку
        let subscriptionAlready = await registration.pushManager.getSubscription();                
        
        if (subscriptionAlready) {
            console.log('Подписка на уведомления уже есть');
            // Если подписка уже есть, можно не создавать новую
            console.log('subscriptionAlready',subscriptionAlready);
            alert('Уведомления уже включены');            
            return;
        }

        // 5. Создаем новую подписку
        const subscription = await registration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: urlBase64ToUint8Array(this.vapidPublicKey)
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
        
    },
    
    // Вспомогательная функция для ожидания готовности Service Worker
    waitForServiceWorkerReady: async function(registration) {
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
    
}