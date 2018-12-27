$(function () {
    //
    $.getJSON("index.php?app=upload.getMaxFilesize",function(result){
        $('#maxfilesize').val(result.maxfilesize);
    });

    'use strict';
    $('#fileupload').fileupload({
        autoUpload: true,
        url: '?app=upload.file',
        dataType: 'json',
        change: function(e, data) {
            if(data.files.length > 1){
                $("#uploadMsg").show();
                $("#uploadButton").show();
                $("#uploadBar").hide();
                $("#uploadMsg").html('Max 1 files are allowed');
                return false;
            }
        },
        drop: function(e, data) {
            if(data.files.length > 1){
                $("#uploadMsg").show();
                $("#uploadButton").show();
                $("#uploadBar").hide();
                $("#uploadMsg").html('Max 1 files are allowed');
                return false;
            }
        },
        done: function (e, data) {
            if(data.result.success!='1'){
                $("#uploadMsg").show();
                $("#uploadButton").show();
                $("#uploadBar").hide();
                $("#uploadMsg").html('Error: '+data.result.msg);
            }else{
                location.href=data.result.copyUrl;
            }
        },
        progressall: function (e, data) {
            var progress = parseInt(data.loaded / data.total * 100, 10);
            $('#progress .progress-bar').css(
                'width',
                progress + '%'
            );
        },
        add:function  (e, data){//判断文件类型 var acceptFileTypes = /\/(pdf|xml)$/i;
            /*
            var acceptFileTypes = /^zip|rar|gif|jpe?g|png|doc|docx|xls|xlsx|pdf|txt$/i;
            var name = data.originalFiles[0]["name"];
            var index = name.lastIndexOf(".")+1;
            var fileType = name.substring(index,name.length);
            if(!acceptFileTypes.test(fileType)){
                $("#uploadMsg").show();
                $("#uploadButton").show();
                $("#uploadBar").hide();
                $("#uploadMsg").html('上传文件类型不被支持！');
                //alert("上传文件类型不被支持！");
                return ;
            }
            */
            var size = data.originalFiles[0]["size"];
            //if(size > (50*1024*1024)){
            if(size > $("#maxfilesize").val()){
                $("#uploadMsg").show();
                $("#uploadButton").show();
                $("#uploadBar").hide();
                $("#uploadMsg").html('上传文件超过最大限制');
                //alert("上传文件超过50M限制！");
                return ;
            }
            data.submit();
            $("#uploadMsg").hide();
            $('#uploadButton').hide();
            $('#uploadBar').show();
        }
    }).prop('disabled', !$.support.fileInput)
        .parent().addClass($.support.fileInput ? undefined : 'disabled');

    //拖拽
    var oFileSpan = $("#fileSpan");					//选择文件框
    //拖拽外部文件，进入目标元素触发
    oFileSpan.on("dragenter",function(){
        //$(this).text("可以释放鼠标了！").css("background","#ccc");
    });
    //拖拽外部文件，进入目标、离开目标之间，连续触发
    oFileSpan.on("dragover",function(){
        //return false;
    });
    //拖拽外部文件，离开目标元素触发
    oFileSpan.on("dragleave",function(){
        //$(this).text("或者将文件拖到此处").css("background","none");
    });
    //拖拽外部文件，在目标元素上释放鼠标触发
    oFileSpan.on("drop",function(ev){
        $("#uploadMsg").hide();
        $('#uploadButton').hide();
        $('#uploadBar').show();
    });
});