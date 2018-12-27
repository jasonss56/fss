<?php
include("global.php");
date_default_timezone_set('PRC');
session_start();
if(configr::config('DEBUG')=='1'){
    error_reporting(E_ALL & ~E_NOTICE);
}else{
    error_reporting(0);
}
if(!get_magic_quotes_gpc()){
    if(isset($_REQUEST)){
        foreach($_REQUEST as $pkey=>$pval){
            if(!is_array($pval)){
                $a_vare[$pkey]=$pval;
            }
        }
    }
    if(isset($_FILES)){
        $a_vare['S_FILES']=$_FILES;
    }
}
$a_vare['g_ip']                     = '0.0.0.0';
$a_vare['g_http_host']              = $_SERVER['HTTP_HOST'];
$a_vare['g_https']                  = $_SERVER['HTTPS'];
$a_vare['g_https']                  = ($a_vare['g_https']=='on')?'1':'0';
$a_vare['g_referer']                = $_SERVER['HTTP_REFERER'];
$a_vare['g_http_client_ip']         = $_SERVER['HTTP_CLIENT_IP'];
$a_vare['g_remote_addr']            = $_SERVER['REMOTE_ADDR'];
$a_vare['g_request_uri']            = $_SERVER['REQUEST_URI'];
$a_vare['g_http_x_forwarded_for']   = $_SERVER['HTTP_X_FORWARDED_FOR'];
$a_vare['g_http_if_modified_since'] = $_SERVER['HTTP_IF_MODIFIED_SINCE'];
$a_vare['g_http_if_none_match']     = $_SERVER['HTTP_IF_NONE_MATCH'];
$a_vare['g_http_user_agent']        = $_SERVER['HTTP_USER_AGENT'];

$a_vare['g_ip']   = assist::getIp($a_vare);
$a_vare['g_usid'] = accountr::isUsid();
router::route($a_vare);
?>
