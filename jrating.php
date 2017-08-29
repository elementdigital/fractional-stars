<?php
/*
* jquery ajax rating system
* Copyright 2011, Bill Pontius
* https://github.com/elementdigital/starfract/
* Version: 0.0.1
* Licensed under MIT
*
* AJAX rating submits here
* NON JS rating submits to "rating.php"
*/

header("Cache-Control: no-cache");
header("Pragma: nocache");

require('lib/rating.php');

if(!$rating->referer){
	//echo "no refferer, go 404 or redir to somewhere";
	header("Location: index.php");
	exit;
}

sleep(2);

if( isset($_REQUEST['i']) && isset($_REQUEST['v']) && isset($_REQUEST['j']) ){
	$objectid = $_REQUEST['i'];//item id
	$votevalue = (int)$_REQUEST['v'];//vote value
	$ajax = $_REQUEST['j'];//vote value
	$newrating = $rating->submitRating($objectid,$votevalue);
}

//Now send our object 
if($ajax){
	//this echos our object and returns it to the page via ajax
	echo $newrating;
}else{
	//if javascript disabled or not accepted, we send the user back to the page they came from
	header("Location: $rating->referer");
	exit;
}

?>

