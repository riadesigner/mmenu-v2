<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');


/*
*   @designed by riadesign.ru
*
*   Создает пустой объект-шаблон по подобию таблицы Product 
*   $so = new Smart_object('product');
*   
*   Сохраняет новый объект(запись) в БД со значениями по умолчанию 
*   $so->save();
*   Присваивает значения существующим полям таблицы 
*   $so->any = 'John';
*   Обновляет запись в БД 
*   $so->save();
*   
*   Создает объект c данными по подобию таблицы product, где id=16
*   $so = new Smart_object('product',16);
*
*   Сохраняет новый объект с корректными значениями,
*   построенными на информации о типе поля в таблице;
*   Созраняет в БД измененные пользователем значения;
*   Кэширует структуру таблиц для ускорения работы;
*   
*   updated 05.03.2010 // add ARR_UPDATE;
*   updated 09.03.2010 // add DATE, TIME;
*   updated 10.03.2010 // correct «set INT && DATE by default»;
*   updated 19.10.2010 // add import()/export();
*
*   Updated 28.09.2018 // Адаптировал к mysqli
*   updated 04.07.2019 // add log
*   updated 08.07.2019 // replace SQL_CALC_FOUND_ROWS to Count(*)
*   
*   updated 13.07.2019 
*   // Replace correct_value function. 
*   // Add default value. 
*   // $this-VALID = true after save();
*   // private vars _VALID, _ID
*
*   //updated 26.08.2023
*   добавлен метод __isset()
*   (возможность проверки у объекта
*   наличия или отсутствия свойства (isset),
*   а также не пустое ли значение (!empty));
*
*/


class Smart_object implements Stringable {
    // Массив с описанием структуры таблицы БД;    
    private $ARR_FIELDS = [];
    // Массив свойств объекта по образу структуры таблицы БД,
    // новых свойств, добавленных пользователем и их значений;
    private $ARR_VALUES = [];
    // Массив имен реально существующих полей таблицы БД;
    private $ARR_NAMES = [];
    // Массив имен реально существующих полей таблицы БД
    // которые пользователь обновил;
    private $ARR_UPDATE = [];  
    // Название таблицы в БД;
    private $TABLE_NAME='';    
    // Если объект не существует в БД;
    private $_VALID = false;
    // Номер объекта в таблице;
    private $_ID=0;

function __construct($table_name='',$id=0){
    if(!$this->TABLE_NAME=$table_name) return false;
    $this->_ID = (int) $id;    
    if(!$this->table_parse()) return false;
    // fill all properties by default
    // if the Object:ID is exist in DB
    $this->_ID && $this->check_and_fill();
    return $this;
}

private function table_parse(){
    $arr = Table_structure::get($this->TABLE_NAME);
    if($arr){
        foreach ($arr as $row) {
            $nm = $row['Field'];     
            $this->ARR_FIELDS[$nm] = $row;
            $this->ARR_NAMES[] = $nm;
            $this->ARR_VALUES[$nm] = '';  
        }
        return true;
    }else{
        return false;    
    }
}

private function check_and_fill(){
    $res = SQL::query("SELECT * FROM {$this->TABLE_NAME} WHERE id={$this->_ID} LIMIT 1");  
    if($res && $res->num_rows){
        $this->_VALID = true;
        $arr = $res->fetch_assoc();
        if($this->ARR_NAMES){
            foreach ($this->ARR_NAMES as $nm) {
                // all properties except ID 
                // can be changed by user
                $this->ARR_VALUES[$nm] = $arr[$nm];
            }
        }
    }else{
        return false;
    }
}

public function save(){
    global $CFG;  

    if(!$this->valid()){ 
        $q = $this->create_query('insert');    
        if(!$q) return false;    

        if($INSERTED_ID = SQL::insert($q)){
            $this->_ID = $INSERTED_ID;                        
            $this->ARR_UPDATE = [];
            // get rest of properties from DB
            // and fill values by default
            $this->check_and_fill();
            return $this->_ID;
        }else{
            return false;
        }
        
    }else{
        $q = $this->create_query('update');
        if(!$q) return false;    
        $res = SQL::update($q);
        if($res){
            $this->ARR_UPDATE = [];
            return $this->_ID;
        }else{
            glogError("cant save: {$this}");
            return false;
        }       
    }
}

public function delete(){
    if($this->valid()){
        $q = "DELETE FROM {$this->TABLE_NAME} WHERE id={$this->id}";
        $res = SQL::delete($q);
        if($res){
            return $res;   
        }else{
            glogError("cant delete $this");
            return false;    
        }        
    }else{
        glogError("cant delete no valid object");
        return false;
    }
} 

public function valid(){
    return $this->_VALID;
}
  
public function getHTML(){
    $str_valid = $this->valid()? "true":"false";
    $html.="<div class='object' data-id='{this->_ID}' data-valid='{$str_valid}'>";
    for($i=0;$i<count($this->ARR_NAMES);$i++){
        $nm=$this->ARR_NAMES[$i];
        $html.="<div value='{$nm}'>{$this->ARR_VALUES[$nm]}</div>";
    }
    $html.="</div>";
    return $html;
}  

public function __set($index,$value){ 
    $index = mb_strtolower((string) $index);
    if($index==='id') {
        // Because ID is AUTO_INCREMENT property from Database
        glogError("trying change private property ID of Smart_object ");
    }elseif($index==='valid'){
        // Because VALID is reserved property
        glogError("trying change private property VALID of Smart_object ");        
    }else{
        if(in_array($index,$this->ARR_NAMES)) {$this->ARR_VALUES[$index] = $value;}        
        if(!in_array($index,$this->ARR_UPDATE)) {$this->ARR_UPDATE[] = $index;}
    }    
}
  
public function __get($index){
    $index = mb_strtolower((string) $index);
    if($index==='id') {
        // Because ID is AUTO_INCREMENT property from Database
        return $this->_ID;
    }elseif($index==='valid'){
        // Because VALID is reserved property
        return $this->_VALID;
    }elseif(
        in_array($index,$this->ARR_NAMES) 
        && array_key_exists($index,$this->ARR_VALUES)){
        // return only properties that table has in DB
        return $this->ARR_VALUES[$index];  
    }else{
        return false;
    }
}

public function __isset($index){    
    return in_array($index,$this->ARR_NAMES);
}

public function __toString(): string{
    $str = implode(', ',$this->ARR_NAMES);
    return "Smart Object: #{$this->_ID} ($str)";
}

public function build_from_fetch($fetch): void{
    foreach($this->ARR_NAMES as $key){
        $this->ARR_VALUES[$key] = $fetch[$key];
    }
    if($id = $this->ARR_VALUES['id']){
        $this->_ID = $id;
        $this->_VALID = true;
    }
}

public function export($params=[]){
   
   $export_all_fields = true;

    if(count($params)){
        
        if(isset($params['actual']) && $params['actual']==true){
            // Request from DB all values before export
            // if any value = 'now()' 
            if($this->valid() && array_search('now()',$this->ARR_VALUES)){
                $this->check_and_fill();
            }
        }
        if(isset($params['except']) && is_array($params['except']) && count($params['except'])>0 ){
            $export_all_fields = false;
            $part_of_array = [];
            foreach ($this->ARR_VALUES as $field_name => $value) {
                if(!in_array($field_name, $params['except'])){
                   $part_of_array[$field_name] = $value; 
                }
            }            
        }
    }

    return $export_all_fields ? $this->ARR_VALUES : $part_of_array;
}  


public function import($smart_object): void{
    $this->ARR_VALUES = array_merge($this->ARR_VALUES, $smart_object->export()); 
} 
      
/*

  PRIVATE

*/

// Построение запросов в БД;
private function create_query($action){   

    switch ($action){

        case 'insert':
            $nms  = $this->ARR_NAMES; 
            if(!$c=count($nms))return false; 
            $q = "INSERT INTO {$this->TABLE_NAME} VALUES(";
            for($i=0;$i<$c;$i++){
                $value = $this->ARR_VALUES[$nms[$i]];
                $value = $this->correct_value($value,$nms[$i]);
                $q.="$value";
                $q.=$i<$c-1?", ":"";
            }
            $q.=")";
            return $q;  
        break;

        case 'update':
        $nms  = $this->ARR_UPDATE;
        if(!$c=count($nms)) return false;        
        $q = "UPDATE {$this->TABLE_NAME} SET  ";
        for($i=0;$i<$c;$i++){
            $value = $this->ARR_VALUES[$nms[$i]];
            $value = $this->correct_value($value,$nms[$i]); 
            $q.="{$nms[$i]} = $value";
            $q.=$i<$c-1?", ":"";
        }
        $q.=" WHERE id={$this->_ID}";
        return $q;
        break;

    }
}

// объединяет types в группы
private function get_type_from_param($param){

    $type = 's'; // default use quotes 
    $str = strtoupper((string) $param['Type']);

    if( str_contains($str, 'INT')    
        // also TINYINT/SMALLINT/MEDIUMINT/BIGINT
        || str_contains($str, 'DECIMAL')
        || str_contains($str, 'FLOAT')
        || str_contains($str, 'DOUBLE')
        || str_contains($str, 'REAL')
        || str_contains($str, 'BIT')
        || str_contains($str, 'BOOLEAN')
        || str_contains($str, 'SERIAL')){
        $type = 'd';        
    }else if(
        str_contains($str, 'DATE') 
        // also DATETIME/TIMESTAMP
        || str_contains($str, 'TIME') 
        || str_contains($str, 'YEAR') 
        ){
        $type = 't';
    }else if(
        str_contains($str, 'CHAR') 
        || str_contains($str, 'TEXT')
        // also TINYTEXT/MEDIUMTEXT/LONGTEXT
        || str_contains($str, 'BINARY')
        // also VARBINARY
        || str_contains($str, 'BLOB')
        // also TINYBLOB/MEDIUMBLOB/LONGBLOB
        || str_contains($str, 'SET')
        ||str_contains($str, 'ENUM')
        ){
        $type = 's';
    }
    return $type;

}

// Корректирует значение для вставки в запрос в БД,
// в зависимости от типа поля таблицы,
// заключает или не заключает $value в кавычки,
// подставляет значения по умолчанию;
// На вход принимает Значение и имя поля.
private function correct_value($v,$nm){

    $param = $this->ARR_FIELDS[$nm];        
    $type = $this->get_type_from_param($param);        
    
    $auto_increment_flag = str_contains(strtoupper((string) $param['Extra']), 'AUTO_INCREMENT');
    $not_null_flag = $param['Null']=='NO'?true:false;
    $default = $param['Default'];

    // если время
    if($type=='t'){    
        if($v!=='now()'){
            if(!$v){
                $v = !$not_null_flag?'null':'now()'; 
            }else{
                $v = "'$v'";
            }
        }
    }

    // если строка
    if($type=='s'){
        if($v){
            $v="'$v'";
        }else{
            if($default){
                $v = "'$default'";
            }else{
                $v = !$not_null_flag?'null':"''";    
            }
        }
    }     

    // если число
    // only UNSIGNED in this version
    if($type=='d'){
        if($auto_increment_flag){
            $v = 'null';   
        }else{
            if(!$v){
               if($v!==0){
                    if($default==''){
                        $v = !$not_null_flag?'null':0;
                    }else{
                        $v = $default;
                    }
               }
            }
        }
    }

    return $v;
}

} // end of class (Smart_object)

class Table_structure {
    // Массив с описанием структуры всеx таблиц
    // к которым обращался class Smart_object
    static private $ARR_TABLES = [];

    static public function get($table_name){

        // Если таблица уже запрашивалась, берем данные из кэша
        if(isset(self::$ARR_TABLES[$table_name])){
            return self::$ARR_TABLES[$table_name];
        }else{
            // Получаем список полей таблицы      
            $res = SQL::query("DESCRIBE {$table_name}");
            if($res){
                $arr = $res->fetch_all(MYSQLI_ASSOC);
                self::$ARR_TABLES[$table_name] = $arr;
                return self::$ARR_TABLES[$table_name];        
            }else{
                return false;
            }
        }
    }
}

?>