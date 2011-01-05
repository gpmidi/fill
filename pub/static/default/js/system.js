//MEOW IS WORKING ON THIS ATM
var rotator_page=1;
$("body").ready(function(){
	var w_remain=0;
	var max_items=0;
	var w_w=$("body").outerWidth();
	var new_nf_l=w_w-80;
	$(".rnext").css({"left":new_nf_l+"px","top":"110px","position":"absolute"});
	$(".rprev").css({"left":"51px","top":"120px","position":"absolute"});
	max_items=Math.floor((w_w-230)/299);
	ndd_width=(max_items*299);
	$(".featured-rotator-wrap #rotator_div").css({"overflow":"hidden"});
	$(".featured-rotator-wrap #rotator_div ul").css({"height":"113px"});
	$(".featured-rotator-wrap #rotator_div ul li:first").css({"marginLeft":"28px"});
	$(".featured-rotator-wrap #rotator_div ul li:last").css({"marginRight":"28px"});
	$(".featured-rotator-wrap #rotator_div").css({"left":"50%","width":(ndd_width)+"px","top":"75px","marginLeft":"-"+Math.ceil(ndd_width/2)+"px","position":"absolute"});
	$(".featured-rotator-wrap #rotator_div ul").css({"width":(ndd_width)+"px"});
	$(".featured-rotator-wrap #rotator_div_inner").css({"width":(ndd_width*2+150)+"px","height":"113px"});
	$(".rprev").click(function(){
		if(rotator_page>1){
			rotator_page--;
			$.post("/featured_rotator/",{"page":rotator_page,"max":max_items},function(data){
				$("#rotator_div_inner").html(data+$("#rotator_div_inner").html());
				$("#rotator_div_inner").css({"marginLeft":"-"+(ndd_width)+"px"});
				$("#rotator_div ul").css({"width":(ndd_width)+"px","float":"left","height":"113px"});
				$("#rotator_div_inner").animate({"marginLeft":"0px"},function(){
					$("#rotator_div ul:last").empty().remove();
				});
			});
		}
	});
	$(".rnext").click(function(){
		if(rotator_page!=0){
			rotator_page++;
			$.post("/featured_rotator/",{"page":rotator_page,"max":max_items},function(data){
				$("#rotator_div_inner").html($("#rotator_div_inner").html()+data);
				$("#rotator_div ul").css({width:(ndd_width)+"px","float":"left","height":"113px"});
				$("#rotator_div_inner").animate({"marginLeft":"-"+(ndd_width)+"px"},function(){
					$("#rotator_div ul:first").empty().remove();
					$("#rotator_div_inner").css({"marginLeft":"0px"});
				});
			});
		}
	});
});