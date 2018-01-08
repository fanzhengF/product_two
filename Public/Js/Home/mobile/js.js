$(function(){
	/*remove*/
	$(".zxq_btn1 a").click(function(){

		$(this).parents("li").remove()
		
	})
	/*tab*/
	$("#tab .meet_joins:gt(0)").hide();
		$(".llv").click(function(){
			var i=$(this).index()
			$(this).addClass('zxq_click').siblings('.llv').removeClass('zxq_click');
			$(this).parent().siblings('.bb').find('.meet_joins').eq(i).show().siblings('.meet_joins').hide()
		})
		
		$(".zxq_albb1").each(function() {
           var first =80;
           if ($(this).text().length > first) {
               $(this).text($(this).text().substring(0,first));
               $(this).html($(this).html() + '...');
           }
       });
	
});

