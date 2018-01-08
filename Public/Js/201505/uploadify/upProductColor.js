/**
 * 上传分类图
 */
 phpsession = 123456
var gh = {
	'buttonText'	 : '选择图片',
	'buttonImg'	  	 : '/Public/Js/201505/uploadify/up.jpg',
	'width'			 : 66,
	'height'		 : 24,
	'uploader'       : '/Public/Js/201505/uploadify/uploadify.swf',
	'script'         : '/index.php?r=product/upimgs&PHPSESSID='+phpsession,
	'scriptData'     : {'PHPSESSID':phpsession},
	'cancelImg'      : '/Public/Js/201505/uploadify/cancel.png',
	'folder'         : '/uploads',
	'queueID'        : 'fileQueue',
	'auto'           : true,
	'multi'          : false,
	'fileDesc'		 : '*.png;*.gif;*.jpg',//出现在上传对话框中的文件类型描述
	'fileExt'		 : '*.png;*.gif;*.jpg;', //控制可上传文件的扩展名，启用本项时需同时声明fileDesc
	'sizeLimit'		 : 2048000, //控制上传文件的大小，单位byte
	'simUploadLimit' : 6,//多文件上传时，同时上传文件数目限制
	'fileDataName'	 : 'filedata',
	'onCancel'		 : function(event, queueID, fileObj,  data){
						//$('#uploadify').uploadifyClearQueue();
	},
	'onSelect'	 	 : function(event, queueID, fileObj, data){

	},
	'onComplete'     : function(event, queueID, fileObj, res, data) {
		var r = $.parseJSON(res);
		if(typeof(r.path) == 'undefined'){
			alert('上传图片失败'); return false;
		} else if(r.size > 2000000) {
			alert('上传图片大于2M,请传不大于2M图片'); return false;
		} else if(r.width > 800) {
			alert('上传图片宽度大于800像素'); return false;
		}
		var d = event.target.id.substr(4, event.target.id.length-4);
		$('#cfi_'+d).val(r.path.replace('_s', ''));
		$('#cco_'+d).removeClass('color');
		$('#cco_'+d).html("<img src='"+imgdomain+r.path+"?r="+new Date()+"'>");
	},
	'onError'		: function (a, b, c, d) {
		if (d.status == 404){
			alert('Could not find upload script. Use a path relative to');
		} else if (d.type === "HTTP") {
			alert('网络错误，图片上传失败');
		} else if (d.type ==="File Size") {
			alert('上传图片不能大于2M！');
		} else {
			alert('网络错误，图片上传失败');
		}
	}
};



