<?php
/**
 * Copyright [2013-2014] [OHsystem]
 * 
 * We spent a lot of time writing this code, so show some respect:
 * - Do not remove this copyright notice anywhere (bot, website etc.)
 * - We do not provide support to those who removed copyright notice
 *
 * OHSystem is free software: You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This file is part of DOTA OPENSTATS.
 * 
 * You can contact the developers on: admin@ohsystem.net
 * or join us directly here: http://ohsystem.net/forum/
 * 
 * Visit us also on http://ohsystem.net/ and keep track always of the latest
 * features and changes.
 * 
 * 
 * This is modified from GHOST++: http://ghostplusplus.googlecode.com/
 * Official GhostPP-Forum: http://ghostpp.com/
*/
if (!isset($website) ) {header('HTTP/1.1 404 Not Found'); die; }

function Get_w3mmdplayers($gameid) {
    global $db;
    global $lang;
    $Data = array();
    $ScourgeRow = 0;
    $SentinelRow = 0;
    $SetWinner = 0;
    $Data[0]["winner"] = 0;
    $c=0;
        
        $sth = $db->prepare(  "SELECT g.creatorname, g.duration, g.datetime, g.gamename, g.stats, g.views, g.map
        FROM ".OSDB_GAMES." AS g
        WHERE g.id='".(int)$gameid."'" );
        $result = $sth->execute();
        $row = $sth->fetch(PDO::FETCH_ASSOC);
        
        $Data[$c]["creatorname"] = $row["creatorname"];
        $Data[$c]["duration"] = secondsToTime($row["duration"]);
        $Data[$c]["datetime"] = date( OS_DATE_FORMAT, strtotime($row["datetime"]));
        $Data[$c]["gamename"] = $row["gamename"];
        $Data[$c]["stats"] = $row["stats"];
        $Data[$c]["views"] = $row["views"];
        $Map = $row["map"];
        
        $sth = $db->prepare(  "SELECT w.id, w.category, w.botid, w.gameid, w.pid, w.name, w.flag, w.leaver, w.practicing, gp.ip, gp.loadingtime, gp.left, gp.leftreason
        FROM ".OSDB_GP." as gp
        LEFT JOIN ".OSDB_W3PL." as w ON w.gameid = gp.gameid AND gp.name = w.name
        WHERE w.gameid ='".(int)$gameid."' 
        ORDER BY w.pid ASC" );
        
        $result = $sth->execute();
        
         if ( $sth->rowCount()>=1 ) {
         
         if ( file_exists("inc/geoip/geoip.inc") ) {
         include("inc/geoip/geoip.inc");
         $GeoIPDatabase = geoip_open("inc/geoip/GeoIP.dat", GEOIP_STANDARD);
         $GeoIP = 1;
         }
         
         
         while($row = $sth->fetch(PDO::FETCH_ASSOC)) {
           $Data[$c]["botid"] = $row["botid"];
           $Data[$c]["category"] = $row["category"];
           $Data[$c]["id"] = $row["id"];
           $Data[$c]["gameid"] = $row["gameid"];
           $Data[$c]["pid"] = $row["pid"];
           $Data[$c]["name"] = $row["name"];
           $Data[$c]["full_name"] = $row["name"];
           $Data[$c]["userid"] = $row["name"];
           $Data[$c]["flag"] = $row["flag"];
           $Data[$c]["leaver"] = $row["leaver"];
           $Data[$c]["practicing"] = $row["practicing"];
           
           $Data[$c]["leftreason"] = $row["leftreason"];
           $Data[$c]["left"] = secondsToTime($row["left"]);
           $Data[$c]["loadingtime"] = $row["loadingtime"];
           $Data[$c]["ip"] = $row["ip"];
           $Data[$c]["hideElement"] = ' hideElement';
           
           $Data[$c]["counter"] = ''.($row["pid"]+1);
           
           if ($GeoIP == 1 ) {
           $Data[$c]["letter"]   = geoip_country_code_by_addr($GeoIPDatabase, $row["ip"]);
           $Data[$c]["country"]  = geoip_country_name_by_addr($GeoIPDatabase, $row["ip"]);
           }
              
           $Data[$c]["item1"] = "";  $Data[$c]["itemname1"] = ""; $Data[$c]["itemicon1"] = "empty.gif";
           $Data[$c]["item2"] = "";  $Data[$c]["itemname2"] = ""; $Data[$c]["itemicon2"] = "empty.gif";
           $Data[$c]["item3"] = "";  $Data[$c]["itemname3"] = ""; $Data[$c]["itemicon3"] = "empty.gif";
           $Data[$c]["item4"] = "";  $Data[$c]["itemname4"] = ""; $Data[$c]["itemicon4"] = "empty.gif";
           $Data[$c]["item5"] = "";  $Data[$c]["itemname5"] = ""; $Data[$c]["itemicon5"] = "empty.gif";
           $Data[$c]["item6"] = "";  $Data[$c]["itemname6"] = ""; $Data[$c]["itemicon6"] = "empty.gif";
           
           $Data[$c]["heroid"] = "blank"; $Data[$c]["hero"] = "blank"; $Data[$c]["description"] = "";
          
           $vars = Get_w3mmdvarsString($gameid, $row["pid"], "race");
           $Hero = Get_TDRace( $vars  );
           $Data[$c]["hero_link"]  = 0;
           
           if ( !empty($Hero) AND file_exists("img/heroes/".$Hero) ) {
           $Data[$c]["heroid"] = $Hero ; $Data[$c]["hero"] = $Hero; $Data[$c]["description"] = "";
           }
           
           $Data[$c]["level"] = ""; 
           $Data[$c]["banned"] = ""; $Data[$c]["admin"] = ""; $Data[$c]["score_points"] = "";   
           $Data[$c]["kills"] = "";  $Data[$c]["deaths"] = ""; $Data[$c]["assists"] = "";
           $Data[$c]["creepkills"] = "";  $Data[$c]["creepdenies"] = ""; $Data[$c]["towerkills"] = "";
           $Data[$c]["raxkills"] = "";  $Data[$c]["courierkills"] = ""; $Data[$c]["neutralkills"] = "";
           $Data[$c]["gold"] = "";       
           
           $Data[$c]["side"] = "";
           
           $LimitTeam = W3mmdLimitTeams( $Map );
           
           if ( $row["pid"]<=$LimitTeam AND $SentinelRow == 0 ) { $Data[$c]["side"] = "sentinel"; $SentinelRow = 1; }
           if ( $row["pid"]>$LimitTeam  AND $ScourgeRow  == 0 ) { $Data[$c]["side"] = "scourge";  $ScourgeRow = 1;  }

           if ( $row["pid"]<=$LimitTeam AND $row["flag"] == "winner" AND $SetWinner == 0)  
                { $Data[0]["winner"] = '1'; $SetWinner = 1;  }
                
           if ( $row["pid"]>$LimitTeam  AND $row["flag"] == "winner" AND $SetWinner == 0)  
                { $Data[0]["winner"] = '2'; $SetWinner = 1; }
        
        if ( !isset($winner) ) {
		  
		  if ( $row["pid"]<=$LimitTeam AND $row["flag"] == "winner" ) $winner = 1; else
		  if ( $row["pid"]<=$LimitTeam AND $row["flag"] == "loser" )  $winner = 2; else 
		  $winner = 0;
		
		}
        
         
        if ( $winner == 1 AND $row["pid"]<= $LimitTeam) $Data[$c]["hideslot"] = "winner_background";
        else $Data[$c]["hideslot"] = "loser_background";
        if ($Data[0]["winner"] == '2' AND $row["pid"]> $LimitTeam)  $Data[$c]["hideslot"] = "winner_background";
         
		//Get custom game "template" // hard-coded stuff here
		//LEGION TD
       	if ( strstr($Map, "Legion TD") ) {
		$lang["sent_winner"] = "West Legion <b>Winner</b>";
		$lang["scou_winner"] = "East Legion <b>Winner</b>";
		
		$lang["sent_loser"] = "West Legion <b>Loser</b>";
		$lang["scou_loser"] = "East Legion <b>Loser</b>";
		}
		//WARLOCK
		if ( substr($Map,0,7) == "Warlock" ) {
		$SentinelRow = 0;
		$ScourgeRow = 0;
		$lang["sent_winner"] = "Warlock winner";
		$lang["scou_winner"] = "Warlock winner";
		
		$lang["sent_loser"] = "Warlock loser";
		$lang["scou_loser"] = "Warlock loser";
		}
		//HEROLINE
       	if ( strstr($Map, "HeroLineWars") ) {
		$lang["sent_winner"] = "Hero's Line 1 <b>Winner</b>";
		$lang["scou_winner"] = "Hero's Line 2 <b>Winner</b>";
		
		$lang["sent_loser"] = "Hero's Line 1 <b>Loser</b>";
		$lang["scou_loser"] = "Hero's Line 2 <b>Loser</b>";
		}
		//TREE TAG
       	if ( strstr($Map, "TreeTag") ) {
		$lang["sent_winner"] = "Ents <b>Winner</b>";
		$lang["scou_winner"] = "Infernal <b>Winner</b>";
		
		$lang["sent_loser"] = "Ents <b>Loser</b>";
		$lang["scou_loser"] = "Infernal <b>Loser</b>";
		
		if ( $row["pid"]<=$LimitTeam ) {
		$Data[$c]["heroid"] = "custom/ent.png"; $Data[$c]["hero"] =  "custom/ent.png"; $Data[$c]["description"] = "";
		}
		
		if ( $row["pid"]>$LimitTeam ) {
		$Data[$c]["heroid"] = "custom/infernal.png"; $Data[$c]["hero"] =  "custom/infernal.png"; $Data[$c]["description"] = "";
		}
		}
		
		//Battleships Battleships
		if ( strstr($Map, "Battleships Cross") ) {
		
		$Data[$c]["heroid"] = "custom/ship.gif"; $Data[$c]["hero"] =  "custom/ship.gif"; $Data[$c]["description"] = "";
		if ( $row["pid"]>=10 AND $ScourgeRow == 1) { $Data[$c]["side"] = "sentinel"; $ScourgeRow = 2; }
		
		if ($row["flag"] != "winner" AND $row["flag"] != "loser") { 
		$winner = 0; 
		$Data[0]["winner"] = 0; 
		$Data[$c]["hideslot"] = "loser_background";
		}
		
		$lang["sent_winner"] = "South Aliance <b>Winner</b>";
		$lang["scou_winner"] = "North Aliance <b>Winner</b>";
		$lang["draw_game"] = "South Aliance <b>Draw</b>";
		if ( $row["pid"]>$LimitTeam ) $lang["draw_game"]  = "North Aliance <b>Draw</b>";
		$lang["sent_loser"] = "South Aliance <b>Loser</b>";
		$lang["scou_loser"] = "North Aliance <b>Loser</b>";
		
		if ( $row["pid"]>=9 ) {
		$lang["sent_winner"] = "Goblin/Greenpeace <b>Winner</b>";
		$lang["scou_winner"] = "Goblin/Greenpeace <b>Winner</b>";
		$lang["draw_game"]  = "Goblin/Greenpeace <b>Draw</b>";
		$lang["sent_loser"] = "Goblin/Greenpeace <b>Loser</b>";
		$lang["scou_loser"] = "Goblin/Greenpeace <b>Loser</b>";
		}
		
		}
		
		
		 
         if ( $winner == 1 ) {  
         $Data[$c]["display_winner"] = '<span class="winner2">'.$lang["sent_winner"]."</span>"; 
         $Data[$c]["display_loser"]  = '<span class="winner1">'.$lang["scou_loser"]."</span>"; 
         } else
         if ( $winner == 2 ) { 
         $Data[$c]["display_winner"] = '<span class="winner1">'.$lang["sent_loser"]."</span>"; 
         $Data[$c]["display_loser"]  = '<span class="winner2">'.$lang["scou_winner"]."</span>"; 
         }  else
         if ( $winner == 0 ) {  
		 $Data[$c]["display_winner"] = $lang["draw_game"];
         $Data[$c]["display_loser"]  = $lang["draw_game"];
		 }
                
           $c++;
         }

         if ( isset($GeoIP) AND $GeoIP == 1) geoip_close($GeoIPDatabase);

         return $Data;
         
         }
   }
   
   function W3mmdLimitTeams( $map ) {
    $val = 5;
    if ( strstr($map, "Legion TD") )     $val = 4;
	if ( substr($map,0,7) == "Warlock" ) $val = 1;
	if ( strstr($map, "HeroLineWars") )  $val = 4;
	if ( strstr($map, "TreeTag") )  $val = 8;
	if ( strstr($map, "Battleships Crossfire") )  $val = 4;
	
	return $val;
   
   }
   
   //Get custom map VARs
   function Get_w3mmdvarsString( $gameID, $pid = "", $varname="" ) {
    global $db;
	$sth = $db->prepare(  "SELECT value_string FROM ".OSDB_W3VARS." 
	WHERE gameid = '".$gameID."' AND pid = '".$pid."' AND varname = '".$varname."' " );
	$result = $sth->execute();

	$row = $sth->fetch(PDO::FETCH_ASSOC);
	return $row["value_string"];
   }
   
   function Get_w3mmdvars( $gameID, $pid = "", $varname="" ) {
   
    $sql = "";
	global $db;
    if ( !empty($pid) )     $sql.=" AND pid='".$pid."'";  
    if ( !empty($varname) ) $sql.=" AND varname='".$varname."'";  
   
    $sth = $db->prepare(  "SELECT * FROM ".OSDB_W3VARS." WHERE gameid = '".$gameID."' $sql " );
	$result = $sth->execute();
	$c=0;
	$Data = array();
	
	 while($row = $sth->fetch(PDO::FETCH_ASSOC)) {
	   $Data[$c]["id"] = $row["id"];
	   $Data[$c]["botid"] = $row["botid"];
	   $Data[$c]["gameid"] = $row["gameid"];
	   $Data[$c]["pid"] = $row["pid"];
	   $Data[$c]["varname"] = $row["varname"];
	   $Data[$c]["value_int"] = $row["value_int"];
	   $Data[$c]["value_real"] = $row["value_real"];
	   $Data[$c]["value_string"] = $row["value_string"];
	   $c++;
	}
	return $Data;
   }
   
   //Get custom map RACE
   function Get_TDRace($race = "" ) {
     $race = str_replace('"', "", $race);
     $return = 'custom/'.$race.'.gif'; 
	  
	 //if ( $race == "Paladin" ) $return = $race.'.gif'; 
	 
	 return $return;
	
   }

   function getSingleGame($gameid) {
	 $sql = "SELECT winner, creatorname, duration, datetime, gamename, stats, views
     FROM ".OSDB_DG." AS dg 
     LEFT JOIN ".OSDB_GAMES." AS d ON d.id = dg.gameid 
     WHERE dg.gameid='".(int)$gameid."'";
	 
	 return $sql;
	}
      ///////////////
     // GAME INFO //
    ///////////////
	function getUserGames ( $id, $MinDuration, $offset, $rowsperpage, $filter = "" ) {
	
	 $sql = "SELECT s.*, g.id, g.map, g.gamename, g.datetime, g.ownername, g.duration,  g.creatorname, dg.winner, 
	 g.gamestate AS type, s.player, dp.kills, dp.deaths, dp.creepkills, dp.creepdenies, dp.assists, dp.hero, dp.neutralkills, dp.newcolour, gp.`left`, g.alias_id, w.flag
	 FROM ".OSDB_STATS." as s 
	 LEFT JOIN ".OSDB_GP." as gp ON (gp.name) = (s.player)
	 LEFT JOIN ".OSDB_GAMES." as g ON g.id = gp.gameid
	 LEFT JOIN ".OSDB_DG." as dg ON g.id = dg.gameid 
	 LEFT JOIN ".OSDB_DP." as dp ON dp.gameid = dg.gameid AND gp.colour = dp.colour
	 LEFT JOIN ".OSDB_W3PL." as w ON w.gameid = g.id AND w.pid = 0
	 WHERE s.id = '".(int) $id."' AND g.duration>='".$MinDuration."' ".$filter."
	 LIMIT $offset, $rowsperpage";
	 
	return $sql;
	}
	
	
	function getAllGames($MinDuration, $offset, $rowsperpage, $filter="", $order = "id DESC" ) {
	  $sql = "SELECT 
          g.id, g.views, g.stats, g.map, g.datetime, g.gamename, g.ownername, g.duration, g.creatorname, dg.winner, 
		  g.gamestate as type, g.creatorserver as server, g.alias_id, w.flag
		  FROM ".OSDB_GAMES." as g 
		  LEFT JOIN ".OSDB_DG." as dg ON g.id = dg.gameid 
		  LEFT JOIN ".OSDB_W3PL." as w ON w.gameid = g.id AND w.`pid` = 2
		  WHERE duration>='".$MinDuration."' $filter
		  ORDER BY $order
		  LIMIT $offset, $rowsperpage";
	return $sql;
	// LEFT JOIN ".OSDB_DP." as dp ON dp.gameid = g.id
	}
	
	
    function getGameInfo($gid){
	$sql = "
	   SELECT winner, dp.gameid, gp.colour, newcolour, original as hero, description, dp.kills, dp.deaths, dp.assists, dp.creepkills, dp.creepdenies, dp.neutralkills, dp.towerkills, dp.gold,  dp.raxkills, dp.courierkills, s.id as userid, s.user_level as admin, s.banned,
	   item1, item2, item3, item4, item5, item6, spoofedrealm, dp.level,
	   it1.icon as itemicon1, 
	   it2.icon as itemicon2, 
	   it3.icon as itemicon3, 
	   it4.icon as itemicon4, 
	   it5.icon as itemicon5, 
	   it6.icon as itemicon6, 
	   it1.name as itemname1, 
	   it2.name as itemname2, 
	   it3.name as itemname3, 
	   it4.name as itemname4, 
	   it5.name as itemname5, 
	   it6.name as itemname6, 
	   leftreason, 
	   gp.left, 
	   gp.name as name, 
	   gp.ip as ip,
	   b.name as banname, g.alias_id
	   FROM ".OSDB_DP." AS dp 
	   LEFT JOIN ".OSDB_GP." AS gp ON gp.gameid = dp.gameid and dp.colour = gp.colour 
	   LEFT JOIN ".OSDB_DG." AS dg ON dg.gameid = dp.gameid 
	   LEFT JOIN ".OSDB_GAMES." AS g ON g.id = dp.gameid 
	   LEFT JOIN ".OSDB_BANS." as b ON b.name=gp.name
	   LEFT JOIN ".OSDB_HEROES." as f ON hero = heroid
	   LEFT JOIN ".OSDB_ITEMS." as it1 ON it1.itemid = item1
	   LEFT JOIN ".OSDB_ITEMS." as it2 ON it2.itemid = item2
	   LEFT JOIN ".OSDB_ITEMS." as it3 ON it3.itemid = item3
	   LEFT JOIN ".OSDB_ITEMS." as it4 ON it4.itemid = item4
	   LEFT JOIN ".OSDB_ITEMS." as it5 ON it5.itemid = item5
	   LEFT JOIN ".OSDB_ITEMS." as it6 ON it6.itemid = item6
	   LEFT JOIN ".OSDB_STATS." as s ON (s.player) = (gp.name) AND s.alias_id = g.alias_id
	   WHERE dp.gameid='".(int)$gid."'
	   GROUP by gp.name
	   ORDER BY newcolour";
	   return $sql;
	}

	function getSentScourWon(){
	$sql = "SELECT COUNT(*) as total, 
          SUM(case when(dg.winner = 1) then 1 else 0 end) as sentinelWon,
		  SUM(case when(dg.winner = 2) then 1 else 0 end) as scourgeWon,
		  SUM(case when(dg.winner = 0) then 1 else 0 end) as draw 
		  FROM ".OSDB_DG." as dg 
		  WHERE dg.winner = 1 OR dg.winner = 2 OR dg.winner = 0
		  LIMIT 1";
		  
		  return $sql;
	}
	
	function getGamesSummary($who){
	$sql = "SELECT 
	SUM(kills) as Kills,
	SUM(deaths) as Deaths,
	SUM(creepkills) as CreepKills,
	SUM(creepdenies) as CreepDenies,
	SUM(towerkills) as towerkills,
	SUM(raxkills) as raxkills,
	SUM(courierkills) as courierkills,
	SUM(assists) as Assists
	FROM ".OSDB_DP." as dp 
	LEFT JOIN ".OSDB_DG." as dg ON dg.gameid = dp.gameid
	WHERE dg.winner = '".(int)$who."' AND  dg.winner != 0 LIMIT 1";
		  
		  return $sql;
	}
	
	  /////////////////////////////////////////////////////////////////
	 //                          ITEMS                              //
	/////////////////////////////////////////////////////////////////
	function getMostUsedHeroByItem($heroid, $itemid, $tot, $itemName ) {

	//FIND AND CHECK ALL GROUPED ITEMS 
       if (
	       !strstr($itemName,"Aghanim") 
	   AND !strstr($itemName,"Armlet of Mordiggian") 
	   AND !strstr($itemName,"Black King Bar") 
	   AND !strstr($itemName,"Dagon Lev")
	   AND !strstr($itemName,"Diffusal Blade")
	   AND !strstr($itemName,"Divine Rapier")
	   AND !strstr($itemName,"Bottle")
	   AND !strstr($itemName,"Linken")
	   AND !strstr($itemName,"Power Treads")
	   AND !strstr($itemName,"Monkey King Bar")
	   AND !strstr($itemName,"Eye of Skadi")
	   AND !strstr($itemName,"Orb of Venom")
	   AND !strstr($itemName,"Necronomicon Lev")
	   AND !strstr($itemName,"Urn of Shadows")
	   AND !strstr($itemName,"Dust of Appearance")
	   AND !strstr($itemName,"s Dagger")
	   AND !strstr($itemName,"Heart of Tarrasque")
	   AND !strstr($itemName,"Radiance")
	   )
	{
	$sql = "SELECT COUNT(*) as total, dp.item1,dp.item2, dp.item3, dp.item4, dp.item5, dp.item6, 
	dp.hero, h.heroid, h.description as heroname 
	FROM ".OSDB_DP." as dp 
	LEFT JOIN ".OSDB_HEROES." as h ON h.original = dp.hero AND h.summary != '-'
	WHERE dp.hero = '$heroid' AND dp.hero !=''
	OR dp.item1 = '$itemid' 
	OR dp.item2 = '$itemid'  
	OR dp.item3 = '$itemid'
	OR dp.item4 = '$itemid'
	OR dp.item5 = '$itemid'
	OR dp.item6 = '$itemid' 
	GROUP BY dp.hero 
	ORDER BY count(*) DESC LIMIT $tot";}
	else {
	if (strstr($itemName,"Aghanim"))           $itemName = "Aghanim";
	if (strstr($itemName,"Black King Bar"))    $itemName = "Black King Bar";
	if (strstr($itemName,"Dagon"))             $itemName = "Dagon";
	if (strstr($itemName,"Diffusal Blade"))    $itemName = "Diffusal Blade";
	if (strstr($itemName,"Divine Rapier"))     $itemName = "Divine Rapier";
	if (strstr($itemName,"Bottle"))            $itemName = "Bottle";
	if (strstr($itemName,"Linken"))            $itemName = "Linken";
	if (strstr($itemName,"Power Treads"))      $itemName = "Power Treads";
	if (strstr($itemName,"Monkey King Bar"))   $itemName = "Monkey King Bar";
	if (strstr($itemName,"Eye of Skadi"))      $itemName = "Eye of Skadi";
	if (strstr($itemName,"Orb of Venom"))      $itemName = "Orb of Venom";
	if (strstr($itemName,"Necronomicon Lev"))  $itemName = "Necronomicon Lev";
	if (strstr($itemName,"Urn of Shadows"))    $itemName = "Urn of Shadows";
	if (strstr($itemName,"Dust of Appearance"))   $itemName = "Dust of Appearance";
	if (strstr($itemName,"s Dagger"))             $itemName = "s Dagger";
	if (strstr($itemName,"Armlet of Mordiggian")) $itemName = "Armlet of Mordiggian";
	if (strstr($itemName,"Heart of Tarrasque"))   $itemName = "Heart of Tarrasque";
	if (strstr($itemName,"Radiance"))          $itemName = "Radiance";
	
	$sql = "SELECT COUNT(*) as total, dp.item1,dp.item2, dp.item3, dp.item4, dp.item5, dp.item6, dp.hero, h.heroid, h.description as heroname, it.name, it.itemid
	FROM ".OSDB_DP." as dp 
	LEFT JOIN ".OSDB_HEROES." as h ON h.original = dp.hero AND h.summary != '-'
	LEFT JOIN ".OSDB_ITEMS." as it  ON it.name  LIKE ('%$itemName%') AND  it.item_info!=''  AND (it.itemid = dp.item1)  
	LEFT JOIN ".OSDB_ITEMS." as it2 ON it2.name LIKE ('%$itemName%') AND  it2.item_info!='' AND (it2.itemid = dp.item2) 
	LEFT JOIN ".OSDB_ITEMS." as it3 ON it3.name LIKE ('%$itemName%') AND  it3.item_info!='' AND (it3.itemid = dp.item3) 
	LEFT JOIN ".OSDB_ITEMS." as it4 ON it4.name LIKE ('%$itemName%') AND  it4.item_info!='' AND (it4.itemid = dp.item4) 
	LEFT JOIN ".OSDB_ITEMS." as it5 ON it5.name LIKE ('%$itemName%') AND  it5.item_info!='' AND (it5.itemid = dp.item5) 
	LEFT JOIN ".OSDB_ITEMS." as it6 ON it6.name LIKE ('%$itemName%') AND  it6.item_info!='' AND (it6.itemid = dp.item6) 
	WHERE dp.hero = '$heroid' AND dp.hero !='' 
    OR it.name LIKE ('%$itemName%') OR it2.name LIKE ('%$itemName%') OR it3.name LIKE ('%$itemName%') 
	OR it4.name LIKE ('%$itemName%') OR it5.name LIKE ('%$itemName%') OR it6.name LIKE ('%$itemName%')
	GROUP BY dp.hero 
	ORDER BY count(*) DESC,  dp.hero DESC LIMIT $tot";
	  }
	
	  return $sql;
	}
	
    function getHero($heroid) {
	$text = "SELECT * FROM ".OSDB_HEROES." WHERE original='".$heroid."' AND summary!= '-' LIMIT 1";
	return $text;
	}

    function getHeroInfo($heroid, $minPlayedRatio, $minPlayedRatio) {
	$text = "SELECT *, 
	(kills*1.0/deaths) as kdratio, 
	(wins*1.0/losses) as winratio, 
	summary, 
	skills, 
	stats 
	FROM 
	(SELECT count(*) as totgames, 
	original,
	SUM(case when(((dg.winner = 1 and dp.newcolour < 6) 
	or (dg.winner = 2 and dp.newcolour > 6)) 
	AND gp.`left`/g.duration >= $minPlayedRatio) then 1 else 0 end) as wins, 
	SUM(case when(((dg.winner = 2 and dp.newcolour < 6) 
	or (dg.winner = 1 and dp.newcolour > 6)) 
	AND gp.`left`/g.duration >= $minPlayedRatio) then 1 else 0 end) as losses, 
	SUM(kills) as kills, 
	SUM(deaths) as deaths, 
	SUM(assists) as assists, 
	SUM(creepkills) as creepkills, 
	SUM(creepdenies) as creepdenies, 
	SUM(neutralkills) as neutralkills, 
	SUM(towerkills) as towerkills, 
	SUM(raxkills) as raxkills, 
	SUM(courierkills) as courierkills, 
	g.alias_id
	FROM ".OSDB_DP." AS dp 
	LEFT JOIN ".OSDB_HEROES." as b ON hero = heroid 
	LEFT JOIN ".OSDB_DG." as dg ON dg.gameid = dp.gameid
	LEFT JOIN ".OSDB_GP." as gp ON gp.gameid = dp.gameid and dp.colour = gp.colour 
	LEFT JOIN ".OSDB_GAMES." as g ON gp.gameid = g.id 
	WHERE original='$heroid' 
	GROUP BY original) as y 
	LEFT JOIN ".OSDB_HEROES." as h ON y.original = h.heroid LIMIT 1";
	
	return $text;
	}
	
	function getHeroHistoryCount($heroid) {
	$text = "
	SELECT COUNT(*) AS  count 
	 FROM (
	       SELECT name 
	       FROM ".OSDB_DP." AS dp 
	       LEFT JOIN ".OSDB_GP." AS gp ON gp.gameid = dp.gameid and dp.colour = gp.colour 
	       LEFT JOIN ".OSDB_DG." AS dg ON dg.gameid = dp.gameid 
	       LEFT JOIN ".OSDB_GAMES." AS g ON g.id = dp.gameid 
	       LEFT JOIN ".OSDB_HEROES." as e ON dp.hero = heroid 
	       WHERE heroid = '$heroid')as t LIMIT 1";
 
	return $text;
	}
	
	//HERO MOST USED ITEMS
	function getHeroItem1($heroid) {
	$sql = "SELECT count(*) as total, dp.item1, i.icon , i.name , i.itemid 
	FROM ".OSDB_DP." as dp
	LEFT JOIN ".OSDB_ITEMS." as i ON i.itemid = dp.item1
	WHERE hero = '$heroid' 
	AND dp.item1 != '\0\0\0\0' 
    AND dp.item1 != '' 
	GROUP BY item1 having count(*) > 1 
	ORDER BY count(*) DESC LIMIT 2";
	return $sql;
	}
	function getHeroItem2($heroid,$mostItem1,$mostItem11) {
	$sql = "SELECT count(*) as total, dp.item2, i.icon , i.name , i.itemid
	FROM ".OSDB_DP." as dp
	LEFT JOIN ".OSDB_ITEMS." as i ON i.itemid = dp.item2
	WHERE hero = '$heroid' 
	AND dp.item2 != '\0\0\0\0' 
    AND dp.item2 != '' 
	AND dp.item2 != '$mostItem1' AND dp.item2 != '$mostItem11'
	GROUP BY item2 having count(*) > 1 
	ORDER BY count(*) DESC LIMIT 2";
	return $sql;
	}
	function getHeroItem3($heroid,$mostItem1,$mostItem11,$mostItem2,$mostItem22) {
	$sql = "SELECT count(*) as total, dp.item3, i.icon , i.name , i.itemid
	FROM ".OSDB_DP." as dp 
	LEFT JOIN ".OSDB_ITEMS." as i ON i.itemid = dp.item3
	WHERE hero = '$heroid' 
	AND dp.item3 != '\0\0\0\0' 
    AND dp.item3 != '' 
	AND dp.item3 != '$mostItem1' AND dp.item3 != '$mostItem11'
	AND dp.item3 != '$mostItem2' AND dp.item3 != '$mostItem22'
	GROUP BY item3 having count(*) > 1 
	ORDER BY count(*) DESC LIMIT 2";
	return $sql;
	}
	function getHeroItem4($heroid,$mostItem1,$mostItem11,$mostItem2,$mostItem22,$mostItem3,$mostItem33) {
	$sql = "SELECT count(*) as total, dp.item4, i.icon , i.name , i.itemid
	FROM ".OSDB_DP." as dp
	LEFT JOIN ".OSDB_ITEMS." as i ON i.itemid = dp.item4
	WHERE hero = '$heroid' 
	AND dp.item4 != '\0\0\0\0' 
    AND dp.item4 != '' 
	AND dp.item4 != '$mostItem1' AND dp.item4 != '$mostItem11'
	AND dp.item4 != '$mostItem2' AND dp.item4 != '$mostItem22'
	AND dp.item4 != '$mostItem3' AND dp.item4 != '$mostItem33'
	GROUP BY item4 having count(*) > 1 
	ORDER BY count(*) DESC LIMIT 2";
	return $sql;
	}
	function getHeroItem5($heroid,$mostItem1,$mostItem11,$mostItem2,$mostItem22,$mostItem3,$mostItem33,$mostItem4,$mostItem44) {
	$sql = "SELECT count(*) as total, dp.item5, i.icon , i.name , i.itemid
	FROM ".OSDB_DP." as dp
	LEFT JOIN ".OSDB_ITEMS." as i ON i.itemid = dp.item5
	WHERE hero = '$heroid' 
	AND dp.item5 != '\0\0\0\0' 
    AND dp.item5 != '' 
	AND dp.item5 != '$mostItem1' AND dp.item5 != '$mostItem11'
	AND dp.item5 != '$mostItem2' AND dp.item5 != '$mostItem22'
	AND dp.item5 != '$mostItem3' AND dp.item5 != '$mostItem33'
	AND dp.item5 != '$mostItem4' AND dp.item5 != '$mostItem44'
	GROUP BY item5 having count(*) > 1 
	ORDER BY count(*) DESC LIMIT 2";
	return $sql;
	}
	function getHeroItem6($heroid,$mostItem1,$mostItem11,$mostItem2,$mostItem22,$mostItem3,$mostItem33,$mostItem4,$mostItem44,$mostItem5,$mostItem55) {
	$sql = "SELECT count(*) as total, dp.item6, i.icon , i.name , i.itemid
	FROM ".OSDB_DP." as dp
	LEFT JOIN ".OSDB_ITEMS." as i ON i.itemid = dp.item6
	WHERE hero = '$heroid' 
	AND dp.item6 != '\0\0\0\0' 
    AND dp.item6 != '' 
	AND dp.item6 != '$mostItem1' AND dp.item6 != '$mostItem11'
	AND dp.item6 != '$mostItem2' AND dp.item6 != '$mostItem22'
	AND dp.item6 != '$mostItem3' AND dp.item6 != '$mostItem33'
	AND dp.item6 != '$mostItem4' AND dp.item6 != '$mostItem44'
	AND dp.item6 != '$mostItem5' AND dp.item6 != '$mostItem55'
	GROUP BY item6 having count(*) > 1 
	ORDER BY count(*) DESC LIMIT 2";
	return $sql;
	}
	
	
	function getHeroHistory($minPlayedRatio,$heroid,$order,$sortdb,$offset, $rowsperpage,$LEAVER) {
	$text = "
	SELECT CASE WHEN (kills = 0) THEN 0 WHEN (deaths = 0) then 1000 ELSE (kills*1.0/deaths*1.0) end as kdratio, 
	dp.gameid as gameid, 
	g.gamename, 
	dg.winner,
	kills, 
	deaths,
	assists, 
	creepkills, 
	neutralkills, 
	creepdenies, 
	towerkills, 
	raxkills, 
	courierkills, 
	b.name as name, 
	b.ip as ip,
	f.name as banname, 
	CASE when(gamestate = '17') then 'PRIV' else 'PUB' end as type, 
	CASE when ((winner=1 AND newcolour < 6) 
	or (winner=2 and newcolour > 5)) 
	AND b.`left`/g.duration >= $minPlayedRatio  then 'WON' when ((winner=2 AND newcolour < 6) 
	or (winner=1 and newcolour > 5)) 
	AND b.`left`/g.duration >= $minPlayedRatio  then 'LOST' when  winner=0 then 'DRAW' else '$LEAVER' end as result, 
	g.alias_id 
	FROM ".OSDB_DP." AS dp 
	LEFT JOIN ".OSDB_GP." AS b ON b.gameid = dp.gameid 
	AND dp.colour = b.colour 
	LEFT JOIN ".OSDB_DG." AS dg ON dg.gameid = dp.gameid
	LEFT JOIN ".OSDB_GAMES." AS g ON g.id = dp.gameid 
	LEFT JOIN ".OSDB_HEROES." as e ON dp.hero = heroid 
	LEFT JOIN ".OSDB_BANS." as f ON b.name = f.name 
	WHERE original = '$heroid' 
	ORDER BY $order $sortdb 
	LIMIT $offset, $rowsperpage";
 
	return $text;
	}
	
	function getUserGameHistory($LEAVER,$username,$order,$sortdb,$offset, $rowsperpage,$minPlayedRatio) {
	$text = "SELECT 
	winner, 
	dp.gameid as id, 
	newcolour, 
	datetime, 
	gamename, 
	original, 
	description, 
	kills, 
	deaths, 
	assists, 
	creepkills, 
	creepdenies, 
	neutralkills, 
	name, 
    CASE when(gamestate = '17') then 'PRIV' else 'PUB' end as type,
    CASE WHEN (kills = 0) THEN 0 WHEN (deaths = 0) then 1000 ELSE (kills*1.0/deaths) end as kdratio,
    CASE when ((winner=1 and newcolour < 6) 
	or (winner=2 and newcolour > 5)) 
	AND gp.`left`/g.duration >= $minPlayedRatio  then 'WON' when ((winner=2 and newcolour < 6) 
	or (winner=1 and newcolour > 5)) 
	AND gp.`left`/g.duration >= $minPlayedRatio  then 'LOST' when  winner=0 then 'DRAW' else '$LEAVER' end as outcome, 
	g.alias_id
	FROM ".OSDB_DP." AS dp 
	LEFT JOIN ".OSDB_GP." AS gp ON gp.gameid = dp.gameid and dp.colour = gp.colour 
	LEFT JOIN ".OSDB_DG." AS dg ON dg.gameid = dp.gameid 
	LEFT JOIN ".OSDB_GAMES." AS g ON g.id = dp.gameid 
	LEFT JOIN ".OSDB_HEROES." as e ON dp.hero = heroid 
	WHERE (name) = ('".$username."') and original <> 'NULL' 
	ORDER BY $order $sortdb, g.id $sortdb 
	LIMIT $offset, $rowsperpage";
 
	return $text;
	}
	
	
	function longGameWon($username, $limit = 1) {
	$sql = "SELECT (dg.min * 60 + dg.sec) AS longgamewon, 
	dg.gameid,
	g.gamename, 
	g.duration, 
	dp.kills, 
	dp.deaths, 
	dp.creepkills, 
    dp.creepdenies,	
	dp.assists, 
	dp.neutralkills,
	dp.newcolour, 
	g.alias_id 
			FROM ".OSDB_GP." as gp
			LEFT JOIN ".OSDB_GAMES." as g ON g.id = gp.gameid 
			LEFT JOIN ".OSDB_DP." as dp ON dp.gameid = g.id AND dp.colour = gp.colour 
			LEFT JOIN ".OSDB_DG." as dg ON g.id = dg.gameid 
			WHERE (name) = ('".$username."')
			AND (
					(
						winner = 1 
						AND dp.newcolour >= 1
						AND dp.newcolour <= 5
					) 
					OR
					(
						winner = 2 
						AND dp.newcolour >= 7 
						AND dp.newcolour <= 11
					)
				)
			GROUP BY dg.gameid
			ORDER BY longgamewon DESC
			LIMIT $limit";
			return $sql;
	}
	
	function fastGameWon($username, $limit = 1) {
	$sql = "SELECT dg.min * 60 + dg.sec AS fastgamewon, 
	dg.gameid, 
	g.gamename, 
	g.duration, 
	dp.kills, 
	dp.deaths, 
	dp.creepkills, 
    dp.creepdenies,	
	dp.assists, 
	dp.neutralkills,
	dp.newcolour, 
	g.alias_id 
			FROM ".OSDB_GP." as gp
			LEFT JOIN ".OSDB_GAMES." as g ON g.id = gp.gameid 
			LEFT JOIN ".OSDB_DP." as dp ON dp.gameid = g.id AND dp.colour = gp.colour 
			LEFT JOIN ".OSDB_DG." as dg ON g.id = dg.gameid 
			WHERE (name) = ('".$username."')
			AND (
					(
						winner = 1 
						AND dp.newcolour >= 1
						AND dp.newcolour <= 5
					) 
					OR
					(
						winner = 2 
						AND dp.newcolour >= 7 
						AND dp.newcolour <= 11
					)
				)
			GROUP BY dg.gameid
			ORDER BY fastgamewon ASC
			LIMIT $limit";
			return $sql;
	}
	
	function GetGameDurations($username) {
	$sql = "SELECT 
	MIN(datetime), 
	MIN(loadingtime), 
	MAX(loadingtime), 
	AVG(loadingtime), 
	MIN(`left`), 
	MAX(`left`), 
	AVG(`left`), 
	SUM(`left`), 
	g.alias_id 
	FROM ".OSDB_GP." as gp
	LEFT JOIN ".OSDB_GAMES." as g ON g.id=gp.gameid 
	LEFT JOIN ".OSDB_DP." as dp ON dp.gameid=g.id 
	AND dp.colour=gp.colour 
	LEFT JOIN ".OSDB_DG." as dg ON g.id=dg.gameid 
	WHERE (name)=('".$username."') LIMIT 1";
	
	return $sql;
	}
	
function GetMostPlayedHero($username, $limit = 1) {
   $username = strtolower($username);
   $sql = "SELECT SUM(`left`) AS timeplayed, original, description, 
	COUNT(*) AS played, g.alias_id 
	FROM ".OSDB_GP." as gp
	LEFT JOIN ".OSDB_GAMES." as g ON g.id=gp.gameid 
	LEFT JOIN ".OSDB_DP." as dp ON dp.gameid=g.id 
	AND dp.colour=gp.colour  
	LEFT JOIN ".OSDB_DG." as dg ON g.id=dg.gameid 
    JOIN ".OSDB_HEROES." on hero = heroid 
	WHERE (name)=('".$username."')
	GROUP BY original 
	ORDER BY played DESC LIMIT ".$limit."";
	return $sql;
}

function GetMostKillsHero($username, $limit = 1) {
   $username = strtolower($username);
   $sql = "SELECT 
	original, description, max(kills) as maxkills, g.id as gameid, g.alias_id
	FROM ".OSDB_DP." as dp
	LEFT JOIN ".OSDB_GP." AS gp ON gp.gameid = dp.gameid AND dp.colour = gp.colour 
	LEFT JOIN ".OSDB_HEROES." on hero = heroid 
	LEFT JOIN ".OSDB_GAMES." as g ON g.id = dp.gameid
	WHERE (name)= ('".$username."') 
	GROUP BY kills 
	ORDER BY maxkills DESC LIMIT ".$limit."";
	return $sql;
}

function GetMostDeathsHero($username, $limit = 1) {
   $username = strtolower($username);
   $sql = "SELECT original, description, max(deaths) as maxdeaths, g.id as gameid
	FROM ".OSDB_DP." AS a 
	LEFT JOIN ".OSDB_GP." AS b ON b.gameid = a.gameid and a.colour = b.colour 
	LEFT JOIN ".OSDB_HEROES." on hero = heroid 
	LEFT JOIN ".OSDB_GAMES." as g ON g.id = a.gameid
	WHERE (name) = ('".$username."') 
	GROUP BY deaths 
	ORDER BY maxdeaths DESC 
	LIMIT ".$limit."";
	return $sql;
}

function GetMostAssistsHero($username, $limit = 1) {
   $username = strtolower($username);
   $sql = "SELECT original, description, max(assists) as maxassists, g.id as gameid, g.alias_id
	FROM ".OSDB_DP." AS a 
	LEFT JOIN ".OSDB_GP." AS b ON b.gameid = a.gameid and a.colour = b.colour 
	LEFT JOIN ".OSDB_HEROES." on hero = heroid 
	LEFT JOIN ".OSDB_GAMES." as g ON g.id = a.gameid
	WHERE (name) = ('".$username."') 
	GROUP BY assists 
	ORDER BY maxassists DESC 
	LIMIT ".$limit."";
	return $sql;
}

function GetMostWinsHero($username, $limit = 1) {
   $username = strtolower($username);
   $sql = "SELECT original, description, COUNT(*) as wins, g.id as gameid, g.alias_id
	FROM ".OSDB_GP." as gp
	LEFT JOIN ".OSDB_GAMES." as g ON g.id=gp.gameid 
	LEFT JOIN ".OSDB_DP." as dp ON dp.gameid=g.id 
	AND dp.colour=gp.colour 
	LEFT JOIN ".OSDB_DG." as dg ON g.id=dg.gameid 
	LEFT JOIN ".OSDB_HEROES." on hero = heroid 
	WHERE (name) = ('".$username."') 
	AND((winner=1 
	AND dp.newcolour>=1 
	AND dp.newcolour<=5) 
	OR (winner=2 
	AND dp.newcolour>=7 
	AND dp.newcolour<=11)) 
	GROUP BY original ORDER BY wins DESC LIMIT ".$limit."";
	return $sql;
}

function GetMostLossesHero($username, $limit = 1) {
   $username = strtolower($username);
   $sql = "SELECT original, description, COUNT(*) as losses 
	FROM ".OSDB_GP." as gp
	LEFT JOIN ".OSDB_GAMES." as g ON g.id=gp.gameid 
	LEFT JOIN ".OSDB_DP." as dp ON dp.gameid=g.id 
	AND dp.colour=gp.colour 
	LEFT JOIN ".OSDB_DG." as dg ON g.id=dg.gameid 
	LEFT JOIN ".OSDB_HEROES." on hero = heroid 
	WHERE (name) = ('".$username."') 
	AND((winner=2 AND dp.newcolour>=1 AND dp.newcolour<=5) OR (winner=1 AND dp.newcolour>=7 AND dp.newcolour<=11)) 
	GROUP BY original ORDER BY losses DESC LIMIT ".$limit."";
	return $sql;
}

function OS_add_custom_field($field_id = "", $field_name = "" , $field_value = "") {

     global $db;
  
     $sth = $db->prepare("SELECT * FROM ".OSDB_CUSTOM_FIELDS." WHERE field_id = :field_id 
     AND field_name = :field_name");
  	 $sth->bindValue(':field_id', $field_id, PDO::PARAM_STR);
	 $sth->bindValue(':field_name', $field_name, PDO::PARAM_STR);
	 $result = $sth->execute();
	
	
  if ( $sth->rowCount()<=0 ) {
    $db->insert( OSDB_CUSTOM_FIELDS, array(
	"field_id" => $field_id,
	"field_name" => $field_name,
	"field_value" => $field_value
	));
  
  } else {
  $update = $db->update(OSDB_CUSTOM_FIELDS, array(
  "field_value" => $field_value
                                ),"field_id = '".$field_id ."' AND field_name = '".$field_name."'" );
  }

}


function OS_delete_custom_field($field_id = "", $field_name = "" , $field_value = "") {

  $db = new db("mysql:host=".OSDB_SERVER.";dbname=".OSDB_DATABASE."", OSDB_USERNAME, OSDB_PASSWORD);
  
  if ( !empty($field_value) ) $sql = " AND field_value = '".$field_value."'"; else $sql = "";
  
  if ( !empty($field_id) AND !empty($field_name) )
  $sth = $db->prepare("DELETE FROM ".OSDB_CUSTOM_FIELDS." 
  WHERE field_id = '".$field_id."' AND field_name = '".$field_name."' $sql");
  
   $result = $sth->execute();

}

//get data from custom field table
function OS_get_custom_field( $field_id = "", $field_name = "" , $field_value = "" ) {
   global $db;
   
   $sth =  $db->prepare("SELECT * FROM ".OSDB_CUSTOM_FIELDS." 
   WHERE field_id = :field_id AND field_name = :field_name ");
   $sth->bindValue(':field_id', $field_id, PDO::PARAM_STR); 
   $sth->bindValue(':field_name', $field_name, PDO::PARAM_STR); 
   
   $result = $sth->execute();
   $row = $sth->fetch(PDO::FETCH_ASSOC);
   $value = $row["field_value"];
   
   return $value;
}

function OS_GetStats( $offset = 0, $row_per_page = 10, $order = 'score DESC', $filter = "" ) {
      
	  $db = new db("mysql:host=".OSDB_SERVER.";dbname=".OSDB_DATABASE."", OSDB_USERNAME, OSDB_PASSWORD);
	  $filter = safeEscape( $filter );
	  $order  = safeEscape( $order );
	  
	  if ( !empty($order) ) $sqlOrder = 'ORDER BY '.$order;
	  
	  if ( is_numeric($offset) AND is_numeric($row_per_page) )
	  $limit = 'LIMIT '.$offset.', '.$row_per_page.' ';
	  else $limit = '';
	  
	  $sth = $db->prepare("SELECT s.id, s.player, s.score, s.games, s.wins, s.losses, s.draw, s.kills, s.deaths, s.assists, s.creeps, s.denies, s.neutrals, s.towers, s.rax, s.banned, s.ip 
	  FROM ".OSDB_STATS." as s 
	  WHERE s.id>=1 $filter
	  $sqlOrder
	  $limit");
	  
	  $result = $sth->execute();
	  
	  		  
	  $c=0;
      $Data = array();
	
	  while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
	   
	    $Data[$c]["id"] = $row["id"]; 
	    $Data[$c]["player"] = $row["player"]; 
	    $Data[$c]["score"] = $row["score"]; 
		$Data[$c]["games"] = $row["games"]; 
		$Data[$c]["wins"] = $row["wins"]; 
		$Data[$c]["losses"] = $row["losses"]; 
		$Data[$c]["draw"] = $row["draw"]; 
		$Data[$c]["kills"] = $row["kills"]; 
		$Data[$c]["deaths"] = $row["deaths"]; 
		$Data[$c]["assists"] = $row["assists"]; 
		$Data[$c]["creeps"] = $row["creeps"]; 
		$Data[$c]["denies"] = $row["denies"]; 
		$Data[$c]["neutrals"] = $row["neutrals"]; 
		$Data[$c]["towers"] = $row["towers"]; 
		$Data[$c]["neutrals"] = $row["neutrals"]; 
		$Data[$c]["rax"] = $row["rax"]; 
		$Data[$c]["banned"] = $row["banned"]; 
		$Data[$c]["ip"] = $row["ip"]; 
		
	    $c++;
	  }
    
	return ( $Data );
}

function OS_GetUserStats( $username = '' ) {
      
	  $db = new db("mysql:host=".OSDB_SERVER.";dbname=".OSDB_DATABASE."", OSDB_USERNAME, OSDB_PASSWORD);
	  
	  if ( !empty($username) ) {
	  
	  $sth = $db->prepare("SELECT s.id, s.player, s.score, s.games, s.wins, s.losses, s.draw, s.kills, s.deaths, s.assists, s.creeps, s.denies, s.neutrals, s.towers, s.rax, s.banned, s.ip 
	  FROM ".OSDB_STATS." as s 
	  WHERE s.id>=1 AND (s.player) = ?
	  LIMIT 1");
	  
	  $sth->bindValue(1, "%".strtolower($username)."%", PDO::PARAM_STR);
	  
	  $result = $sth->execute();	  
	  $c=0;
      $Data = array();
	
	  $row = $sth->fetch(PDO::FETCH_ASSOC);
	   
	    $Data[$c]["id"] = $row["id"]; 
	    $Data[$c]["player"] = $row["player"]; 
	    $Data[$c]["score"] = $row["score"]; 
		$Data[$c]["games"] = $row["games"]; 
		$Data[$c]["wins"] = $row["wins"]; 
		$Data[$c]["losses"] = $row["losses"]; 
		$Data[$c]["draw"] = $row["draw"]; 
		$Data[$c]["kills"] = $row["kills"]; 
		$Data[$c]["deaths"] = $row["deaths"]; 
		$Data[$c]["assists"] = $row["assists"]; 
		$Data[$c]["creeps"] = $row["creeps"]; 
		$Data[$c]["denies"] = $row["denies"]; 
		$Data[$c]["neutrals"] = $row["neutrals"]; 
		$Data[$c]["towers"] = $row["towers"]; 
		$Data[$c]["neutrals"] = $row["neutrals"]; 
		$Data[$c]["rax"] = $row["rax"]; 
		$Data[$c]["banned"] = $row["banned"]; 
		$Data[$c]["ip"] = $row["ip"]; 
    
	return ( $Data );
	}
}

function OS_GetUserStatsByID( $userID = '' ) {
      
	  $db = new db("mysql:host=".OSDB_SERVER.";dbname=".OSDB_DATABASE."", OSDB_USERNAME, OSDB_PASSWORD);
	  
	  if ( is_numeric($userID) ) {
	  
	  $sth = $db->prepare("SELECT s.id, s.player, s.score, s.games, s.wins, s.losses, s.draw, s.kills, s.deaths, s.assists, s.creeps, s.denies, s.neutrals, s.towers, s.rax, s.banned, s.ip 
	  FROM ".OSDB_STATS." as s 
	  WHERE s.id=:userID LIMIT 1");
	  
	  $sth->bindValue(':userID', (int) $userID, PDO::PARAM_INT); 	
      $result = $sth->execute();	  
	  $c=0;
      $Data = array();
	
	  $row = $sth->fetch(PDO::FETCH_ASSOC);
	   
	    $Data[$c]["id"] = $row["id"]; 
	    $Data[$c]["player"] = $row["player"]; 
	    $Data[$c]["score"] = $row["score"]; 
		$Data[$c]["games"] = $row["games"]; 
		$Data[$c]["wins"] = $row["wins"]; 
		$Data[$c]["losses"] = $row["losses"]; 
		$Data[$c]["draw"] = $row["draw"]; 
		$Data[$c]["kills"] = $row["kills"]; 
		$Data[$c]["deaths"] = $row["deaths"]; 
		$Data[$c]["assists"] = $row["assists"]; 
		$Data[$c]["creeps"] = $row["creeps"]; 
		$Data[$c]["denies"] = $row["denies"]; 
		$Data[$c]["neutrals"] = $row["neutrals"]; 
		$Data[$c]["towers"] = $row["towers"]; 
		$Data[$c]["neutrals"] = $row["neutrals"]; 
		$Data[$c]["rax"] = $row["rax"]; 
		$Data[$c]["banned"] = $row["banned"]; 
		$Data[$c]["ip"] = $row["ip"]; 
    
	return ( $Data );
	}
}

function OS_MostPlayedHero( $username) {

   	$sql = "SELECT SUM(`left`) AS timeplayed, original, description, 
	COUNT(*) AS played 
	FROM ".OSDB_GP." as gp 
	LEFT JOIN ".OSDB_GAMES." as g ON g.id=gp.gameid 
	LEFT JOIN ".OSDB_DP." as dp ON dp.gameid=g.id 
	AND dp.colour=gp.colour  
	LEFT JOIN ".OSDB_DG." as dg ON g.id=dg.gameid 
    JOIN ".OSDB_HEROES." on hero = heroid 
	WHERE (name)=('".safeEscape($username)."')
	GROUP BY original 
	ORDER BY played DESC LIMIT 1";
	
	return $sql;
}

function OS_AddLog($admin = "", $data = "", $time = "" ) {
   global $SaveAdminLogs; 
   
   if ($SaveAdminLogs == 1) {
  $time = date("Y-m-d H:i:00", time());
  
  if ( !empty($data) ) {
    
	global $db;
	
	    $db->insert( OSDB_ADMIN_LOG, array(
	    "log_time" => $time,
	    "log_admin" => $admin,
	    "log_data" => $data
                                 ));
								 
	 $lastID = $db->lastInsertId(); 
	
	 return $lastID;
    }
  }
}

?>