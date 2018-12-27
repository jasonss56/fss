<?php
class fssr{

    /*文件信息*/
	public static function fileInfo($fileKey){
        try{
            $filePath = configr::config('PATH_UPLOAD');
            $fileConf = $filePath.'/'.$fileKey.'.conf';
            if(!file_exists($fileConf)){
                throw new Exception('file not exists. #1');
            }
            if(!$jsonConf=file_get_contents($fileConf)){
                throw new Exception('file not exists. #2');
            }
            $a_conf=json_decode($jsonConf,true);

            $ret['fileKey']      = $fileKey;
            $ret['fileName']     = $a_conf['fileName'];
            $ret['fileType']     = $a_conf['fileType'];
            $ret['fileSize']     = $a_conf['fileSize'];
            $ret['downFileName'] = $a_conf['downFileName'];
            $ret['insdate']      = $a_conf['insdate'];//配置文件新增\文件上传时间
            $ret['upddate']      = $a_conf['upddate'];//配置文件更新时间
            $ret['downMaxQty']   = $a_conf['downMaxQty'];//最大下载次数 0不限制
            $ret['downUseQty']   = $a_conf['downUseQty'];//已经下载次数
            $ret['downPasswd']   = $a_conf['downPasswd'];//下载文件密码
            $ret['usid']         = $a_conf['usid'];//
            $ret['mk']           = $a_conf['mk'];//1:正常 -1:删除
            $ret['filePath']     = $filePath.'/'.$ret['fileName'];

            //触发生成删除标记
            $writeConf=false;
            //大于最大下载次数删除
            if($ret['mk']=='1' && isset($ret['downMaxQty']) && isset($ret['downUseQty']) && $ret['downMaxQty']>0 && $ret['downUseQty']>=$ret['downMaxQty']){
                $ret['mk']='-1';
                $writeConf=true;
            }
            //大于配置最大保留天数
            if($ret['mk']=='1' && configr::config('FILE_RETAIN_DAYS')>0 && (time()-strtotime($ret['upddate']))>(configr::config('FILE_RETAIN_DAYS')*86400)){
                $ret['mk']='-1';
                $writeConf=true;
            }
            if($writeConf){
                if(!self::fileSet($fileKey,array('mk'=>'-1'))){
                    throw new Exception(assist::get_msg());
                }
            }
            return $ret;
        }catch(Exception $ex){
            assist::set_msg($ex->getMessage());
            return false;
        }
    }

    /*同fileInfo*/
    public static function isFile($filekey){
        try{
            if(!$a_finfo=self::fileInfo($filekey)){
                throw new Exception(assist::get_msg());
            }
            if($a_finfo['mk']!='1'){
                if(!self::fileDel($filekey)){
                    throw new Exception(assist::get_msg());
                }
                throw new Exception('file has been deleted.');
            }
            //删除过期
            $exceedDays=configr::config('FILE_RETAIN_DAYS');
            if($exceedDays && $exceedDays>0){
                if(ceil((time()-strtotime($a_finfo['insdate']))/86400)>$exceedDays){
                    if(!self::fileDel($filekey)){
                        throw new Exception(assist::get_msg());
                    }
                }
            }
            return $a_finfo;
        }catch(Exception $ex){
            assist::set_msg($ex->getMessage());
            return false;
        }
    }

    /*文件配置更新*/
    public static function fileSet($fileKey,$a_upd){
        try{
            if(!is_array($a_upd)){
                throw new Exception('param must exists.');
            }
            $filePath = configr::config('PATH_UPLOAD');
            $confName = $fileKey.'.conf';
            //存在时更新 $a_conf
            if(file_exists($filePath.'/'.$confName)){
                if(!$confJson=file_get_contents($filePath.'/'.$confName)){
                    throw new Exception('file not exists. #1');
                }
                $a_conf=json_decode($confJson,true);
            }else{
                $a_conf = array(
                    'fileKey'      => $fileKey,
                    'fileName'     => '',//MD5文件名
                    'fileType'     => '',//文件类型
                    'fileSize'     => '',//文件大小
                    'downFileName' => '',//真实文件名
                    'insdate'      => '',//配置文件新增\文件上传时间
                    'upddate'      => '',//配置文件更新时间
                    'downMaxQty'   => 0,//最大下载次数 0不限制
                    'downUseQty'   => 0,//已经下载次数
                    'downPasswd'   => '',//下载文件密码
                    'usid'         => '',//用户唯一SID
                    'mk'           => '',//1:正常 -1:删除
                );
            }
            foreach($a_upd as $key=>$val){
                if(in_array($key,array_keys($a_conf))){
                    if($key=='downUseQty' && $val=='+1'){
                        $val=((int)$a_conf['downUseQty']+1);
                    }
                    $a_conf[$key]=$val;
                }
            }
            if(!file_put_contents($filePath.'/'.$confName,json_encode($a_conf))){
                throw new Exception('fileUpdate fail');
            }
            return true;
        }catch(Exception $ex){
            assist::set_msg($ex->getMessage());
            return false;
        }
    }

    /*文件删除*/
    public static function fileDel($fileKey){
        if(!$a_finfo=self::fileInfo($fileKey)){
            return true;
        }
        $filePath = configr::config('PATH_UPLOAD');
        @unlink($filePath.'/'.$a_finfo['fileKey'].'.conf');//conf
        @unlink($filePath.'/'.$a_finfo['fileName']);//file
        if(configr::config('DEBUG')==1){
            assist::in_log('[autoDelete] delete '.$a_finfo['fileName'].' success.','debug.log');
        }
        return true;
    }

    /*自动删除超过7天的文件*/
    public static function autoDelete(){
        try{
            $exceedDays=configr::config('FILE_RETAIN_DAYS');
            //0不删除
            if($exceedDays==false || $exceedDays==='0'){
                return true;
            }
            $pathUpload=configr::config('PATH_UPLOAD');
            if(!file_exists($pathUpload)){
                assist::in_log('PATH_UPLOAD not exists. #autoDelete','error.log');
                return true;
            }
            $fileList=glob($pathUpload.'/*.conf');
            if(!is_array($fileList)){
                return true;
            }
            foreach($fileList as $fnameC){
                $filenmConf=substr(substr($fnameC,strrpos($fnameC,'/')+1),0,strpos($fnameC,'.'));
                self::isFile(substr($filenmConf,0,strpos($filenmConf,'.')));
            }
        }catch(Exception $ex){

        }
        return true;
    }

    /*环境检测*/
    public static function checkSystemEnvironment(){
        try{
            $a_fssconfig=configr::config();
            $a_error=array();
            if(!is_writable($a_fssconfig['PATH_UPLOAD'])){
                $a_error[]='PATH_UPLOAD['.$a_fssconfig['PATH_UPLOAD'].']无写入权限';
            }
            if(!is_writable($a_fssconfig['LOG_PATH'])){
                $a_error[]='LOG_PATH['.$a_fssconfig['LOG_PATH'].']无写入权限';
            }
            if(count($a_error)>0){
                throw new Exception();
            }
            return true;
        }catch(Exception $ex){
            assist::set_msg(implode(',',$a_error));
            return false;
        }

    }
}
?>
