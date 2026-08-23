---
name: mmenu-monolith-dev
description: >-
  mmenu-v2 monolith workflow: edit only app/devs/dev05, never app/export snapshots.
  Export new app-v* with export.py after PHP-only changes; after JS/src changes run
  rollup first. Use when changing mmenu-v2, dev05, export, rollup, or monolith PHP/JS.
---

# mmenu-v2 — разработка и export

## Источник правды

| Путь | Роль |
|------|------|
| `app/devs/dev05/` | **Единственная** рабочая копия кода |
| `app/export/app-v*/` | Замороженные снимки для prod / отката — **не редактировать** |

Prod на сервере берёт код из `app/{CURRENT_APP_DIR}/` (например `export/app-v1.7.2`).

## Жёсткие запреты

- **Не** править, **не** копировать вручную и **не** коммитить изменения под `app/export/**`.
- **Не** «синхронизировать» старые export-версии (1.7.0, 1.7.1, …) с dev05.

Если случайно затронули export — откатить эти файлы из git и выпустить **новую** версию через `export.py`.

## Где править

Все изменения — только внутри `app/devs/dev05/`:

- PHP: `adm/lib/`, `pbl/lib/`, `site/lib/`, `core/`, `rds/`, …
- JS исходники: `adm/src/`, `pbl/src/`, `site/src/`, `rds/src/`, `webcart/src/`
- Сборки (после rollup): `*/dist/` в dev05
- Скрипт export: `app/devs/dev05/export.py`

## После задачи: export или нет

### Только PHP (и прочие не-JS файлы в dev05)

Можно выпустить новый export:

```bash
cd app/devs/dev05
python3 export.py X.Y.Z   # например 1.7.3 — версия не должна существовать
```

Скрипт создаёт `app/export/app-vX.Y.Z/` (копия dev05 без `*/src`, `node_modules`, …).

Сообщи пользователю:
- новый путь export;
- на сервере обновить `CURRENT_APP_DIR=export/app-vX.Y.Z` и при необходимости `CURRENT_APP_VERSION`.

### Были изменения в `*/src/` (JavaScript)

**Сначала** собрать bundle в dev05, **потом** export:

```bash
cd app/devs/dev05
npm run dev          # или однократно: npx rollup -c
python3 export.py X.Y.Z
```

Без rollup в export попадут **старые** `dist/` — **не** экспортировать новую версию, пока сборка не выполнена.

Признаки JS-изменений: правки в `adm/src/`, `pbl/src/`, `site/src/`, `rds/src/`, `webcart/src/`, `rollup.config.mjs`.

### Только правки уже собранного `dist/` без `src/`

Редкий случай (ручная подстановка). Export допустим без rollup, если пользователь явно собрал dist.

## export.py — поведение

- Аргумент: версия строкой, например `1.7.3` → папка `app/export/app-v1.7.3/`.
- Если папка уже есть — скрипт **останавливается** (нужна следующая версия).
- Исключения из копии: `export.py`, `node_modules`, `*/src`, `package.json`, rollup-конфиги, …

## Git

- Коммитить: изменения в `app/devs/dev05/` + **новая** папка `app/export/app-vX.Y.Z/` (если export делали).
- Не коммитить правки в существующие `app/export/app-v1.7.0`, `1.7.1`, …

## Чеклист перед завершением задачи

```
- [ ] Все правки только в app/devs/dev05/
- [ ] app/export/* (старые версии) не тронуты
- [ ] Если менялся JS/src — rollup выполнен
- [ ] Если нужен prod — export.py с новой версией (или пользователь сделает сам)
- [ ] Напомнить про CURRENT_APP_DIR на сервере
```
