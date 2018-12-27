<?php
class configr{
    public static $a_config=array();
    public static function config($key=''){
        if(count(self::$a_config)>0){
            if(!empty($key)){
                if(empty(self::$a_config[$key])){
                    return false;
                }
                return self::$a_config[$key];
            }
            return self::$a_config;
        }

        $fssConfigBase=array(
            'PATH_ROOT'        => dirname(__FILE__),//程序根目录 一般不用修改
            'PATH_UPLOAD'      => dirname(__FILE__).'/upload',//文件上传目录，绝对路径读写权限
            'SITE_URL'         => '',//网站访问路径
            'SITE_NAME'        => 'FSS',//网站标题
            'LOG_OPEN'         => 1,//日志功能 0关闭 1打开
            'LOG_PATH'         => dirname(__FILE__).'/log',//日志路径,绝对路径读写权限
            'DEBUG'            => 1,//调试模式 0关闭 1打开
            'FILE_RETAIN_DAYS' => '5',//文件保留天数 0:永久
            'API_KEYID'        => 'IDFKFcVBCLdiefsA',//文件操作签名keyid(公钥16位) 建议修改
            'API_SECRET'       => 'NsfeOFICKksdfiekfakd8227asdf12e1',//文件操作签名secret(私钥32位) 建议修改
            'FSS_VERSION'      => '1.0.2',
        );
        $fssConfig='';
        include(dirname(dirname(__FILE__)).'/config.php');
        foreach($fssConfig as $key=>$val){
            $fssConfigBase[$key]=$val;
        }
        self::$a_config=$fssConfigBase;
        if(!empty($key)){
            if(empty(self::$a_config[$key])){
                return false;
            }
            return self::$a_config[$key];
        }
        return self::$a_config;
    }
}
?>