<?php
class router{
	
	public static function route($a_vare){
        $app=empty($a_vare['app'])?'index':$a_vare['app'];
        if(strstr($app,".")){
            $a_app      = explode(".",$app);
            $app_class  = $a_app[0];
            $app_method = $a_app[1];
        }else{
            $app_class  = $app;
            $app_method = 'show';
        }
        if(!in_array($app_class,array('test','index','upload','download'))){
            return displayr::abnormal('Not defined app');
        }
        if(!method_exists($app_class,$app_method)){
            return displayr::abnormal('Not defined act');
        }
        variabler::mset($a_vare);
        //print_r($a_vare);

        //环境检测
        if(configr::config('DEBUG')=='1'){
            if(!fssr::checkSystemEnvironment()){
                $html="Error:<br>";
                $html.=str_replace(",","<br>",assist::get_msg());
                return displayr::abnormal($html);
            }
        }
		$result='';
        eval('$result='.$app_class.'::'.$app_method.'($a_vare);');
		return $result;
	}
}
?>