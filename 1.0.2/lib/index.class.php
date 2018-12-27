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
                if(!$a_fss=fssr::isFile($fileKey)){
                    throw new Exception(assist::get_msg());
                }
                //print_r($a_fss);exit;
                $fileName     = $a_fss['fileName'];
                $fileType     = $a_fss['fileType'];
                $fileSize     = $a_fss['fileSize'];
                $filePath     = $a_fss['filePath'];
                $downFileName = $a_fss['downFileName'];
                $downUseQty   = $a_fss['downUseQty'];
                $downPasswd   = $a_fss['downPasswd'];
            }
            if($action=='copy'){
                assist::in_log($a_vare['g_ip'].'|copy|'.$fileKey.'|'.$downFileName.'|'.round($fileSize/1024/1024,2).'MB|'.$a_vare['g_http_user_agent']);
                $shareUrl = configr::config('SITE_URL').'/?fk=SH'.$fileKey;
                $frdays   = configr::config('FILE_RETAIN_DAYS');

                if($frdays){
                    $attTitle='<br><span>该文件链接将在'.$frdays.'天后失效</span>';
                }
                $html='
                <script type="text/javascript" src="'.configr::config('SITE_URL').'/style/clipboard/2.0.0/clipboard.min.js"></script>
                <div class="fs-item" style="margin-top:40px;">
                    <span style="font-size:24px;">复制链接分享您的文件</span>
                    '.$attTitle.'
                    <br>
                    <br><span style="color:#999999;">手机端也可以</span>
                    <br><img src="https://sapi.k780.com/?app=qr.get&&size=4&margin=1&data='.urlencode(''.$shareUrl.'').'">
                </div>
                <div class="fs-item">
                    <span class="item-floatLeft" style="font-size:16px;">复制链接分享您的文件 <i style="color:#99999; font-size:14px;">'.$downFileName.' ('.round($fileSize/1024/1024,2).'MB)</i></span>
                    <div class="form-group">
                        <div class="input-group">
                            <input type="text" class="form-control" id="shareUrl" value="'.$shareUrl.'">
                            <span class="input-group-btn">
                                <button class="btn btn-default" style="width:120px;" type="button" id="copyDownloadUrl" data-clipboard-target="#shareUrl">复制到剪切板</button>
                            </span>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="input-group" style="width:230px;">
                            <input type="password" class="form-control" id="CP_passwd" value="" placeholder="给文件设个密码">
                            <span class="input-group-btn">
                                <button class="btn btn-primary" type="button" id="CP_butSetPasswd">提交</button>
                            </span>
                        </div>
                    </div>

                    <input type="hidden" id="fk" value="'.$fileKey.'">
                    <span class="item-floatLeft"><a id="CP_delFile" href="javascript:void(0)">删除这个文件</a></span>


                </div>

                <div class="fs-item">
                    <a href="'.configr::config('SITE_URL').'">还要上传</a>
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
                </script>';
            }
            elseif($action=='share'){
                assist::in_log($a_vare['g_ip'].'|share|'.$fileKey.'|'.$downFileName.'|'.round($fileSize/1024/1024,2).'MB|'.$a_vare['g_http_user_agent']);
                //生成签名
                unset($a_sign);
                $a_sign['fk']        = 'DL'.$fileKey;
                $a_sign['spw']       = '';
                $a_sign['t']         = time();
                $a_sign['api_keyid'] = configr::config('API_KEYID');
                $sign                = assist::hashSignature($a_sign,configr::config('API_SECRET'));
                $shareUrl     = configr::config('SITE_URL').'/?fk=SH'.$fileKey;
                $downloadUrl  = configr::config('SITE_URL').'/?fk='.$a_sign['fk'].'&spw=&t='.$a_sign['t'].'&api_keyid='.$a_sign['api_keyid'].'&sign='.$sign;
                //$showPB       = false;

                //是否有密码
                if(!empty($downPasswd)){
                    $passItemDis = 'block';
                    $downItemDis = 'none';
                }else{
                    $passItemDis = 'none';
                    $downItemDis = 'block';
                }

                $html='
                <div class="fs-item" style="margin-top:40px;">
                    <span style="font-size:24px;">下载 '.$downFileName.' ('.round($fileSize/1024/1024,2).'MB)</span>
                    <br><span>对方通过FSS传送文件给您，这是一个加密的链接，自动失效功能确保文件不会在网络上无限期停留。</span>
                </div>
                <div class="fs-item" id="SH_passItem" style="width:300px; display:'.$passItemDis.';">
                    <span class="item-floatLeft" style="">输入密码立即下载</span>
                    <div class="form-group">
                        <div class="input-group">
                            <input type="password" class="form-control" id="SH_passwd" name="pw" value="">
                            <span class="input-group-btn">
                                <button class="btn btn-success" id="SH_isPasswd" type="button"><span class="glyphicon glyphicon-download-alt"></span> 立即下载</button>
                            </span>
                        </div>
                        <input type="hidden" id="fk" value="'.$fileKey.'">
                    </div>
                </div>
                <div class="fs-item" id="SH_downItem" style="width:300px; display:'.$downItemDis.';">
                    <a class="btn btn-success btn-lg" target="_blank" id="SH_downloadUrl" href="'.$downloadUrl.'"><span class="glyphicon glyphicon-download-alt"></span> 立即下载</a>
                    <br><span style="color:#999999;">('.$downUseQty.'次下载)</span>
                </div>
                <div class="fs-item">
                    <span style="color:#999999;">手机端也可以</span>
                    <br><img src="https://sapi.k780.com/?app=qr.get&&size=4&margin=1&data='.urlencode(''.$shareUrl.'').'">
                </div>
                <div class="fs-item">
                    <a href="'.configr::config('SITE_URL').'">尝试 FSS</a>
                </div>';
            }
            //
            elseif($action=='download'){
                assist::in_log($a_vare['g_ip'].'|download|'.$fileKey.'|'.$downFileName.'|'.round($fileSize/1024/1024,2).'MB|'.$a_vare['g_http_user_agent']);
                //验证签名
                $a_sign['fk']        = 'DL'.$fileKey;
                $a_sign['spw']       = $a_vare['spw'];
                $a_sign['t']         = $a_vare['t'];
                $a_sign['api_keyid'] = configr::config('API_KEYID');
                $sign                = assist::hashSignature($a_sign,configr::config('API_SECRET'));
                if($a_vare['sign']!=$sign){
                    //print_r($a_sign);
                    die('Incorrect sign.');
                }
                //验证链接过期
                if((time()-$a_sign['t'])>600){
                    die('Link expired.');
                }
                //passwd
                if(!empty($downPasswd) && $a_vare['spw']!=md5($downPasswd)){
                    die('Incorrect password.');
                }
                //get
                if(!fssr::fileSet($fileKey,array('downUseQty'=>'+1'))){
                    throw new Exception('System abnormality');
                }
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
                <div class="fs-item" id="IX_header" style="margin-top:40px;">
                    <span style="font-size:24px;">有加密的文件分享服务</span>
                    <br><span>通过安全、加密的链接传送文件，到期自动删除</span>
                </div>

                <div class="fs-item-uploadbox" id="uploadButton" style="margin-top:40px;">
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

                <div class="fs-item" id="IX_qrcode" style="margin-top:40px;">
                    <span style="color:#999999;">手机端也可以</span>
                    <br><img src="https://sapi.k780.com/?app=qr.get&&size=4&margin=1&data='.urlencode(''.configr::config('SITE_URL').'').'">
                </div>
                <script>
                    $(document).ready(function(){
                        $.getJSON("index.php?app=upload.ajaGetMaxFilesize",function(result){
                            $("#maxfilesize").val(result.maxfilesize);
                        });
                    });
                </script>';
            }
        }catch(Exception $ex){
            $html='
             <div class="fs-item" style="margin-top:40px;">
                <span style="font-size:20px;"><span class="glyphicon glyphicon-info-sign"> </span> <span>'.$ex->getMessage().'</span>
                <br><i style="font-size:10px; color:#c0c0c0;">'.$fileKey.'</i>
             </div>
             <div class="fs-item">
                <a class="btn btn-primary" href="'.configr::config('SITE_URL').'">我要上传文件</a>
             </div>';
        }

        $html='<div id="remind" class="remind alert alert-danger">...</div>'.$html;
        $a_rep['BODY_HTML']  = $html;
        $a_rep['SITE_TITLE'] = configr::config('SITE_NAME');
        return displayr::index($a_vare,$a_rep);
    }

}
?>
