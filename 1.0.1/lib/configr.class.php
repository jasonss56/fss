<?php
class configr{
    public static $arr_config=array();

    public static function config($keys=''){
        $ret=array(
            'PATH_ROOT'   => dirname(dirname(__FILE__)),//程序根目录 一般不用修改
            'PATH_UPLOAD' => dirname(dirname(__FILE__)).'/upload',//文件上传目录，绝对路径需，需读写权限
            'SITE_URL'    => 'http://'.$_SERVER['HTTP_HOST'].'',//网站访问路径
            'SITE_NAME'   => 'FSS 有加密功能的文件分享服务',//网站标题
            'LOG_OPEN'    => 1,//日志功能 0关闭 1打开 (日志路径PATH_ROOT/log)
            'DEBUG'       => 1,//调试模式 0关闭 1打开
        );
        if(!empty($keys)){
            $result=$ret[$keys];
        }else{
            $result=$ret;
        }
        self::$arr_config=$result;
        return self::$arr_config;
    }
}
?>