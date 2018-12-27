<?php
class fssr{

	public static function info($fileKey){
        try{
            $filePath = configr::config('PATH_UPLOAD');
            $confName = $fileKey.'.conf';
            if(!file_exists($filePath.'/'.$confName)){
                throw new Exception('file not exists. #conf 1');
            }
            if(!$confJson=file_get_contents($filePath.'/'.$confName)){
                throw new Exception('file not exists. #conf 2');
            }
            $a_conf=json_decode($confJson,true);

            $ret['fileName']     = $a_conf['fileName'];
            $ret['fileType']     = $a_conf['fileType'];
            $ret['fileSize']     = $a_conf['fileSize'];
            $ret['filePath']     = $filePath.'/'.$ret['fileName'];
            $ret['downFileName'] = $a_conf['downFileName'];
            return $ret;
        }catch(Exception $ex){
            assist::set_msg($ex->getMessage());
            return false;
        }
    }

    /*自动删除超过7天的文件*/
    public static function autoDelete($exceedDays=7){
        try{
            $pathUpload=configr::config('PATH_UPLOAD');
            if(!file_exists($pathUpload)){
                assist::in_log('PATH_UPLOAD not exists. #autoDelete','error.log');
                return true;
            }
            /*
            $dirLists=scandir($pathUpload);
            print_r($dirLists);
            foreach($dirLists as $filenm){
                if(in_array($filenm,array('.','..'))){
                    continue;
                }
            }
            */
            $fileList=glob($pathUpload.'/*.conf');
            if(!is_array($fileList)){
                return true;
            }
            foreach($fileList as $fnameC){
                if(!$fmtime=filemtime($fnameC)){
                    continue;
                }
                if((time()-$fmtime)>(86400*$exceedDays)){
                    //读取conf
                    $confJson   = file_get_contents($fnameC);
                    $a_fileinfo = json_decode($confJson,true);
                    $fnameF     = $pathUpload.'/'.$a_fileinfo['fileName'];
                    //echo $fnameF."\r\n";
                    //echo $fnameC."\r\n";
                    @unlink($fnameC);
                    @unlink($fnameF);
                    if(configr::config('DEBUG')==1){
                        assist::in_log('[autoDelete] delete '.$fnameF.' success.','debug.log');
                    }
                }
                clearstatcache();
            }
        }catch(Exception $ex){

        }
        return true;
    }
}
?>
