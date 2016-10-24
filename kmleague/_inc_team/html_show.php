<?php

Function show_flag($country){
	if(NOFLAGS!=1){
		global $countries;
		global $lang;
		return '<img alt="'.$lang['flag'].'" title="'.$countries[$country].'" src="'.DIR.'_country/'.$country.'.gif" />';
	}
}

Function show_place($link){
	global $lang;
	#$categories = array('news'=>$lang['news'], 'crew'=>$lang['team'], 'users'=>$lang['users'], 'com'=>$lang['news'], 'avatar'=>$lang['prof_photo'], 'pm'=>'Messenger', 'spm'=>'Messenger', 'pms'=>'Messenger', 'dpm'=>'Messenger', 'profile'=>'Info', 'reg'=>$lang['register'], 'fpass'=>$lang['fpass'], 'npass'=>$lang['fpass'], 'add_user'=>$lang['register']);
	#if($link == 'com') $link = 'news';
	$rtr = '<a href="index.php?'.KML_LINK_SL2.'">-=-</a>';
	#if($_REQUEST['op']) print(' &raquo; <a href="index.php?op='.$link.'">'.$categories[$link].'</a>');
	return $rtr;
}

# INFO
Function show_rules(){
	global $lang;
	$rtr = content_line('T',$lang['rules']);
	$rtr .= '<td class="news">';
	if(file_exists('data/rules_'.LEAGUE.'.htm')){
		$link = fopen('data/rules_'.LEAGUE.'.htm', 'r');
		while(!feof($link)) $rtr .= fgets($link, 4096); 
	}
	$rtr .= content_line('B');
	return $rtr;
}

Function interviews($id,$dl_grants,$dls_grants){
	global $lang;
	$id = intval($id);
	if(!$id){
		$rtr = content_line('T',$lang['interviews']);
		$rtr .= '<td>';
		$qry = 'SELECT u.login, u.country, i.idi, i.date, i.author, i.subject FROM '.KML_PREFIX.'_interview AS i, '.KML_PREFIX.'_users AS u WHERE i.league='.LEAGUE.' AND u.iduser=i.author AND i.ids='.IDS.' AND i.visible="Y" ORDER BY i.date DESC';
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		if(mysql_num_rows($rsl)>0){
			$rtr .= '<table cellspacing="1" cellpadding="5" class="tab_brdr" width="400">
			<tr><td class="tab_head3">'.ucfirst($lang['title']).'</td><td class="tab_head3">'.$lang['author'].'</td><td class="tab_head3">'.$lang['date'].'</td></tr>';
			while($row=mysql_fetch_assoc($rsl)){
				foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
				$rtr .= '<tr class="content2"><td class="content1"><a class="link" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=inter&amp;id='.$row['idi'].'">'.$row['subject'].'</a></td><td><a class="link" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=users&amp;id='.$row['author'].'">'.show_flag($row['country']).' '.$row['login'].'</a></td><td>'.show_date($row['date'], 1).'</td></tr>';
			}
			$rtr .= '</table><br/>';
		}else $rtr .= $lang['no_interv'].'<br/><br/>';
		$rtr .= content_line('B');
	}else{
		$qry = 'SELECT u.login, u.country, i.idi, i.date, i.author, i.subject, i.content FROM '.KML_PREFIX.'_interview AS i, '.KML_PREFIX.'_users AS u WHERE i.league='.LEAGUE.' AND i.visible="Y" AND u.iduser=i.author AND i.idi='.$id;
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		if(mysql_num_rows($rsl)!=1){
			$rtr .= $lang['sure'];
			return $rtr;
		}
		$row = mysql_fetch_assoc($rsl);
		foreach($row as $ky=>$vl){
			if($ky!='content') $row[$ky] = intoBrowser($vl);
		}
		$rtr .= content_line('T',$lang['interview'].': '.$row['subject']);
		$rtr .= '<td class="news">'.$row['content'].'
		<div align="right" class="italic"><a class="link" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=users&amp;id='.$row['author'].'">'.show_date($row['date'], 2).' &copy; '.$row['login'].'</a></div>';
		$rtr .= content_line('M',ucfirst($lang['comments']));
		$rtr .= comms(ARTICLE_ID,$id,$dl_grants,$dls_grants);
		if(IDSA==IDS){
			$rtr .= content_line('M',$lang['new_com']);
			if(banned($_SERVER['REMOTE_ADDR'])){
				$rtr .= $lang['banned'];
				return $rtr;
			}
			if($dl_grants>0) $rtr .= comment_form($id,ARTICLE_ID);
			else $rtr .= $lang['comm_info'];
		}
		$rtr .= content_line('B');
	}
	return $rtr;
}

Function sign_ups($dts,$dl_grants,$dl_login){
	global $lang;
	global $countries;
	if(SINGUP!='H'){
		$qry = 'SELECT COUNT(idc) FROM '.KML_PREFIX.'_signup WHERE league='.LEAGUE;
		$num = ': '.mysql_result(query(__FILE__,__FUNCTION__,__LINE__,$qry,0),0);
	}
	$rtr = content_line('T',$lang['sign'].$num);
	$rtr .= '<td align="center">';
	if(SINGUP=='H') $rtr .= $lang['signup_turnoff'];
	else{
		$rtr .= '<div class="note">';
		if($dts['opt1']){
			if(LEAGUE_TYPE=='T'){
				$qry = SQL('SELECT ids FROM '.KML_PREFIX.'_signup WHERE league='.LEAGUE.' AND idc=%d', $_SESSION['dl_config']['idc']);
				$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
				if(mysql_num_rows($rsl)==0){
					$qry = SQL('INSERT INTO '.KML_PREFIX.'_signup(league, iduser, idc) VALUES("'.LEAGUE.'", %d, %d)', $dl_login, $_SESSION['dl_config']['idc']);
					if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) $rtr .= '<br/>'.$lang['sign_inf2'].'<br/>';
				}
			}elseif(LEAGUE_TYPE=='D'){
				$qry = 'SELECT ids FROM '.KML_PREFIX.'_signup WHERE league='.LEAGUE.' AND iduser="'.(int)$dl_login.'"';
				$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
				if(mysql_num_rows($rsl)==0){
					$qry = 'INSERT INTO '.KML_PREFIX.'_signup(league, iduser) VALUES('.LEAGUE.', "'.(int)$dl_login.'")';
					if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) $rtr .= '<br/>'.$lang['sign_inf2'].'<br/>';
				}
			}
		}
		$rtr .= '</div>';
		if(SINGUP=='E'){
			if($dl_grants>0){
				$rtr .= '<form action="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=sign" method="post">';
				$rtr .= '<div class="note"><input type="submit" name="opt1" value="'.$lang['signup'].'" /></div>';
				$rtr .= '</form>';
			}else $rtr .= $lang['reg_err'];
		}

		if(LEAGUE_TYPE=='D') $qry = 'SELECT u.iduser, u.login, u.country, u.clan FROM '.KML_PREFIX.'_signup AS s, '.KML_PREFIX.'_users AS u WHERE s.league='.LEAGUE.' AND u.iduser=s.iduser ORDER BY s.ids DESC';
		elseif(LEAGUE_TYPE=='T') $qry = 'SELECT u.iduser, u.login, u.country, c.country, c.cname, c.channel, s.idc FROM '.KML_PREFIX.'_clan AS c, '.KML_PREFIX.'_signup AS s, '.KML_PREFIX.'_users AS u WHERE s.league='.LEAGUE.' AND u.iduser=s.iduser AND s.idc=c.idc ORDER BY s.ids DESC';
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		if(mysql_num_rows($rsl)>0){
			$rtr .= '<br/><table cellspacing="1" cellpadding="5" class="tab_brdr">';
			if(LEAGUE_TYPE=='D') $rtr .= '<tr><td class="tab_head3">'.$lang['clan'].'</td><td class="tab_head3">'.$lang['prof_clan'].'</td></tr>';
			elseif(LEAGUE_TYPE=='T') $rtr .= '<tr><td class="tab_head3">'.$lang['clan'].'</td><td class="tab_head3">IRC</td><td class="tab_head3">'.$lang['signed'].'</td></tr>';
			while($row=mysql_fetch_assoc($rsl)){
				foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
				if($row['irc_channel']=='#') $row['irc_channel'] = '';
				if(LEAGUE_TYPE=='D'){
					$rtr .= '<tr class="content1"><td><a href="index.php?'.KML_LINK_SL.'op=clans&amp;id='.$row['iduser'].'">'.show_flag($row['country']).' '.$row['login'].'</a></td><td>'.$row['clan'].'</td></tr>';
				}else $rtr .= '<tr class="content1"><td class="content2"><a class="link" href="index.php?'.KML_LINK_SL.'op=clans&amp;id='.$row['idc'].'">'.show_flag($row['country']).' '.$row['cname'].'</a></td><td>'.$row['irc_channel'].'</td><td><a href="index.php?'.KML_LINK_SL.'op=users&amp;id='.$row['iduser'].'">'.show_flag($row['country']).' '.$row['login'].'</a></td></tr>';
			}
			$rtr .= '</table>';
		}
	}
	$rtr .= '<br/>';
	$rtr .= content_line('B');
	return $rtr;
}

Function awards(){
	global $lang;
	$rtr = content_line('T',$lang['awards']);
	$rtr .= '<td>';
	if(LEAGUE_TYPE=='D') $qry = 'SELECT a.ida, a.name as aname, a.img, a.grade, x.idc, c.country, c.login AS cname, s.ids, s.sname FROM '.KML_PREFIX.'_award AS a, '.KML_PREFIX.'_season AS s, '.KML_PREFIX.'_clan_award AS x, '.KML_PREFIX.'_users AS c WHERE c.iduser=x.idc AND a.ida=x.ida AND s.ids=x.ids AND a.league='.LEAGUE.' ORDER BY s.ids DESC, a.grade ASC';
	else $qry = 'SELECT a.ida, a.name as aname, a.img, a.grade, c.idc, c.country, c.cname, s.ids, s.sname FROM '.KML_PREFIX.'_award AS a, '.KML_PREFIX.'_season AS s, '.KML_PREFIX.'_clan_award AS x, '.KML_PREFIX.'_clan AS c WHERE c.idc=x.idc AND a.ida=x.ida AND s.ids=x.ids AND a.league='.LEAGUE.' ORDER BY s.ids DESC, a.grade ASC';
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	if(mysql_num_rows($rsl)==0) $rtr .= $lang['no_awards'];
	else{
		$rtr .= '<table cellspacing="1" cellpadding="5" class="tab_brdr">
		<tr><td class="tab_head3">'.$lang['awards'].'</td><td class="tab_head3">'.$lang['clans'].'</td></tr>';
		while($row=mysql_fetch_assoc($rsl)){
			foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
			if($ids!=$row['ids']) $rtr .= '<tr><td colspan="2" class="content4">'.$lang['season'].' '.$row['sname'].'</td></tr>';
			$rtr .= '<tr><td class="content2"><img alt="'.$lang['awards'].'" src="awards/'.$row['img'].'"/>'.$row['aname'].' <img alt="'.$lang['awards'].'" title="'.$lang['level'].': '.$row['grade'].'" src="'.DIR.'_img/award'.$row['grade'].'.gif"/></td><td class="content1"><a class="link" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=clans&amp;id='.$row['idc'].'">'.show_flag($row['country']).' '.$row['cname'].'</a></td></tr>';
			$ids = $row['ids'];
		}
		$rtr .= '</table>';
	}
	$rtr .= '<br/>'.content_line('B');
	return $rtr;
}

?>
