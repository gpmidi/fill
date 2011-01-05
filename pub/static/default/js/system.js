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
	$(".featured-rotator-wrap #rotator_div ul").css({"height":"113px"});
	$(".featured-rotator-wrap #rotator_div ul li:first").css({"marginLeft":"28px"});
	$(".featured-rotator-wrap #rotator_div ul li:last").css({"marginRight":"28px"});
	$(".featured-rotator-wrap #rotator_div").css({"left":"50%","width":(ndd_width)+"px","top":"75px","marginLeft":"-"+Math.ceil(ndd_width/2)+"px","position":"absolute"});
	$(".featured-rotator-wrap #rotator_div ul").css({width:(ndd_width)+"px","float":"none"});
	$(".rprev").click(function(){
		$.post("/featured_rotator/",{"page":rotator_page},function(data){
			
		});
	});
});