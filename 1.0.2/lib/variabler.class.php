<?php
class variabler{
    public static function set($key,$val){
        $mkey='G_CUSTOM';
        $GLOBALS[$mkey][$key]=$val;
        return true;
    }
    public static function mset($a_kv){
        $mkey='G_CUSTOM';
        if(!is_array($a_kv)){
            return false;
        }
        if(isset($GLOBALS[$mkey]) && is_array($GLOBALS[$mkey])){
            //$GLOBALS[$mkey]=array_merge_recursive($GLOBALS[$mkey],$a_kv);
            foreach($a_kv as $key=>$val){
                $GLOBALS[$mkey][$key]=$val;
            }
        }else{
            $GLOBALS[$mkey]=$a_kv;
        }
        return true;
    }

    public static function get($key='',$poinr=''){
        $mkey='G_CUSTOM';
        if(empty($key)){
            if(isset($GLOBALS[$mkey])){
                return assist::pointer($GLOBALS[$mkey],$poinr);
            }
            return false;
        }
        if(isset($GLOBALS[$mkey][$key])){
            return assist::pointer($GLOBALS[$mkey][$key],$poinr);
        }
        return false;
    }

    public static function del($key=''){
        $mkey='G_CUSTOM';
        if(empty($key)){
            unset($GLOBALS[$mkey]);
            return true;
        }
        if(isset($GLOBALS[$mkey][$key])){
            unset($GLOBALS[$mkey][$key]);
        }
        return true;
    }
}
?>
