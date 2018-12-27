<?php
class upload{
	public static function file($a_vare){
        try{
            fssr::autoDelete();//自动删除

            $maxFilesize = self::isMaxFileSize();
            $constimeStr = assist::microtime_float();
            if(!$a_vare['S_FILES']['files']){
                throw new Exception('Please select the file.');
            }
            $a_upfile      = $a_vare['S_FILES']['files'];
            //print_r($a_upfile);
            $upFileName    = $a_upfile['name'];
            $upFileSuffix  = substr($upFileName,strripos($upFileName,'.')+1);
            $upFileType    = $a_upfile['type'];
            $upFileTmpname = $a_upfile['tmp_name'];
            $upFileError   = $a_upfile['error'];
            $upFileSize    = $a_upfile['size'];
            $upFileName    = mb_substr(substr($upFileName,0,strripos($upFileName,'.')),0,20).'.'.$upFileSuffix;

            $a_def_type=array(
                'application/zip'          => 'zip',
                'application/octet-stream' => 'rar',
                'application/pdf'          => 'pdf',
                'application/vnd.ms-excel' => 'xls',
                'image/jpeg'               => 'jpg',
                'image/png'                => 'png',
                'text/plain'               => 'txt',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'       => 'xlsx',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            );
            if($upFileError>0){
                throw new Exception('上传失败.['.$upFileError.']');
            }
            /*
            if(!in_array($upFileType,array_keys($a_def_type))){
                throw new Exception('暂不支持该类型文件.['.$upFileType.']');
            }*/
            //max 50M
            if($upFileSize>$maxFilesize){
                throw new Exception('文件不得大于'.($maxFilesize/1024/1024).'M. ['.($upFileSize/1024/1024).']');
            }

            $filePath = configr::config('PATH_UPLOAD');
            //$fileKey  = substr(md5(file_get_contents($upFileTmpname)),8,16);
            $fileKey  = substr(md5(rand(0,99999).assist::microtime_float()),8,16);
            $fileName = $fileKey.".".$upFileSuffix;
            $confName = $fileKey.'.conf';
            //write file
            if(!file_exists($filePath.'/'.$fileName)){
                //move file
                if(!move_uploaded_file($upFileTmpname,$filePath.'/'.$fileName)){
                    throw new Exception('file upload fail #move');
                }
            }
            //write config file
            $fileConf=array(
                'fileKey'      => $fileKey,
                'fileName'     => $fileName,//MD5文件名
                'fileType'     => $upFileType,//文件类型
                'fileSize'     => $upFileSize,//文件大小
                'downFileName' => $upFileName,//真实文件名
                'insdate'      => date('Y-m-d H:i:s'),//配置文件新增\文件上传时间
                'upddate'      => date('Y-m-d H:i:s'),//配置文件更新时间
                'downMaxQty'   => 0,//最大下载次数 0不限制
                'downUseQty'   => 0,//已经下载次数
                'downPasswd'   => '',//下载文件密码
                'usid'         => $a_vare['g_usid'],//用户唯一SID
                'mk'           => '1',//1:正常 -1:删除
            );
            if(!fssr::fileSet($fileKey,$fileConf)){
                throw new Exception(assist::get_msg());
            }
            $ret['success'] = '1';
            $ret['copyUrl'] = configr::config('SITE_URL').'/?fk=CP'.$fileKey;
            $ret['msg']     = '';
        }catch (Exception $ex){
            $ret['success'] = '0';
            $ret['copyUrl'] = '';
            $ret['msg']     = $ex->getMessage();
        }
        $ret['constime']=round((assist::microtime_float()-$constimeStr),4);
assist::in_log($a_vare['g_ip'].'|upload|'.json_encode($ret).'|'.$a_vare['g_http_user_agent']);
        header('Content-type: application/json; charset=utf-8;');
        die(json_encode($ret));
    }

    private static function isMaxFileSize(){
        $maxFilesize=(49*1024*1024);
        $i_upload_max_filesize = ini_get('upload_max_filesize');
        $i_post_max_size       = ini_get('post_max_size');
        if(!$i_upload_max_filesize || !$i_post_max_size){
            return $maxFilesize;
        }
        if(strstr($i_upload_max_filesize,'M')){
            $i_upload_max_filesize=substr($i_upload_max_filesize,0,-1)*1024*1024;
        }
        if(strstr($i_post_max_size,'M')){
            $i_post_max_size=substr($i_post_max_size,0,-1)*1024*1024;
        }

        if($i_upload_max_filesize==$i_post_max_size || $i_post_max_size>$i_upload_max_filesize){
            $maxFilesize=$i_upload_max_filesize;
        }else{
            $maxFilesize=$i_post_max_size;
        }
        return $maxFilesize;
    }

    /*获取最大支持SIZE*/
    public static function ajaGetMaxFilesize($a_vare){
        $ret['success']     = '1';
        $ret['maxfilesize'] = self::isMaxFileSize();
        return displayr::ajax($ret);
    }

    /*设置文件下载密码*/
    public static function ajaSetPasswd($a_vare){
        $usid    = accountr::isUsid();
        $fileKey = $a_vare['fk'];
        $passwd  = $a_vare['passwd'];

        try{
            if(!empty($passwd) && !(strlen($passwd)>3 && strlen($passwd)<=12)){
                throw new Exception('密码不合规则[长度大于3小于12位]');
            }
            if(!empty($passwd) && !preg_match("/^[a-zA-Z0-9_\.]+$/",$passwd)){
                throw new Exception('密码不合规则[支持字母数字下划线]');
            }
            if(empty($fileKey)){
                throw new Exception('非法请求[fk]');
            }
            if(!$a_finfo=fssr::isFile($fileKey)){
                throw new Exception(assist::get_msg());
            }
            if($a_finfo['usid']!=$usid){
                throw new Exception('非法请求[usid]');
            }
            if(!fssr::fileSet($fileKey,array('downPasswd'=>$passwd))){
                throw new Exception(assist::get_msg());
            }
            $ret['success'] = '1';
            $ret['msg']     = '密码设置成功！';
        }catch(Exception $ex){
            $ret['success'] = '-1';
            $ret['msg']     = $ex->getMessage();
        }
        return displayr::ajax($ret);
    }

    /*判断密码是否正确*/
    public static function ajaIsPasswd($a_vare){
        //$usid    = accountr::isUsid();
        $fileKey = $a_vare['fk'];
        $passwd  = $a_vare['passwd'];

        try{
            if(!empty($passwd) && !(strlen($passwd)>3 && strlen($passwd)<=12)){
                throw new Exception('密码不合规则[长度大于3小于12位]');
            }
            if(!empty($passwd) && !preg_match("/^[a-zA-Z0-9_\.]+$/",$passwd)){
                throw new Exception('密码不合规则[支持字母数字下划线]');
            }
            if(empty($fileKey)){
                throw new Exception('非法请求[fk]');
            }
            if(!$a_finfo=fssr::isFile($fileKey)){
                throw new Exception(assist::get_msg());
            }
            if($a_finfo['downPasswd']!=$passwd){
                throw new Exception('密码不正确');
            }
            $a_sign['fk']      = 'DL'.$fileKey;
            $a_sign['spw']     = md5($passwd);
            $a_sign['t']       = time();
            $a_sign['api_keyid'] = configr::config('API_KEYID');
            $sign              = assist::hashSignature($a_sign,configr::config('API_SECRET'));
            $ret['success'] = '1';
            $ret['msg']     = '验证成功，下载链接已经生成！';
            $ret['result']['downloadUrl']  = configr::config('SITE_URL').'/?fk='.$a_sign['fk'].'&spw='.$a_sign['spw'].'&t='.$a_sign['t'].'&api_keyid='.$a_sign['api_keyid'].'&sign='.$sign;
        }catch(Exception $ex){
            $ret['success'] = '-1';
            $ret['msg']     = $ex->getMessage();
        }
        return displayr::ajax($ret);
    }

    /*删除文件*/
    public static function ajaDelFile($a_vare){
        $usid    = accountr::isUsid();
        $fileKey = $a_vare['fk'];

        try{
            if(empty($fileKey)){
                throw new Exception('非法请求[fk]');
            }
            if(!$a_finfo=fssr::isFile($fileKey)){
                throw new Exception(assist::get_msg());
            }
            if($a_finfo['usid']!=$usid){
                throw new Exception('非法请求[usid]');
            }
            if(!fssr::fileDel($fileKey)){
                throw new Exception(assist::get_msg());
            }
            $ret['success'] = '1';
            $ret['msg']     = '删除成功！';
        }catch(Exception $ex){
            $ret['success'] = '-1';
            $ret['msg']     = $ex->getMessage();
        }
        return displayr::ajax($ret);
    }






}
?>
