<?php
class index{
	
	public static function show($a_vare){
        $fk       = $a_vare['fk'];
        $actionNo = substr($fk,0,2);
        $a_def_action=array(
            'CP'=>'copy',//上传成功后显示复制页面
            'SH'=>'share',//分享找开页面
            'DL'=>'download',//下载页面
        );

        try{
            $action   = '';
            $fileKey  = substr($fk,2);
            if(in_array($actionNo,array_keys($a_def_action))){
                $action=$a_def_action[$actionNo];
                if(empty($fileKey)){
                    throw new Exception('非法请求');
                }
                //读取文件
                if(!$a_fss=fssr::info($fileKey)){
                    throw new Exception('文件不存在');
                }
                //print_r($a_fss);exit;
                $fileName     = $a_fss['fileName'];
                $fileType     = $a_fss['fileType'];
                $fileSize     = $a_fss['fileSize'];
                $filePath     = $a_fss['filePath'];
                $downFileName = $a_fss['downFileName'];
            }
            if($action=='copy'){
assist::in_log($a_vare['g_ip'].'|copy|'.$fileKey.'|'.$downFileName.'|'.round($fileSize/1024/1024,2).'MB|'.$a_vare['g_http_user_agent']);
                $shareUrl     = configr::config('SITE_URL').'/?fk=SH'.$fileKey;
                $html='
                <script type="text/javascript" src="'.configr::config('SITE_URL').'/style/clipboard/2.0.0/clipboard.min.js"></script>
                <div class="fs-item" style="margin-top:40px;">
                    <span style="font-size:20px;">该文件链接将在7天后失效</span>
                    <br><br><img src="https://sapi.k780.com/?app=qr.get&&size=4&margin=1&data='.urlencode(''.$shareUrl.'').'">
                </div>
                <div class="fs-item">
                        <span class="item-floatLeft" style="font-size:16px;">复制链接分享您的文件 <i style="color:#99999; font-size:14px;">'.$downFileName.'</i></span>
                        <div class="form-group">
                            <div class="input-group">
                                <input type="text" class="form-control" id="shareUrl" value="'.$shareUrl.'">
                                <span class="input-group-btn">
                                    <button class="btn btn-default" type="button" id="copyDownloadUrl" data-clipboard-target="#shareUrl">复制到剪切板</button>
                                </span>
                            </div>
                        </div>
                    <!--</div>-->
                </div>
                <div class="fs-item">
                    <a class="btn btn-default" href="'.configr::config('SITE_URL').'">继续分享文件</a>
                </div>
                <script>
                    var clipboard = new ClipboardJS(\'#copyDownloadUrl\');
                    //优雅降级:safari 版本号>=10,提示复制成功;否则提示需在文字选中后，手动选择“拷贝”进行复制
                    clipboard.on(\'success\', function(e) {
                        $("#copyDownloadUrl").html(\'<span class="glyphicon glyphicon-ok" style="color:#286090;"> 复制成功</span>\');
                        setTimeout(function(){
                            $("#copyDownloadUrl").html(\'复制到剪切板\');
                        },5000);
                        e.clearSelection();
                    });
                    clipboard.on(\'error\', function(e) {
                        alert(\'请选择“拷贝”进行复制!\')
                    });
                </script>
                ';
            }
            elseif($action=='share'){
assist::in_log($a_vare['g_ip'].'|share|'.$fileKey.'|'.$downFileName.'|'.round($fileSize/1024/1024,2).'MB|'.$a_vare['g_http_user_agent']);
                $shareUrl     = configr::config('SITE_URL').'/?fk=SH'.$fileKey;
                $downloadUrl  = configr::config('SITE_URL').'/?fk=DL'.$fileKey;
                $html='
                <div class="fs-item" style="margin-top:40px;">
                    <span style="font-size:20px;">下载 '.$downFileName.' ('.round($fileSize/1024/1024,2).'MB)</span>
                    <br><span>对方通过FSS传送文件给您，这是一个加密的链接，自动失效功能确保文件不会在网络上无限期停留。</span>
                </div>
                <div class="fs-item">
                    <a class="btn btn-success btn-lg" target="_blank" href="'.$downloadUrl.'"><span class="glyphicon glyphicon-download-alt"></span> 立即下载</a>
                </div>
                <div class="fs-item">
                    手机端也可以<br>
                    <img src="https://sapi.k780.com/?app=qr.get&&size=4&margin=1&data='.urlencode(''.$shareUrl.'').'">
                </div>
                <div class="fs-item">
                    <a href="'.configr::config('SITE_URL').'">尝试 FSS</a>
                </div>';
            }
            //
            elseif($action=='download'){
assist::in_log($a_vare['g_ip'].'|download|'.$fileKey.'|'.$downFileName.'|'.round($fileSize/1024/1024,2).'MB|'.$a_vare['g_http_user_agent']);
                //header('Content-type: '.$fileType.'');
                header('Content-type: application/octet-stream');
                header('Content-Length: '.$fileSize.'');
                header('Content-Disposition: attachment; filename="'.$downFileName.'"');
                readfile($filePath);
                exit();
            }
            //上传控件
            else{
                assist::in_log($a_vare['g_ip'].'|index|'.$a_vare['g_http_user_agent']);

                $html='
                <div class="fs-item" style="margin-top:40px;">
                    <span style="font-size:24px;">有加密的文件分享服务</span>
                    <br><span>通过安全、加密的链接传送文件，到期自动删除</span>
                </div>

                <div class="fs-item-uploadbox" style="margin-top:40px;" id="uploadButton">
                    <div id="uploadMsg" class="alert alert-danger" style="display:none;">...</div>
                    <span style="font-size:18px;">将文件放到此处开始上传</span>
                    <br><i style="font-size:10px; color:#c0c0c0;">为了系统能稳定运行，请尽量将文件控制在50M以内</i>
                    <br>
                    <br>
                    <span class="btn btn-primary btn-lg fileinput-button">
                        <span>选择要上传的文件</span>
                        <input id="fileupload" type="file" name="files" multiple>
                        <input type="hidden" id="maxfilesize" value="10">
                    </span>
                    <br>
                    <br>
                </div>
                <div class="fs-item-uploadbox" id="uploadBar" style="display:none;">
                    <i style="font-size:14px; color:#c0c0c0;">Uploading...</i>
                    <div id="progress" class="progress">
                        <div class="progress-bar progress-bar-success"></div>
                    </div>
                </div>
                <div class="fs-item" style="margin-top:40px;">
                    手机端也可以
                    <br><img src="https://sapi.k780.com/?app=qr.get&&size=4&margin=1&data='.urlencode(''.configr::config('SITE_URL').'').'">
                </div>
                ';
            }
        }catch(Exception $ex){
            $html='
             <div class="fs-item" style="margin-top:40px;">
                <span style="font-size:20px;"><span class="glyphicon glyphicon-info-sign"> </span> <span>'.$ex->getMessage().'</span>
                <br><i style="font-size:10px; color:#c0c0c0;">1.文件超过上传者设定的时效. </i>
                <br><i style="font-size:10px; color:#c0c0c0;">2.文件被上传者删除.</i>
             </div>
             <div class="fs-item">
                <a class="btn btn-primary" href="'.configr::config('SITE_URL').'">我要上传文件</a>
             </div>';
        }

        $a_rep['BODY_HTML']  = $html;
        $a_rep['SITE_TITLE'] = configr::config('SITE_NAME');
        return displayr::index($a_vare,$a_rep);
    }

}
?>
