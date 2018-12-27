<?php
class displayr{
	
	/*异常*/
    public static function abnormal($msg){
        header('HTTP/1.1 404 Not Found');
        $html=$msg;
        die($html);
    }

    /**/
    public static function ajax($a_ret){
        header('Content-type: application/json; charset=utf-8;');
        die(json_encode($a_ret));
    }

    public static function index($a_vare,$a_replace=array()){
        header("Cache-Control: no-cache, must-revalidate");
        header("Pragma: no-cache");
        header("Content-Security-Policy: block-all-mixed-content;");
        $html='<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="{SITE_URL}/style/css/index.css" type="text/css" rel="stylesheet"/>
    <link href="{SITE_URL}/style/bootstrap/3.3.4/css/bootstrap.min.css" type="text/css" rel="stylesheet"/>
    <link href="{SITE_URL}/style/jquery.fileupload/css/jquery.fileupload.css" rel="stylesheet">
    <script type="text/javascript" src="{SITE_URL}/style/jquery/1.11.3/jquery.min.js"></script>
    <script type="text/javascript" src="{SITE_URL}/style/bootstrap/3.3.4/js/bootstrap.min.js"></script>
    <title>{SITE_TITLE}</title>
</head>
<body>
{BODY_HTML}

<script src="{SITE_URL}/style/jquery/1.11.3/jquery.min.js"></script>
<script src="{SITE_URL}/style/jquery.fileupload/js/vendor/jquery.ui.widget.js"></script>
<script src="{SITE_URL}/style/jquery.fileupload/js/jquery.iframe-transport.js"></script>
<script src="{SITE_URL}/style/jquery.fileupload/js/jquery.fileupload.js"></script>
<script src="{SITE_URL}/style/bootstrap/3.3.4/js/bootstrap.min.js"></script>
<script src="{SITE_URL}/style/js/upload.js"></script>

</body>
</html>';
        $html=str_replace("{SITE_URL}",'//'.substr(configr::config('SITE_URL'),strpos(configr::config('SITE_URL'),'://')+3),$html);
        //replace
        if(is_array($a_replace)){
            foreach($a_replace as $key=>$val){
                $html=str_replace("{".$key."}",$val,$html);
            }
        }
        $html=str_replace("{SITE_URL}",configr::config('SITE_URL'),$html);
        die($html);
    }
}
?>