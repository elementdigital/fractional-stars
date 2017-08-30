/*
 * jquery ajax rating system
 *
 * Copyright (c) 2011, elementdigital
 * Licensed under MIT
 *
 * https://github.com/elementdigital/fractional-stars/
 * Version: 0.0.2
*/

function ajaxRating(item_id,vote_value,target){
	var targetId = "#"+target;
	var targetClassAjaxlink = targetId+" .ajaxlink";
	var targetClassLoading = targetId+" .loading";
	var targetClassError = targetId+" .error";
	var targetClassSuccess = targetId+" .success";
	var targetClassCurrent = targetId+" .currentRating";
	$.ajax({
		type: "get",
		url: "jrating.php",
		data: "j=1&v="+vote_value+"&i="+item_id,
		cache: true,
		async: true,
		dataType: "html",
		target: targetId,
		beforeSend: function(){
			//alert("before");
			$("body").css("cursor", "wait");
			$(targetClassError).hide();
			$(targetClassLoading).show();
			$(targetClassAjaxlink).hide();
		},
		success: function(html){
			$(targetId).parent().html(html).show();
			$(targetClassAjaxlink).hide();
			$(targetClassLoading).show();
		},
		complete: function(){
			$(targetId).parent().show();
			$(targetClassLoading).hide();
			$(targetClassAjaxlink).delay(2000).fadeIn(800);
			$(targetClassSuccess).delay(3000).fadeOut(3000);
			$(targetClassError).delay(7000).fadeOut(2000);
			
			//console.log("targetid: "+targetId);
			//console.log("objClass: "+objectClass);
			//console.log($(targetId).parent().get(0).className);
			
			var objectClass = "."+$(targetId).parent().get(0).className;
			//var itemsfound = $(objectClass).get();
			var itemsfound = $(targetId).get();
			
			var newwidth = $(targetId).parent().find('li.currentRating').width();
			var newheight = $(targetId).parent().find('li.currentRating').height();
			var newunits = $(targetId).parent().find('.ratingLinks li').get().length;
			var newvotetext = $(targetId).parent().find('span.votes').text();
			var newrating = (newwidth/newheight);
			var multivoteanchors = $(targetId).parent().find('li a.ajaxlink').get(0);
			
			for(key in itemsfound) {
				var oldwidth = $(itemsfound[key]).find('li.currentRating').width();
				var oldheight = $(itemsfound[key]).find('li.currentRating').height();
				var oldunits = $(itemsfound[key]).find('.ratingLinks li').get().length;
				var ab = (newwidth/newheight);
				var abc = (newunits/ab);
				var currentrating = (oldunits/abc).toFixed(2);
				var currentratingwidth = (currentrating*oldheight);
				var newrating2 = (oldwidth/oldheight);
				
				$(itemsfound[key]).find('li.currentRating').css({"width":currentratingwidth+"px"});
				$(itemsfound[key]).find('span.rating').text(currentrating).show;
				$(itemsfound[key]).find('span.votes').text(newvotetext).show;
				
				if(!multivoteanchors){
					$(itemsfound[key]).find('li a.ajaxlink').hide();
				}
			}
			//$("body").css("cursor", "auto");
			$("body").removeAttr("style");
		},
		error: function(result) { 
			alert("Sorry, your rating could NOT be added, an error occured!");
		}
	});
}

function ratePhoto(e){
	e.preventDefault();
	var vote_value = $(this).attr("id");
	var item_type = $(this).parent().get(0).className;
	var item_id = $(this).parentsUntil('.ratingObject').parent().attr("id");
	var target = $(this).parentsUntil('.ratingObject').parent().attr("id");
	ajaxRating(item_id,vote_value,target);
}

function ratingEvents(){
	$(".starRating").on("click", "a", {}, ratePhoto);
}

$(document).ready(function() {
	ratingEvents();
});