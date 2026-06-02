### Структура таблиц


## Menu
```
- id: int
- id_cafe: int
- id_external: string (внешний id в iiko|R-keeper)
- name: string
- name_lngs: json (дополнительные языки)
- pos: tinyint
- updated: date

```

## Category
```
- id: int
- id_cafe: int
- id_menu: int
- id_external: string (внешний id в iiko|R-keeper)
- name: string
- name_lngs: json (дополнительные языки)
- updated: date
```

## Product
```
- id: int
- id_cafe: int
- id_menu: int
- id_category: int
- id_external: string (внешний id в iiko|R-keeper)
- name: string
- name_lngs: json (дополнительные языки)
- description: text
- description_lngs: json (дополнительные языки)
- sizes: json (здесь весь размерный ряд)
- modifiers: json (здесь все модификаторы)
- image_url
- iiko_order_item_type (iiko only)
- updated: date
- pos: tinyint

```

API


Получение списка меню и разделов

- GET /chefs-v3.ru/api/cafe-we241/

```
menus    
- menu1(Основное)                
-- cat 1
-- cat 2
-- cat 3        
- menu2(Детское)
-- cat 1
-- cat 2
-- cat 3        
- menu3(Карта бара)
-- cat 1
-- cat 2
-- cat 3    
```

```
category
    products    
    - product 1
    - product 2
    ...
    - product 30
```
