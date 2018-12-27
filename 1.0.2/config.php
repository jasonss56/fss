<?php
$fssConfig=array(
    //base
    'PATH_ROOT'   => dirname(__FILE__),//程序根目录 一般不用修改
    'PATH_UPLOAD' => dirname(__FILE__).'/upload',//文件上传目录，绝对路径读写权限
    'SITE_URL'    => 'http://'.$_SERVER['HTTP_HOST'].'',//网站访问路径
    'SITE_NAME'   => 'FSS 有加密功能的文件分享服务',//网站标题
    //Log Debug
    'LOG_OPEN'    => 1,//日志功能 0关闭 1打开
    'LOG_PATH'    => dirname(__FILE__).'/log',//日志路径,绝对路径读写权限
    'DEBUG'       => 1,//调试模式 0关闭 1打开
    //File
    'FILE_RETAIN_DAYS' => '5',//文件保留天数 0:永久
    //sign
    'API_KEYID'   => 'IDFKFcVBCLdiefsA',//文件操作签名keyid(公钥16位) 建议修改
    'API_SECRET'  => 'NsfeO854sdtsd851rtakd8227asdf12e',//文件操作签名secret(私钥32位) 建议修改
);
?>
