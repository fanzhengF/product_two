$(function(){
    $('#Product_country_id').change(function(){
        if($(this).val() == "中国") {
            $('#city_div').html($('#hidden_city').html());
            $('#city_div').show();
        }else{
            $('#Product_city_id').val(0);
            $('#city_div').hide();
        }
    });
    /*数组去重复元素*/
    Array.prototype.unique=function(){
        var newArray=[];
        var provisionalTable = {};
        for (var i = 0, item; (item= this[i]) != null; i++) {
            if (!provisionalTable[item]) {
                newArray.push(item);
                provisionalTable[item] = true;
            }
        }
        return newArray;
    }
    var cate_id = "0";
    var productId = "";
    window.pobj = {};
    //重新设置颜色
//    $(":checkbox").prop("checked",0);
    //表单验证提示
    pobj.ealert = function(id, msg) {
        $('#'+id).addClass('padderror');
        $('#pErrorBodyEnd').html(msg);
    }
    pobj.eclear = function() {
        $('.padderror').removeClass('padderror');
        $('#pErrorBodyEnd').html('');
    }
    //添加规格
    var _sa = 0,chks = 0;
    pobj.standAdd = function() {
        _sa++;
        var html = '<div id="sabody_'+_sa+'" style="float:left;"><input type="checkbox" checked class="checkbox_stand input_check1" value="" id="sacheck_'+_sa+'" onclick="pobj.click('+_sa+')"/><input type="text" class="w90 f99 text_stand" id="satext_'+_sa+'" name="Product[stand][]"/></div>';
        $('#stand_body').append(html);
        
    }
    pobj.chkstand = function(){
        var stand_arr = new Array();
        $('#stand_body div').each(function(k,v){
            var stand_name = $(v).find("[type=text]").val();
            if(stand_name != '')
                stand_arr.push(stand_name);
        });
        stand_arr = stand_arr.unique();
        if(stand_arr.length > 23) {
            alert('抱歉,您的规格已满24个,不能再添加!您可以修改之前的规格名称');
            chks = 1;
            return false;
        }
        if(stand_arr.length != $('#stand_body div').length){
            alert('请确认您输入的规格：规格名称不能为空且不能出现重复的规格名称');
            //清空填写错误的DOM
            $('#stand_body div:last').find("[type=text]").val('');
            chks = 1;
            return false;
        }else{
            chks = 0;
        }
    };
    pobj.strCount = function(str){
            var byteLen = 0;
            var strLen  = str.length;
            if(strLen)
            {
                for(var i = 0; i < strLen; i++)
                {
                    if(str.charCodeAt(i)>255)
                        byteLen += 1;
                    else
                        byteLen += 0.5; //0.5不存在精度问题
                }
            }
            return byteLen;
    };
    function autoSetSize(obj){
            var tem = '';
        obj.find("input").each(function(index, dom){
            if(index != 4){
                tem = tem+ dom.value + ',';
            }
        });
        if(/\S[,]$/.test(tem)){
            tem = tem.substring(0,tem.length-1);
        }
        obj.find("input").eq(4)[0].value = tem;
    }
    $('#addStand').click(function(){
        pobj.chkstand();

        if(chks==1){return false;}
        if(chks==0){pobj.standAdd();}
    })
    pobj.click = function(index) {
      
        if (!$('#satext_'+index).val()) {
            $('#sabody_'+index).remove();
            _sa--;
        };
        
        //$(this).parent().remove();
    }


    //选择颜色，自定义颜色名，图片(添加时)
    $('.addCusPic').click(function(){
        var cv = this.value.split(',');
        var id = this.id.replace('color_', '');
        var tr = $('<tr id="ctr_'+id+'"><td style="width:80px"><i class="color" style="background:'+cv[1]+'" id="cco_'+id+'">&nbsp;</i></td><td class="color_s">'
               + '<span style="border:1px solid #000; cursor:pointer;">点此上传</span>'
               + '</td><td><span><input type="text" style="height:18px; line-height:18px; padding:0 2px; color:#999; width:90px" value="'+cv[0]+'" name="cusCName['+id+']" class="text_color" id="cusCText_'+id+'"/><input type="hidden" name="cusCFile['+id+']" id="cfi_'+id+'"></span></td></tr>');

        if(this.checked && $('#ctr_'+id).html() == null) {
            $('#colorDlBody').show();
            $('#colorBody').append(tr);
        } else {
            try{
                $('#ctr_'+id).remove();
                if($('#colorBody').html().length == 0) {
                    $('#colorDlBody').hide();
                }
            }catch(e){}
        }

        var box = tr.find('.color_s').get(0);
        var color = tr.find('.color').get(0);
        var input = $('#cfi_'+id).get(0);

        var index;
        //选择颜色上传图片
        new qq.FileUploaderBasic({
            allowedExtensions: ['jpg','gif','jpeg','png'],
            button: box,
            multiple: false,
            action:     '/Admin/Product/doUpload',
            inputName: 'Filedata',
            forceMultipart: true, //用$_FILES
            messages: {
                typeError: '不允许上传的文件类型！',
                sizeError: '文件大小不能超过4M！',
                minSizeError: '文件大小不能小于0！',
                emptyError: '请文件为空，请重新选择！',
                noFilesError: '没有选择要上传的文件！',
                onLeave: '正在上传文件，离开此页将取消上传！'
            },
            showMessage: function(message){
                layer.msg(message,{icon:2});

            },
            onSubmit: function(id, fileName){
                index = layer.load();
            },
            onComplete: function(id, fileName, result){
                layer.close(index);
                if(result.status == '1'){
                    color.style.background = 'url('+result.data.info.src_all_path+')';
                    color.style.backgroundSize = '100% 100%';
                    input.value = result.data.info.src;
                } else {
                    layer.msg(result.data.msg,{icon:2});
                    
                }
            }
        });
    });




    //  商品录入费率计算
    $(".js_sell_price").blur(function(){
         var $rates = $(this).find(".js_rates"); 
         var val1 = $(this).parents("tr").find(".js_purchase_price").val();
        var val2 = $(this).parents("tr").find(".js_sell_price").val();
        if( val1.length && /^[0-9]+(\.[0-9]{1,2})?$/.test(val1) && val2.length && /^[0-9]+(\.[0-9]{1,2})?$/.test(val2)){
            $rates.val(Number(($(this).val()-$(this).parent("td").prev().find("input").val())/$(this).val()).toFixed(3));
            $(this).parent().next().find("span").html(Number(($(this).val()-$(this).parent("td").prev().find("input").val())/$(this).val()).toFixed(3));
            $(".js_rates").val(Number(($(this).val()-$(this).parent("td").prev().find("input").val())/$(this).val()).toFixed(3));

        }else{
            $rates.val("");
            $(this).parent().next().find("span").html("");
            $(".js_rates").val("");

            //alert('请输入不超过两位小数的采购价格和销售价格');
        }
    });
/*    $(".js_rebate").blur(function(){
        var val1 = $(this).parents("tr").find(".js_purchase_price").val();
        var val2 = $(this).parents("tr").find(".js_sell_price").val();
        if( val1.length && /^[0-9]+(.[0-9]{2})?$/.test(val1) && val2.length && /^[0-9]+(.[0-9]{2})?$/.test(val2)){
            if($(this).val() >= $(this).parents("tr").find(".js_rates").val()){
                alert("返佣率应该小于服务费费率");
             }
        }else{
            alert("请输入不超过两位小数的采购价格和销售价格");
        }
    });*/

    //  商品录入提交
    $('#js_Goods_luru_form').submit(function(e){
        
        e.preventDefault();
        var $name = $("#js_name"),
            $unit = $("#js_unit"),
            $shop_city = $(".js_shop_city"),
            $unit = $("#js_unit"),
            $body = $(".js_body");
        function validate_shop_city(){
            var pass = true;
            $(".js_shop_city").each(function(){
                var $purchase_price = $(this).find(".js_purchase_price");
                var $sell_price = $(this).find(".js_sell_price");
                var $rates = $(this).find(".js_rates");
                var $rebate = $(this).find(".js_rebate");
                var flag = true;
                var val = $purchase_price.val() , val2 = $sell_price.val(), val3 = $sell_price.val(), val4;
                if(val == "" || !/^[0-9]+(\.[0-9]{1,2})?$/.test(val) ){
                    $("#pErrorBodyEnd").html("请输入不超过两位小数的采购价格");
                    pass = false;
                    flag = false;
                    return false;
                }
                if(flag && (val2 == "" || !/^[0-9]+(\.[0-9]{1,2})?$/.test(val2)) ){
                    $("#pErrorBodyEnd").html("请输入不超过两位小数的销售价格");
                    pass = false;
                    return false;
                }
				/*
                if(flag && (parseInt(val2) < parseInt(val)) ){
                    $("#pErrorBodyEnd").html("销售价格必须大于采购价格");
                    pass = false;
                    return false;
                }*/
                if(flag && (parseInt(val2) > parseInt($("#js_market_price").val())) ){
                    $("#pErrorBodyEnd").html("销售价必须小于等于市场价");
                    pass = false;
                    return false;
                }
                
                if(flag){
                    $rates.val(Number((val2-val)/val3).toFixed(3));
                    $(this).find("td").eq(3).find("span").html(Number((val2-val)/val3).toFixed(3));
                }
                if(flag && $rebate.val() == ""){
                    $("#pErrorBodyEnd").html("返佣率不能为空");
                    pass = false;
                    return false;
                }
                if(flag && (!/^[+]?[\d]+(([\.]{1}[\d]+)|([\d]*))$/.test($rebate.val())) ){
                    $("#pErrorBodyEnd").html("返佣率不能为负数");
                    pass = false;
                    return false;
                }
				
//                if( flag && ($rebate.val()>= $rates.val()) ){
//                    $("#pErrorBodyEnd").html("返佣率应该小于服务费费率"+$rates.val());
//                    pass = false;
//                    return false;
//                }else{
                    $(this).find(".js_rebate_h").val($rebate.val());
//                }
            });

            return pass;

        }
		function validate_shop_city1(){
            var pass = true;
            $(".js_shop_city1").each(function(){
				var $othername = $(this).find(".js_othername");
                
                var flag = true;
				var othername = $othername.val().replace(/(^\s*)|(\s*$)/g, "");
                var js_reg=/<script.*?>?/i;
				if(flag && (othername.length > 30 || js_reg.test(othername))){
					$("#pErrorBodyEnd").html("请输入30个字以内非特殊标记的俗称");
					pass = false;
					return false;
				}
            });

            return pass;

        }

        if( !(/[\u4e00-\u9fa5]/.test($name.val())) || $name.val().length == 0 || $name.val().length > 30){
            $("#pErrorBodyEnd").html("请输入30个字以内汉字的商品名称");
            return false;
        }else if($unit.val() == ""){
            $("#pErrorBodyEnd").html("请选择商品单位");
            return false;
        }
        
        if($("#js_market_price").val() == ""){
            $("#pErrorBodyEnd").html("商品市场价不能为空");
            return false;
        }
        if($("#js_market_price").val() <= 0 ){
            $("#pErrorBodyEnd").html("商品市场价必须大于0");
            return false;
        }
        
        var a = validate_shop_city();
        if(!a){
            return a;
        }
		
		var a1 = validate_shop_city1();
        if(!a1){
            return a1;
        }

        var len = $('#js_imgBox .js_img').length;
        if (!len) {
             $("#pErrorBodyEnd").html('请上传图片！');
            return false;
        }else if(len>6){
             $("#pErrorBodyEnd").html('最多上传6张图片，现有'+len+'张图片');
            return false;
        };
        
        var p_rates = $("#js_package_rates").val();
        if(p_rates == ""){
            $("#pErrorBodyEnd").html("包装率不能为空");
            return false;
        }
        if( p_rates &&　!/^[1-9]*[1-9][0-9]*$/.test(p_rates) ){
            $("#pErrorBodyEnd").html("包装率请输入大于0的正整数");
            return false;
        }
        
        if($(".js_body").val() == ""){
            $("#pErrorBodyEnd").html("商品描述不能为空");
            return false;
        }

        var ID_ACTION = "";
        if( is_edit==1 ){
            var getValueParam = function(key){
                var url = window.location.href,
                para = url.split('?')[1],
                arrparam = [],
                val = undefined;
                if( para && para.length){
                    arrparam = para.split('&');
                    for( var i = 0; i<arrparam.length; i++ ){
                        if(arrparam[i].split("=")[0] == key){
                            val = arrparam[i].split("=")[1];
                            break;
                        }
                    }
                }
                return val;
            };
            ID_ACTION = "?id=" + getValueParam("id");
        }
               
        var postData = $(this).serializeArray();
        var formURL = $(this).attr("action");

        var layerIndex = layer.load(0, {     
            shade: [0.4,'#000'] 
        });
        $.ajax(
        {
            url : formURL+ID_ACTION,
            type: "POST",
            data : postData,
            dataType : "json",
            success:function(data, textStatus, jqXHR) 
            {
                layer.close(layerIndex)
                if(textStatus == 'success'){
                    if(data.status ){
                        alert(data.data.msg);
                        window.location.href = data.data.url;
                    }else{
                    	alert(data.data.msg);
                    }
                }
                //data: return data from server
            },
            error: function(jqXHR, textStatus, errorThrown) 
            {
                layer.close(layerIndex)
                layer.msg('请求超时',{icon: 2})
                   
            }
        });
    });

    $('#js_uploadConfirm').submit(function(e){
        e.preventDefault();
        var len = $('#js_imgBox .js_img').length;
        if (!len) {
             $("#pErrorBodyEnd").html('请上传确认书！');
            return false;
        }else if(len>1){
             $("#pErrorBodyEnd").html('最多上传1张图片，现有'+len+'张图片');
            return false;
        };
        var ID_ACTION = "";
        var postData = $(this).serializeArray();
        var formURL = $(this).attr("action");

        var layerIndex = layer.load(0, {     
            shade: [0.4,'#000'] 
        });
        $.ajax(
        {
            url : formURL+ID_ACTION,
            type: "POST",
            data : postData,
            dataType : "json",
            success:function(data, textStatus, jqXHR) 
            {
                layer.close(layerIndex)
                if(textStatus == 'success'){
                    if(data.status ){
                        alert(data.data.msg);
                        window.location.href = data.data.url;
                    }else{
                    	alert(data.data.msg);
                    }
                }
                //data: return data from server
            },
            error: function(jqXHR, textStatus, errorThrown) 
            {
                layer.close(layerIndex)
                layer.msg('请求超时',{icon: 2})
                   
            }
        });
    });

	var calc = {
		Subtr:function(arg1,arg2){
			var r1,r2,m,n;
			try{r1=arg1.toString().split(".")[1].length}catch(e){r1=0}
			try{r2=arg2.toString().split(".")[1].length}catch(e){r2=0}
			m=Math.pow(10,Math.max(r1,r2));
			n=(r1>=r2)?r1:r2;
			return ((arg1*m-arg2*m)/m).toFixed(n);
		},
		Add:function(arg1,arg2){
			var r1,r2,m;
			try{r1=arg1.toString().split(".")[1].length}catch(e){r1=0}
			try{r2=arg2.toString().split(".")[1].length}catch(e){r2=0}
			m=Math.pow(10,Math.max(r1,r2))
			return (arg1*m+arg2*m)/m
		},
		Acc:function(arg1,arg2){
			var t1=0,t2=0,r1,r2;
			try{t1=arg1.toString().split(".")[1].length}catch(e){}
			try{t2=arg2.toString().split(".")[1].length}catch(e){}
			with(Math){
				r1=Number(arg1.toString().replace(".",""))
				r2=Number(arg2.toString().replace(".",""))
				return (r1/r2)*pow(10,t2-t1);
			}
		},
		Mul:function(arg1,arg2)
		{
			var m=0,s1=arg1.toString(),s2=arg2.toString();
			try{m+=s1.split(".")[1].length}catch(e){}
			try{m+=s2.split(".")[1].length}catch(e){}
			return Number(s1.replace(".",""))*Number(s2.replace(".",""))/Math.pow(10,m)
		}
	};


    //  平台订单信息 更改数量
    $("#js_order_info_form input").blur(function(){
        var goods_id = $(this).attr("goods_id");
        var shop_id = $(this).attr("shop_id");
        var value = parseInt($(this).val());
        var that = this, total =0;
        if( /^[1-9][0-9]*$/.test($(this).val()) ){
            
            $.post("/Admin/Order/authBuyNum",{"goods_id":goods_id,"amount":value},function(){},"json").done(function(datas){
                if(datas.status){
					
					// 验证成功
					if(datas.status == 1){
						$(that).closest('tr').find('.js_num_ordertip').html('').removeClass('bg_order_error');
					}
					
                    $.post("/Admin/Order/updateCart",{"shop_id":shop_id,"goods_id":goods_id,"amount":value},function(data){
                        if(!data.status){
                            alert(data.data);
                        }else{
                            var price = parseFloat($(that).parents("tr").find("td").eq(5).html());
                            $(that).parents("tr").find("td").eq(8).html(calc.Mul(price,value));
                            $(".l-order-detail").each(function(){
                                var total_s = 0;
                                $(this).find("tr").each(function(index){
                                    if( index != 0 ){
                                        total_s = total_s + parseFloat($(this).find("td").eq(8).html());
                                    }
                                });
                                $(this).find(".l-order-total span").html(total_s);
                                total = calc.Add(total,total_s);
                            });
                            
                            $(".l-total span").html(total);
                        }
                    },"json");
                }else{
					if(datas.info){
						alert(datas.info);
					}else{
						alert(datas.data);
					}
                }
            }); 
        }
    });
    //  平台订单信息 删除
    $("#js_order_info_form a.js_del").click(function(){
        var that = this;
        var goods_id = $(this).attr("goods_id");
        var shop_id = $(this).attr("shop_id");

        tanchuang(function (){
            $.post("/Admin/Order/delCartGood",{"shop_id":shop_id,"goods_id":goods_id},function(data){
                if(data.status){
                    alert(data.msg);
                    window.location.href=window.location.href;
                }
            },"json");
        });
    });
// 平台订单信息  提交订单
    $("#js_order_info_form").submit(function(e){


        var pass = true;
        e.preventDefault();
		
		(function(oForm){
			// 	购买数量(基数整数倍) 与 基数的 验证
			oForm.find('.js_num_order').each(function(){
				
				
				var oTip = $(this).closest('tr').find('.js_num_ordertip');
				
				oTip.html('').removeClass('bg_order_error');
				
				//购买数量
				var buyNum = parseInt(this.value);
				
				// 基数
				var baseNum = parseInt($(this).closest('tr').find('.js_num_base').text());
				
				// 判断是否为数字
				if(!isNaN(buyNum) && !isNaN(baseNum)){
					if(buyNum%baseNum!=0)
					{
						pass = false;
						oTip.text('购买数量不符！').addClass('bg_order_error');
					}	
				}
				else
				{
					pass = false;	
				}
			});
			
			// 备注限制50
			oForm.find('.js_remark').each(function(){
				
				var oTip = $(this).parent().find('.js_remark_tip');
				
				var str = $(this).val();
				
				var len = str.replace(/[\u4e00-\u9fa5]/g,'aa').length;
				
				if(len > 50*2)
				{
					oTip.show();	
					pass = false;
					
				}
				else
				{
					oTip.hide();	
				}
				
			});
		
		})($(this));
		
		
        $("#js_order_info_form input.js_num_order").each(function(){
            var value = parseInt($(this).val());
            if( /^[1-9][0-9]*$/.test($(this).val()) ){

                $(this).css("border","none");
            }else{
                $(this).css("border","1px solid #f00");
                alert("购买数量请输入大于0的正整数");
                pass = false;
                return false;
            }
        });
        $(".js_select_cangku").each(function(){
            if(this.value == ""){
                pass = false;
                alert("请选择仓库");
                return false;
            }
        });
		var aSkuid = [];
		$(".skuid").each(function(){
            aSkuid.push($(this).text());			
        });
		var res = unique(aSkuid);
		if(res.length != aSkuid.length){			
			//alert(res.length);
			pass = false;
			alert("购物车中有重复的商品");
			return false;
		}

        if(pass){
            var postData = $(this).serializeArray();
            var formURL = $(this).attr("action");
            $.ajax(
            {
                url : formURL,
                type: "POST",
                data : postData,
                dataType : "json",
                success:function(data, textStatus, jqXHR) 
                {
                    if(textStatus == 'success'){
                        if(data.status ){
                            alert(data.data.msg);
                            window.location.href = data.data.url;

                        }else{
							alert(data.data);
						}
                    }
                    //data: return data from server
                },
                error: function(jqXHR, textStatus, errorThrown) 
                {
                    //if fails      
                }
            });

        }
    });
	function unique(arr) {
		var result = [], hash = {};
		for (var i = 0, elem; (elem = arr[i]) != null; i++) {
			if (!hash[elem]) {
				result.push(elem);
				hash[elem] = true;
			}
		}
		return result;
	}
     // 产品录入提交
    $('#js_Product_luru_form').submit(function(e){

        e.preventDefault();
        autoSetSize($(".Product_real_size"));
        autoSetSize($(".Product_pack_size"));
        pobj.eclear();
        var ID_ACTION = "";
        if( isArray(cate_brand) ){
            var getValueParam = function(key){
                var url = window.location.href,
                para = url.split('?')[1],
                arrparam = [],
                val = undefined;
                if( para && para.length){
                    arrparam = para.split('&');
                    for( var i = 0; i<arrparam.length; i++ ){
                        if(arrparam[i].split("=")[0] == key){
                            val = arrparam[i].split("=")[1];
                            break;
                        }
                    }
                }
                return val;
            };
            ID_ACTION = "?id=" + getValueParam("id");
        }
        //判断品牌分类
        // 经营类别
        if(!$(".js_select_cate input").val().length){
            pobj.ealert('',"请选择一个经营类别");
                return false;
        }
        if(!$("#brand_name").val()){
            alert($("#brand_name").val());
            pobj.ealert('',"请选择一个品牌");
                return false;
        }
        //产品名称
        if($('#Product_name').val() == '' || pobj.strCount($.trim($('#Product_name').val())) > 30) {
            pobj.ealert('Product_name', '产品名称没有填写或大于30个汉字');return false;
        }
        //产品型号
        if($('#Product_type').val() == '') {
            pobj.ealert('Product_type', '型号没有填写');return false;
        } else if( pobj.strCount($('#Product_type').val()) > 10) {
            pobj.ealert('Product_type', '型号字数超出范围');return false;
        }
        
        //自定义颜色名称不能为空
                    var tc = $('.text_color');
            for(var t=0; t<tc.length; t++) {
                if(!$.trim(tc[t].value)) {
                    pobj.ealert(tc[t].id, '自定义颜色名称不能为空');return false;
                } else if(pobj.strCount(tc[t].value) > 8) {
                    pobj.ealert(tc[t].id, '自定义颜色名称不能超过8个中文字');return false;
                }
            }
        
        var textnum = 1;

        //验证规格不能重复
        var stand_arr = new Array();
        $('#stand_body div').each(function(k,v){
            var stand_name = $(v).find("[type=text]").val();
            if(stand_name != '')
                stand_arr.push(stand_name);
        });
        stand_arr = stand_arr.unique();
        if(stand_arr.length != $('#stand_body div').length){
            alert('请确认您输入的规格：规格名称不能为空且不能出现重复的规格名称');
            //清空填写错误的DOM
            $('#stand_body div:last').find("[type=text]").val('');
            return false;
        }

        
        var _textStand = false;//规格为空 不让提交
        $('.text_stand').each(function(){
            if(pobj.strCount($(this).val()) > 12){
                textnum = 2;
                pobj.ealert('text_stand', '规格名称不能超过12个中文字');return false;
            }
             //规格为空 不让提交
            if (!$.trim($(this).val())) {
                layer.alert('规格名称不能为空');
                _textStand =  true;
            };
        });
        if(textnum == 2 || _textStand) {
            return false;
        }

        var postData = $(this).serializeArray();
        var formURL = $(this).attr("action");
        var layerIndex = layer.load(0, {     
            shade: [0.4,'#000'] 
        });
        $.ajax(
        {
            url : formURL+ID_ACTION,
            type: "POST",
            data : postData,
            dataType : "json",
            success:function(data, textStatus, jqXHR) 
            {
                layer.close(layerIndex);
                if(textStatus == 'success'){
                    if(data.status ){
                        if(data.data.result){
                            layer.alert(data.data.msg,function (index){
                                window.location.href = data.data.url;
                            });
                            
                        }

                    }
                }
                //data: return data from server
            },
            error: function(jqXHR, textStatus, errorThrown) 
            {
                layer.close(layerIndex);
                layer.msg('请求超时',{icon: 2})

                //if fails      
            }
        });
    });
 
    //弹窗            --迷你版,正式版有待开发 by hezhe 
    function tanchuang(success,faild,message){
        var $box = $('.js_sc');
        var $close = $box.find('.close');
        var $no = $box.find('.sh_n');
        var $yes = $box.find('.sh_y');
        var $mask = $('.shadow');
        var $title = $box.find('.title');

        $title.text(message||'确认删除？');
        show();
        bind();

        function successFn(){
            unbind();
            hide();
            success && success();
        }
        function faildFn(){
            unbind();
            hide();
            faild && faild();
        }

        function bind(){
            
            $close.bind('click',faildFn);
            $no.bind('click',faildFn);
            $yes.bind('click',successFn);
        }
        function unbind(){
            $close.unbind('click',faildFn);
            $no.unbind('click',faildFn);
            $yes.unbind('click',successFn);
        }
        function hide(){
            $box.css('display','none');
            $mask.css('display','none');
        }
        function show(){
            $box.css('display','block');
            $mask.css('display','block');
        }

    }
});