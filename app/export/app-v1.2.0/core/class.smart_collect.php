<?php
/*
*   @designed by riadesign.ru
*   
*   Depends on Smart_object
*   
*   Создает коллекцию объектов c данными по подобию таблицы в Базе Данных (БД), с условиями
*   $co = new Smart_collect(TABLE_NAME,CONDITIONS,ORDER,LIMIT);
*   $co = new Smart_collect('product','WHERE age=17','ORDER BY pos','LIMIT 1000');
*   
*   
*/

class Smart_collect implements Stringable
{
    // Если хотя бы один объект существует; 
    private $_FULL = false;  
    // Название таблицы в БД;
    private $TABLE_NAME='';
    // переменные
    private $VARS = [];    
    // Всего в БД по данному запросу;
    private $_TOTAL = 0;
    // Выбрано по запросу;
    private $_FOUND = 0;   
    // Найдено по запросу;
    private $ARR_SMART_OBJECTS = [];     
    // Обрезаем вывод слишком большого массива найденных значений
    private $MAX_IN_ARRAY = 1000;

    function __construct($table_name='',$COND="", $ORDER="", $LIMIT=""){
    
        $this->VARS['total'] = &$this->_TOTAL;
        $this->VARS['found'] = &$this->_FOUND;
        $this->VARS['full'] = &$this->_FULL;

        if(empty($table_name)) return false;
        $this->TABLE_NAME = $table_name;

        $q = "SELECT * FROM {$this->TABLE_NAME} ";
        if($COND) $q .= " {$COND}";
        if($ORDER) $q .=" {$ORDER}";
        if($LIMIT) $q .=" {$LIMIT}";
        
        $res = SQL::query($q);
        
        if(!$res) return false;
        
        if($res->num_rows){

            $this->_FOUND = $res->num_rows;
            $this->_FULL = true;
            $this->_TOTAL = $this->_FOUND;

            if(!empty($LIMIT)){
                // update total without limit
                $q = "SELECT count(id) as total FROM {$this->TABLE_NAME} ";
                if($COND) $q .= $COND;
                $res_total = SQL::query($q);
                if($res_total && $res_total->num_rows){
                    $counter = $res_total->fetch_object();
                    $this->_TOTAL = $counter->total;
                }
            }

            $SMART_OBJECT = new Smart_object($this->TABLE_NAME);
            
            $counter = 1;
            
            while ($fetch = $res->fetch_assoc()){
                // cut too large array
                if($counter > $this->MAX_IN_ARRAY) break; 
                // collect Smart objects into array
                $CLON = clone $SMART_OBJECT;
                $CLON->build_from_fetch($fetch);
                $this->ARR_SMART_OBJECTS[]= $CLON;
                $counter++;
            }
        }

        return $this;

    }

    // возвращает смарт объект(ы)
    public function get($index=null){
        if($index===null)
        return $this->ARR_SMART_OBJECTS;
        else return $this->ARR_SMART_OBJECTS[$index];
    }
    // возвращает количество собранных смарт объектов
    public function found(){
       return $this->_FOUND;
    }   
    // возвращает количество всех записей,
    // удовлетворяющих запросу, не учитывая лимит
    public function total(){
       return $this->_TOTAL;
    }
    // возвращает bool значение,
    // есть ли собранные объекты.
    public function full(){
       return $this->_FULL;
    }   
    // возвращает приватные значения
    // alternative way to get 
    // full, total, found properties;
    public function __get($index){        
       return $this->VARS[$index];
    }
    // возвращает в массиве 
    // экспорты всех своих объектов
    public function export(){
        $arr = [];        
        if($this->full()){
            foreach ($this->get() as $obj) {                
                $arr[] = $obj->export();
            }
        }
        return $arr;
    }    
    public function __toString(): string{
       return $this->_TOTAL." / ".$this->_FOUND;
    }
  
}

?>