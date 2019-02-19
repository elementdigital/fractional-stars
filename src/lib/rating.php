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

//check if config file exists
if( !file_exists("config/config.php") ){
	echo "<h1>Configuration Error</h1><h2>Fractional-Stars application could not find the configuration file.</h2><h3>config/config.php</h3>  <p>You probably need to copy \"config/config-example.php\" to \"config/config.php\" and configure your database settings in \"config.php\"</p>";
	exit();
}

require("config/config.php");

class Rating {
	
	var $params = array();
	
	public $multivote = false;
	
	public $referer;
	//var $referer = $_SERVER['HTTP_REFERER'];
	
	private $database = array();
	private $userip;
	private $showdiag;
	
	function Rating(){
		//session_destroy(); 
		if(!isset($_SESSION)){
			session_start();
			//echo session_cache_expire();//default in PHP is usually 180 min.
		}
		
		$this->userip = $_SERVER['REMOTE_ADDR'];
		//$this->userip = "fakeip"; //This fakes the ip during testing.
		
		if(isset($_SERVER['HTTP_REFERER'])){
			$this->referer = $_SERVER['HTTP_REFERER'];
		}else{
			$this->referer = $_SERVER['PHP_SELF'];
		}
		
		$this->setParams();
		
		$this->setDatabase();
	}
	
	function setParams(){
		$this->params['unitwidth'] = 30; //pixel width of rating graphic (each star)
		$this->params['units'] = 5; //default number of units (eg. stars) to display.
		$this->params['multivote'] = true; //if true users can rate an object multiple time per session.
		$this->params['rounding'] = false; //rounds to whole units
		$this->params['imgpath'] = "images/";//path to images

		$this->errors = array();
	}
		
	function setDatabase(){

		//Enter database hostname (often:locathost)
		$this->database['dbhost'] = FS_DB_SERVER;//local
		
		//Enter data base user name
		$this->database['dbuser'] = FS_DB_USER;//local
		
		//Enter data base user password
		$this->database['dbpass'] = FS_DB_PASS;
		
		//Eneter your database name
		$this->database['dbname'] = FS_DB_NAME;
		
		//default data structure uses the table name "ratings"
		$this->database['dbtable'] = "ratings";
		
		//concatinate db and table
		$this->database['dbstring'] = $this->database['dbname'].".".$this->database['dbtable'];
		
		//connect
		$this->rating_conn = mysqli_connect($this->database['dbhost'], $this->database['dbuser'], $this->database['dbpass']) or die  ('Error connecting to mysql');
	}

	function destroysession(){
		//working with sessions can be tricky! This comes in handy here.
		//var_dump(session_name());
		//session_destroy();
		unset($_SESSION['fsrs_objects']);
		unset($_SESSION['fsrs_items']);
		//var_dump($_SESSION);
		header("Location: ".$_SERVER['PHP_SELF']);
		exit();
	}
	
	//database functions
	function getCurrentRating($itemid){
		$result = mysqli_query($this->rating_conn, "SELECT total_votes, total_value, used_ips FROM ".$this->database['dbstring']." WHERE id='$itemid' ")or die(" Error: ".mysqli_error());
		$num_rows = mysqli_num_rows($result);
		if($num_rows == 0){
			//create a new row
			$this->addNewItem($itemid);
			//get data from the new row
			$result = mysqli_query($this->rating_conn, "SELECT total_votes, total_value, used_ips FROM ".$this->database['dbstring']." WHERE id='$itemid' ")or die(" Error: ".mysqli_error());
		}
		return $result;
	}
	
	function addNewItem($itemid){
		$sql = "INSERT INTO ".$this->database['dbstring']." (`id`,`total_votes`, `total_value`, `used_ips`) VALUES ('$itemid', '0', '0', '')";
		$result = mysqli_query($this->rating_conn, $sql);
	}
	
	function previousVotes($itemid){
		$result=mysqli_num_rows(mysqli_query($this->rating_conn, "SELECT used_ips FROM ".$this->database['dbstring']." WHERE used_ips LIKE '%".$this->userip."%' AND id='".$itemid."' "));
		if($result == 1){
			$result = true;
		}else{
			$result = false;
		}
		return $result;
	}
	
	function randomString(){
		$randlength = 10;
		$randcharacters = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
		$randstring = "";    
		for ($i = 0; $i < $randlength; $i++) {
			$randstring .= $randcharacters[mt_rand(0, strlen($randcharacters)-1)];
		}
		
		if(isset($_SESSION['fsrs_objects'])){
			if (array_key_exists($randstring, $_SESSION['fsrs_objects'])){
				randomString();
			}
		}
		return $randstring;
	}
	
	function getObjectidByItemid($itemid,$itemtype,$units,$unitwidth,$multivote,$rounding){
		if (in_array($itemid, $_SESSION['fsrs_objects'])){
			$thesekeys = array_keys($_SESSION['fsrs_objects'], $itemid);
			$keycount = count($thesekeys);
			foreach ($thesekeys as $objectid){
				//we are searching for a single match
				if($itemid == $_SESSION['fsrs_items'][$objectid]['itemid']&&$units == $_SESSION['fsrs_items'][$objectid]['units']&&$unitwidth == $_SESSION['fsrs_items'][$objectid]['unitwidth']&&$multivote == $_SESSION['fsrs_items'][$objectid]['multivote']&&$rounding == $_SESSION['fsrs_items'][$objectid]['rounding']){
					//looking for the object key..
					$match[] = $_SESSION['fsrs_items'][$objectid]['objectid'];
				}
			}
			if(isset($match)){
				$result = $match[0];
				return $result;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	
	function getItemidByObjectid($objectid){
		if (array_key_exists($objectid, $_SESSION['fsrs_objects'])){
			$result = $_SESSION['fsrs_objects'][$objectid];
		}else{
			$result = false;
		}
		return $result;
	}
	
	function addObject($itemid,$itemtype,$units,$unitwidth,$multivote,$rounding){
		$objectid = $this->randomString();
		$_SESSION['fsrs_objects'][$objectid] = $itemid;
		$_SESSION['fsrs_items'][$objectid]['objectid'] = $objectid;
		$_SESSION['fsrs_items'][$objectid]['itemid'] = $itemid;
		$_SESSION['fsrs_items'][$objectid]['itemtype'] = $itemtype;
		$_SESSION['fsrs_items'][$objectid]['units'] = $units;
		$_SESSION['fsrs_items'][$objectid]['unitwidth'] = $unitwidth;
		$_SESSION['fsrs_items'][$objectid]['multivote'] = $multivote;
		$_SESSION['fsrs_items'][$objectid]['rounding'] = $rounding;
		return $objectid;
	}
	
	//Starts Object Creation, requested by page
	function setRatingObject($itemid=0,$itemtype='default',$units="",$unitwidth="",$multivote="",$rounding=null,$error=0){
		if(empty($units)){
			$units = $this->params['units'];
		}
		if(empty($unitwidth)){
			$unitwidth = $this->params['unitwidth'];
		}
		if(empty($multivote)){
			$multivote = $this->params['multivote'];
		}
		if(empty($rounding)){
			$rounding = $this->params['rounding'];
		}
		//var_dump($rounding);
		if(!isset($_SESSION['fsrs_objects']) or !isset($_SESSION['fsrs_items'])){
			$result = $this->addObject($itemid,$itemtype,$units,$unitwidth,$multivote,$rounding);
			if(!$result){
				//echo "<br>fail to add object by itemid, when no session, setRatingObject";
				$error = 100;
			}else{
				$objectid = $result;
			}
		}else{
			//first check if exact item is in session
			$result = $this->getObjectidByItemid($itemid,$itemtype,$units,$unitwidth,$multivote,$rounding);
			if(!$result){
				//object appears new, add it
				$result = $this->addObject($itemid,$itemtype,$units,$unitwidth,$multivote,$rounding);
				if(!$result){
					//echo "<br>fail to add object by itemid, when session, setRatingObject";
					$error = 200;
				}else{
					$objectid = $result;
				}
			}else{
				$objectid = $result;
			}
		}
		
		//by here we should have the itemid and the objectid
		//echo "itemid: ".$itemid."<br>objectid: ".$objectid;
		
		//we are creating not updating
		$update = false;
		$ratingobject = $this->createHtmlObject($objectid,$itemtype,$units,$unitwidth,$multivote,$rounding,$update,$error);
		
		//return html object to initiating page
		return $ratingobject;
	}
	
	function submitRating($objectid,$votevalue){
		//check is session exists
		if(!isset($_SESSION['fsrs_objects']) or !isset($_SESSION['fsrs_items'])){
			//echo "<br>FAIL! session has expired!";
			//no build, no submit
			//we might want to refresh the page automaticaly
			$ratingobject = "<div class=\"ratingContainer1\"><div class=\"error\">session has expired! <a href=\"\">click here to refresh the page.</a></div></div>";
		}else{
			//check if object id exists
			$itemid = $this->getItemidByObjectid($objectid);
			//var_dump($itemid);
			if(!$itemid){
				//echo "<br>FAIL! could not get item id from objectid";
				//no build, no submit
				//we might want to refresh the page automaticaly
				$ratingobject = "<div class=\"ratingContainer1\"><div class=\"error\">session has expired! <a href=\"\">click here to refresh the gallery.</a></div></div>";
			}else{
				//get item info from database
				$current_values = mysqli_fetch_assoc($this->getCurrentRating($itemid));
				if(!$current_values){
					//echo "<br>fail to get current info from database by itemid, submitRating";
					//no build, no submit
					//we might want to refresh the page automaticaly, also might just link to section index
					$ratingobject = "<div class=\"ratingContainer1\"><div class=\"error\">session has expired! <a href=\"/photogallery/browse.php?image=".$itemid."\">click here to refresh the page.</a></div></div>";
				}else{
					//check if vote values are within range
					if($votevalue > $_SESSION['fsrs_items'][$objectid]['units'] || $votevalue <= 0){
						//echo "<br>fail, vote values out of range, submitRating";
						//build, no submit
						$error = 700;
					}else{
						//check user ip and add it
						if(!$current_values['used_ips']){
							$newips = $this->userip;
						}else{
							$pvotes = $this->previousVotes($itemid);
							if (!$pvotes){
								$newips = $current_values['used_ips'].",".$this->userip;
							}else{
								$newips = $current_values['used_ips'];
							}
						}
						if($_SESSION['fsrs_items'][$objectid]['multivote'] == false && isset($pvotes)){
							//echo "<br>fail, multivote not allowed, vote not counted.";
							//build, no submit
							$error = 900;
							$update = true;
						}else{
							//build, submit
							$newvotes = $current_values['total_votes']+1;
							$newvalue = $current_values['total_value']+(10/$_SESSION['fsrs_items'][$objectid]['units'])*$votevalue;
							//update the db
							$result = mysqli_query($this->rating_conn, "UPDATE ".$this->database['dbstring']." SET total_votes='".$newvotes."', total_value='".$newvalue."', used_ips='".$newips."' WHERE id='$itemid'");
							$update = true;
						}
					}
					//build
					if (!isset($itemtype)){
						$itemtype = "";
					}
					if (!isset($error)){
						$error = "";
					}
					$ratingobject = $this->createHtmlObject($objectid,$itemtype,$_SESSION['fsrs_items'][$objectid]['units'],$_SESSION['fsrs_items'][$objectid]['unitwidth'],$_SESSION['fsrs_items'][$objectid]['multivote'],$_SESSION['fsrs_items'][$objectid]['rounding'],$update,$error);
				}
			}
		}
		return $ratingobject;
	}
	
	function createHtmlObject($objectid,$itemtype,$units,$unitwidth,$multivote,$rounding,$update,$error){
		$itemid = $this->getItemidByObjectid($objectid);
		if(!$itemid){
			//echo "<br>fail to get itemid by objectid, createHtmlObject";
			$error = 500;
		}
		
		$result = $this->getCurrentRating($itemid);
		if(!$result){
			//echo "<br>fail to get current info from database by itemid, createHtmlObject";
			$error = 600;
		}else{
			$current_values = mysqli_fetch_assoc($result);
		}
		
		if($current_values['total_value'] <= 0 || $current_values['total_votes'] <= 0){
			$current_rating = 0;
		}else{
			$current_rating = ($current_values['total_value']/$current_values['total_votes'])/(10/$units);
		}

		if($rounding == true){
			$current_rating = round($current_rating, 0);
		}
		
		$rating_totalwidth = $units*$unitwidth;
			
		$current_rating_width = round($current_rating*$unitwidth,2);
		//echo $current_rating_width;
		//echo "<hr>DUMP<br>objectid: ".$objectid;
		//echo "<br>itemid: ".$itemid;
		//echo "<br>itemtype: ".$itemtype;
		//echo "<br>units: ".$units;
		//echo "<br>unitwidth: ".$unitwidth;
		//echo "<br>multivote: ".$multivote;
		//echo "<br>rounding: ".$rounding;
		//echo "<br>error: ".$error;
		//echo "<br>total_votes['total_votes']: ".$current_values['total_votes'];
		//echo "<br>total_values['total_value']: ".$current_values['total_value'];
		//echo "<hr>CALC.";
		//echo "<br>current_rating: ".$current_rating;
		//echo "<br>current_rating_width: ".$current_rating_width;
		//echo "<br>rating_totalwidth: ".$rating_totalwidth;
		//echo"<hr>";
		
		//NOW WE ARE READY TO ASSEMBLE OUR HTMLOBJECT
		//create the html object
		$ratingobject = "";
		$ratingobject.="<div id=\"".$objectid."\" class=\"ratingObject size".$unitwidth." clearfix\">";
		$ratingobject.="<ul class=\"ratingContainer size".$unitwidth." clearfix\" style=\"width:".$rating_totalwidth."px;\">";
		if($current_rating_width > $rating_totalwidth){
			$ratingobject.="<li class=\"currentRating\" style=\"width:".$current_rating_width."px;\"><span>".round($current_rating)."</span></li>";
		}else{
			$ratingobject.="<li class=\"currentRating\" style=\"width:".$current_rating_width."px;\"><span>".round($current_rating)."</span></li>";
		}
		$ratingobject.="<ol class=\"ratingLinks\" style=\"width:".$rating_totalwidth."px;\">";

		$pvotes = $this->previousVotes($itemid);
		if(!$multivote && $pvotes){
			//$error = 800;
			//static, already voted
			for($i=1; $i<$units+1; $i++){
				$ratingobject.="<li class=\"".$itemtype."\">";
				$ratingobject.="<span class=\"voted\">Already Voted</span>";
				$ratingobject.="</li>";
			}
		}else{
			for($i=1; $i<$units+1; $i++){
				$ratingobject.="<li class=\"".$itemtype."hover".$unitwidth*$i."\">";
				$ratingobject.="<a class=\"ajaxlink hover".$unitwidth*$i."\" href=\"jrating.php?t=".$itemtype."&i=".$objectid."&v=".$i."\" title=\"".$i." of ".$units."\" id=\"".$i."\" rel=\"".$objectid."\" >";
				$ratingobject.="<span>".$i."star</span>";
				$ratingobject.="</a>";
				$ratingobject.="</li>";
			}
		}
		$ratingobject.="</ol>";
		$ratingobject.="</ul>";
		//and some rediculous error handeling, ? :-/ ? 
		if($error){
			if($error == 100){
				$ratingobject.="<div class=\"error\">an error occured, try again!(".$error."), fail to add object by itemid, when no session, setRatingObject</div>";
			}elseif($error == 200){
				$ratingobject.="<div class=\"error\">an error occured, try again!(".$error."), fail to add object by itemid, when session, setRatingObject</div>";
			}elseif($error == 300){
				$ratingobject.="<div class=\"error\">an error occured, try again!(".$error."), fail to get item by objectid, submitRating</div>";
			}elseif($error == 400){
				$ratingobject.="<div class=\"error\">an error occured, try again!(".$error."), fail to get current info from database by itemid, submitRating</div>";
			}elseif($error == 500){
				$ratingobject.="<div class=\"error\">an error occured, try again!(".$error."), fail to get itemid by objectid, createHtmlObject</div>";
			}elseif($error == 600){
				$ratingobject.="<div class=\"error\">an error occured, try again!(".$error."), fail to get current info from database by itemid, createHtmlObject</div>";
			}elseif($error == 700){
				$ratingobject.="<div class=\"error clearfix\">values out of range! (".$error.")</div>";
			}elseif($error == 800){
				$ratingobject.="<div class=\"error\">Already Voted(".$error.")</div>";
			}elseif($error == 900){
				$ratingobject.="<div class=\"error\">Already Voted</div>";
			}else{
				$ratingobject.="<div class=\"error\">an error occured, try again!(".$error.")</div>";
			}
		}else{
			if($update){
				$ratingobject.="<div class=\"success\"><span>Thanks for rating!</span></div>";
			}
		}
		$ratingobject.="<div class=\"loading\"><span>Rating... .  .</span></div>";
		$ratingobject.="<div class=\"currentVotes\"><span class=\"rating\">".round($current_rating,2)."</span><span class=\"votes\"> in ".$current_values['total_votes']." votes</span></div>";
		$ratingobject.="</div>";
	return $ratingobject;
	}
	
	
}

$rating = new Rating;

?>