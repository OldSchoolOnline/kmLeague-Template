<?php

Function profile_options(){
	global $lang;
	$r = '&raquo; <a class="menu" href="index.php?'.KML_LINK_SL.'op=profile">'.$lang['profile'].'</a><br/>
	&raquo; <a class="menu" href="index.php?'.KML_LINK_SL.'op=avatar">'.$lang['prof_photo'].'</a><br/>
	&raquo; <a class="menu" href="index.php?'.KML_LINK_SL.'op=pm&amp;id=1">'.$lang['messenger'].'</a> '.check_mess($_SESSION['dl_login']).'<br/>
	&raquo; <a class="menu" href="index.php?'.KML_LINK_SL.'op=player_teams">'.$lang['player_teams'].'</a><br/>';
	if($_SESSION['dl_grants'][MAIN_ID]>0) $r .= '&raquo; <a class="menu" href="admin.php?'.KML_LINK_SL2.'">'.$lang['admin'].'</a><br/>';
	$r .= '&raquo; <a class="menu" href="index.php?'.KML_LINK_SL.'op=logout">'.$lang['prof_logout'].'</a>';
	return $r;
}

Function form_log(){
	global $lang;
	$rtr = '<div align="center">
	<form method="post" action="index.php?'.KML_LINK_SL2.'">
	<table cellspacing="3" cellpadding="0">
	<tr><td class="login">login:<br/><input type="text" name="login" size="15" /></td></tr><tr><td class="login">'.strtolower($lang['password']).':<br/><input type="password" name="passw" size="15" /></td></tr><tr><td class="login">'.$lang['remember'].' <input type="checkbox" name="remember" /></td></tr><tr><td align="center"><input type="submit" value="'.$lang['log_in'].'" /><br/>'.$lang['register_link'].'</td></tr>
	</table>
	</form>
	</div>';
	return $rtr;
}

Function cl_options(){
	global $lang;
	if(!empty($_SESSION['dl_clan'])){
		foreach($_SESSION['dl_clan'] as $ky=>$vl) $clans[$ky] = intoBrowser($vl[1]);
		if(is_array($clans)) $rtr .= '<form action="index.php?'.KML_LINK_SL2.'" method="post"><select name="idc">'.array_assoc($clans,$_SESSION['dl_config']['idc']).'</select> <input type="submit" name="change_clan" value="&raquo;"/></form>';
		$privl = $_SESSION['dl_clan'][$_SESSION['dl_config']['idc']][0];
		if($privl=='C') $rtr .= '&raquo; <a class="menu" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=team_managment">'.$lang['team_managment'].'</a><br/>';
#		if($privl=='C' || $privl=='W') $rtr .= '&raquo; <a class="menu" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=team_roster">'.$lang['team_roster'].'</a><br/>';
	}
	if(USER_CLANS=='Y') $rtr .= '&raquo; <a class="menu" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=add_clan">'.$lang['team_new'].'</a><br/>';
	return $rtr;
}

Function main_menu(){
	global $lang;
	$qry = 'SELECT `head`, `head_lang`, `link`, `target`, `local`, `column`, `row`, `privilages_global`, `privilages_local` FROM '.KML_PREFIX.'_menu WHERE `visible`="Y" AND `league`='.LEAGUE.' ORDER BY `column` ASC, `row` ASC, `head` ASC, `head_lang` ASC';
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	while($row=mysql_fetch_assoc($rsl)){
		foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
#you can make some action when number of column change
		if(++$m!=1 && $infoColumn!=$row['column']) $r .= '<br/>';
#you can make some action when number of row change
#		if($infoRow!=$row['row']){};
		if($row['local']!='N'){
			$row['link'] = str_replace('index.php?', 'index.php?'.KML_LINK_SL, $row['link']);
			$row['link'] = str_replace('admin.php?', 'admin.php?'.KML_LINK_SL, $row['link']);
		}
		if($row['head']) $head = $row['head']; else $head = $lang[$row['head_lang']];
		if($row['target']=='N') $target = ' target="_blank"'; else $target = '';
		#if set check user privilages
		if(($_SESSION['dl_grants']['main']>$row['privilages_global']-1 || $row['privilages_global']==0) && ($_SESSION['dl_grants'][MAIN]>$row['privilages_local']-1 || $row['privilages_local']==0)){
			if(!$row['link']) $r .= '<div class="separator">'.$head.'</div>';
			else $r .= '&raquo; <a class="menu"'.$target.' href="'.$row['link'].'">'.$head.'</a><br/>';
		}
		$infoColumn = $row['column'];
		$infoRow = $row['row'];
	}
	return $r;
}

Function show_tables(){
	global $lang;
	$qry = 'SELECT idpt, name FROM '.KML_PREFIX.'_ptable WHERE league='.LEAGUE.' AND ids='.IDS.' ORDER BY idpt DESC';
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	while($row=mysql_fetch_assoc($rsl)){
		foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
		$rtr .= '&raquo; <a class="menu" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=poff&amp;id='.$row['idpt'].'">'.$row['name'].'</a><br/>';
	}
	$qry = 'SELECT g.gname, g.idg FROM '.KML_PREFIX.'_group AS g, '.KML_PREFIX.'_table AS t WHERE t.league='.LEAGUE.' AND g.ids='.IDS.' AND t.idg=g.idg GROUP BY t.idg ORDER BY g.gphase DESC, g.gname';
	$rslt = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	while($row = mysql_fetch_assoc($rslt)){
		foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
		$rtr .= '&raquo; <a class="menu" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=gr&amp;id='.$row['idg'].'">'.strtoupper($row['gname']).'</a><br/>';
	}
	return $rtr;
}

Function services(){
	$qry = 'SELECT dir, name FROM '.KML_PREFIX.'_website ORDER BY name';
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	while($row=mysql_fetch_assoc($rsl)){
		foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
		$rtr .= '&raquo; <a class="menu" href="'.KML_WEBSITE.'/'.$row['dir'].'">'.$row['name'].'</a><br/>';
	}
	return $rtr;
}

Function powered_by(){
	global $lang;
	$rtr .= '<a href="http://kmleague.net"><img alt="KMleague" title="'.$lang['powered_by'].'" src="http://kmleague.net/kml.php?type=1&amp;ver=1.4.0&amp;web='.ADDRESS.'"/></a>';
	return $rtr;
}

Function latest_news($limit){
	global $lang;
	$qry ='SELECT idn, date, title, title_enc, encode FROM '.KML_PREFIX.'_news WHERE service="'.MAIN_ID.'" AND visible="Y" ORDER BY idn DESC LIMIT 0,'.(int)$limit;
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	while($row=mysql_fetch_assoc($rsl)){
		foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
		if($_SESSION['dl_config']['lang']==$row['encode'] && $row['title_enc']){
			$row['title'] = $row['title_enc'];
		}
		if(strlen($row['title'])>25) $title = substr($row['title'],0,21).' ...'; else $title = $row['title'];
		$rtr .= '<a class="top_box" href="index.php?'.KML_LINK_SL.'op=com&amp;id='.$row['idn'].'">'.show_date($row['date'], 1).' '.$title.'</a><br/>';
	}
	return $rtr;
}

Function last_match($limit){
	global $lang;
	if(LEAGUE_TYPE=='D') $qry = 'SELECT c1.login AS cnm1, c2.login AS cnm2, c1.country AS ctry1, c2.country AS ctry2, m.idm, m.win1, m.win2, m.draw FROM '.KML_PREFIX.'_match AS m, '.KML_PREFIX.'_users AS c1, '.KML_PREFIX.'_users AS c2 WHERE m.league='.LEAGUE.' AND m.idc1=c1.iduser AND m.idc2=c2.iduser AND m.ids='.IDS.' AND (m.win1>0 OR m.win2>0 OR m.draw>0) ORDER BY m.date DESC LIMIT 0,'.$limit;
	else $qry = 'SELECT c1.tag AS cnm1, c2.tag AS cnm2, c1.country AS ctry1, c2.country AS ctry2, m.idm, m.win1, m.win2, m.draw FROM '.KML_PREFIX.'_match AS m, '.KML_PREFIX.'_clan AS c1, '.KML_PREFIX.'_clan AS c2 WHERE m.league='.LEAGUE.' AND m.idc1=c1.idc AND m.idc2=c2.idc AND m.ids='.IDS.' AND (m.win1>0 OR m.win2>0 OR m.draw>0) ORDER BY m.date DESC LIMIT 0,'.$limit;
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	while($row=mysql_fetch_assoc($rsl)){
		foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
		$rtr .= '<div class="lastMatches"><a class="menu" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=descr&amp;id='.$row['idm'].'">'.$row['win1'].':'.$row['win2'].' | '.show_flag($row['ctry1']).' '.$row['cnm1'].' vs '.$row['cnm2'].' '.show_flag($row['ctry2']).'</a></div>';
	}
	return $rtr;
}

Function informations(){
	global $lang;
	$yesno = array('Y'=>strtolower($lang['yes']), 'N'=>strtolower($lang['no']));
	$onoff = array('Y'=>strtolower($lang['on']), 'N'=>strtolower($lang['off']));
	$rtr .= '<div class="bold">'.KML_SEASON_NAME.'</div>
	IRC: <span class="bold">'.IRC_SERVER.'</span><br/>'.
	$lang['sign'].': <span class="bold">';
	if(SINGUP=='E') $rtr .= $onoff['Y']; else $rtr .= $onoff['N'];
	$rtr .= '</span><br/>';
	if(LEAGUE_TYPE=='T'){
		$rtr .= $lang['transfers'].': <span class="bold">';
		if(TRANSFERS=='Y') $rtr .= $yesno['Y']; else $rtr .= $yesno['N'];
		$rtr .= '</span><br/>';
		$rtr .= $lang['players_limit'].': <span class="bold">'.MAX_PLAYERS.'</span><br/>';
		if($row['signup_tlimit']>0) $limit = SIGNUP_TLIMIT.' ('.$lang['teams'].')';
		if($row['signup_dlimit']>0) $limit .= ' '.date('Y-m-d', SIGNUP_DLIMIT);
		if($limit) $rtr .= $lang['signup_limit'].': <span class="bold">'.$limit.'</span><br/>';
		$rtr .= $lang['usr_clans'].': <span class="bold">'.$onoff[USER_CLANS].'</span><br/>';
	}
	return $rtr;
}

Function languages(){
	$langs = get_langs();
	if($_SESSION['dl_config']['lang']==DEFAULT_LANG) $clang = DEFAULT_LANG; else $clang = $_SESSION['dl_config']['lang'];
	$rtr .= '<div align="center"><form method="get" action="index.php">'.KML_LINK_SLF.'<select name="lang">'.array_assoc($langs,$clang).'</select> <input type="submit" value="&raquo;"/></form></div>';
	return $rtr;
}

Function leagues_list(){
	$qry = 'SELECT league, work_name, visible FROM '.KML_PREFIX.'_config ORDER BY work_name';
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	if(mysql_num_rows($rsl)>1) define(KML_MULTI_LEAGUES, "Y");
	else define(KML_MULTI_LEAGUES, "N");
	$r = '<div class="note">
		<form action="index.php" method="get">
			<select name="league">';
	while($row=mysql_fetch_assoc($rsl)){
		foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
		if($row['visible']=='N' && $_SESSION['dl_grants']['main']<10) $dis = ' disabled'; else $dis = '';
		$r .= '<option'.$dis.' value="'.$row['league'].'"';
		if($row['league'] == LEAGUE) $r .= ' selected';
		$r .= '>'.$row['work_name'].'</option>';
	}
	$r .= '</select> <input type="submit" value="&raquo;"/></form></div>';
	return $r;
}

Function season_list(){
	global $lang;
	$qry = 'SELECT sname FROM '.KML_PREFIX.'_season WHERE league='.LEAGUE.' AND ids='.IDSA;
	$sname = @mysql_result(query(__FILE__,__FUNCTION__,__LINE__,$qry,0),0);
	$seasons[IDSA] = intoBrowser($sname);
	$qry = 'SELECT s.ids, s.sname FROM '.KML_PREFIX.'_season AS s, '.KML_PREFIX.'_match AS m WHERE m.league='.LEAGUE.' AND m.ids=s.ids AND s.ids!='.IDSA.' GROUP BY s.ids ORDER BY ids DESC';
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	if(mysql_num_rows($rsl)>0){
		while($row=mysql_fetch_assoc($rsl)){
			foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
			$seasons[$row['ids']] = $row['sname'];
		}
		$rtr .= '<div class="note">
		<form action="index.php" method="get"><input type="hidden" name="league" value="'.LEAGUE.'"/>
			<select name="season">'.array_assoc($seasons,IDS).'</select> <input type="submit" value="&raquo;"/>
			</form>
		</div>';
	}
	return $rtr;
}

Function upc_matches(){
	global $lang;
	if(LEAGUE_TYPE=='D') $qry = 'SELECT m.idm, c1.login AS cnm1, c2.login AS cnm2, m.date, m.idc1, m.idc2, m.win1, m.win2 FROM '.KML_PREFIX.'_match AS m, '.KML_PREFIX.'_users AS c1, '.KML_PREFIX.'_users AS c2 WHERE m.league='.LEAGUE.' AND m.idc1=c1.iduser AND m.idc2=c2.iduser AND m.ids='.IDS.' AND (m.win1=0 AND m.win2=0 AND m.draw=0) AND m.date>0 ORDER BY m.date ASC';
	else $qry = 'SELECT m.idm, c1.tag AS cnm1, c2.tag AS cnm2, m.date, m.idc1, m.idc2, m.win1, m.win2 FROM '.KML_PREFIX.'_match AS m, '.KML_PREFIX.'_clan AS c1, '.KML_PREFIX.'_clan AS c2 WHERE m.league='.LEAGUE.' AND m.idc1=c1.idc AND m.idc2=c2.idc AND m.ids='.IDS.' AND (m.win1=0 AND m.win2=0 AND m.draw=0) AND m.date>0 ORDER BY m.date ASC';
	$rslt = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	if(mysql_num_rows($rslt)>0){
		while($row=mysql_fetch_assoc($rslt)){
			foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
			$rtr .= '<a class="menu" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=descr&amp;id='.$row['idm'].'">'.show_date($row['date'], 2).'</a> | <a class="menu" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=clans&amp;id='.$row['idc1'].'">'.$row['cnm1'].'</a> vs <a class="menu" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=clans&amp;id='.$row['idc2'].'">'.$row['cnm2'].'</a><br/>';
		}
	}
	return $rtr;
}

Function mini_tables(){
	global $lang;
	$qry = 'SELECT x.idc, MIN(a.grade) AS grade FROM '.KML_PREFIX.'_clan_award AS x, '.KML_PREFIX.'_award AS a WHERE a.ida=x.ida AND x.league='.LEAGUE.' GROUP BY x.idc';
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	while($row=mysql_fetch_assoc($rsl)) $awards[$row['idc']] = $row['grade'];
	$wqry = 'SELECT idc, count(idc) AS wc FROM '.KML_PREFIX.'_wildcard WHERE league='.LEAGUE.' AND ids='.IDS.' GROUP BY idc';
	$wrsl = query(__FILE__,__FUNCTION__,__LINE__,$wqry,0);
	if(mysql_num_rows($wrsl)>0){
		while($wrow=mysql_fetch_assoc($wrsl)) $wildcards[$wrow['idc']] = $wrow['wc'];
	}
	$gqry = 'SELECT g.gname, g.idg FROM '.KML_PREFIX.'_group AS g, '.KML_PREFIX.'_table AS t WHERE t.league='.LEAGUE.' AND g.ids='.IDS.' AND t.idg=g.idg GROUP BY t.idg ORDER BY g.idg';
	$grsl = query(__FILE__,__FUNCTION__,__LINE__,$gqry,0);
	if(mysql_num_rows($grsl)>0){
		$rtr .= '<table cellpadding="0" cellspacing="1" align="center" width="100%"><tr>';
		while($grow = mysql_fetch_array($grsl)){
			foreach($grow as $ky=>$vl) $grow[$ky] = intoBrowser($vl);
			if($k++%2==0 && $k!=1) $rtr .= '</tr><tr>';
			$rtr .= '<td valign="top">
			<table cellspacing="1" cellpadding="0" align="center">
			<tr><td colspan="2" class="minitabHead"><a class="menu" href="index.php?'.KML_LINK_SL.'op=gr&amp;id='.$grow['idg'].'">'.strtoupper($grow['gname']).'</a></td></tr>';
			if(LEAGUE_TYPE=='D') $qry = 'SELECT (t.frags/(t.frags+t.deaths)*100) AS eff, t.points, t.idc, c.country, c.login AS tag FROM '.KML_PREFIX.'_table AS t, '.KML_PREFIX.'_users AS c WHERE t.league='.LEAGUE.' AND t.idc=c.iduser AND t.idg="'.$grow['idg'].'" ORDER BY t.points DESC, eff DESC, c.login ASC';
			else $qry = 'SELECT (t.frags/(t.frags+t.deaths)*100) AS eff, t.points, t.idc, c.country, c.tag FROM '.KML_PREFIX.'_table AS t, '.KML_PREFIX.'_clan AS c WHERE t.league='.LEAGUE.' AND t.idc=c.idc AND t.idg="'.$grow['idg'].'" ORDER BY t.points DESC, eff DESC, c.cname ASC';
			$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
			$i= 0;
			$points = '';
			$eff = '';
			while($row=mysql_fetch_array($rsl)){
				foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
				++$i;
				if($row['points']!=$points) $j = $i; elseif($row['points']==$points && $row['eff']!=$eff) $j = $i;
				if($wildcards[$row['idc']]>0) $wc = '<span class="wildcard">'.$wildcards[$row['idc']].'</span>'; else $wc = '';
				if($awards[$row['idc']]) $ainf = ' <a href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=clans&amp;id='.$row['idc'].'&amp;so=awa"><img alt="'.$lang['awards'].'" title="'.$lang['level'].': '.$awards[$row['idc']].'" src="'.DIR.'_img/award'.$awards[$row['idc']].'.gif"/></a>'; else $ainf = '';
				$rtr .= '<tr><td><span class="bold">'.$j.'</span>. <a class="menu" href="index.php?'.KML_LINK_SL.'op=clans&amp;id='.$row['idc'].'">'.show_flag($row['country']).' '.$row['tag'].'</a>'.$ainf.$wc.'</td><td>'.$row['points'].$lang['short_points'].'</td></tr>';
				$points = $row['points'];
				$eff = $row['eff'];
			}
			$rtr .= '</table>
			</td>';
		}
		$rtr .= '</tr></table>';
	}
	return $rtr;
}

Function show_poll($login,$row){
	global $lang;
	if($login>0){
		$qry = 'SELECT c.id_user FROM '.KML_PREFIX.'_p_choices AS c, '.KML_PREFIX.'_p_answers AS a WHERE c.ida=a.ida AND a.idp='.$row['idp'].' AND c.id_user='.(int)$login;
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		if(mysql_num_rows($rsl)>0){
			$info = $lang['poll_va'];
			$vote = 1;
		}
	}else $info = $lang['poll_nl'];
	if($row['active']=='A' || (time()>$row['up_time'] && $row['up_time']!=0)) $info = $lang['poll_fin'];
	if(!$info){
		$rtr .= '<form method=post action="index.php?'.KML_LINK_SL.'op=vote">
		<table style="width: 95%" cellpadding="4" cellspacing="0">
		<tr><td colspan="2" class="bold">'.stripslashes($row['question']).'</td></tr>';
		$cqry = 'SELECT a.answer, a.ida FROM '.KML_PREFIX.'_p_answers AS a, '.KML_PREFIX.'_p_main AS m WHERE a.idp=m.idp and m.idp='.$row['idp'];
		$crsl = query(__FILE__,__FUNCTION__,__LINE__,$cqry,0);
		while($crow=mysql_fetch_assoc($crsl)){
			foreach($crow as $ky=>$vl) $crow[$ky] = intoBrowser($vl);
			$chk = '';
			if(++$t==1) $chk = ' checked';
			$rtr .= '<tr><td class="top_box">'.stripslashes($crow['answer']).'</td><td><input type="radio" name="id" value="'.$crow['ida'].'"'.$chk.' /></td></tr>';
		}
		$rtr .= '<tr><td colspan="2" align="center"><input type="hidden" name="pool" value="'.$row['idp'].'" /><input type="submit" value="'.$lang['vote'].'" /><br/><a class="top_box" href="index.php?'.KML_LINK_SL.'op=polls&amp;id='.$row['idp'].'">'.$lang['score'].'</a></td></tr>
		</table>
		</form>';
	}else{
		$rtr .= scores_poll($row['idp'],0);
		$rtr .= '<div class="note">'.$info.'</div>';
	}
	$rtr .= '<br/>';
	return $rtr;
}

Function watchers(){
	global $lang;
	$qry = 'SELECT COUNT(iduser) FROM '.KML_PREFIX.'_watchers WHERE iduser="0" AND service='.MAIN_ID;
	$guests = mysql_result(query(__FILE__,__FUNCTION__,__LINE__,$qry,0),0);
	$qry = 'SELECT u.login, u.grants, u.iduser FROM '.KML_PREFIX.'_watchers AS w, '.KML_PREFIX.'_users AS u WHERE w.iduser=u.iduser AND w.service="'.MAIN_ID.'" ORDER BY u.grants DESC, u.login ASC';
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	$clanmates = mysql_num_rows($rsl);
	if(MULTI_LEAGUES=='Y'){
		$qry = 'SELECT COUNT(iduser) FROM '.KML_PREFIX.'_watchers WHERE iduser="0"';
		$guests_total = ' ('.mysql_result(query(__FILE__,__FUNCTION__,__LINE__,$qry,0),0).')';
		$qry = 'SELECT DISTINCT u.iduser, u.login, u.grants FROM '.KML_PREFIX.'_watchers AS w, '.KML_PREFIX.'_users AS u WHERE w.iduser=u.iduser AND w.service!="'.MAIN_ID.'" ORDER BY u.grants DESC, u.login ASC';
		$rsl_total = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		$mates_total = mysql_num_rows($rsl_total);
		$clanmates_total = ' ('.($mates_total+$clanmates).')';
	}
	$rtr .= $lang['guests'].': '.$guests.$guests_total.'<br/>'.$lang['registered'].': '.$clanmates.$clanmates_total.'<br/>';
	if($clanmates>0){
		if(MULTI_LEAGUES=='Y') $rtr .= '<br/>'.$lang['watchers_here'].': <br/>';
		while($row = mysql_fetch_assoc($rsl)){
			foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
			if($dot==1) $rtr .= ', ';
			$rtr .= '<a class="top_box" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=users&amp;id='.$row['iduser'].'">';
			if($row['grants']==4) $rtr .= '<span class="bold underline">';
			elseif($row['grants']>1) $rtr .= '<span class="bold">';
			$rtr .= $row['login'];
			if($row['grants']>1) $rtr .= '</span>';
			$rtr .= '</a>';
			$dot = 1;
		}
	}
	if($mates_total>0){
		$dot = 0;
		$rtr .= '<br/><br/>'.$lang['watchers_global'].':<br/>';
		while($row = mysql_fetch_assoc($rsl_total)){
			foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
			if($dot==1) $rtr .= ', ';
			$rtr .= '<a class="top_box" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=users&amp;id='.$row['iduser'].'">';
			if($row['grants']==4) $rtr .= '<span class="bold underline">';
			elseif($row['grants']>1) $rtr .= '<span class="bold">';
			$rtr .= $row['login'];
			if($row['grants']>1) $rtr .= '</span>';
			$rtr .= '</a>';
			$dot = 1;
		}
	}
	return $rtr;
}

Function special_table_short(){
	if(LEAGUE_TYPE=='D') $qry = 'SELECT u.iduser AS idc, u.login AS tag, u.country, SUM(x.points) AS sum_points FROM '.KML_PREFIX.'_users AS u,'.KML_PREFIX.'_special_table AS x WHERE x.idc=u.iduser AND x.ids='.IDS.' AND x.league='.LEAGUE.' GROUP BY x.idc ORDER BY sum_points DESC LIMIT 0,5';
	else $qry = 'SELECT c.idc, c.tag, c.country, SUM(x.points) AS sum_points FROM '.KML_PREFIX.'_clan AS c, '.KML_PREFIX.'_special_table AS x WHERE x.idc=c.idc AND x.ids='.IDS.' AND x.league='.LEAGUE.' GROUP BY x.idc ORDER BY sum_points DESC LIMIT 0,5';
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	if(mysql_num_rows($rsl)>0){
		$rtr .= '<table cellpadding="0" cellspacing="1" align="center" width="100%">';
		while($row=mysql_fetch_assoc($rsl)){
			foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
			$rtr .= '<tr><td><span class="bold">'.(++$j).'</span>. <a class="menu" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=clans&amp;id='.$row['idc'].'">'.show_flag($row['country']).' '.$row['tag'].'</a></td><td>'.round($row['sum_points'],2).$lang['short_points'].'</td></tr>';
		}
		$rtr .= '</table>';
	}
	return $rtr;
}

?>
