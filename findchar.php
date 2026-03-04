<?php
require('auth.php');
$rpp  = 50;

require_once('dbGmAdm.php');
mysql_select_db($database_dbGmAdm, $dbGmAdm);

$wid=$_REQUEST[wid];
if($HTTP_GET_VARS[wid])$wid=$HTTP_GET_VARS[wid];
if($wid=="")
{
	$wid=$HTTP_SESSION_VARS['wid'];
}
else
{
	$HTTP_SESSION_VARS['wid']=$wid;
}

if(!has_perm($HTTP_SESSION_VARS['userid'], $wid, "gmchar", "") )
{
	$HTTP_SESSION_VARS['wid'] = '';
	die("Access denied.");
}

$view = $_REQUEST[view];

$display1 = $display2 = $display4 = $display5 = "none";

switch($HTTP_GET_VARS['a'])
{
	case 'wc':
		if($_REQUEST[wid]!='')$HTTP_SESSION_VARS['wc']=$_REQUEST[wid];
		header("Location: findchar.php");
		exit;
		break;
	case 'f':
		//if($HTTP_SERVER_VARS['REQUEST_METHOD'] == "POST" || 1)

		if($_REQUEST[sort] > "0" && $_REQUEST[sort] < "9")
		{

			//default

			//user request
			if($_REQUEST[rpp] >0) $rpp = $_REQUEST[rpp];
			$ops = array("", "=", "<>", ">", "<", ">=");
			$sorts = array("CharacterName", "s.Level", "s.Level DESC", "CreateDate", "CreateDate DESC", "s.RedPoints", "s.RedPoints DESC", "s.HeroPoints", "s.HeroPoints DESC");
			$op1 = $_REQUEST[op1];
			$op2 = $_REQUEST[op2];
			$op4 = $_REQUEST[op4];
			$op5 = $_REQUEST[op5];
			$sort = $_REQUEST[sort];
			$clan = $_REQUEST[clan];
			$rank = $_REQUEST[rank];
			if($op1== 5) $display1="" ;
			if($op2== 5) $display2="" ;
			if($op4== 5) $display4="" ;
			if($op5== 5) $display5="" ;
			eval("\$selected1_$op1 = 'SELECTED';");
			eval("\$selected2_$op2 = 'SELECTED';");
			eval("\$selected4_$op4 = 'SELECTED';");
			eval("\$selected5_$op5 = 'SELECTED';");
			eval("\$selected3_$sort = 'SELECTED';");
			eval("\$selected6_$clan = 'SELECTED';");
			eval("\$selected7_$rank = 'SELECTED';");
			$op1 = $ops[$op1];	//levl
			$op2 = $ops[$op2];	//create dt
			$op4 = $ops[$op4];	//red pk
			$op5 = $ops[$op5];	//hero pnt
			$sort = $sorts[$sort];
			$charname = ($_REQUEST[charname]);
			$level1 = htmlentities($_REQUEST[level1]);
			$level2 = htmlentities($_REQUEST[level2]);
			$dt1 = htmlentities($_REQUEST[dt1]);
			$dt2 = htmlentities($_REQUEST[dt2]);
			$redp1 = htmlentities($_REQUEST[redp1]);
			$redp2 = htmlentities($_REQUEST[redp2]);
			$herop1 = htmlentities($_REQUEST[herop1]);
			$herop2 = htmlentities($_REQUEST[herop2]);
			$y1 = $_REQUEST[y1];
			$m1 = $_REQUEST[m1];
			$d1 = $_REQUEST[d1];
			$y2 = $_REQUEST[y2];
			$m2 = $_REQUEST[m2];
			$d2 = $_REQUEST[d2];
			$itemid = $_REQUEST[itemid];
			$experience = $_REQUEST[experience];
			$dt1 = "$y1-$m1-$d1";
			$dt2 = "$y2-$m2-$d2";

			if($op1 == ">="){
				$cond_lvl = "AND s.Level $op1 '$level1' AND s.Level <= '$level2'";
			}elseif($op1 != "") {
				$cond_lvl = "AND s.Level $op1 '$level1'";
			}

			if($op2 == ">="){
				$cond_dt = "AND DATE_FORMAT(FROM_UNIXTIME(CreateDate),'%Y-%m-%d')  $op2 '$dt1' AND DATE_FORMAT(FROM_UNIXTIME(CreateDate),'%Y-%m-%d')  <= '$dt2' ";
			}elseif($op2 != ""){
				$cond_dt = "AND DATE_FORMAT(FROM_UNIXTIME(CreateDate),'%Y-%m-%d')  $op2 '$dt1'";
			}

			if($op4 == ">="){
				$cond_redpt = "AND s.RedPoints $op4 '$redp1' AND s.RedPoints <= '$redp2'";
			}elseif($op4 != ""){
				$cond_redpt = "AND s.RedPoints $op4 '$redp1'";
			}

			if($op5 == ">="){
				$cond_heropt = "AND s.HeroPoints $op5 '$herop1' AND s.HeroPoints <= '$herop2'";
			}elseif($op5 != ""){
				$cond_heropt = "AND s.HeroPoints $op5 '$herop1'";
			}

			if(strlen($clan) > 0){
				$cond_clan = "AND i.ClanID='$clan'";
			}

			if(strlen($rank) > 0){
				$cond_rank = "AND i.job='$rank'";
			}


			if($_REQUEST[charname]!="")
			{
				$hex_charnm = hexstring(U8toU16(stripslashes($_REQUEST[charname])));
				$cond_charnm = " AND LOCATE(0x{$hex_charnm}, CharacterName)=1";
			}

                        if($_REQUEST[experience]!="")
                        {
				$cond_exp = "AND s.MulPerc='$experience'";
                        }


			if($wid)
			{
				$rsSvr = mysql_query("SELECT * FROM gm_server WHERE id='{$wid}'", $dbGmAdm) or die(mysql_error());
				$row_rsSvr=mysql_fetch_assoc($rsSvr) or die("invalid worldcontroller");
				mysql_free_result($rsSvr);
				$dbWc = mysql_pconnect($row_rsSvr[ip],$row_rsSvr[dbuser],$row_rsSvr[dbpasswd]);
				if(!$dbWc)
				{
					$HTTP_SESSION_VARS['wid'] = "";
					echo "
						<script>
						function reload(){location.href='findchar.php'}
						setTimeout(reload, 3000)
						</script>
						<p><font color=red>Page will be redirected in 3 seconds.</p>
					";
					die(mysql_error());
				}
				mysql_select_db($row_rsSvr[db], $dbWc);

$droptable = "DROP TABLE IF EXISTS `pcharstats_all`";
$updatesql = "
CREATE TABLE IF NOT EXISTS `pcharstats_all` (
	`CharID` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`Strength` SMALLINT(5) UNSIGNED NULL DEFAULT '0',
	`Constitution` SMALLINT(5) UNSIGNED NULL DEFAULT '0',
	`Agility` SMALLINT(5) UNSIGNED NULL DEFAULT '0',
	`Mind` SMALLINT(5) UNSIGNED NULL DEFAULT '0',
	`Perception` SMALLINT(5) UNSIGNED NULL DEFAULT '0',
	`AttackRating` SMALLINT(5) UNSIGNED NULL DEFAULT '0',
	`DefenseRating` SMALLINT(5) UNSIGNED NULL DEFAULT '0',
	`BaseDamage` SMALLINT(5) UNSIGNED NULL DEFAULT '0',
	`MaxHP` SMALLINT(5) UNSIGNED NULL DEFAULT '0',
	`CurrHP` SMALLINT(5) UNSIGNED NULL DEFAULT '0',
	`MaxChi` SMALLINT(5) UNSIGNED NULL DEFAULT '0',
	`CurrChi` SMALLINT(5) UNSIGNED NULL DEFAULT '0',
	`HPRegen` SMALLINT(5) UNSIGNED NULL DEFAULT '0',
	`ChiRegen` SMALLINT(5) UNSIGNED NULL DEFAULT '0',
	`FireResist` SMALLINT(6) NULL DEFAULT '0',
	`ColdResist` SMALLINT(6) NULL DEFAULT '0',
	`PoisonResist` SMALLINT(6) NULL DEFAULT '0',
	`LightningResist` SMALLINT(6) NULL DEFAULT '0',
	`PhysicalResist` SMALLINT(6) NULL DEFAULT '0',
	`MovementMode` TINYINT(3) UNSIGNED NULL DEFAULT '0',
	`Experience` BIGINT(10) UNSIGNED NULL DEFAULT '0',
	`Level` SMALLINT(5) UNSIGNED NULL DEFAULT '0',
	`CharGold` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`StashGold` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`Prestige` SMALLINT(6) NULL DEFAULT '0',
	`AttributePoints` SMALLINT(5) UNSIGNED NULL DEFAULT '0',
	`StancePoints` SMALLINT(5) UNSIGNED NULL DEFAULT '0',
	`PowerPoints` SMALLINT(5) UNSIGNED NULL DEFAULT '0',
	`SkillPoints` SMALLINT(5) UNSIGNED NULL DEFAULT '0',
	`EntityState` TINYINT(3) UNSIGNED NULL DEFAULT '0',
	`ActiveWeapon` INT(10) UNSIGNED NULL DEFAULT '0',
	`ActiveWeaponSlot` TINYINT(3) UNSIGNED NULL DEFAULT '0',
	`AttackMode` TINYINT(3) UNSIGNED NULL DEFAULT '0',
	`ElementalAdv` TINYINT(3) UNSIGNED NULL DEFAULT '0',
	`Gender` TINYINT(3) UNSIGNED NULL DEFAULT '0',
	`MinUnarmedDamage` TINYINT(3) UNSIGNED NULL DEFAULT '0',
	`MaxUnarmedDamage` TINYINT(3) UNSIGNED NULL DEFAULT '0',
	`ClanID` INT(10) UNSIGNED NULL DEFAULT '0',
	`Job` SMALLINT(5) UNSIGNED NULL DEFAULT '0',
	`ElderBrotherID` INT(10) UNSIGNED NULL DEFAULT '0',
	`PartyID` INT(10) UNSIGNED NULL DEFAULT '0',
	`TaskChainTag` INT(10) UNSIGNED NULL DEFAULT '0',
	`ChainStringID` SMALLINT(5) UNSIGNED NULL DEFAULT '0',
	`XPPool` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`ClanQuit` TINYINT(3) UNSIGNED NULL DEFAULT '0',
	`LastKillerID` INT(10) UNSIGNED NULL DEFAULT '0',
	`DuelScore` INT(10) UNSIGNED NULL DEFAULT '0',
	`LastDuelID` INT(10) UNSIGNED NULL DEFAULT '0',
	`DuelsWon` SMALLINT(5) UNSIGNED NULL DEFAULT '0',
	`DuelsLost` SMALLINT(5) UNSIGNED NULL DEFAULT '0',
	`DuelsOffered` SMALLINT(5) UNSIGNED NULL DEFAULT '0',
	`DuelsRefused` SMALLINT(5) UNSIGNED NULL DEFAULT '0',
	`DuelsInterrupted` SMALLINT(5) UNSIGNED NULL DEFAULT '0',
	`NPCKilled` INT(10) UNSIGNED NULL DEFAULT '0',
	`PCKilled` INT(10) UNSIGNED NULL DEFAULT '0',
	`PCResus` INT(10) UNSIGNED NULL DEFAULT '0',
	`RelicStolen` INT(10) UNSIGNED NULL DEFAULT '0',
	`RelicReturned` INT(10) UNSIGNED NULL DEFAULT '0',
	`GuildID` INT(10) UNSIGNED NULL DEFAULT '0',
	`RedPoints` INT(10) UNSIGNED NULL DEFAULT '0',
	`GreenPoints` INT(10) UNSIGNED NULL DEFAULT '0',
	`HeroPoints` INT(10) UNSIGNED NULL DEFAULT '0',
	`WarEventID` INT(10) UNSIGNED NULL DEFAULT '0',
	`NumChainPowers` TINYINT(3) UNSIGNED NULL DEFAULT '0',
	`ReadyWeapon` INT(10) UNSIGNED NULL DEFAULT '0',
	`PKWarning` INT(10) UNSIGNED NULL DEFAULT '0',
	`WaitPeriod` INT(10) UNSIGNED NULL DEFAULT '0',
	`ReSpecPoints` TINYINT(3) UNSIGNED NULL DEFAULT '0',
	`LastResTime` INT(10) UNSIGNED NULL DEFAULT '0',
	`TeamID` INT(10) UNSIGNED NULL DEFAULT '0',
	`HeroCount` TINYINT(3) UNSIGNED NULL DEFAULT '0',
	`LastHeroReset` INT(10) UNSIGNED NULL DEFAULT '0',
	`MulPerc` INT(10) UNSIGNED NOT NULL DEFAULT '100',
	INDEX `charid` (`CharID`)
)
COLLATE='latin1_swedish_ci'
ENGINE='MRG_MYISAM'
UNION=(`pcharstats_0`,`pcharstats_1`,`pcharstats_2`,`pcharstats_3`,`pcharstats_4`,`pcharstats_5`,`pcharstats_6`,`pcharstats_7`,`pcharstats_8`,`pcharstats_9`);
";
				mysql_query($droptable, $dbWc) or die(mysql_error($dbWc));
				mysql_query($updatesql, $dbWc) or die(mysql_error($dbWc));

$droptableint = "DROP TABLE IF EXISTS `intdata_b`";
$updatesqlint = "
CREATE TABLE IF NOT EXISTS `intdata_b` (
	`CharID` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`XPPool` MEDIUMINT(11) UNSIGNED NOT NULL DEFAULT '0',
	`ClanID` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`ElderBrotherID` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`job` SMALLINT(5) UNSIGNED NULL DEFAULT '0',
	`AuctionGold` INT(10) UNSIGNED NULL DEFAULT '0',
	`Reason` SMALLINT(5) UNSIGNED NULL DEFAULT '0',
	`GuildID` INT(10) UNSIGNED NULL DEFAULT '0',
	`RClanID` SMALLINT(5) UNSIGNED NULL DEFAULT '0',
	`Rating` TINYINT(3) UNSIGNED NULL DEFAULT '0',
	`HeroPoints` SMALLINT(5) UNSIGNED NULL DEFAULT '0',
	PRIMARY KEY (`CharID`)
)
COLLATE='latin1_swedish_ci'
ENGINE=MRG_MYISAM
UNION=(`intdata_0`,`intdata_1`,`intdata_2`,`intdata_3`,`intdata_4`,`intdata_5`,`intdata_6`,`intdata_7`,`intdata_8`,`intdata_9`);
";
                                mysql_query($droptableint, $dbWc) or die(mysql_error($dbWc));
                                mysql_query($updatesqlint, $dbWc) or die(mysql_error($dbWc));

				if($itemid > 0 && $itemid < 10000000000)
				{
$droptableinv = "DROP TABLE IF EXISTS `charinv_all`";
$updatesqlinv = "
CREATE TABLE IF NOT EXISTS `charinv_all` (
	`Indx` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`CharID` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`ItemID` INT(10) UNSIGNED NULL DEFAULT '0',
	`SlotNum` TINYINT(3) UNSIGNED NULL DEFAULT '0',
	`Quantity` TINYINT(3) UNSIGNED NULL DEFAULT '0',
	`Identified` TINYINT(3) UNSIGNED NULL DEFAULT '0',
	`Durability` TINYINT(4) NULL DEFAULT '100',
	`Field1` INT(10) UNSIGNED NULL DEFAULT '0',
	`Field2` INT(10) UNSIGNED NULL DEFAULT '0',
	`Field3` INT(10) UNSIGNED NULL DEFAULT '0',
	`Field4` INT(10) UNSIGNED NULL DEFAULT '0',
	`Field5` INT(10) UNSIGNED NULL DEFAULT '0',
	`Hardness` TINYINT(3) UNSIGNED NULL DEFAULT '100',
	`Level` TINYINT(3) UNSIGNED NULL DEFAULT '1',
	`XP` INT(10) UNSIGNED NULL DEFAULT '0',
	PRIMARY KEY (`Indx`),
	INDEX `charid` (`CharID`)
)
COLLATE='latin1_swedish_ci'
ENGINE=MRG_MYISAM
UNION=(`charinv_0`,`charinv_1`,`charinv_2`,`charinv_3`,`charinv_4`,`charinv_5`,`charinv_6`,`charinv_7`,`charinv_8`,`charinv_9`);

";
                                mysql_query($droptableinv, $dbWc) or die(mysql_error($dbWc));
                                mysql_query($updatesqlinv, $dbWc) or die(mysql_error($dbWc));

					$from_inv = ", charinv_all inv";
					$cond_inv = "AND c.CharID=inv.CharID AND inv.ItemID='$itemid' AND inv.Quantity>0 AND inv.CharID>0";
					$group_inv = "GROUP BY inv.CharID";
				}
				else
				{
					$itemid = "";
				}

				$arr = array("from_inv", "cond_inv", "cond_dt", "cond_charnm", "cond_exp", "cond_redpt", "cond_heropt", "cond_clan", "cond_rank");
				foreach ($arr as &$value) {
					echo ($value . " " . $$value . "<br />");
				}

				$query_rs = "SELECT COUNT(DISTINCT(c.CharID)) FROM pcharacter c, pcharstats_all s, intdata_b i $from_inv WHERE c.CharID=s.CharID AND c.CharID=i.CharID $cond_inv $cond_dt $cond_lvl $cond_charnm $cond_exp $cond_redpt $cond_heropt $cond_clan $cond_rank";

				echo($query_rs);

				$rs = mysql_query($query_rs, $dbWc) or die(mysql_error($dbWc));
				list($total_found) = mysql_fetch_row($rs);
				mysql_free_result($rs);

				$pg = $HTTP_GET_VARS[pg];
				$lp = ceil($total_found / $rpp);
				if($pg < 1){
					$pg = 1;
				}elseif($pg > $lp){
					$pg = $lp;
				}
				$offset = ($pg - 1) * $rpp;

				$query_rs = "SELECT c.CharID, CharacterName, s.Level, s.RedPoints, s.HeroPoints, from_unixtime(CreateDate) AS dt FROM pcharacter c, pcharstats_all s, intdata_b i $from_inv WHERE c.CharID=s.CharID AND c.CharID=i.CharID $cond_inv $cond_dt $cond_lvl $cond_charnm $cond_exp $cond_redpt $cond_heropt $cond_clan $cond_rank $group_inv ORDER BY $sort LIMIT $offset, $rpp";

				$rs = mysql_query($query_rs, $dbWc) or die(mysql_error($dbWc));

				switch($view)
				{
					case 1:
						$htmllist = "<tr><th>#</th><th>Character</th><th>Hero Points</th></tr>";
						break;
					case 2:
						$htmllist = "<tr><th>#</th><th>Character</th><th>Red PK Points</th></tr>";
						break;
					default:
						$htmllist = "<tr><th>#</th><th>Character</th><th>Level</th><th>Creation Date</th><th>Red PK Points</th><th>Hero Points</th></tr>";
						break;
				}
				$idx = $offset;
				echo( mysql_num_rows($rs) );
				while($row = mysql_fetch_assoc($rs))
				{
					$idx++;
					$charnm = U16btoU8str($row[CharacterName]);

					switch($view)
					{
						case 1:
							$htmllist .= "<tr><td>$idx</td><td><a href=\"pcharacter.php?i=$row[CharID]&wid=$wid\" target=\"p$row[CharID]\">$charnm<img src='images/blank.bmp' border=0></a></td><td>$row[HeroPoints]</td></tr>";
							break;
						case 2:
							$htmllist .= "<tr><td>$idx</td><td><a href=\"pcharacter.php?i=$row[CharID]&wid=$wid\" target=\"p$row[CharID]\">$charnm<img src='images/blank.bmp' border=0></a></td><td>$row[RedPoints]</td></tr>";
							break;
						default:
							$htmllist .= "<tr><td>$idx</td><td><a href=\"pcharacter.php?i=$row[CharID]&wid=$wid\" target=\"p$row[CharID]\">$charnm<img src='images/blank.bmp' border=0></a></td><td>$row[Level]</td><td>$row[dt]</td><td>$row[RedPoints]</td><td>$row[HeroPoints]</td></tr>";
							break;
					}
				}

				function mklink($n){
					global $pg;
					$tag = $n == $pg?"<b>$n</b>":"$n";
					return "<a href=\"javascript:document.form1.pg.value='{$n}';document.form1.a.value='f';document.form1.submit()\">$tag</a>";
				}

				if($lp > 0)
				{
					$s1 = -10;
					$s2 = 10;

					$html_page = "Found $total_found character(s).<br>Page: ";

					if($pg + $s1 > 1) $html_page .= mklink(1) . "... ";
					for($n = $s1; $n < $s2; $n++)
					{
						$pp = $pg + $n;
						if($pp > $lp) break;
						if($pp > 0 )
							$html_page .= mklink($pp) . " ";
					}
					if($pg + $s2 < $lp) $html_page .= " ..." . mklink($lp);
				}
			}

			break;
		}
}

$arWc = get_accessible_server($HTTP_SESSION_VARS['userid'], 'wc');

$htmlWc="<select name=wid onChange=\"postform(document.form1,'findchar.php?a=wc')\"><option value=''></option>";
foreach($arWc as $row)
{
	$selected=($wid==$row[id])?"SELECTED":"";
	$htmlWc.="<option value='{$row[id]}' $selected>{$row[name]}</option>";
}
$htmlWc.="</select>";
?>
<html>
<head>
<title><?=BROWSER_TITLE?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="ga.css" rel="stylesheet" type="text/css"/>
<script language="JavaScript" type="text/JavaScript">
<!--
function postform(form,url){
	form.action=url;form.submit()
}
//-->
</script>
</head>
<body>
<form name="form1" method="GET">
<h3>Advanced Character Search</h3>
(World Controller: <?=$htmlWc?>)
<br><br>
<?
if($wid)
{
?>
<span style="display:<?=$view==""?"":"none"?>">
<table border=1 cellspacing=0>
	<tr>
		<td>Charname</td>
		<td><input name="charname" value="<?=$charname?>"></td>
	</tr>
	<tr>
		<td>Level</td>
		<td>
			<select name="op1" onchange="document.all.span1.style.display=(this.value=='5')?'':'none' ">>
				<option value="">--Select--</option>
				<option value="1" <?=$selected1_1?>>=</option>
				<option value="2" <?=$selected1_2?>>&lt;&gt;</option>
				<option value="3" <?=$selected1_3?>>&gt;</option>
				<option value="4" <?=$selected1_4?>>&lt;</option>
				<option value="5" <?=$selected1_5?>>from</option>
			</select>
			<input name="level1" value="<?=$level1?>" size="4" maxlength="3">
			<span id="span1" style="display:<?=$display1?>">To <input name="level2" value="<?=$level2?>" size="4" maxlength="3"></span>
		</td>
	</tr>
	<tr>
		<td>Clan</td>
		<td>
			<select name="clan">
				<option value="">--Select--</option>
				<option value="1" <?=$selected6_1?>>Supreme Sword Clan</option>
				<option value="2" <?=$selected6_2?>>King of Heroes Clan</option>
				<option value="3" <?=$selected6_3?>>Matchless Clan</option>
				<option value="4" <?=$selected6_4?>>Sword Worship Clan</option>
				<option value="5" <?=$selected6_5?>>Swift Meaning Clan</option>
				<option value="100" <?=$selected6_100?>>* GM *</option>
			</select>
		</td>
	</tr>
	<tr>
		<td>Clan Rank</td>
		<td>
			<select name="rank">
				<option value="">--Select--</option>
				<option value="1" <?=$selected7_1?>>Leader</option>
				<option value="2" <?=$selected7_2?>>Minister</option>
				<option value="3" <?=$selected7_3?>>Master</option>
				<option value="4" <?=$selected7_4?>>Senior</option>
				<option value="5" <?=$selected7_5?>>Member</option>
			</select>
		</td>
	</tr>
	<tr>
		<td>Red PK Points</td>
		<td>
			<select name="op4" onchange="document.all.span4.style.display=(this.value=='5')?'':'none' ">>
				<option value="">--Select--</option>
				<option value="1" <?=$selected4_1?>>=</option>
				<option value="2" <?=$selected4_2?>>&lt;&gt;</option>
				<option value="3" <?=$selected4_3?>>&gt;</option>
				<option value="4" <?=$selected4_4?>>&lt;</option>
				<option value="5" <?=$selected4_5?>>from</option>
			</select>
			<input name="redp1" value="<?=$redp1?>" size="6" maxlength="10">
			<span id="span4" style="display:<?=$display4?>">To <input name="redp2" value="<?=$redp2?>" size="6" maxlength="10"></span>
		</td>
	</tr>
	<tr>
		<td>Hero Points</td>
		<td>
			<select name="op5" onchange="document.all.span5.style.display=(this.value=='5')?'':'none' ">>
				<option value="">--Select--</option>
				<option value="1" <?=$selected5_1?>>=</option>
				<option value="2" <?=$selected5_2?>>&lt;&gt;</option>
				<option value="3" <?=$selected5_3?>>&gt;</option>
				<option value="4" <?=$selected5_4?>>&lt;</option>
				<option value="5" <?=$selected5_5?>>from</option>
			</select>
			<input name="herop1" value="<?=$herop1?>" size="6" maxlength="10">
			<span id="span5" style="display:<?=$display5?>">To <input name="herop2" value="<?=$herop2?>" size="6" maxlength="10"></span>
		</td>
	</tr>
	<tr>
		<td>Creation Date</td>
		<td>
			<select name="op2" onchange="document.all.span2.style.display=(this.value=='5')?'':'none' ">
				<option value="">--Select--</option>
				<option value="1" <?=$selected2_1?>>=</option>
				<option value="2" <?=$selected2_2?>>&lt;&gt;</option>
				<option value="3" <?=$selected2_3?>>&gt;</option>
				<option value="4" <?=$selected2_4?>>&lt;</option>
				<option value="5" <?=$selected2_5?>>from</option>
			</select>
			<!--input name="dt1" value="<?=$dt1?>"  size=10 maxlength="10"-->

			Year: <select name="y1"><?=add_options($array_year, $y1)?></select>
			Month: <select name="m1"><?=add_options($array_month, $m1)?></select>
			Day: <select name="d1"><?=add_options($array_day, $d1)?></select>

			<span id="span2" style="display:<?=$display2?>">
				To
				<!--input name="dt2" value="<?=$dt2?>" size=10 maxlength="10"-->
				Year:	<select name="y2"><?=add_options($array_year, $y2)?></select>
				Month: <select name="m2"><?=add_options($array_month, $m2)?></select>
				Day: <select name="d2"><?=add_options($array_day, $d2)?></select>
			</span>
		</td>
	</tr>
	<tr>
		<td>Sort By</td>
		<td><select name="sort">
				<option value="1" <?=$selected3_1?>>Level</option>
				<option value="2" <?=$selected3_2?>>Level (Descending)</option>
				<option value="3" <?=$selected3_3?>>Create Date</option>
				<option value="4" <?=$selected3_4?>>Create Date (Descending)</option>
				<option value="5" <?=$selected3_5?>>Red PK Point</option>
				<option value="6" <?=$selected3_6?>>Red PK Point (Descending)</option>
				<option value="7" <?=$selected3_7?>>Hero Point</option>
				<option value="8" <?=$selected3_8?>>Hero Point (Descending)</option>
			</select>
		</td>
	</tr>
	<tr>
		<td>Has Item (ID)</td>
		<td><input type="text" name="itemid" value="<?=$itemid?>" maxlength="8" size="10">
		</td>
	</tr>
        <tr>
                <td>Experience</td>
                <td><input type="text" name="itemid" value="<?=$experience?>" maxlength="8" size="10">
                </td>
        </tr>

	<tr>
		<td>Entry Per Page</td>
		<td><input type="text" name="rpp" value="<?=$rpp?>" maxlength="3" size="3">
		</td>
	</tr>
</table>
<input type="submit" value="Search" onclick="document.form1.a.value='f'">
<input type="hidden" name="pg">
<input type="hidden" name="a">
<input type="hidden" name="view" value="<?=$view?>">
</span>
<?
} //if($wid)
?>
</form>
<?=$html_page?>
<table border="1" cellspacing=0>
<?=$htmllist?>
</table>
</body>
</html>
