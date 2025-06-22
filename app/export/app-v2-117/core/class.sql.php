<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class SQL {

    static private $db;    

    static public function connect(){        
        global $CFG;
        if(!self::$db){
            self::$db = new mysqli($CFG->dblocation, $CFG->dbuser, $CFG->dbpasswd, $CFG->dbname);
            if (!self::$db->connect_errno) {
                self::$db->set_charset('utf8');
                // glog('db connect ok');
                return self::$db;
            }else{
                glogError('no db connect, '.self::$db->connect_error); 
                return false; 
            }
        }else{
            return self::$db;
        }
   }  

    static public function get(){                
        return self::$db;
    }

    static public function query($query){
        if(!self::connect()) return false;        
        glog($query);
        $result = self::$db->query($query);
        if($result){            
            return $result;            
        }else{            
            glogError($query.", ".self::$db->error);
            return false;
        }
    }

    static public function update($query){   
        glog($query);     
        $result = self::query($query);
        return $result ? self::$db->affected_rows: false;
    }

    static public function insert($query){
        glog($query);
        $result = self::query($query);
        return $result ? self::$db->insert_id : false;
    }

    static public function delete($query){  
        glog($query);
        $result = self::query($query);
        return $result;
    }

    static public function first($query){
        glog($query);
        $result = self::query($query);
        return  ($result && $result->num_rows) ? $result->fetch_assoc(): false; 
    }
    
    static public function fields($table_name){
        $result = self::query("SELECT * FROM $table_name LIMIT 1");
        return ($result && $result->num_rows) ? $result->fetch_fields() : false;
    }

    static public function disconnect(): void{        
        self::$db && self::$db->close();
    }  



}

//SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'mmenu2' AND TABLE_NAME = 'cafe'
//SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'contracts'

// static private function mysqli_field_name($result, $field_offset){
//     $properties = mysqli_fetch_field_direct($result, $field_offset);
//     return is_object($properties) ? $properties->name : null;
// }

?>