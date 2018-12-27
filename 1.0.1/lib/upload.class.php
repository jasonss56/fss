<?php
class upload{

    //{"files":[{"name":"2.jpg","size":56469,"type":"image\/jpeg","url":"http:\/\/172.16.20.32\/fileshare\/test\/files\/2.jpg","thumbnailUrl":"http:\/\/172.16.20.32\/fileshare\/test\/files\/thumbnail\/2.jpg","deleteUrl":"http:\/\/172.16.20.32\/fileshare\/test\/?file=2.jpg","deleteType":"DELETE"}]}
	public static function file($a_vare){
        try{
            //执行自动删除
            fssr::autoDelete(7);

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
            $fileKey  = substr(md5(file_get_contents($upFileTmpname)),8,16);
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
                'fileName'     => $fileName,
                'fileType'     => $upFileType,
                'fileSize'     => $upFileSize,
                'downFileName' => $upFileName,
            );
            if(!file_put_contents($filePath.'/'.$confName,json_encode($fileConf))){
                throw new Exception('file upload fail #conf');
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

    public static function getMaxFilesize($a_vare){
        $ret['success']     = '1';
        $ret['maxfilesize'] = self::isMaxFileSize();
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
}
?>
