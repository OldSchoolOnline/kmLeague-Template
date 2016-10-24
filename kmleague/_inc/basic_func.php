<?php

Function get_league_cfg(){
	$qry = 'SELECT name, signup, signup_dlimit, signup_tlimit, country, user_clans, block_ident, max_players, transfers, dispute_limit_before, dispute_limit_after, default_lang, main_id, wars_id, roster_id, article_id, league_type, skin, player_type, address, score, irc_server, poll_live_time, news_limit_show, short_latest_news, short_latest_matches, forum, forum_prefix, no_flags, score_details, meta_keywords FROM '.KML_PREFIX.'_config WHERE league='.LEAGUE;
	$rsl = mysql_query($qry);
	if(mysql_num_rows($rsl)!=1) exit;
	$row = mysql_fetch_assoc($rsl);
	if($row['signup']=='E'){
		if($row['signup_dlimit']>0){
			if($row['signup_dlimit']<time()) $row['singup'] = 'D';
			else{
				if($row['signup_tlimit']>0){
					$qry = 'SELECT ids from '.KML_PREFIX.'_signup WHERE league='.LEAGUE;
					$rsl = mysql_query($qry);
					if(mysql_num_rows($rsl)>=$row['signup_tlimit']) $row['singup'] = 'D'; else $row['singup'] = 'E';
				}else $row['singup'] = 'E';
			}
		}elseif($row['signup_tlimit']>0){
			$qry = 'SELECT ids from '.KML_PREFIX.'_signup WHERE league='.LEAGUE;
			$rsl = mysql_query($qry);
			if(mysql_num_rows($rsl)>=$row['signup_tlimit']) $row['singup'] = 'D'; else $row['singup'] = 'E';
		}else $row['singup'] = 'E';
	}else $row['singup'] = 'H';
	unset($row['signup_dlimit']);
	$row['dispute_limit_before'] *= 86400;
	$row['dispute_limit_after'] *= 86400;
	foreach($row as $ky=>$vl) define(strtoupper($ky), htmlspecialchars($vl));
}

get_league_cfg();

function getmicrotime(){ 
   list($usec, $sec) = explode(" ",microtime()); 
   return ((float)$usec + (float)$sec); 
}

?>
