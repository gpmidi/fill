//MEOW IS WORKING ON THIS ATM

$("body").ready(function(){
	var w_remain=0;
	var max_items=0;
	var w_w=$("body").outerWidth();
	var new_nf_l=w_w-80;
	$(".rnext").css({"left":new_nf_l+"px","top":"110px","position":"absolute"});
	$(".rprev").css({"left":"51px","top":"120px","position":"absolute"});
	if(w_w>271){
		w_remain=w_w-271-230;
		max_items=1;
	}
	if(w_remain>271){
		w_remain=w_remain-271;
		max_items+=1;
	}
	var ndd_width=0;
	max_items+=Math.floor((w_remain)/299);
	if(max_items>=1){
		ndd_width=271;
		max_items=max_items-1;
	}
	if(max_items>=1){
		ndd_width+=271;
		max_items=max_items-1;
	}
	ndd_width+=max_items*299;
	$(".featured-rotator-wrap #rotator_div ul li:first").css({"marginLeft":"0px"});
	$(".featured-rotator-wrap #rotator_div ul li:last").css({"marginRight":"0px"});
	$(".featured-rotator-wrap #rotator_div").css({"left":"50%",width:(ndd_width)+"px","top":"79px","marginLeft":"-"+Math.ceil(ndd_width/2)+"px","position":"absolute"});
	$(".featured-rotator-wrap #rotator_div ul").css({width:(ndd_width)+"px","float":"none"});

});