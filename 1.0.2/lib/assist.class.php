<?php
class assist{
	
	/*
	 * 数据指针
	 * @param $array 原始数组
	 * @param $poinr 指针键值 例: result.ret.msg
	*/
	public static function pointer($array,$poinr){
		if(empty($poinr)){
			return $array;
		}
		foreach(explode('.',$poinr) as $pkey){
			$array=$array[$pkey];
		}
		if(!isset($array)){
			return false;
		}
		return $array;
	}
	
	/*设置消息*/
	private static $msgcon=array();
	public static function set_msg($msgval,$msgkey='msg'){
		self::$msgcon[$msgkey]=$msgval;
		return self::$msgcon;
	}
    public static function set_msgno($msgval,$msgkey='msgno'){
        self::$msgcon[$msgkey]=$msgval;
        return self::$msgcon;
    }
	/*获取消息*/
	public static function get_msg($poinr='msg'){
        $ret=self::pointer(self::$msgcon,$poinr);
        //self::$msgcon[$poinr]='';
        return $ret;
	}
    public static function get_msgno($poinr='msgno'){
        $ret=self::pointer(self::$msgcon,$poinr);
        //self::$msgcon[$poinr]='';
        return $ret;
    }
	
	/*写入本地日志*/
    public static function in_log($val,$logfile='log.txt',$mode='a+'){
        if(configr::config('LOG_OPEN')!=1){
            return true;
        }
        //assist::show_log('    INLOG : '.$val);
        $logpath  =configr::config('LOG_PATH');
        $logfilenm=substr($logfile,0,strpos($logfile,'.'));
        if(empty($mode)){
            $mode='a+';
        }
        if(!$handle=fopen($logpath.'/'.$logfile,$mode)){
            assist::set_msg("无法打开log文件");
            return false;
        }
        //写入
        $val=date('Y-m-d H:i:s')." ".$val."\r\n";
        fwrite($handle,$val);
        fclose($handle);

        //文件过大处理
        $logsize=filesize($logpath.'/'.$logfile);
        $logsize=$logsize/1024;
        if($logsize>(2*1024)){
            rename($logpath.'/'.$logfile,$logpath.'/'.$logfilenm.'_'.date('Ymd').'_'.rand(1000,9999).'.txt');
        }
        return true;
    }

    /*获取IP地址*/
    public static function getIp($a_vare){
        $ip=false;
        $emsg=''.$a_vare['g_http_client_ip'].'/'.$a_vare['g_http_x_forwarded_for'].'/'.$a_vare['g_remote_addr'];

        //可伪造
        if(!empty($a_vare['g_http_client_ip'])){
            if(strlen($a_vare['g_http_client_ip'])>200){
                assist::set_msg('getIp http_client_ip too long. param['.$emsg.']');
                return false;
            }
            $ip=$a_vare['g_http_client_ip'];
        }
        //特殊g_http_x_forwarded_for 2607:8700:101:c464::, 66.249.84.16
        if(!empty($a_vare['g_http_x_forwarded_for'])){
            if(strlen($a_vare['g_http_x_forwarded_for'])>200){
                assist::set_msg('getIp http_x_forwarded_for too long. param['.$emsg.']');
                return false;
            }
            $HTTP_X_FORWARDED_FOR=$a_vare['g_http_x_forwarded_for'];
            $HTTP_X_FORWARDED_FOR=str_replace(" ","",$HTTP_X_FORWARDED_FOR);
            $ips=explode (',',$HTTP_X_FORWARDED_FOR);
            if($ip){
                array_unshift($ips,$ip);
                $ip=false;
            }
            //for($i=0; $i<count($ips); $i++){
            for($i=(count($ips)-1); $i>=0; $i--){//倒序 取代理的最后一个IP
                if(!filter_var($ips[$i],FILTER_VALIDATE_IP,FILTER_FLAG_IPV4)){
                    continue;
                }
                if(!$ipint=sprintf("%u",ip2long($ips[$i]))){
                    assist::set_msg('getIp sprintf false. param['.$emsg.']');
                    return false;
                }
                if($ipint>=167772160 && $ipint<=184549375){//10.0.0.0-10.255.255.255
                    continue;
                }elseif($ipint>=2886729728 && $ipint<=2887778303){//172.16.0.0-172.31.255.255
                    continue;
                }elseif($ipint>=3232235520 && $ipint<=3232301055){//192.168.0.0-192.168.255.255
                    continue;
                }elseif($ipint==2130706433){//127.0.0.1
                    continue;
                }else{
                    $ip=$ips[$i];
                    break;
                }
            }
        }
        if(!$ip){
            $ip=$a_vare['g_remote_addr'];//不可伪造
        }
        if(!filter_var($ip,FILTER_VALIDATE_IP,FILTER_FLAG_IPV4)){
            assist::set_msg('getIp filter_var false. param['.$emsg.']');
            return false;
        }
        return $ip;
    }

    /*网址跳转*/
    public static function tourl($url){
        if(empty($url)){
            return false;
        }
        header("Content-Type: text/html; charset=utf-8");
        header("HTTP/1.1 301 Moved Permanently");
        header("Location: ".$url."");
        return true;
    }

    public static function microtime_float(){
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

    public static function hashSignature($parameters,$accessKeySecret) {
        ksort ( $parameters );
        $canonicalizedQueryString = '';
        foreach ( $parameters as $key => $value ) {
            $canonicalizedQueryString .= '&' . self::percentEncode ( $key ) . '=' . self::percentEncode ( $value );
        }
        $stringToSign = 'GET&%2F&' . self::percentencode ( substr ( $canonicalizedQueryString, 1 ) );
        $signature = base64_encode ( hash_hmac ( 'sha1', $stringToSign, $accessKeySecret . '&', true ) );
        return base64_encode($signature);
    }
    private static function percentEncode($string) {
        $string = urlencode ( $string );
        $string = preg_replace ( '/\+/', '%20', $string );
        $string = preg_replace ( '/\*/', '%2A', $string );
        $string = preg_replace ( '/%7E/', '~', $string );
        return $string;
    }

}
?>
