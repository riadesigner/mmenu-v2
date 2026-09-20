### ----------- DEVELOPING & EXPORT (LOCAL) -----------

запуск docker
```bash
docker compose up --build
```  

Локально apache зеркалит prod-шлюз:
- `/x/` → `menu-app-frontend:3007` (новое меню)
- `/api/v1/` → `super-admin-backend:3006` (публичный каталог)

Пример: http://chefsmenu.localhost/x/cafe/323acg/table/1-p5jj  
(нужны поднятые `m-menu-app` и `m-super-admin` в `shared-app-network`)

установка зависимостей в php
(если не установлены)
```bash
docker exec -it php-container-id /bin/sh
composer install
```  

запуск js
```bash
cd /app/devs/dev04-site3
npm i 
npm run dev
```  

export ready project
```bash
cd /app/devs/dev04-site3
python3 export 1.2.3
```  


### ----------- UPDATING (SERVER) -----------

переходим в каталог
```bash
cd /home/webuser/mmenu-v2
```  

чтобы получить обновление - запускаем:   
```bash
bash update.sh
```  

Эта инструкция запустит ряд комманд:
```bash
git restore .
git fetch -a
git pull
chmod -R 777 ./app/tmp
```

Причина использования - необходимость выдавать права 777 на папку tmp.   
git такие права не запоминает. Он по умолчанию ставит 755.   

После этого редактируем .env   





