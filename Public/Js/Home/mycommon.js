$(function(){
    $('.left_lis:last').css({ 'border-bottom':'0px'});
    //获取左侧导航高度
    var body_cons=$('.body_cons').height();//alert(cons_right);
    $('.cons_left').css({ 'height':body_cons});
    //页码点击增加当前状态
    $('.pages span').click(function(){
        $(this).addClass('pages_cur').siblings('span').removeClass('pages_cur');
    })
    //ul奇偶行背景颜色
    $('.my_meets li:even').css({ 'background-color':' #F8F8F8'})


//左侧导航栏点击
        var arr = new Array();
        arr[0] = "__PUBLIC__/Images/Home/mess2.png";
        arr[1] = "__PUBLIC__/Images/Home/home2.png";
        arr[2] = "__PUBLIC__/Images/Home/duan2.png";
        arr[3] = "__PUBLIC__/Images/Home/bao2.png";
        arr[4] = "__PUBLIC__/Images/Home/usr2.png";
        var arr1 = new Array();
        arr1[0] = "__PUBLIC__/Images/Home/mess1.png";
        arr1[1] = "__PUBLIC__/Images/Home/home1.png";
        arr1[2] = "__PUBLIC__/Images/Home/duan1.png";
        arr1[3] = "__PUBLIC__/Images/Home/bao1.png";
        arr1[4] = "__PUBLIC__/Images/Home/usr1.png";
        var arr2 = new Array();
        arr2[0] = "__PUBLIC__/Images/Home/shou.png";
        arr2[1] = "__PUBLIC__/Images/Home/shou.png";
        arr2[2] = "__PUBLIC__/Images/Home/shou.png";
        arr2[3] = "__PUBLIC__/Images/Home/shou.png";
        arr2[4] = "__PUBLIC__/Images/Home/shou.png";
        var arr3 = new Array();
        arr3[0] = "__PUBLIC__/Images/Home/zhan.png";
        arr3[1] = "__PUBLIC__/Images/Home/zhan.png";
        arr3[2] = "__PUBLIC__/Images/Home/zhan.png";
        arr3[3] = "__PUBLIC__/Images/Home/zhan.png";
        arr3[4] = "__PUBLIC__/Images/Home/zhan.png";
        $('.left_lis').each(function(i){
            $(this).click(function(){
                $(this).append('<span class="left_limgs"></span>');
                if ($(this).siblings('.left_lis').find('.left_limgs')==true) {
                    $(this).find('.left_limgs').hide();
                }else{
                    $(this).append('<span class="left_limgs"></span>');
                    $(this).siblings('.left_lis').find('.left_limgs').hide()
                }
                var attrs=arr[i];
                var attrs1=arr1[i];
                var attrs2=arr2[i];
                var attrs3=arr3[i];
                if ($(this).find('.li_cons').hasClass('li_conscur')) {
                    $(this).find('.li_cons').removeClass('li_conscur');
                } else {
                    $(this).find('.li_cons').addClass('li_conscur');
                }

                if ($(this).find('.bg_wid').attr('src')==attrs1&&$(this).find('.shou').attr('src')==attrs2) {
                    $(this).find('.bg_wid').attr('src',attrs);
                    $(this).find('.shou').attr('src',attrs3);
                    if ($(this).find('.child_uls')){
                        $('.child_uls').show();
                        $(this).siblings('.left_lis').find('.child_uls').hide();
                    }
                } else if($(this).find('.bg_wid').attr('src')==attrs&&$(this).find('.shou').attr('src')==attrs3){
                    $(this).find('.bg_wid').attr('src',attrs1);
                    $(this).find('.shou').attr('src',attrs2);
                    if ($(this).find('.child_uls')){
                        $('.child_uls').hide();
                    }
                }
            })
        })
        $('.child_uls li').click(function(event){
            event.stopPropagation();
            $(this).parents('.child_uls').show();
            $(this).find('a').addClass('curs').siblings('li').find('a').removeClass('curs');
        })


    
        /*4.18我发起的会议收件箱发件箱操作*/
        //tab切换
        $('.emails_bot li').click(function(){
        	
            $(this).addClass('email_licur').siblings('li').removeClass('email_licur');
            $(this).find('.writes2').show();
            $(this).find('.writes1').hide();
            $(this).siblings('li').find('.writes1').show();
            $(this).siblings('li').find('.writes2').hide();
            var sos=$(this).index();
            $('.email_shows').eq(sos).show().siblings('.email_shows').hide();
        })
        //点击全选
        $('.all_value').click(function(){           
            if ($(this).parents('.email_shows').find('.new_values').prop('checked')==true) {
                $(this).parents('.email_shows').find('.new_values').prop('checked',false)
            }else{
               $(this).parents('.email_shows').find('.new_values').prop('checked',true); 
            }
        })
        //批量删除信息
        $('.chose_delete').click(function(){
            $(this).parents('.oper_mess').siblings('.email_uls').find('.new_values:checked').parents('li').remove();
        })
//      $('.email_delete').click(function(){
//          $(this).parents('li').remove();
//      })






        //$('.teach_all')
        //点击全选
        $('.teach_all').click(function(){   //alert(1);
            $(this).parents('.teach_h4').siblings('.gunim').find('.teach_check').prop('checked',true);         
            // if ($(this).parents('.teach_h4').siblings('.teach_uls').find('.teach_check').prop('checked')==true) {
            //     $(this).parents('.teach_h4').siblings('.teach_uls').find('.teach_check').prop('checked',false)
            // }else{
            //    $(this).parents('.teach_h4').siblings('.teach_uls').find('.teach_check').prop('checked',true); 
            // }
        })
        $('.student_all').click(function(){   
            $(this).parents('.teach_h4').siblings('.student_uls').find('.student_check').prop('checked',true);        
            // if ($(this).parents('.teach_h4').siblings('.student_uls').find('.student_check').prop('checked')==true) {
            //     $(this).parents('.teach_h4').siblings('.student_uls').find('.student_check').prop('checked',false)
            // }else{
            //    $(this).parents('.teach_h4').siblings('.student_uls').find('.student_check').prop('checked',true); 
            // }
        })
        //改变奇偶行颜色
        $('.teach_uls li:odd').css({ 'background-color':'#F6F6F6'});
        $('.student_uls li:odd').css({ 'background-color':'#F6F6F6'});

})
   
)
   
