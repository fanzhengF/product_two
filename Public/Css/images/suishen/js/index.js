
		
$(function(){
	//随时问律师二级标题
	$("#suiwen").hover(function(){
		$(".suiwen").show();
		$("#suiwen").css("background-color","#ff683e");
		$("#suiwen").css("color","#fff");
		},function(){
			$(".suiwen").hide();
			$("#suiwen").css("background-color","#fff");
		$("#suiwen").css("color","#333");
			}
		)
	$(".suiwen").hover(function(){
		$(".suiwen").show();
		$("#suiwen").css("background-color","#ff683e");
		$("#suiwen").css("color","#fff");
		},function(){
			$(".suiwen").hide();
			$("#suiwen").css("background-color","#fff");
		$("#suiwen").css("color","#333");
			}
		)
		
	$("#suika").hover(function(){
		$(".suika").show();
		$("#suika").css("background-color","#ff683e");
		$("#suika").css("color","#fff");
		},function(){
			$(".suika").hide();
			$("#suika").css("background-color","#fff");
		$("#suika").css("color","#333");
			}
		)
	$(".suika").hover(function(){
		$(".suika").show();
		$("#suika").css("background-color","#ff683e");
		$("#suika").css("color","#fff");
		},function(){
			$(".suika").hide();
			$("#suika").css("background-color","#fff");
		$("#suika").css("color","#333");
			}
		)
	
		
		// 导航左侧栏js效果 start
		$(".pullDownList li").hover(function(){
			$(".yMenuListCon").fadeIn();
			var index=$(this).index(".pullDownList li");
			if (!($(this).hasClass("menulihover")||$(this).hasClass("menuliselected"))) {
				$($(".yBannerList")[index]).css("display","block").siblings().css("display","none");
				$($(".yBannerList")[index]).removeClass("ybannerExposure");
				setTimeout(function(){
				$($(".yBannerList")[index]).addClass("ybannerExposure");
				},60)
			}else{	
			}
			$(this).addClass("menulihover").siblings().removeClass("menulihover");
				$(this).addClass("menuliselected").siblings().removeClass("menuliselected");
			$($(".yMenuListConin")[index]).fadeIn().siblings().fadeOut();
		},function(){
			
		})
		$(".pullDown").mouseleave(function(){
			$(".yMenuListCon").fadeOut();
			$(".yMenuListConin").fadeOut();
			$(".pullDownList li").removeClass("menulihover");

		})
		// 导航左侧栏js效果  end
		
	})
	
	
	
	