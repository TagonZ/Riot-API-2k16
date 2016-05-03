<?php
session_start();
if(!isset($_SESSION['nickname']))
{
	$_SESSION['nickname'] = null;
}
function httpresponsecode($code = null)
{
	if($code != null)
	{
		switch($code)
		{
			case 400: die("Bad request"); break;
			case 401: die("Unauthorized"); break;
			case 403: die("Forbidden"); break;
			case 404: die("No summoner data found for any specified inputs"); break;
			case 429: die("Rate limit exceeded"); break;
			case 500: die("Internal server error"); break;
			case 503: die("Service unavailable"); break;
			default:
				return;
				break;
		}
	}
}
function getDecodedJson($url) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	$response = curl_exec($ch);
	$response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);
	
	httpresponsecode($response_code);
	
	$json = json_decode($response, true);
	return $json;
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Mastery Champion Profile</title>
	<meta charset="utf-8">
	<link rel="icon" href="img/fav/favicon.ico" type="image/x-icon">
	<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="1280" height="720">
	  <defs>
		<filter id="filter">
		  <feGaussianBlur stdDeviation="40"/>
		</filter>
		<mask id="mask">
		  <ellipse cx="50%" cy="50%" rx="40%" ry="40%" fill="white" filter="url(#filter)"></ellipse>
		</mask>
	  </defs>
	  <image xlink:href="img/bg/Ashe_Splash_Centered_0.jpg" width="1280" height="720" mask="url(#mask)"></image>
	</svg>
	<script src="js/jquery-2.2.3.min.js" type="text/javascript"></script>
	<link rel="stylesheet" type="text/css" href="css/style.css" property="stylesheet">
	<script>
	$(document).ready(function() {
		$('.expbar').each(function() {
			$(this).find('.expbar-bar').animate({
				width: $(this).attr('data-percent')
			}, 1500);
			$('.exp-bar-percent').text(
				$(this).attr('data-percent')
			);
		});
		
		var iconID = $('.avatar-blur').attr('profileIcon');
		var version = $('.avatar-blur').attr('ver');
		$('.avatar-blur').css({'background-image':'url(http://ddragon.leagueoflegends.com/cdn/'+version+'/img/profileicon/'+iconID+'.png)'});
		var tierlevel = $('.mastery-level').attr('tier');
		$('.mastery-level').css({'background-image':'url(img/tier/tier'+tierlevel+'.png)'});
	});
	function validateForm()
	{
		
		var nicknameValue = document.forms["form"]["nickname"].value;
		var valid = true;
		if(nicknameValue == null || nicknameValue == "")
		{
			$('#nickname').css({'border-bottom' : '2px solid #F00', 'background-color' : 'rgba(255, 0, 0, 0.38)'});
			valid = false;
			return false;
		} 
		
		var regionValue = $("#region").val();
		if(regionValue == null || regionValue == "")
		{
			$('#region-img').css({'background-color' : 'rgba(255, 40, 40, 0.3'});
			valid = false;
			return false;
		}
		
		if(valid) {
			var form = document.getElementById("form");
			form.submit();	
		}
	}
	</script>
<?php
if(!isset($_GET['nickname']) || !isset($_GET['region']) || $_GET['nickname'] == "" || $_GET['region'] == "") {
	echo '<script src="js/Multi-Column-Select.js" type="text/javascript"></script>
		<link rel="stylesheet" type="text/css" href="css/Multi-Column-Select.css" property="stylesheet">';
}
?>
</head>
<body>
<?php
if(!isset($_GET['nickname']) || !isset($_GET['region']) || $_GET['nickname'] == "" || $_GET['region'] == "") {
echo <<<END
<script>
	$(document).ready(function(){
        $('#selectcontrol').MultiColumnSelect({
			useOptionText :		false,
            menuclass : 		'mcs', 
            openmenuClass : 	'mcs-open',
            openmenuText : 		'<img style="border-radius:15px; -moz-border-radius:15px; -webkit-border-radius:15px; padding: 2px;" id="region-img" src="img/region/transfer.png" height="128" width="128" alt="Transfer region">',
            containerClass :	'mcs-container',
            itemClass : 		'mcs-item',
			duration : 			200,
            onOpen: function() {
				$('#region-img').css({'background-color' : 'rgba(0, 0, 0, 0.0)'});
			},
            onClose: function() {},
            onItemSelect: function() {
				validateForm();
			}
        });       
    });  
</script>
<div class="main-form">
<form id="form" name="form" action="" method="get" onsubmit="return validateForm()">
	<input class="textbox" type="text" id="nickname" name="nickname" autocomplete="off" placeholder="Summoner name..">
	<div id="selectcontrol">
		<select name="region" id="region">
			<option value="na" data-image="img/region/na.png">NA</option>
			<option value="eune" data-image="img/region/eune.png">EUNE</option>
			<option value="euw" data-image="img/region/euw.png">EUW</option>
			<option value="br" data-image="img/region/br.png">BR</option>
			<option value="lan" data-image="img/region/lan.png">LAN</option>
			<option value="las" data-image="img/region/las.png">LAS</option>
			<option value="oce" data-image="img/region/oce.png">OCE</option>
			<option value="ru" data-image="img/region/ru.png">OCE</option>
			<option value="tr" data-image="img/region/tr.png">TR</option>
		</select>
	</div>
</form>
</div>
END;
}
if(isset($_GET['nickname']) && isset($_GET['region']) && $_GET['nickname'] != "" && $_GET['region'] != "" ) {
	if($_GET['nickname'] == $_SESSION['nickname'])
	{
		header('Location: /riotapi');
		session_destroy();
		die();
	}
	$_SESSION['nickname'] = $_GET['nickname'];
	$api_key = "<api_key_here_!>";
	
	$lownick = $_GET['nickname'];
	$region = $_GET['region'];
	
	$url = "https://global.api.pvp.net/api/lol/static-data/$region/v1.2/versions?api_key=$api_key";
	$version = getDecodedJson($url)[0];
	
	$regionIDs = array(
		'br' => 'BR1',
		'eune' => 'EUN1',
		'euw' => 'EUW1',
		'lan' => 'LA1',
		'las' => 'LA2',
		'na' => 'NA1',
		'oce' => 'OC1',
		'ru' => 'RU',
		'tr' => 'TR1'
	);
	$regionID = $regionIDs[$region];
	
	$lownick = strtolower(str_replace(" ","",$lownick));
	$url = "https://$region.api.pvp.net/api/lol/$region/v1.4/summoner/by-name/$lownick?api_key=$api_key";
	$json = getDecodedJson($url);
	
	$playerID = $json[$lownick]['id'];
	$nick = $json[$lownick]['name'];
	$avatar = $json[$lownick]['profileIconId'];
	
	$url = "https://global.api.pvp.net/api/lol/static-data/$region/v1.2/champion?api_key=$api_key";
	$json = getDecodedJson($url);
	
	$champions_db = array();
	foreach($json['data'] as $elem)
	{
		$champions_db[$elem['id']] = array('name' => $elem['name'], 'title' => $elem['title'], 'key' => $elem['key']);
	}
	
	$liczba_postaci = count($champions_db);
	
	$one_champion_max_exp = 0;
	$tier_exp = array(
		'tier1' => 0,
		'tier2' => 1800,
		'tier3' => 4200,
		'tier4' => 6600,
		'tier5' => 9000
	);
	foreach($tier_exp as $exp)
	{
		$one_champion_max_exp += $exp;
	}
	
	$max_exp = $liczba_postaci * $one_champion_max_exp;
	
	$url = "https://$region.api.pvp.net/championmastery/location/$regionID/player/$playerID/champions?api_key=$api_key";
	$json = getDecodedJson($url);
	
	$current_masteryExp = 0;
	$hexbox_collected = 0;
	$maxed_mastery_champion = 0;
	foreach($json as $elem)
	{
		if($elem['championLevel'] == 5)
		{
			$maxed_mastery_champion++;
			$current_masteryExp += $one_champion_max_exp;
			continue;
		}
		$current_masteryExp += $elem['championPoints'];
		
		if($elem['chestGranted'] != "")
			$hexbox_collected += $elem['chestGranted'];
	}
	$procent = round(($current_masteryExp / $max_exp) * 100);
	
	foreach($tier_exp as $exp)
	{
		$tier_level[] = round(($exp / $one_champion_max_exp) * 100);
	}

	for($i = 0; $i < count($tier_level); $i++)
	{
		$tier_proc_lvl += $tier_level[$i];
		if($tier_proc_lvl <= $procent)
		{
			$mastery_level = $i+1;
		}
	}
	
	$url = "https://$region.api.pvp.net/championmastery/location/$regionID/player/$playerID/score?api_key=$api_key";
	$masteryScore = getDecodedJson($url);

	$arrGrade = array(
			'F'		=> array(0 => 0, 1 => 0, 2 => 'F'),
			'D-'	=> array(0 => 1, 1 => 7, 2 => 'D-'),
			'D'		=> array(0 => 8, 1 => 14, 2 => 'D'),
			'D+'	=> array(0 => 15, 1 => 21, 2 => 'D+'),
			'C-'	=> array(0 => 22, 1 => 28, 2 => 'C-'),
			'C'		=> array(0 => 29, 1 => 35, 2 => 'C'),
			'C+'	=> array(0 => 36, 1 => 42, 2 => 'C+'),
			'B-'	=> array(0 => 43, 1 => 49, 2 => 'B-'),
			'B' 	=> array(0 => 50, 1 => 56, 2 => 'B'),
			'B+'	=> array(0 => 57, 1 => 63, 2 => 'B+'),
			'A-'	=> array(0 => 64, 1 => 70, 2 => 'A-'),
			'A' 	=> array(0 => 71, 1 => 77, 2 => 'A'),
			'A+'	=> array(0 => 78, 1 => 84, 2 => 'A+'),
			'S-'	=> array(0 => 85, 1 => 91, 2 => 'S-'),
			'S' 	=> array(0 => 92, 1 => 99, 2 => 'S'),
			'S+'	=> array(0 => 100, 1 => 100, 2 => 'S+')
		);
	
	$profileGrade = "F";
	foreach($arrGrade as $grade)
	{
		if($procent >= $grade[0] && $procent <= $grade[1])
		{
			$profileGrade = $grade[2];
			break;
		}
	}
	
	echo '
	<div class="nd-web">
		<div class="profile_box">
			<div class="avatar-blur" profileIcon="'.$avatar.'" ver="'.$version.'"></div>
			<div class="expbar clearfix " data-percent="'.$procent.'%">
				<div class="expbar-bar">
					<div class="exp-bar-percent"></div>
				</div>
			</div>
			
			<div class="nick-box">'.$nick.'</div>
			<div class="hex-box">
				<div class="hex-box-text">x'.$hexbox_collected.'</div>
			</div>
			<img class="big-grade" src="img/profileGrade/'.$profileGrade.'.png" alt="MissPic :(">
			<div class="mastery-level" tier="'.$mastery_level.'">
				<div class="mastery-level-text">Mastery level: '.$mastery_level.'</div>
			</div>
		</div>
		<div class="content">
			<div class="main-mastery-info"><br>
				Truly masteried champions: '.$maxed_mastery_champion.' of '.$liczba_postaci.' ('.round(($maxed_mastery_champion / $liczba_postaci) * 100).'%)
			</div>
			<div class="mastery-top">
				<div class="mastery-score">'.$masteryScore.'</div>
			</div>
			<div class="champion-blocks-container">';
			
			foreach($json as $element)
			{
				echo '<div class="champion-block">
							<img class="avatar-block" src="http://ddragon.leagueoflegends.com/cdn/'.$version.'/img/champion/'.$champions_db[$element['championId']]['key'].'.png" alt="MissPic :(">
							<div class="name-block">
								'.$champions_db[$element['championId']]['name'].'<br>
								<span>'.$champions_db[$element['championId']]['title'].'</span><br>'.$nospaceChampionName.'
							</div>
							<img class="tier-mastery-block" src="img/tier/tier'.$element['championLevel'].'.png" alt="MissPic :(">
							<div class="mastery-points">Mastery points: '.number_format($element['championPoints'], 0, ',', ' ').'</div>';
							
							if($element['chestGranted'] == "")
								$chestGranted = 0;
							else 
								$chestGranted = $element['chestGranted'];
							
					  echo '<div class="granted-chests">
								<img src="img/hexbox.png" alt="MissPic :(">
								<span>x'.$chestGranted.'</span>
							</div>
							<div class="grade">'.$element['highestGrade'].'</div>
					  </div>';
			}
		echo '</div>
		</div>
	</div>';
}
?>
</body>
</html>