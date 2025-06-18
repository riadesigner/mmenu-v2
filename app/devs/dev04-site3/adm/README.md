### Добавление заказа в корзину

При добавлении заказа в корзину создается предварительный объект __preorderObject__,   
который содержит информацию о товаре, выборе размера, выбранных модификаторах    
и количестве всей этой группы выборов, объедиенные в строку (позицию) заказа.   

Всей этой позиции заказа выдается уникальный идентификатор __uniq_name__.   

```JavaScript
/**
    * preorderObject = { 
    *   chosen_modifiers: array; // (IIKO only)
    *   count: number;
    *   itemId: string;
    *   item_data: object;
    *   originalPrice: number; // (IIKO only) for virtual size (from modifier->sizes[0]->price)
    *   sizeGroupId: string; // (IIKO only) for virtual size (from modifier->modifierGroupId )
    *   price: number;
    *	sizeCode: string; // (IIKO only)
    *	sizeId: string; // (IIKO only)	 
    *	sizeName: string; // (IIKO only)	 
    *   uniq_name: string; // chefs-order-7330-930162801
    *	units: string; // г|мл|л|кг
    *   sizeName:string;
    *	volume: number;
    * }
```

## Особенности реализации размерного ряда (aka Pizzaiolo)

originalPrice, sizeGroupId – эти переменные содержат информацию о виртуальном размере. Виртуальный размер (при построении интерфейса пользователя) создается из группы модификаторов, которые содержат в себе массив размеров. Это фишка Pizaiolo (вместо размерного ряда использовать специальные модификаторы).   

Перед отправкой в iiko (при создании JSON объекта __order__), виртуальный размер разворачивается снова в модификатор, из которого он был собран (т.к. iiko не знает ничего про размеры).

Для того, чтобы из размера снова собрать модификатор:
- originalPrice  – хранит стоимость модификатора (modifier->sizes[0]->price)
- sizeId – хранит Id модификатора (modifier->modifierId)
- sizeGroupId – хранит Id родительской группы модификатора (modifier->modifierGroupId)  

