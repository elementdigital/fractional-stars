<?php
/*
* jquery ajax rating system
* Copyright 2011, Bill Pontius
* https://github.com/elementdigital/starfract/
* Version: 0.0.1
* Licensed under MIT
*/

require('lib/rating.php');
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Ratings Object</title>

    <link href="css/rating.css" rel="stylesheet">

</head>

<body class="no-js">
	
	<h1>Fractional Rating System</h1>
	<p>AJAX star rating system with php server side AJAX handler</p>
	<ul>
		<li>Calculates cumulative average rating</li>
		<li>Dispays fractional stars</li>
		<li>Multiple objects on a single page</li>
		<li>Works in No JS environment</li>
		<li>Includes server side AJAX handler</li>
	</ul>
	
	<div class="starRating">
		<?php 
		echo $rating->setRatingObject(
			$itemid = "a4",
			$itemtype = 'test',
			$units = 10,
			$unitwidth = 20,
			$multivote = true
		); 
		?>
	</div>
	
	<h3>Parameters</h3>
	<ul>
		<li>itemid: (required) assign an id to the rating object</li>
		<li>itemtype: </li>
		<li>units: (int) how many stars to show</li>
		<li>unitwidth: (int) square size of star images</li>
		<li>multivote: (bool) allow users to rate multiple times per session</li>
	</ul>
		
	<h3>Example use</h3>
	<p>Default, 'itemid' is always required</p>
	<code>
	&lt;?php echo $rating->setRatingObject("a1"); ?&gt;
	</code>
	<div class="starRating">
		<?php echo $rating->setRatingObject("a1"); ?>
	</div>
	
	<p>Custom Params</p>
	<code>
	&lt;?php echo $rating->setRatingObject("a2",'demo',5,40,true); ?&gt;
	</code>	
	<div class="starRating">
		<?php echo $rating->setRatingObject("a2",'demo',5,40,true); ?>
	</div>
	
	<p>Single vote per IP</p>
	<code>
	&lt;?php echo $rating->setRatingObject("a3",'demo',7,30,false); ?&gt;
	</code>
	<div class="starRating">
		<?php echo $rating->setRatingObject("a3",'demo',7,30,false); ?>
	</div>
	
	<script src="js/jquery-2.1.4.min.js"></script>
	<script src="js/jquery.rating.js"></script>
	
</body>
</html>