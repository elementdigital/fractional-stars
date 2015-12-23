<?php
/*
* jquery ajax rating system
* Copyright 2011, Bill Pontius
* https://github.com/elementdigital/starfract/
* Version: 0.0.1
* Licensed under MIT
*/

class Rating {
	
	#public $params = array();
	var $params = array();
	
	public $multivote = false;
	
	public $referer;
	//var $referer = $_SERVER['HTTP_REFERER'];
	
	private $database = array();
	private $userip;
	private $showdiag;
	
	function Rating(){
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
		//these are dead now!
		$this->params['unitwidth'] = 20; //pixel width of rating graphic
		$this->params['units'] = 10; //default number of units (eg. stars) to display
		$this->params['imgpath'] = "images/";//path to images
	}
		
	function setDatabase(){
		//Enter database hostname (often:locathost)
		$this->database['dbhost'] = "localhost";//local
		
		//Enter data base user name
		$this->database['dbuser'] = "YOUR-DATBASE-USERNAME";//local
		
		//Enter data base user password
		$this->database['dbpass'] = "YOUR-DATABASE-PASSWORD";
		
		//Eneter your database name
		$this->database['dbname'] = "YOUR-DATABASE-NAME";
		
		//default data structure uses the table name "ratings"
		$this->database['dbtable'] = "ratings";
		
		//concatinate db and table
		$this->database['dbstring'] = $this->database['dbname'].".".$this->database['dbtable'];
		
		//connect
		$this->rating_conn = mysql_connect($this->database['dbhost'], $this->database['dbuser'], $this->database['dbpass']) or die  ('Error connecting to mysql');
	}
	
	//database functions
	function getCurrentRating($itemid){
		$result = mysql_query("SELECT total_votes, total_value, used_ips FROM ".$this->database['dbstring']." WHERE id='$itemid' ")or die(" Error: ".mysql_error());
		$num_rows = mysql_numrows($result);
		if($num_rows == 0){
			//create a new row
			$this->addNewItem($itemid);
			//get data from the new row
			$result = mysql_query("SELECT total_votes, total_value, used_ips FROM ".$this->database['dbstring']." WHERE id='$itemid' ")or die(" Error: ".mysql_error());
		}
		return $result;
	}
	
	function addNewItem($itemid){
		$sql = "INSERT INTO ".$this->database['dbstring']." (`id`,`total_votes`, `total_value`, `used_ips`) VALUES ('$itemid', '0', '0', '')";
		$result = mysql_query($sql);
	}
	
	function previousVotes($itemid){
		$result=mysql_num_rows(mysql_query("SELECT used_ips FROM ".$this->database['dbstring']." WHERE used_ips LIKE '%".$this->userip."%' AND id='".$itemid."' "));
		if($result == 1){
			$result = true;
		}else{
			$result = false;
		}
		return $result;
	}
	
	//generic functions
	function randomString(){
		$randlength = 10;
		$randcharacters = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
		$randstring = "";    
		for ($i = 0; $i < $randlength; $i++) {
			$randstring .= $randcharacters[mt_rand(0, strlen($randcharacters)-1)];
		}
		
		if(isset($_SESSION['objects'])){
			if (array_key_exists($randstring, $_SESSION['objects'])){
				randomString();
			}
		}
		return $randstring;
	}
	
	function getObjectidByItemid($itemid,$itemtype,$units,$unitwidth,$multivote){
		if (in_array($itemid, $_SESSION['objects'])){
			$thesekeys = array_keys($_SESSION['objects'], $itemid);
			$keycount = count($thesekeys);
			//if($itemkeys> 1){
				foreach ($thesekeys as $objectid){
					//echo $objectid.", ";
					//we are searching for a single match
					if($itemid == $_SESSION['items'][$objectid]['itemid']&&$units == $_SESSION['items'][$objectid]['units']&&$unitwidth == $_SESSION['items'][$objectid]['unitwidth']&&$multivote == $_SESSION['items'][$objectid]['multivote']){
						//looking for the object key..
						$match[] = $_SESSION['items'][$objectid]['objectid'];
					}
				}
				$result = $match[0];
			//}
			return $result;
		}else{
			return false;
		}
	}
	
	function getItemidByObjectid($objectid){
		if (array_key_exists($objectid, $_SESSION['objects'])){
			$result = $_SESSION['objects'][$objectid];
		}else{
			$result = false;
		}
		return $result;
	}
	
	function addObject($itemid,$itemtype,$units,$unitwidth,$multivote){
		$objectid = $this->randomString();
		$_SESSION['objects'][$objectid] = $itemid;
		$_SESSION['items'][$objectid]['objectid'] = $objectid;
		$_SESSION['items'][$objectid]['itemid'] = $itemid;
		$_SESSION['items'][$objectid]['itemtype'] = $itemtype;
		$_SESSION['items'][$objectid]['units'] = $units;
		$_SESSION['items'][$objectid]['unitwidth'] = $unitwidth;
		$_SESSION['items'][$objectid]['multivote'] = $multivote;
		return $objectid;
	}
	
	//Starts Object Creation, requested by page
	function setRatingObject($itemid=0,$itemtype='default',$units=5,$unitwidth=30,$multivote='true',$error=0){
		if(!isset($_SESSION['objects']) or !isset($_SESSION['items'])){
			$result = $this->addObject($itemid,$itemtype,$units,$unitwidth,$multivote);
			if(!$result){
				//echo "<br>fail to add object by itemid, when no session, setRatingObject";
				$error = 100;
			}else{
				$objectid = $result;
			}
		}else{
			//first check if exact item is in session
			$result = $this->getObjectidByItemid($itemid,$itemtype,$units,$unitwidth,$multivote);
			if(!$result){
				//object appears new, add it
				$result = $this->addObject($itemid,$itemtype,$units,$unitwidth,$multivote);
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
		$ratingobject = $this->createHtmlObject($objectid,$itemtype,$units,$unitwidth,$multivote,$update,$error);
		
		//return html object to initiating page
		return $ratingobject;
	}
	
	function submitRating($objectid,$votevalue){
		//check is session exists
		if(!isset($_SESSION['objects']) or !isset($_SESSION['items'])){
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
				$current_values = mysql_fetch_assoc($this->getCurrentRating($itemid));
				if(!$current_values){
					//echo "<br>fail to get current info from database by itemid, submitRating";
					//no build, no submit
					//we might want to refresh the page automaticaly, also might just link to section index
					$ratingobject = "<div class=\"ratingContainer1\"><div class=\"error\">session has expired! <a href=\"/photogallery/browse.php?image=".$itemid."\">click here to refresh the page.</a></div></div>";
				}else{
					//check if vote values are within range
					//if(!$votevalue <= $_SESSION['items'][$objectid]['units'] && !$votevalue > 0){
					//if(!$votevalue <= $_SESSION['items'][$objectid]['units'] || !$votevalue > 0){
					if($votevalue > $_SESSION['items'][$objectid]['units'] || $votevalue <= 0){
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
						if($_SESSION['items'][$objectid]['multivote'] == false && $pvotes){
							//echo "<br>fail, multivote not allowed, vote not counted.";
							//build, no submit
							$error = 900;
							$update = true;
							//$ratingobject = $this->createHtmlObject($objectid,$itemtype,$_SESSION['items'][$objectid]['units'],$_SESSION['items'][$objectid]['unitwidth'],$_SESSION['items'][$objectid]['multivote'],$update,$error);
						}else{
							//build, submit
							$newvotes = $current_values['total_votes']+1;
							$newvalue = $current_values['total_value']+(10/$_SESSION['items'][$objectid]['units'])*$votevalue;
							//update the db
							$result = mysql_query("UPDATE ".$this->database['dbstring']." SET total_votes='".$newvotes."', total_value='".$newvalue."', used_ips='".$newips."' WHERE id='$itemid'");
							$update = true;
							//$ratingobject = $this->createHtmlObject($objectid,$itemtype,$_SESSION['items'][$objectid]['units'],$_SESSION['items'][$objectid]['unitwidth'],$_SESSION['items'][$objectid]['multivote'],$update,$error);
						}
					}
					//build
					if (!isset($itemtype)){
						$itemtype = "";
					}
					if (!isset($error)){
						$error = "";
					}
					$ratingobject = $this->createHtmlObject($objectid,$itemtype,$_SESSION['items'][$objectid]['units'],$_SESSION['items'][$objectid]['unitwidth'],$_SESSION['items'][$objectid]['multivote'],$update,$error);
				}
			}
		}
		return $ratingobject;
	}
	
	function createHtmlObject($objectid,$itemtype,$units,$unitwidth,$multivote,$update,$error){
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
			$current_values = mysql_fetch_assoc($result);
		}
		
		if($current_values['total_value'] <= 0 || $current_values['total_votes'] <= 0){
			$current_rating = 0;
		}else{
			$current_rating = ($current_values['total_value']/$current_values['total_votes'])/(10/$units);
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
		//echo "<br>error: ".$error;
		//echo "<br>current_values['total_votes']: ".$current_values['total_votes'];
		//echo "<br>current_values['total_value']: ".$current_values['total_value'];
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
			//$ratingobject.="<li class=\"loading\" style=\"width:".$rating_totalwidth."px;height:".$unitwidth."px;\">Voting... .  .</li>";
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
					//$ratingobject.="<li class=\"".$itemtype."\" style=\"width:".$rating_totalwidth."px;height:".$unitwidth."px;\">";
					$ratingobject.="<li class=\"".$itemtype."\">";
					$ratingobject.="<span class=\"voted\">Already Voted</span>";
					$ratingobject.="</li>";
				}
			}else{
				for($i=1; $i<$units+1; $i++){
					$ratingobject.="<li class=\"".$itemtype."hover".$unitwidth*$i."\">";
					//$ratingobject.="<a class=\"ajaxlink hover".$unitwidth*$i."\" style=\"width:".$unitwidth."px;height:".$unitwidth."px;\" href=\"jrating.php?t=".$itemtype."&i=".$objectid."&v=".$i."\" title=\"".$i." of ".$units."\" id=\"".$i."\" rel=\"".$objectid."\" >";
					$ratingobject.="<a class=\"ajaxlink hover".$unitwidth*$i."\" href=\"jrating.php?t=".$itemtype."&i=".$objectid."&v=".$i."\" title=\"".$i." of ".$units."\" id=\"".$i."\" rel=\"".$objectid."\" >";
					$ratingobject.="<span>".$i."star</span>";
					$ratingobject.="</a>";
					$ratingobject.="</li>";
				}
			}
			$ratingobject.="</ol>";
			$ratingobject.="</ul>";
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
					$ratingobject.="<div class=\"success\">Thanks for rating!</div>";
				}
			}
			$ratingobject.="<div class=\"loading\">Rating... .  .</div>";
			$ratingobject.="<div class=\"currentVotes\"><span class=\"rating\">".round($current_rating,2)."</span><span class=\"votes\"> in ".$current_values['total_votes']." votes</span></div>";
			$ratingobject.="</div>";
	return $ratingobject;
	}
	
	
}

$rating = new Rating;

?>