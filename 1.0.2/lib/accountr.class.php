<?php
class accountr{
	/*自动生成用户唯一ID*/
    public static function isUsid(){
        //不存在时创建
        if(empty($_COOKIE['FSS_C_LA']) || strlen($_COOKIE['C_LA'])!==32){
            $usid=session_id();
            setcookie("FSS_C_LA",md5($usid),time()+(86400*30),'/');
            return $usid;
        }
        return $_COOKIE['FSS_C_LA'];
    }


}
?>
