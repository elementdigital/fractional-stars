<?php
/*
 * jquery ajax rating system
 *
 * Copyright (c) 2011, elementdigital
 * Licensed under MIT
 *
 * https://github.com/elementdigital/fractional-stars/
 * Version: 0.0.3
*/

require('lib/rating.php');

if(isset($_GET["killsession"])){
	$rating->destroysession();
}

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
    <style>
    	h1,h3,h4,h5{
    		margin: 24px 0 0 0;
    		padding: 0px 0 0 0;
    	}
    	h1+h2{
    		padding-top: 0px;
    		margin-top: 0px;
    		font-size: 16px;
    	}
    	h2+h5{
    		padding-top: 0px;
    		margin-top: 0px;
    	}
    	h5{
    		font-size: 15px;
    	}
    	p,code{
    		margin: 5px 0 4px 0;
    		padding:0 0 0 0;
    	}
    	.footer{
    		margin: 20px 0 40px 0;
    		padding: 20px 0 0 0;
    		border-top: 1px solid #333;
    	}
    </style>

    <script src="js/jquery.min.js"></script>
	<script src="js/jquery.rating.js"></script>

</head>

<body class="no-js">
	
	<h1>Fractional Stars Rating System</h1>
	<h2>AJAX Rating System written in PHP, MySQL & jQuery</h2>
	<ul>
		<li>Calculates cumulative average rating</li>
		<li>Dispays fractional stars</li>
		<li>Multiple objects on a single page</li>
		<li>Works in No JS environment</li>
		<li>Easy to integrate into your own applications</li>
	</ul>
	
	<div class="starRating">
		<?php 
		echo $rating->setRatingObject(
			$itemid = "a4",
			$itemtype = 'test',
			$units = 10,
			$unitwidth = 20,
			$multivote = true,
			$rounding = true
		); 
		?>
	</div>
	
	<h2>Parameters</h2>
	<ul>
		<li>itemid: *required (string) assign an id to the rating object</li>
		<li>itemtype: (string) additional identifier</li>
		<li>units: (int) how many stars to show</li>
		<li>unitwidth: (int) square size of star images</li>
		<li>multivote: (bool) allow users to rate multiple times per session</li>
		<li>rounding: (bool) rounds average to whole units (ex. 4.6=5)</li>
	</ul>
		
	<h2>Example use</h2>
	<h5>Generates: default rating object, 'itemid' is always required</h5>
	<code>
	&lt;?php echo $rating->setRatingObject("a1"); ?&gt;
	</code>
	<div class="starRating">
		<?php echo $rating->setRatingObject("a1"); ?>
	</div>
	
	<h3>Custom Params</h3>

	<h5>Generates: 30px + 5 star + multivote + rounding</h5>
	<code>
	&lt;?php echo $rating->setRatingObject("a2", 'demo', 5, 30, true, true); ?&gt;
	</code>	
	<div class="starRating">
		<?php echo $rating->setRatingObject("a2", 'demo', 5, 30, true, true); ?>
	</div>

	<h5>Generates: 40px + 7 star + multivote + NOrounding</h5>
	<code>
	&lt;?php echo $rating->setRatingObject("a3", 'demo', 7, 40, true, false); ?&gt;
	</code>	
	<div class="starRating">
		<?php echo $rating->setRatingObject("a3", 'demo', 7, 40, true, false); ?>
	</div>

	<h5>Generates: 50px + 10 star + multivote + NOrounding</h5>
	<code>
	&lt;?php echo $rating->setRatingObject("a4", 'demo', 10, 50, true, false); ?&gt;
	</code>	
	<div class="starRating">
		<?php echo $rating->setRatingObject("a4", 'demo', 10, 50, true, false); ?>
	</div>

	<h4>Single vote per IP/session</h4>
	<p>Restricts voting by ip address, <a href ="?killsession=1">destroy session</a> to test. </p>
	<p>It's cool, but the only way to ensure true single voting is by using a login system in your application.</p>
	<code>
	&lt;?php echo $rating->setRatingObject("a5", 'demo', 10, 30, false, true; ?&gt;
	</code>
	<div class="starRating">
		<?php echo $rating->setRatingObject("a5", 'demo', 10, 30, false, true); ?>
	</div>

	<div class="footer">
		<p>Fractional Stars: AJAX Rating System written in PHP, MySQL & jQuery.</p>
		<p>Project on: <a href="https://github.com/elementdigital/fractional-stars" target="_blank">Github.com</a></p>
	</div>
	
</body>
</html>