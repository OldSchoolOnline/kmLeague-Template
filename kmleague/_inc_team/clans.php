<?php

Function new_clan($dts){
	global $lang;
	global $countries;
	$rtr = content_line('T',$lang['new_clan']);
	$rtr .= '<td align="center">';
	if($dts['opt'] && (!$dts['tag'] || !$dts['cname'] || !$dts['country'])) $dts['opt'] = $lang['edit'];
	elseif($dts['opt']){
		$qry = SQL('SELECT idc, cname FROM '.KML_PREFIX.'_clan WHERE cname=%s', $dts['cname']);
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		if(mysql_num_rows($rsl)>0){
			$dts['opt'] = $lang['edit'];
			$row = mysql_fetch_assoc($rsl);
			foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
			$error_msg = $lang['new_clan_err'].' => <a href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=clans&amp;id='.$row['idc'].'">'.$row['cname'].'</a>';
		}
	}
	switch($dts['opt']){
		case $lang['add']:{
			if($dts['cwww'] && !eregi("^http://",$dts['cwww'])) $dts['cwww'] = 'http://'.$dts['cwww'];
			$rtr .= '<form method="post" action="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=add_clan">
			<table cellspacing="1" cellpadding="5" width="350" class="tab_brdr">
			<tr><td class="tab_head2">*'.$lang['tag'].':</td><td class="content1"><input type="hidden" name="tag" value="'.$dts['tag'].'" />'.$dts['tag'].'</td></tr>
			<tr><td class="tab_head2">*'.$lang['name'].':</td><td class="content1"><input type="hidden" name="cname" value="'.$dts['cname'].'" />'.$dts['cname'].'</td></tr>
			<tr><td class="tab_head2">'.$lang['clan_site'].':</td><td class="content1"><input type="hidden" name="cwww" value="'.$dts['cwww'].'" />'.$dts['cwww'].'</td></tr>
			<tr><td class="tab_head2">*'.$lang['country'].':</td><td class="content1"><input type="hidden" name="country" value="'.$dts['country'].'" />'.$countries[$dts['country']].'</td></tr>
			<tr><td class="tab_head2" colspan="2" align="right"><input type="submit" name="opt" value="'.$lang['edit'].'" /> <input type="submit" name="opt" value="'.$lang['approve'].'" /></td></tr>
			</table>
			</form>';
			break;
		}
		case $lang['approve']:{
			$qry = SQL('INSERT INTO '.KML_PREFIX.'_clan(cname, tag, cwww, date, activ, country, iduser) VALUES(%s, %s, %s, "'.time().'", "Y", %d, %d)', $dts['cname'], $dts['tag'], $dts['cwww'], $dts['country'], $_SESSION['dl_login']);
			query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
			$rtr .= $lang['clan_add'];
			//add new clan into sessions
			$newIDC = mysql_insert_id();
			$_SESSION['dl_clan'][$newIDC] = array('C',$dts['tag']);
			$_SESSION['dl_config']['idc'] = $newIDC;
			break;
		}
		default:{
			$rtr .= $error_msg;
			if(!$dts['country']) $dts['country'] = COUNTRY;
			$rtr .= '<form method="post" action="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=add_clan">
			<table cellspacing="1" cellpadding="5" width="350" class="tab_brdr">
			<tr><td class="tab_head2">*'.$lang['tag'].':</td><td class="content1"><input type="text" name="tag" maxlength="15" size="25" value="'.$dts['tag'].'" /></td></tr>
			<tr><td class="tab_head2">*'.$lang['name'].':</td><td class="content1"><input type="text" name="cname" maxlength="30" size="25" value="'.$dts['cname'].'" /></td></tr>
			<tr><td class="tab_head2">'.$lang['clan_site'].':</td><td class="content1"><input type="text" name="cwww" maxlength="40" size="25" value="'.$dts['cwww'].'" /></td></tr>
			<tr><td class="tab_head2">*'.$lang['country'].':</td><td class="content1"><select name="country">'.array_assoc($countries,$dts['country']).'</select></td></tr>
			<tr><td class="tab_head2" colspan="2" align="right"><input type="submit" name="opt" value="'.$lang['add'].'" /></td></tr>
			</table>
			</form>
			<div class="note">'.$lang['form_error2'].'</div>';
		}
	}
	$rtr .= content_line('B');
	return $rtr;
}

Function edit_team_opt(){
	global $lang;
	$qry = 'SELECT idc FROM '.KML_PREFIX.'_clan AS c WHERE iduser='.$_SESSION['dl_login'].' AND idc='.(int)$_SESSION['dl_config']['idc'];
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	$qry = 'SELECT idc FROM '.KML_PREFIX.'_player WHERE function="C" AND iduser='.$_SESSION['dl_login'].' AND idc='.(int)$_SESSION['dl_config']['idc'];
	$rsl2 = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	if(mysql_num_rows($rsl)<1 && mysql_num_rows($rsl2)<1) die($lang['sure']);
}

Function new_clanmates(){
	global $lang;
	$functions = array(''=>'', 'C'=>$lang['func_cl'], 'W'=>$lang['func_war'], 'M'=>$lang['team_member'], 'R'=>$lang['team_recruit']);
	$qry = 'SELECT COUNT(p.idp) FROM '.KML_PREFIX.'_player_in AS x, '.KML_PREFIX.'_player AS p WHERE p.idp=x.idp AND x.league='.LEAGUE.' AND p.idc='.(int)$_SESSION['dl_config']['idc'];
	$all_players = @mysql_result(query(__FILE__,__FUNCTION__,__LINE__,$qry,0),0);
	$rtr = content_line('M',$lang['new_clanmts']);
	if(TRANSFERS=='N' && KML_MULTI_LEAGUES!='Y') $err = $lang['sqd_edit_err'].'<br/>';
	if($all_players>=MAX_PLAYERS && KML_MULTI_LEAGUES!='Y') $err .= $lang['max_players'].'<br/>';
	if($err) $rtr .= $err;
	else{
		if(MAX_PLAYERS>0 && KML_MULTI_LEAGUES!='Y') $limit = MAX_PLAYERS - $all_players; else $limit = 10;
		if($limit>10) $limit = 10;
		if(KML_MULTI_LEAGUES=='Y'){
			$inl = '<td class="tab_head3">'.$lang['in_league'].'</td>';
			$all_players2 = $all_players;
		}
		$rtr .= '<form method="post" action="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=team_managment&amp;opx=add_clanmates">
		<table cellspacing="1" cellpadding="5" class="tab_brdr">
		<tr>'.$inl.'<td class="tab_head3">'.$lang['nick'].'</td><td class="tab_head3">'.$lang['contact'].'</td><td class="tab_head3">'.$lang['ip'].'</td><td class="tab_head3">'.$lang['ply_other'].'</td><td class="tab_head3">'.$lang['function'].'</td></tr>';
		if(KML_MULTI_LEAGUES=='Y'){
			if(TRANSFERS=='N' || (MAX_PLAYERS>0 && $all_players>=MAX_PLAYERS)){
				if(TRANSFERS=='N') $err2 = $lang['sqd_edit_err'].'<br/>';
				if(MAX_PLAYERS>0 && $all_players>=MAX_PLAYERS) $err2 .= $lang['max_players'].'<br/>';
				$inl = '<td class="content1">X</td><td class="content1">';
			}
		}
		for($i=0;$i<$limit;$i++){
			if(KML_MULTI_LEAGUES=='Y'){
				if(empty($err2)){
					if(MAX_PLAYERS>0 && ($all_players2)<MAX_PLAYERS) $inl = '<td class="content1"><input type="checkbox" value="Y" name="in_league['.$i.']" /></td><td class="content1">'; else $inl = '<td class="content1">X</td><td class="content1">';
				}
				++$all_players2;
			}else $inl = '<td class="content1"><input value="Y" type="hidden" name="in_league['.$i.']"/>';
			$rtr .= '<tr>'.$inl.'<input type="text" name="name['.$i.']" maxlength="15" size="15" /></td><td class="content1"><input type="text" name="contacts['.$i.']" size="30" maxlength="60" /></td><td class="content1"><input type="text" name="ident['.$i.']" size="30" maxlength="30" /></td><td class="content1"><input type="text" name="other['.$i.']" size="20" maxlength="20" /></td><td class="content1"><select name="function['.$i.']">'.array_assoc($functions,'').'</select></td></tr>';
		}
		$cspan = 5;
		if(KML_MULTI_LEAGUES=='Y') ++$cspan;
		$rtr .= '<tr><td colspan="'.$cspan.'" align="right" class="content2"><input type="submit" value="'.$lang['save'].'" /></td></tr>
		</table>
		</form>';
	}
	$rtr .= '<br/>'.$err2;
	return $rtr;
}

Function add_clanmates($dts){
	global $lang;
	$rtr = content_line('M',$lang['new_clanmts']);
	$qry = 'SELECT COUNT(p.idp) FROM '.KML_PREFIX.'_player_in AS x, '.KML_PREFIX.'_player AS p WHERE p.idp=x.idp AND x.league='.LEAGUE.' AND p.idc='.(int)$_SESSION['dl_config']['idc'];
	$all_players = @mysql_result(query(__FILE__,__FUNCTION__,__LINE__,$qry,0),0);
	if(BLOCK_IDENT == 'Y'){
		$qry = 'SELECT p.ident FROM '.KML_PREFIX.'_player AS p, '.KML_PREFIX.'_player_in AS x WHERE x.idp=p.idp AND x.league='.LEAGUE;
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		while($srow=mysql_fetch_row($rsl)){
			foreach($srow as $ky=>$vl) $srow[$ky] = intoBrowser($vl);
			$idents[] = $srow[0];
		}
	}
	for($i=0;$i<10;$i++){
		$err = '';
		if(!empty($dts['name'][$i])){
			$dts['name'][$i] = str_replace('', '', $dts['name'][$i]);
			if(BLOCK_IDENT == 'Y'){
				if(in_array($dts['ident'][$i],$idents)) $err = $dts['name'][$i].' - '.$lang['error_ident'].'<br/>';
				$iident = SQL('%s, ', $dts['ident'][$i]);
			}else $iident = SQL('%s, ', $dts['ident'][$i]);
			$qry = SQL('INSERT INTO '.KML_PREFIX.'_player(idc, pname, contact, ident, function, other) VALUES(%d, %s, %s, '.$iident.'%s, %s)', $_SESSION['dl_config']['idc'], $dts['name'][$i], $dts['contacts'][$i], $dts['function'][$i], $dts['other'][$i]);
			if(!$err){
				if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) $rtr .= $dts['name'][$i].'<br/>';
				if($dts['in_league'][$i]=='Y' && TRANSFERS=='Y' && ($all_players<MAX_PLAYERS || MAX_PLAYERS==0)){
					$qry = 'INSERT INTO '.KML_PREFIX.'_player_in(idp, league) VALUES('.mysql_insert_id().', '.LEAGUE.')';
					query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
					++$all_players;
				}
			}else $rtr .= $err;
		}
	}
	return $rtr;
}

Function player_teams($get,$post){
	global $lang;
	$rtr = content_line('T',$lang['player_teams']);
	$rtr .= '<td align="center">';
	$get['id'] = intval($get['id']);
	if($get['opt']==1){
		$qry = 'UPDATE '.KML_PREFIX.'_player SET approve_user="Y" WHERE idp='.$get['id'].' AND iduser='.$_SESSION['dl_login'];
		query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		$qry = 'SELECT invite_by FROM '.KML_PREFIX.'_player WHERE idp='.$get['id'];
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		$row = mysql_fetch_assoc($rsl);
		foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
		send_pm($row['invite_by'],$_SESSION['dl_login'],$lang['team_invite'],str_replace('{{player_name}}', $_SESSION['dl_name'], $lang['team_invite_accepted']));
		$rtr .= $lang['invitation_accepted'].'<br/><br/>';
	}elseif($get['opt']==2){
		$qry = 'UPDATE '.KML_PREFIX.'_player SET approve_user="N", iduser=0 WHERE idp='.$get['id'].' AND iduser='.$_SESSION['dl_login'];
		query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		$qry = 'SELECT invite_by FROM '.KML_PREFIX.'_player WHERE idp='.$get['id'];
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		$row = mysql_fetch_assoc($rsl);
		foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
		send_pm($row['iduser'],$_SESSION['dl_login'],$lang['team_invite'],str_replace('{{player_name}}', $_SESSION['dl_name'], $lang['team_invite_rejected']));
		$rtr .= $lang['invitation_refused'].'<br/><br/>';
	}elseif($get['opt']==3){
		$qry = 'DELETE FROM '.KML_PREFIX.'_player_in_clan WHERE idc='.$get['id'].' AND iduser='.$_SESSION['dl_login'];
		query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		$cls = get_all_cl($get['id']);
		foreach($cls as $vl) send_pm($vl,$_SESSION['dl_login'],$lang['team_invite'],str_replace('{{player_name}}', $_SESSION['dl_name'], $lang['team_invite_changed']));
		$rtr .= $lang['invitation_canceled'].'<br/><br/>';
	}elseif($get['opt']==4){
		$cls = get_all_cl($get['id']);
		if($post['clan_passw']){
			$qry = SQL('SELECT idc FROM '.KML_PREFIX.'_clan WHERE clan_passw=%s AND idc='.$get['id'], $post['clan_passw']);
			$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
			if(mysql_num_rows($rsl)==1){
				$qry = SQL('INSERT INTO '.KML_PREFIX.'_player(idc, iduser, approve_user, invite_by, pname) VALUES('.$get['id'].', '.$_SESSION['dl_login'].', "Y", 0, %s)', $_SESSION['dl_name']);
				$msg = str_replace('{{player_name}}', $_SESSION['dl_name'], $lang['player_joined_pass']);
				foreach($cls as $vl) send_pm($_SESSION['dl_login'],$vl,$lang['player_joined'],$msg);
				if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) $rtr .= $lang['request_pass_added'].'<br/><br/>';
			}else{
				 $rtr .= $lang['request_pass_wrong'].'<br/><br/>';
				 $invit = 1;
			}
		}else $invit = 1;
		if($invit==1){
			$qry = 'INSERT INTO '.KML_PREFIX.'_player_in_clan(idc,iduser) VALUES('.$get['id'].', '.$_SESSION['dl_login'].')';
			query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
			$msg = str_replace('{{player_name}}', $_SESSION['dl_name'], $lang['team_invite_wanna']);
			if($post['reason']) $msg = str_replace('{{reason}}', ' ('.$lang['clan_join_reason'].': '.$post['reason'].')', $msg); else $msg = str_replace('{{reason}}', '', $msg);
			foreach($cls as $vl) send_pm($_SESSION['dl_login'],$vl,$lang['team_invite'],$msg);
			$rtr .= $lang['request_sent'].'<br/><br/>';
		}
	}
	$qry = 'SELECT p.idp, p.idc, p.iduser, p.approve_user, c.cname, c.tag FROM '.KML_PREFIX.'_player AS p, '.KML_PREFIX.'_clan AS c WHERE p.iduser='.$_SESSION['dl_login'].' AND c.idc=p.idc';
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	if(mysql_num_rows($rsl)>0){
		$rtr .= '<div class="bold">'.$lang['team_links'].'</div>';
		while($row=mysql_fetch_assoc($rsl)){
			foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
			$rtr .= '<a href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=clans&amp;id='.$row['idc'].'">'.$row['tag'].' - '.$row['cname'].'</a>';
			if($row['approve_user']=='Y'){
				$topt = $lang['quit_team'];
			}else{
				$topt = $lang['refuse'];
				$rtr .= ' [<a href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=player_teams&amp;id='.$row['idp'].'&amp;opt=1">'.$lang['accept'].'</a>] ';
			}
			$rtr .= ' [<a href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=player_teams&amp;id='.$row['idp'].'&amp;opt=2">'.$topt.'</a>]<br/>';
		}
	}
	$qry = 'SELECT x.idc, c.cname, c.tag FROM '.KML_PREFIX.'_player_in_clan AS x, '.KML_PREFIX.'_clan AS c WHERE c.idc=x.idc AND x.iduser='.$_SESSION['dl_login'];
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	if(mysql_num_rows($rsl)>0){
		$rtr .= '<div class="bold"><br/>'.$lang['your_invitation'].'</div>';
		while($row=mysql_fetch_assoc($rsl)){
			foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
			$rtr .= '<a href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=clans&amp;id='.$row['idc'].'">'.$row['tag'].' - '.$row['cname'].'</a> [<a href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=player_teams&amp;id='.$row['idc'].'&amp;opt=3">'.$lang['delete'].'</a>]<br/>';
		}
	}
	$rtr .= content_line('B');
	return $rtr;
}

Function link_player($idp,$post){
	global $lang;
	$idp = intval($idp);
	$rtr = content_line('M',$lang['sqd_edit']);
	if($post['opt1']){
		$qry = SQL('SELECT iduser FROM '.KML_PREFIX.'_users WHERE login=%s', $post['login']);
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		if(mysql_num_rows($rsl)>0){
			$row = mysql_fetch_assoc($rsl);
			foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
			$qry = 'UPDATE '.KML_PREFIX.'_player SET iduser='.$row['iduser'].', approve_user="N" WHERE idp='.$idp.' AND idc='.(int)$_SESSION['dl_config']['idc'];
			query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
			$rtr .= $lang['request_sent'];
			$qry = 'SELECT tag, cname FROM '.KML_PREFIX.'_clan WHERE idc='.(int)$_SESSION['dl_config']['idc'];
			$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
			$crow = mysql_fetch_assoc($rsl);
			foreach($crow as $ky=>$vl) $crow[$ky] = intoBrowser($vl);
			send_pm($row['iduser'],$_SESSION['dl_login'],$lang['team_invite'],str_replace('{{team_name}}', $crow['tag'].' - '.$crow['cname'], $lang['team_invite_content']));
		}else $rtr .= $lang['account_doesnt_exist'];
	}elseif($post['opt3']){
		$qry = 'UPDATE '.KML_PREFIX.'_player SET iduser="0" WHERE idp='.$idp;
		query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		$rtr .= $lang['player_link_removed'];
	}
	$qry = 'SELECT p.iduser, p.pname, u.login, p.approve_user FROM '.KML_PREFIX.'_player AS p LEFT JOIN '.KML_PREFIX.'_users AS u USING(iduser) WHERE idp='.$idp;
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	$row = mysql_fetch_assoc($rsl);
	foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
	$rtr .= '<form action="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=team_managment&amp;opx=link_player" method="post"><input type="hidden" name="idp" value="'.$idp.'"/>';
	if($row['iduser']>0){
		if($row['approve_user']=='N') $apvd = '['.$lang['not_approved'].']';
		$rtr .= $row['pname'].' '.$lang['player_linked'].' -> <a href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=users&amp;id='.$row['iduser'].'">'.$row['login'].'</a> '.$apvd.' <input type="submit" name="opt3" value="'.$lang['delete'].'"/>';
	}else $rtr .= $row['pname'].' -> '.$lang['player_link_with'].' <input type="text" name="login" value="'.$post['login'].'"/> <input type="submit" name="opt1" value="'.$lang['link'].'"/>';
	$rtr .= '</form>';
	return $rtr;
}

Function players_invitation($get){
	global $lang;
	$rtr = content_line('M',$lang['players_invitation']);
	if(KML_MULTI_LEAGUES=='N'){
		$qry = 'SELECT COUNT(p.idp) FROM '.KML_PREFIX.'_player_in AS x, '.KML_PREFIX.'_player AS p WHERE p.idp=x.idp AND x.league='.LEAGUE.' AND p.idc='.(int)$_SESSION['dl_config']['idc'];
		$players_limit = (MAX_PLAYERS - mysql_result(query(__FILE__,__FUNCTION__,__LINE__,$qry,0),0));
		if($players_limit<1 && MAX_PLAYERS>0){
			$block = 1;
		}
	}
	if($get['opt']==1){
		$qry = 'DELETE FROM '.KML_PREFIX.'_player_in_clan WHERE iduser='.(int)$get['id'].' AND idc='.(int)$_SESSION['dl_config']['idc'];
		query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		if(mysql_affected_rows()==1){
			$qry = 'SELECT login FROM '.KML_PREFIX.'_users WHERE iduser='.(int)$get['id'];
			$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
			$row = mysql_fetch_assoc($rsl);
			foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
			if(KML_MULTI_LEAGUES=='Y'){
				$qry = 'INSERT INTO '.KML_PREFIX.'_player(idc, iduser, pname, approve_user, function) VALUES('.(int)$_SESSION['dl_config']['idc'].', '.(int)$get['id'].', "'.$row['login'].'", "Y", "")';
				query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
				$qry = 'SELECT tag, cname FROM '.KML_PREFIX.'_clan WHERE idc='.(int)$_SESSION['dl_config']['idc'];
				$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
				$crow = mysql_fetch_assoc($rsl);
				foreach($crow as $ky=>$vl) $crow[$ky] = intoBrowser($vl);
				send_pm($get['id'],$_SESSION['dl_login'],$lang['team_invite'],str_replace('{{team_name}}', $crow['tag'].' - '.$crow['cname'], $lang['player_invite_accepted']));
				$rtr .= $lang['player_added'].'<br/><br/>';
			}else{
				if($block==1) $rtr .= $lang['players_limit_invit_err1'];
				else{
					$qry = 'INSERT INTO '.KML_PREFIX.'_player(idc, iduser, pname, approve_user, function) VALUES('.(int)$_SESSION['dl_config']['idc'].', '.(int)$get['id'].', "'.$row['login'].'", "Y", "")';
					query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
					$qry = 'INSERT INTO '.KML_PREFIX.'_player_in(idp, league) VALUES('.mysql_insert_id().', '.LEAGUE.')';
					query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
					$qry = 'SELECT tag, cname FROM '.KML_PREFIX.'_clan WHERE idc='.(int)$_SESSION['dl_config']['idc'];
					$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
					$crow = mysql_fetch_assoc($rsl);
					foreach($crow as $ky=>$vl) $crow[$ky] = intoBrowser($vl);
					send_pm($get['id'],$_SESSION['dl_login'],$lang['team_invite'],str_replace('{{team_name}}', $crow['tag'].' - '.$crow['cname'], $lang['player_invite_accepted']));
					$rtr .= $lang['player_added'].'<br/><br/>';
				}
			}
		}
	}elseif($get['opt']==2){
		$qry = 'DELETE FROM '.KML_PREFIX.'_player_in_clan WHERE iduser='.(int)$get['id'].' AND idc='.(int)$_SESSION['dl_config']['idc'];
		query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		$qry = 'SELECT tag, cname FROM '.KML_PREFIX.'_clan WHERE idc='.(int)$_SESSION['dl_config']['idc'];
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		$crow = mysql_fetch_assoc($rsl);
		foreach($crow as $ky=>$vl) $crow[$ky] = intoBrowser($vl);
		send_pm($get['id'],$_SESSION['dl_login'],$lang['team_invite'],str_replace('{{team_name}}', $crow['tag'].' - '.$crow['cname'], $lang['player_invite_refused']));
		$rtr .= $lang['invitation_refused2'].'<br/><br/>';
	}
	$qry = 'SELECT u.login, u.iduser FROM '.KML_PREFIX.'_users AS u, '.KML_PREFIX.'_player_in_clan AS x WHERE u.iduser=x.iduser AND x.idc='.(int)$_SESSION['dl_config']['idc'];
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	if(mysql_num_rows($rsl)>0){
		while($row=mysql_fetch_assoc($rsl)){
			foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
			$rtr .= '<a href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=users&amp;id='.$row['iduser'].'">'.$row['login'].'</a>';
			if($block!=1) $rtr .= ' [<a href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=team_managment&amp;opx=players_invitation&amp;id='.$row['iduser'].'&amp;opt=1">'.$lang['accept'].'</a>]';
			$rtr .= ' [<a href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=team_managment&amp;opx=players_invitation&amp;id='.$row['iduser'].'&amp;opt=2">'.$lang['refuse'].'</a>]<br/>';
		}
		if($block==1) $rtr .= '<br/><br/>'.$lang['players_limit_invit_err1'];
	}else $rtr .= $lang['no_invitations'];
	return $rtr;
}

Function edit_clanmates(){
	global $lang;
	$functions = array(''=>'', 'C'=>$lang['func_cl'], 'W'=>$lang['func_war'], 'M'=>$lang['team_member'], 'R'=>$lang['team_recruit']);
	$rtr = content_line('M',$lang['sqd_edit']);
	$qry = 'SELECT i.idp, c.name FROM '.KML_PREFIX.'_player_in AS i, '.KML_PREFIX.'_config AS c WHERE c.league=i.league AND c.transfers="N"';
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	while($row=mysql_fetch_assoc($rsl)){
		foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
		$restricted_players[$row['idp']] = $row['name'];
	}
	$qry = 'SELECT p.idp FROM '.KML_PREFIX.'_player_in AS x, '.KML_PREFIX.'_player AS p WHERE p.idp=x.idp AND p.idc='.(int)$_SESSION['dl_config']['idc'].' AND x.league='.LEAGUE;
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	while($row=mysql_fetch_assoc($rsl)) $players_in[$row['idp']] = 1;
	$qry = 'SELECT COUNT(p.idp) FROM '.KML_PREFIX.'_player_in AS x, '.KML_PREFIX.'_player AS p WHERE p.idp=x.idp AND x.league='.LEAGUE.' AND p.idc='.(int)$_SESSION['dl_config']['idc'];
	$all_players = @mysql_result(query(__FILE__,__FUNCTION__,__LINE__,$qry,0),0);
	if(TRANSFERS=='N' && KML_MULTI_LEAGUES!='Y') $err = $lang['sqd_edit_err'].'<br/>';
	if($err) $rtr .= $err;
	else{
		$cqry = 'SELECT idp, pname, contact, ident, function, other, iduser, approve_user FROM '.KML_PREFIX.'_player WHERE idc='.(int)$_SESSION['dl_config']['idc'].' ORDER BY function ASC, pname ASC';
		$crsl = query(__FILE__,__FUNCTION__,__LINE__,$cqry,0);
		if(KML_MULTI_LEAGUES=='Y'){
			$cspan = 7;
			$inl = '<td class="tab_head3">'.$lang['in_league'].'</td>';
			if(TRANSFERS=='N'){
				$err2 = $lang['sqd_edit_err'].'<br/>';
				$blocked = 1;
			}
			if($all_players>=MAX_PLAYERS && MAX_PLAYERS>0){
				$err3 = $lang['max_players'].'<br/>';
				$blocked_new = 1;
			}
		}else $cspan = 6;
		$rtr .= '<form method="post" action="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=team_managment&amp;opx=save_clanmates"><input type="hidden" name="idc" value="'.$row['idc'].'" />
		<table cellspacing="1" cellpadding="5" class="tab_brdr">
		<tr>'.$inl.'<td class="tab_head3">'.$lang['nick'].'</td><td class="tab_head3">'.$lang['contact'].'</td><td class="tab_head3">'.$lang['ply_other'].'</td><td class="tab_head3">'.$lang['ip'].'</td><td class="tab_head3">'.$lang['function'].'</td><td class="tab_head2"></td></tr>';
		while($crow=mysql_fetch_assoc($crsl)){
			foreach($crow as $ky=>$vl) $crow[$ky] = intoBrowser($vl);
			$ronly = '';
			$ronlyN = '';
			$checkd = '';
			if(KML_MULTI_LEAGUES=='Y'){
				if($blocked) $ronly = ' disabled';
				if($restricted_players[$crow['idp']]) $ronlyN = ' readonly';
				if($players_in[$crow['idp']]) $checkd = ' checked';
				if(!$players_in[$crow['idp']] && $blocked_new==1) $ronly = ' disabled';
				if($checkd == ' checked') $old = 'Y'; else $old = 'N';
				if($ronly == ' disabled') $dis = 'Y'; else $dis = 'N';
				$inl = '<td><input type="hidden" name="in_league_old['.$crow['idp'].']" value="'.$old.'"/><input type="hidden" name="in_league_dis['.$crow['idp'].']" value="'.$dis.'"/><input'.$checkd.' type="checkbox"'.$ronly.' name="in_league['.$crow['idp'].']" value="Y"/></td><td>';
			}else $inl = '<td><input type="hidden" name="in_league_old['.$crow['idp'].']" value="Y"/><input type="hidden" name="in_league['.$crow['idp'].']" value="Y"/>';
			if($crow['approve_user']=="Y" && $crow['iduser']>0) $linked = '<a href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=users&amp;id='.$crow['iduser'].'"><img title="'.$lang['profile'].'" alt="link" src="'.SKIN.'img/linked.gif"/></a>';
			else $linked = '';
			$rtr .= '<tr class="content2">'.$inl.'
			<input type="hidden" name="idp['.$crow['idp'].']" value="'.$crow['idp'].'" /><input'.$ronlyN.' type="text" name="pname['.$crow['idp'].']" value="'.$crow['pname'].'" maxlength="15" size="15" /></td><td><input type="text" name="contact['.$crow['idp'].']" value="'.$crow['contact'].'" size="30" maxlength="60" /></td><td><input type="text" name="other['.$crow['idp'].']" value="'.$crow['other'].'" size="20" maxlength="20" /></td><td><input'.$ronlyN.' type="text" name="ident['.$crow['idp'].']" value="'.$crow['ident'].'" size="30" maxlength="30" /></td><td><select name="function['.$crow['idp'].']">'.array_assoc($functions,$crow['function']).'</select></td><td><a href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=team_managment&amp;opx=link_player&amp;idp='.$crow['idp'].'"><img title="'.$lang['link_with_account'].'" alt="link" src="'.SKIN.'/img/link.gif"/></a>'.$linked.'</td>
			</tr>';
		}
		$rtr .= '<tr><td align="right" colspan="'.$cspan.'" class="content3"><input type="submit" name="option" value="'.$lang['save'].'" /></td></tr>
		</table>
		</form>';
	}
	if(KML_MULTI_LEAGUES=='Y') $rtr .= '<br/>'.$lang['clan_squad_inf2'].'<br/>';
	$rtr .= '<br/>'.$err2.$err3;
	return $rtr;
}

Function save_clanmates($dts){
	global $lang;
	$dts['idc'] = intval($dts['idc']);
	$qry = 'SELECT COUNT(p.idp) FROM '.KML_PREFIX.'_player_in AS x, '.KML_PREFIX.'_player AS p WHERE p.idp=x.idp AND x.league='.LEAGUE.' AND p.idc='.(int)$_SESSION['dl_config']['idc'];
	$all_players = @mysql_result(query(__FILE__,__FUNCTION__,__LINE__,$qry,0),0);
	$rtr .= content_line('M',$lang['sqd_edit']);
	foreach($dts['idp'] as $ky=>$ply){
		if(!$dts['pname'][$ply]) $qry = 'DELETE FROM '.KML_PREFIX.'_player WHERE idp='.$ply;
		else{
			$err = '';
			if(BLOCK_IDENT == 'Y'){
				$qry = SQL('SELECT p.idp FROM '.KML_PREFIX.'_player AS p, '.KML_PREFIX.'_player_in AS x WHERE x.idp=p.idp AND x.league='.LEAGUE.' AND p.ident=%s AND p.idp!='.(int)$ply, $dts['ident'][$ply]);
				$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
				if(mysql_num_rows($rsl)) $err = $dts['pname'][$ply].' - '.$lang['error_ident'].'<br/>';
				$ident = SQL('ident=%s, ', $dts['ident'][$ply]);
			}else $ident = SQL('ident=%s, ', $dts['ident'][$ply]);
			$qry = SQL('UPDATE '.KML_PREFIX.'_player SET pname=%s, contact=%s, '.$ident.'function=%s, other=%s WHERE idp=%d', $dts['pname'][$ply], $dts['contact'][$ply], $dts['function'][$ply], $dts['other'][$ply], $ply);
		}
		if(!$err){
			query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
			$qryc = '';
			if(!$dts['pname'][$ply]){
				$qryc = 'DELETE FROM '.KML_PREFIX.'_player_in WHERE idp='.(int)$ply;
			}elseif($dts['in_league_old'][$ply]=='Y' && $dts['in_league_dis'][$ply]!="Y" && $dts['in_league'][$ply]!='Y'){
				$qryc = 'DELETE FROM '.KML_PREFIX.'_player_in WHERE idp='.(int)$ply.' AND league='.LEAGUE;
				--$all_players;
			}elseif($dts['in_league_old'][$ply]!='Y' && $dts['in_league'][$ply]=='Y'){
				if($all_players>=MAX_PLAYERS && MAX_PLAYERS>0) $err2 = $lang['max_players'].'<br/>';
				else $qryc = 'INSERT INTO '.KML_PREFIX.'_player_in(idp, league) VALUES('.(int)$ply.', '.LEAGUE.')';
				++$all_players;
			}
#			echo $qryc.'|'.$dts['pname'][$ply].'|'.$dts['in_'.KML_PREFIX.'_old'][$ply].'|'.$dts['in_'.KML_PREFIX.'_dis'][$ply].'|'.$dts['in_league'][$ply].'<br/>';
			if($qryc) query(__FILE__,__FUNCTION__,__LINE__,$qryc,0);
		}else $rtr .= $err;
	}
	$rtr .= $lang['squad_upd'];
	$rtr .= '<br/><br/>'.$err2;
	return $rtr;
}

Function team_managment($opx,$post,$get,$req){
	global $lang;
	edit_team_opt();
	$rtr = content_line('T',$lang['team_managment']);
	$rtr .= '<td align="center"> &raquo; <a class="linkC" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=team_managment&amp;opx=clan_info">'.$lang['team_info'].'</a> &laquo; &raquo; <a class="linkC" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=team_managment&amp;opx=edit_clanmates">'.$lang['sqd_edit'].'</a> &laquo; &raquo; <a class="linkC" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=team_managment&amp;opx=new_clanmates">'.$lang['new_clanmts'].'</a> &laquo; &raquo; <a class="linkC" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=team_managment&amp;opx=players_invitation">'.$lang['players_invitation'].'</a> &laquo;';
	switch($opx){
		case 'new_clanmates': $rtr .= new_clanmates(); break;
		case 'add_clanmates': $rtr .= add_clanmates($post); break;
		case 'edit_clanmates': $rtr .= edit_clanmates(); break;
		case 'save_clanmates': $rtr .= save_clanmates($post); break;
		case 'link_player': $rtr .= link_player($req['idp'],$post); break;
		case 'players_invitation': $rtr .= players_invitation($get); break;
		case 'save_claninfo': $rtr .= save_claninfo($post); break;
		default: $rtr .= clan_info();
	}
	$rtr .= content_line('B');
	return $rtr;
}

Function clan_info(){
	global $lang;
	global $countries;
	$rtr = content_line('M',$lang['clan_info']);
	$qry = 'SELECT cname, tag, cwww, country, channel, info, clan_passw FROM '.KML_PREFIX.'_clan WHERE idc='.$_SESSION['dl_config']['idc'];
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	$row = mysql_fetch_assoc($rsl);
	foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
	$rtr .= '<form method="post" enctype="multipart/form-data" action="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=team_managment&amp;opx=save_claninfo"><input type="hidden" name="idc" value="'.$row['idc'].'" />
	<table cellspacing="1" cellpadding="5" width="400" class="tab_brdr">
	<tr><td align="center" colspan="2" class="tab_head2">'.$lang['prof_info'].'</td></tr>
	<tr><td class="tab_head2">'.$lang['name'].':</td><td class="content1">'.$row['cname'].'</td></tr>
	<tr><td class="tab_head2">'.$lang['tag'].':</td><td class="content1">'.$row['tag'].'</td></tr>
	<tr><td class="tab_head2">'.$lang['clan_site'].':</td><td class="content1"><input type="text" name="cwww" value="'.$row['cwww'].'" maxlength="30" size="40" /></td></tr>
	<tr><td class="tab_head2">'.$lang['country'].':</td><td class="content1"><select name="country">'.array_assoc($countries,$row['country']).'</select></td></tr>
	<tr><td class="tab_head2">#'.$lang['channel'].':</td><td class="content1"><input type="text" name="channel" value="'.$row['channel'].'" maxlength="20" size="40" /></td></tr>
	<tr><td class="tab_head2">'.$lang['clan_join_passw'].':</td><td class="content1"><input type="text" name="clan_passw" value="'.$row['clan_passw'].'" maxlength="20" size="40" /></td></tr>
	<tr><td class="tab_head2">'.$lang['clan_logo'].':</td><td class="content1"><input size="22" name="logo" type="file" /></td></tr>
	<tr><td class="tab_head2" valign="top">'.$lang['other_info'].':</td><td class="content1"><textarea name="info" rows="4" cols="40">'.$row['info'].'</textarea></td></tr>
	<tr><td colspan="2" align="right" class="content1"><input type="submit" value="'.$lang['save'].'" /></td></tr>
	</table>
	</form>';
	return $rtr;
}

Function save_claninfo($dts){
	global $lang;
	$dts['idc'] = intval($dts['idc']);
	$qry = 'SELECT extension FROM '.KML_PREFIX.'_clan WHERE idc='.$_SESSION['dl_config']['idc'];
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	$row = mysql_fetch_assoc($rsl);
	foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
	$rtr = content_line('M',$lang['edit_cinfo']);
/*
	$size = getimagesize($_FILES['logo']['tmp_name']);
	$width = $size[0];
	$height = $size[1];
	$res = explode(':', CLAN_LOGO_RES);
	if((int)$res[0]!=0 || (int)$res[1]!=0){
		if($width>$res[0] || $height>$res[1]) $status .= $lang['photo_info1'];
	}
*/
	if($_FILES['logo']['size']>0){
		if($_FILES['logo']['size']>200000) $rtr .= $lang['logo_err1'];
		else{
			$allow_ext = array('jpg', 'gif', 'png');
			if($row['extension']) @unlink(DIR.'_clan_logo/'.$_SESSION['dl_config']['idc'].'.'.$row['extension']);
			$ext = strtolower(substr($_FILES['logo']['name'],-3));
			if(!in_array($ext,$allow_ext)) die($lang['sure']);
			$extension = SQL(', extension=%s', $ext);
			$new_name = DIR.'_clan_logo/'.$_SESSION['dl_config']['idc'].'.'.$ext;
			move_uploaded_file($_FILES['logo']['tmp_name'], $new_name);
			chmod($new_name, 0777);
			$rtr .= $lang['logo_upl'].'<br/>';
		}
	}
	if($dts['cwww']!='' && !eregi("^http://",$dts['cwww'])) $dts['cwww'] = 'http://'.$dts['cwww'];
	if($dts['channel']!='' && eregi("^#",$dts['channel'])) $dts['channel'] = substr($dts['channel'],1);
	$qry = SQL('UPDATE '.KML_PREFIX.'_clan SET cwww=%s, channel=%s, info=%s, country=%d'.$extension.', clan_passw=%s WHERE idc=%d', $dts['cwww'], $dts['channel'], substr($dts['info'], 0, 250), $dts['country'], $dts['clan_passw'], $_SESSION['dl_config']['idc']);
	if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) $rtr .= $lang['info_sav'];
	return $rtr;
}

Function clans($id,$so,$grants){
	global $lang;
	$id = intval($id);
	if($id>0){
		if(LEAGUE_TYPE=='D') $qry = 'SELECT iduser AS idc, login AS cname, login AS tag, clan_www AS cwww, country FROM '.KML_PREFIX.'_users WHERE iduser='.$id;
		else $qry = 'SELECT idc, cname, tag, cwww, date, info, activ, country, channel, extension, iduser FROM '.KML_PREFIX.'_clan WHERE idc='.$id;
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		if(mysql_num_rows($rsl)!=1){
			$rtr = $lang['sure'];
			return $rtr;
		}
		$row = mysql_fetch_assoc($rsl);
		foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
		$rtr = content_line('T',$row['cname'].' '.show_flag($row['country']));
		$rtr .= '<td align="center">
		&raquo; <a class="linkC" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=clans&amp;id='.$id.'">'.$lang['prof_info'].'</a> &laquo;';
		if(LEAGUE_TYPE=='T') $rtr .= ' &raquo; <a class="linkC" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=clans&amp;id='.$id.'&amp;so=sqd">'.$lang['squad'].'</a> &laquo;';
		$rtr .= ' &raquo; <a class="linkC" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=clans&amp;id='.$id.'&amp;so=pld">'.$lang['pld_matches'].'</a> &laquo; &raquo; <a class="linkC" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=clans&amp;id='.$id.'&amp;so=upc">'.$lang['umatch'].'</a> &laquo; &raquo; <a class="linkC" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=clans&amp;id='.$id.'&amp;so=sts">'.$lang['lstats'].'</a> &laquo; &raquo; <a class="linkC" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=clans&amp;id='.$id.'&amp;so=awa">'.$lang['awards'].'</a> &laquo;';
		$file = DIR.'_clan_logo/'.$id.'.'.$row['extension'];
		if(file_exists($file)) $logo = '<div align="center"><a href="'.$row['cwww'].'" target="_blank"><img alt="logo" title="'.$row['cname'].'" src="'.$file.'" hspace="10" vspace="10" /></a></div>';
		switch($so){
			case 'sts': $rtr .= clan_stats_info($id); break;
			case 'awa': $rtr .= clan_awards_info($id); break;
			case 'sqd': $rtr .= clan_squad_info($id,$grants,$logo); break;
			case 'pld': $rtr .= clan_matches_info($id,1); break;
			case 'upc': $rtr .= clan_matches_info($id,2); break;
			default: $rtr .= main_clan_info($row,$logo);
		}
		$rtr .= content_line('B');
	}else{
		$rtr = content_line('T',$lang['clans']);
		$rtr .= '<td align="center">';
		$qry = 'SELECT x.idc, MIN(a.grade) AS grade FROM '.KML_PREFIX.'_clan_award AS x, '.KML_PREFIX.'_award AS a WHERE a.ida=x.ida AND x.league='.LEAGUE.' GROUP BY x.idc';
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		while($row=mysql_fetch_assoc($rsl)) $awards[$row['idc']] = $row['grade'];
		if(LEAGUE_TYPE=='T') $qry = 'SELECT c.idc, c.tag, c.cwww, c.cname, c.country FROM '.KML_PREFIX.'_clan AS c, '.KML_PREFIX.'_in AS x WHERE x.idc=c.idc AND x.league='.LEAGUE.' ORDER BY c.cname';
		elseif(LEAGUE_TYPE=='D') $qry = 'SELECT u.login, u.country, c.idc, u.clan, u.clan_www FROM '.KML_PREFIX.'_in AS c, '.KML_PREFIX.'_users AS u WHERE c.league='.LEAGUE.' AND c.idc=u.iduser ORDER BY login';
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		if(LEAGUE_TYPE=='T') $h2 = $lang['website']; elseif(LEAGUE_TYPE=='D') $h2 = $lang['clan2'];
		$rtr .= '<table cellspacing="1" cellpadding="5" class="tab_brdr">
		<tr><td class="tab_head3">'.$lang['clan'].'</td><td class="tab_head3">'.$h2.'</td></tr>';
		while($row=mysql_fetch_assoc($rsl)){
			foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
			if(LEAGUE_TYPE=='T'){
				$inf1 = $row['tag'].' - '.$row['cname']; 
				if($row['cwww']) $inf2 = '<a class="link" target="_blank" href="'.$row['cwww'].'">'.$row['cwww'].'</a>'; else $inf2 = '&#8212;&#8212;&#8212;';
			}elseif(LEAGUE_TYPE=='D'){
				$inf1 = $row['login'];
				if($row['clan']){
					$inf2 = '';
					if($row['clan_www']) $inf2 = '<a class="link" target="_blank" href="'.$row['clan_www'].'">';
					$inf2 .= $row['clan'];
					if($row['clan_www']) $inf2 .= '</a>';
				}else $inf2 = '&#8212;&#8212;&#8212;';
			}
			if($awards[$row['idc']]) $ainf = ' <a href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=clans&amp;id='.$row['idc'].'&amp;so=awa"><img alt="'.$lang['awards'].'" title="'.$lang['level'].': '.$row['grade'].'" src="'.DIR.'_img/award'.$awards[$row['idc']].'.gif"/></a>'; else $ainf = '';
			$rtr .= '<tr class="content2"><td class="content1">'.show_flag($row['country']).' <a class="tab_link" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=clans&amp;id='.$row['idc'].'">'.$inf1.'</a>'.$ainf.'</td><td>'.$inf2.'</td></tr>';
		}
		$rtr .= '</table>'.content_line('B');
	}
	return $rtr;
}

Function main_clan_info($row,$logo){
	global $lang;
	$rtr = content_line('M',$lang['prof_info']);
	if(LEAGUE_TYPE=='D') $rtr .= show_profile($row['idc'], $grants);
	elseif(LEAGUE_TYPE=='T'){
		if($logo) $rtr .= $logo;
		$rtr .= '<table cellspacing="1" cellpadding="5" width="480" class="tab_brdr">
		<tr><td align="center" colspan="2" class="tab_head2">'.$lang['prof_info'].'</td></tr>
		<tr><td class="tab_head2" width="30%">'.$lang['name'].':</td><td width="70%" class="content1">'.$row['cname'].'</td></tr>
		<tr><td class="tab_head2" width="30%">'.$lang['tag'].':</td><td class="content1">'.$row['tag'].'</td></tr>';
		if($row['activ']=='Y') $clan_status= $lang['cactive']; else $clan_status=$lang['nactive'];
		$rtr .= '<tr><td class="tab_head2" width="30%">Status:</td><td class="content1">'.$clan_status.'</td></tr>';
		if($row['cwww']) $rtr .= '<tr><td class="tab_head2">'.$lang['website'].':</td><td class="content1"><a class="link" target="_blank" href="'.$row['cwww'].'">'.$row['cwww'].'</a></td></tr>';
		if($row['channel']) $rtr .= '<tr><td class="tab_head2">#'.$lang['channel'].':</td><td class="content1"><a class="link" target="_blank" href="irc://'.IRC_SERVER.'/'.$row['channel'].'">'.$row['channel'].'</a></td></tr>';
		$rtr .= '<tr><td class="tab_head2">'.$lang['cregistered'].':</td><td class="content1">'.show_date($row['date'], 1).'</td></tr>';
		if($row['info']) $rtr .= '<tr><td class="tab_head2">'.$lang['other_info'].':</td><td valign="top" class="content1">'.$row['info'].'</td></tr>';
		$rtr .= '</table>';
	}
	return $rtr;
}

Function clan_squad_info($id,$grants,$logo){
	global $lang;
	$function = array(1=>$lang['func_cl'], 2=>$lang['func_war'], 3=>$lang['team_member'], 4=>$lang['team_recruit']);
	$funcsort = array('C'=>1, 'W'=>2, 'M'=>3, 'R'=>4, ''=>5);
	$rtr = content_line('M',$lang['squad']);
	if($logo) $rtr .= $logo;
	$qry = 'SELECT p.idp FROM '.KML_PREFIX.'_player_in AS x, '.KML_PREFIX.'_player AS p WHERE p.idp=x.idp AND p.idc='.$id.' AND x.league='.LEAGUE;
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	while($row=mysql_fetch_assoc($rsl)) $players_in[$row['idp']] = 1;
	$qry = 'SELECT idp, pname, contact, ident, function, other, iduser, approve_user FROM '.KML_PREFIX.'_player WHERE idc='.$id.' ORDER BY pname DESC';
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	if(mysql_num_rows($rsl)>0){
		while($row=mysql_fetch_assoc($rsl)){
			foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
			if($row['approve_user']!='Y') $row['iduser'] = 0;
			$players[$row['idp']] = array('pname'=>$row['pname'], 'contact'=>$row['contact'], 'ident'=>$row['ident'], 'function'=>$funcsort[$row['function']], 'other'=>$row['other'], 'iduser'=>$row['iduser']);
		}
		function cmpl ($a, $b) { 
		   if ($a['function'] == $b['function']) return 0; 
		   return ($a['function'] > $b['function']) ? 1 : -1; 
		}
		uasort($players,'cmpl');
		$rtr .= '<table cellspacing="1" cellpadding="5" class="tab_brdr" width="480">
		<tr><td class="tab_head3">'.$lang['nick'].'</td><td class="tab_head3">'.$lang['contact'].'</td><td class="tab_head3">'.$lang['function'].'</td>';
		if($grants>0) $rtr .= '<td class="tab_head2">'.$lang['ip'].'</td>';
		$rtr .= '<td class="tab_head3">'.$lang['ply_other'].'</td></tr>';
		foreach($players as $ky=>$row){
			if($players_in[$ky]==1 && KML_MULTI_LEAGUES=='Y') $in = '* '; else $in = '';
			if($row['iduser']>0) $row['pname'] = '<a href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=users&amp;id='.$row['iduser'].'">'.$row['pname'].'</a>';
			$rtr .= '<tr class="content2"><td class="content1">'.$in.$row['pname'].'</td><td>'.$row['contact'].'</td><td>'.$function[$row['function']].'</td>';
			if($grants>0) $rtr .= '<td>'.$row['ident'].'</td>';
			$rtr .= '<td>'.$row['other'].'</td></tr>';
		}
		$rtr .= '</table>';
		if(KML_MULTI_LEAGUES=='Y') $rtr .= $lang['clan_squad_inf'].'<br/><br/>';
	if($_SESSION['dl_login']) $rtr .= '<div>
	<form style="text-align: left;" action="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=player_teams&amp;id='.$id.'&amp;opt=4" method="post">'.$lang['clan_join_passw'].': <input type="password" name="clan_passw"/> '.$lang['clan_join_reason'].': <input type="text" name="reason"/> <input type="submit" value="'.$lang['join_team'].'"/></form>
	</div>';

	}
	return $rtr;
}

Function clan_matches_info($id,$typ=1){
	global $lang;
	if($typ==1){
		$head = $lang['pld_matches'];
		$where = '(win1>0 OR draw>0 OR win2>0)';
		$info = $lang['no_matches'];
	}elseif($typ==2){
		$head = $lang['umatch'];
		$where = '(win1=0 AND draw=0 AND win2=0)';
		$info = $lang['schedule_err1'];
		$mscore = '&mdash;&mdash;&mdash;';
	}
	$rtr = content_line('M',$head);
	if(LEAGUE_TYPE=='D') $qry = 'SELECT m.ids, s.sname, m.idm, m.rnd, m.idpt, g.gname, m.idc1, c1.login AS cnm1, c1.country AS c1ctry, m.idc2, c2.login AS cnm2, c2.country AS c2ctry, m.idg, m.judge, m.date, m.idg, m.type, m.win1, m.win2 FROM '.KML_PREFIX.'_users AS c1, '.KML_PREFIX.'_users AS c2, '.KML_PREFIX.'_season AS s, '.KML_PREFIX.'_match AS m LEFT JOIN '.KML_PREFIX.'_group AS g USING(idg) WHERE m.ids=s.ids AND m.league='.LEAGUE.' AND m.idc1=c1.iduser AND m.idc2=c2.iduser AND (m.idc1='.$id.' or m.idc2='.$id.') AND '.$where.' ORDER BY m.date DESC';
	else $qry = 'SELECT m.ids, s.sname, m.idm, m.rnd, g.gname, m.idpt, m.idc1, c1.cname AS cnm1, c1.country AS c1ctry, m.idc2, c2.cname AS cnm2, c2.country AS c2ctry, m.idg, m.judge, m.date, m.idg, m.type, m.win1, m.win2 FROM '.KML_PREFIX.'_clan AS c1, '.KML_PREFIX.'_clan AS c2, '.KML_PREFIX.'_season AS s, '.KML_PREFIX.'_match AS m LEFT JOIN '.KML_PREFIX.'_group AS g USING(idg) WHERE m.ids=s.ids AND m.league='.LEAGUE.' AND m.idc1=c1.idc AND m.idc2=c2.idc AND (m.idc1='.$id.' or m.idc2='.$id.') AND '.$where.' ORDER BY m.date DESC';
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	if(mysql_num_rows($rsl)>0){
		$rtr .= '<table cellspacing="1" cellpadding="5" class="tab_brdr">
		<tr><td class="tab_head3">'.$lang['date'].'</td><td class="tab_head3">'.$lang['enmy'].'</td><td class="tab_head3">'.$lang['type'].'</td><td class="tab_head3">'.$lang['score'].'</td><td class="tab_head3">'.$lang['referee'].'</td></tr>';
		while($row = mysql_fetch_assoc($rsl)){
			foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
			if($row['idg']) $type = '<a class="link" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=gr&amp;id='.$row['idg'].'">'.$row['gname'].'</a>'; elseif(!$row['type']) $type = '<a class="link" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=poff&amp;id='.$row['idpt'].'">'.$lang['playoff'].'</a>'; else $type = $row['type'];
			if($row['idc1']==$id){
				$enemy = '<a class="link" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=clans&amp;id='.$row['idc2'].'">'.show_flag($row['c2ctry']).' '.$row['cnm2'].'</a>';
				if(!$mscore){
					if($row['win1']>$row['win2']) $score = '<img alt="'.$lang['win'].'" title="'.$lang['win'].'" src="'.SKIN.'img/win.gif" /> '; elseif($row['win1']<$row['win2']) $score = '<img alt="'.$lang['defeat'].'" title="'.$lang['defeat'].'" src="'.SKIN.'img/lost.gif" /> '; else $score = '<img alt="'.$lang['draw'].'" title="'.$lang['draw'].'" src="'.SKIN.'img/draw.gif" /> ';
					$score .= $row['win1'].':'.$row['win2'];
				}else $score = $mscore;
			}else{
				$enemy = '<a class="link" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=clans&amp;id='.$row['idc1'].'">'.show_flag($row['c1ctry']).' '.$row['cnm1'].'</a>';
				if(!$mscore){
					if($row['win1']<$row['win2']) $score = '<img alt="'.$lang['win'].'" title="'.$lang['win'].'" src="'.SKIN.'img/win.gif"/> '; elseif($row['win1']>$row['win2']) $score = '<img alt="'.$lang['defeat'].'" title="'.$lang['defeat'].'" src="'.SKIN.'img/lost.gif" /> '; else $score = '<img alt="'.$lang['draw'].'" title="'.$lang['draw'].'" src="'.SKIN.'img/draw.gif" /> ';
					$score .= $row['win2'].':'.$row['win1'];
				}else $score = $mscore;
			}
			if($row['judge']>0){
				$jqry = 'SELECT login, country, iduser FROM '.KML_PREFIX.'_users WHERE iduser='.$row['judge'];
				$jrsl = query(__FILE__,__FUNCTION__,__LINE__,$jqry,0);
				if(mysql_num_rows($jrsl)>0){
					$jrow = mysql_fetch_assoc($jrsl);
					foreach($jrow as $ky=>$vl) $jrow[$ky] = intoBrowser($vl);
					$judge = '<a class="link" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=users&amp;id='.$jrow['iduser'].'">'.show_flag($jrow['country']).' '.$jrow['login'].'</a>';
				}else $judge = '&mdash;&mdash;&mdash;';
			}else $judge = '&mdash;&mdash;&mdash;';
			if($ids != $row['ids']) $rtr .= '<tr><td colspan="5" class="content4">'.$row['sname'].'</td></tr>';
			$rtr .= '<tr><td class="content2">'.show_date($row['date'], 1).'</td><td class="content1">'.$enemy.'</td><td class="content2">'.$type.'</td><td class="content2" align="center"><a class="link" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=descr&amp;id='.$row['idm'].'">'.$score.'</a></td><td class="content1">'.$judge.'</td></tr>';
			$ids = $row['ids'];
		}
		$rtr .= '</table>';
	}else $rtr .= $info.'<br/><br/>';
	return $rtr;
}

Function clan_stats_info($id){
	global $lang;
	$qry = 'SELECT win1, win2, draw, idc1, idc2, frags1, frags2 FROM '.KML_PREFIX.'_match WHERE league='.LEAGUE.' AND (win1>0 OR win2>0 OR draw>0) AND (idc1='.$id.' OR idc2='.$id.')';
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	$rtr = content_line('M',$lang['lstats']);
	$wo = 0;
	$plwo = 0;
	$wins = 0;
	$losts = 0;
	$win = 0;
	$lost = 0;
	if(mysql_num_rows($rsl)>0){
		while($row=mysql_fetch_assoc($rsl)){
			foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
			if($row['idc1']==$id){
				$frags += $row['frags1'];
				$deaths += $row['frags2'];
				if($row['win1']>$row['win2']) ++$win; elseif($row['win1']<$row['win2']) ++$lost; else ++$draw;
			}elseif($row['idc2']==$id){
				$frags += $row['frags2'];
				$deaths += $row['frags1'];
				if($row['win1']<$row['win2']) ++$win; elseif($row['win1']>$row['win2']) ++$lost; else ++$draw;
			}
		}
		$qry = 'SELECT m.idc1, m.idc2, x.map, x.frags1, x.frags2, x.wo, x.played FROM '.KML_PREFIX.'_match_map AS x, '.KML_PREFIX.'_match AS m WHERE m.idm=x.idm AND (m.idc1='.$id.' OR m.idc2='.$id.')';
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		while($row=mysql_fetch_assoc($rsl)){
			foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
			if($row['played']==1){
				if($row['wo']==$id) ++$wo; elseif($row['wo']>0) ++$plwo;
				else{
					++$maps;
					++$all_maps[$row['map']];
					if($row['idc1']==$id){
						if($row['frags1']>$row['frags2']) ++$wins; elseif($row['frags1']<$row['frags2']) ++$losts; else ++$draws;
					}else{
						if($row['frags2']>$row['frags1']) ++$wins; elseif($row['frags2']<$row['frags1']) ++$losts; else ++$draws;
					}
				}
			}
		}
		$fnet = ($frags-$deaths);
		if($frags<1) $frags = 0;
		if($deaths<1) $deaths = 0;
		$all_matches = $win+$lost+$draw;
		$maps_sum = $maps+$wo+$plwo;
		$rtr .= '<table>
		<tr><td valign="top">
		<table cellspacing="1" cellpadding="5" class="tab_brdr">
		<tr><td colspan="2" class="tab_head3">'.$lang['matches'].'</td></tr>
		<tr><td class="content2">'.$lang['m_all'].':</td><td class="content1">'.$all_matches.'</td></tr>
		<tr><td class="content2">'.$lang['m_win'].':</td><td class="content1">'.$win.' ('.round(($win/$all_matches)*100,2).' %)</td></tr>
		<tr><td class="content2">'.$lang['m_lost'].':</td><td class="content1">'.$lost.' ('.round(($lost/$all_matches)*100,2).' %)</td></tr>
		<tr><td class="content2">'.$lang['net'].':</td><td class="content1">'.($win-$lost).'</td></tr>
		<tr><td class="content2">'.$lang['m_eff'].':</td><td class="content1">'.round(($win/$all_matches)*100,2).' %</td></tr>
		</table>
		</td><td valign="top">
		<table cellspacing="1" cellpadding="5" class="tab_brdr">
		<tr><td colspan="2" class="tab_head3">'.$lang['maps'].'</td></tr>
		<tr><td class="content2">'.$lang['m_all'].':</td><td class="content1">'.$maps_sum.'</td></tr>
		<tr><td class="content2">'.$lang['m_win'].':</td><td class="content1">'.$wins.' ('.round(($wins/($wins+$losts))*100,2).' %)</td></tr>
		<tr><td class="content2">'.$lang['m_lost'].':</td><td class="content1">'.$losts.' ('.round(($losts/($wins+$losts))*100,2).' %)</td></tr>
		<tr><td class="content2">'.$lang['gv_wo'].':</td><td class="content1">'.$wo.'</td></tr>
		<tr><td class="content2">'.$lang['gt_wo'].':</td><td class="content1">'.$plwo.'</td></tr>
		<tr><td class="content2">'.$lang['net'].':</td><td class="content1">'.($wins+$plwo-$losts-$wo).'</td></tr>
		<tr><td class="content2">'.$lang['m_eff'].':</td><td class="content1">'.round((($wins+$plwo)/($maps_sum))*100,2).' %</td></tr>
		</table>
		</td><td valign="top">';
		if($frags>0 || $deaths>0){
			$rtr .= '<table cellspacing="1" cellpadding="5" class="tab_brdr">
			<tr><td colspan="2" class="tab_head3">'.$lang['frags2'].' - '.$lang['deaths2'].'</td></tr>
			<tr><td class="content2">'.$lang['frags2'].':</td><td class="content1">'.$frags.' ('.round(($frags/($frags+$deaths))*100,2).' %)</td></tr>
			<tr><td class="content2">'.$lang['deaths2'].':</td><td class="content1">'.$deaths.' ('.round(($deaths/($frags+$deaths))*100,2).' %)</td></tr>
			<tr><td class="content2">'.$lang['net'].':</td><td class="content1">'.$fnet.'</td></tr>
			<tr><td class="content2">'.$lang['m_eff'].':</td><td class="content1">'.round(($frags/($frags+$deaths))*100,2).' %</td></tr>
			<tr><td class="content2">'.$lang['m_frg'].':</td><td class="content1">'.round($frags/($all_matches),2).'</td></tr>
			<tr><td class="content2">'.$lang['m_dts'].':</td><td class="content1">'.round($deaths/($all_matches),2).'</td></tr>
			<tr><td class="content2">'.$lang['m_frg2'].':</td><td class="content1">'.round($frags/($maps),2).'</td></tr>
			<tr><td class="content2">'.$lang['m_dts2'].':</td><td class="content1">'.round($deaths/($maps),2).'</td></tr>
			</table>';
		}
		$rtr .= '</td></tr><tr><td colspan="3">';
		if(count($all_maps)>0){
			$rtr .= '<table cellspacing="1" cellpadding="5" class="tab_brdr">
			<tr><td colspan="4" class="tab_head3">'.$lang['maps'].'</td></tr>';
			arsort($all_maps);
			foreach($all_maps as $key=>$value){
				$prct = round(($value/$maps)*100,2);
				$rtr .= '<tr><td class="content1">'.$key.':</td><td class="content2"><img alt="vote" src="'.SKIN.'img/vote.gif" height="11" width="'.floor($prct).'" /><img alt="bb" src="'.DIR.'_img/bb.gif" height="11" width="'.ceil((($maps-$value)/$maps)*100).'" /></td><td class="content1">'.$value.'</td><td class="content2">'.$prct.' %</td></tr>';
			}
			$rtr .= '</table>';
		}
		$rtr .= '</td></tr>
		</table>';
	}else $rtr .= $lang['no_matches'].'<br/><br/>';
	return $rtr;
}

Function clan_awards_info($id){
	global $lang;
	$rtr = content_line('M',$lang['awards']);
	$qry = 'SELECT s.sname, a.name, a.ida, a.img, a.grade FROM '.KML_PREFIX.'_season AS s, '.KML_PREFIX.'_award AS a, '.KML_PREFIX.'_clan_award AS x WHERE x.league='.LEAGUE.' AND x.ida=a.ida AND x.ids=s.ids AND x.idc='.$id.' ORDER BY x.ids DESC';
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	$rtr .= '<table cellspacing="1" cellpadding="5" class="tab_brdr">
	<tr><td class="tab_head3" colspan="2">'.$lang['name'].'</td><td class="tab_head3">'.ucfirst(strtolower($lang['season'])).'</td></tr>';
	while($row=mysql_fetch_assoc($rsl)){
		foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
		$rtr .= '<tr class="content2"><td><img alt="'.$lang['awards'].'" src="awards/'.$row['img'].'"/></td><td class="content1">'.$row['name'].' <img alt="'.$lang['awards'].'" title="'.$lang['level'].': '.$row['grade'].'" src="'.DIR.'_img/award'.$row['grade'].'.gif"/></td><td>'.$row['sname'].'</td></tr>';
	}
	$rtr .= '</table>';
	return $rtr;
}

#TEAM ROSTER
Function team_roster($idm,$post,$dl_grants,$dls_grants){
	global $lang;
	$idm = intval($idm);
	//check if user can access rosters
	//check if roster is active
	if($idm){
		$qry = 'SELECT m.idm, m.idc1, m.idc2, m.date, c1.country AS ctry1, c1.cname AS c1name, c2.country AS ctry2, c2.cname AS c2name FROM '.KML_PREFIX.'_match AS m, '.KML_PREFIX.'_clan AS c1, '.KML_PREFIX.'_clan AS c2 WHERE  m.idc1=c1.idc AND m.idc2=c2.idc AND m.date>'.(time()-DISPUTE_LIMIT).' AND m.idm='.$idm;
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		if(mysql_num_rows($rsl)!=1){
			unset($idm);
			$comm .= $lang['roster_unactive'].'<br/><br/>';
		}else{
			$mrow = mysql_fetch_assoc($rsl);
			foreach($mrow as $ky=>$vl) $mrow[$ky] = intoBrowser($vl);
			$headAdd = ': <a href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=clans&amp;id='.$mrow['idc1'].'">'.$mrow['c1name'].'</a> '.$lang['vs'].' <a href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=clans&amp;id='.$mrow['idc2'].'">'.$mrow['c2name'].'</a>';
		}
	}
	$rtr = content_line('T',$lang['team_roster'].$headAdd);
	$rtr .= '<td align="center">'.$comm;
	if($idm){
		//roster for choosen match
		$dates = create_dates(5, array(date('Y'), date('Y')+1));
		if($post['addTerm']){
			$accept_idc1 = 0;
			$accept_idc2 = 0;
			$comment_idc1 = '';
			$comment_idc2 = '';
			if($_SESSION['dl_config']['idc']==$mrow['idc1']){
				$accept_idc1 = $_SESSION['dl_login']; 
				$comment_idc1 = $post['comment'];
			}else{
				$accept_idc2 = $_SESSION['dl_login'];
				$comment_idc2 = $post['comment'];
			}
			$mkDate = mktime($post['godzina'], $post['minute'], 0, $post['month'], $post['day'], $post['year']);
			$qry = SQL('INSERT INTO '.KML_PREFIX.'_roster_date(idm, date, comment_idc1, comment_idc2, accept_idc1, accept_idc2) VALUES('.$idm.', '.$mkDate.', %s, %s, '.$accept_idc1.', '.$accept_idc2.')', $comment_idc1, $comment_idc2);
			if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)){
				#send PM
				$rtr .= $lang['roster_date_added'];
			}
		}
		$rtr .= '&raquo; <a href="#roster_date">'.$lang['roster_date'].'</a> &laquo; &raquo; <a href="#roster_score">'.$lang['roster_score'].'</a> &laquo; &raquo; <a href="#comments">'.ucfirst($lang['comments']).'</a> &laquo; &raquo; <a href="#newcom">'.$lang['new_com'].'</a> &laquo; ';
		$rtr .= content_line('M','<a name="roster_date"></a>'.$lang['roster_date']);
		$qry = 'SELECT id_rd, date, comment_idc1, comment_idc2, accept_idc1, accept_idc2 FROM '.KML_PREFIX.'_roster_date WHERE idm='.$idm;
		$rsl = getArray($qry, 'A');
		$mdates = $rsl[0];
		$rtr .= '<form action="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=team_roster" method="post"><input type="hidden" name="id" value="'.$idm.'"/>
		<table cellspacing="1" cellpadding="5" class="tab_brdr" style="width: 60%;">
		<tr><td class="tab_head2">'.$lang['date'].'</td><td class="tab_head2">'.$mrow['c1name'].'</td><td class="tab_head2">'.$mrow['c2name'].'</td></tr>';
		if(count($mdates)>0){
			foreach($mdates as $ky=>$vl){
				$accept1 = '<img alt="reject" title="'.$lang['rejected'].'" src="'.SKIN.'img/reject.gif"/>';
				$login1 = '';
				$accept2 = '<img alt="reject" title="'.$lang['rejected'].'" src="'.SKIN.'img/reject.gif"/>';
				$login2 = '';
				if($vl['accept_idc1']>0){
					$accept1 = '<img alt="accept" title="'.$lang['accepted'].'" src="'.SKIN.'img/accept.gif"/>';
					$qry = 'SELECT login FROM '.KML_PREFIX.'_users WHERE iduser='.$vl['accept_idc1'];
					$login1 = '<a href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=users&amp;id='.$vl['accept_idc2'].'">'.mysql_result(query(__FILE__,__FUNCTION__,__LINE__,$qry,0),0).'</a>';
				}
				if($vl['accept_idc2']>0){
					$accept2 = '<img alt="accept" title="'.$lang['accepted'].'" src="'.SKIN.'img/accept.gif"/>';
					$qry = 'SELECT login FROM '.KML_PREFIX.'_users WHERE iduser='.$vl['accept_idc2'];
					$login2 = '<a href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=users&amp;id='.$vl['accept_idc2'].'">'.mysql_result(query(__FILE__,__FUNCTION__,__LINE__,$qry,0),0).'</a>';
				}
				$rtr .= '<tr><td class="content1" style="width: 110px;">'.show_date($vl['date'],2).'</td><td class="content2">'.$accept1.$login1.'<br/>'.$vl['comment_idc1'].'</td><td class="content2">'.$accept2.$login2.'<br/>'.$vl['comment_idc2'].'</td></tr>';
			}
		}
		$rtr .= '</table>
		<table cellspacing="1" cellpadding="5" class="tab_brdr">
		<tr><td class="tab_head2">'.$lang['date'].'</td><td class="tab_head2">'.ucfirst($lang['comments']).'</td><td class="tab_head2"></td></tr>
		<tr><td class="content1"><select name="year">'.array_norm($dates['years'],date('Y')).'</select>-<select name="month">'.array_norm($dates['months'],date('m')).'</select>-<select name="day">'.array_norm($dates['days'],date('d')).'</select> <select name="hour">'.array_norm($dates['hours'],'20').'</select>:<select name="minute">'.array_norm($dates['minutes'],'00').'</select></td><td class="content1"><input type="text" name="comment" maxlength="200" style="width: 200px;"/></td><td class="content2"><input type="submit" name="addTerm" value="'.$lang['submit'].'"/></td></tr>
		</table>
		</form>';
		$rtr .= content_line('M','<a name="roster_score"></a>'.$lang['roster_score']);
		$qry = 'SELECT idmm, map, frags1, frags2, wo, accept_idc1, accept_idc2 FROM '.KML_PREFIX.'_roster_map_score WHERE idm='.$idm;
		$rsl = getArray($qry, 'A');
		$dates = $rsl[0];
		$rtr .= 'score form';
		$rtr .= content_line('M','<a name="comments"></a>'.ucfirst($lang['comments']));
		$rtr .= comms(ROSTER_ID,$idm,$dl_grants,$dls_grants);
		$rtr .= content_line('M','<a name="newcom"></a>'.$lang['new_com']);
		$rtr .= comment_form($idm,ROSTER_ID);
	}else{
//list of matches in team roster
		$qry = 'SELECT m.idm, m.idc1, m.idc2, m.date_from, m.date_to, c1.country AS ctry1, c1.cname AS c1name, c2.country AS ctry2, c2.cname AS c2name, m.date_from-'.DISPUTE_LIMIT_BEFORE.', m.date_to+'.DISPUTE_LIMIT_AFTER.' FROM '.KML_PREFIX.'_match AS m, '.KML_PREFIX.'_clan AS c1, '.KML_PREFIX.'_clan AS c2 WHERE  m.idc1=c1.idc AND m.idc2=c2.idc AND (m.idc1='.$_SESSION['dl_config']['idc'].' OR m.idc2='.$_SESSION['dl_config']['idc'].') AND m.roster="1" AND (m.date_from-'.DISPUTE_LIMIT_BEFORE.'<'.time().' AND m.date_to+'.DISPUTE_LIMIT_AFTER.'>'.time().')';
		$rsl = getArray($qry, 'A');
		$rosters = $rsl[0];
		if(count($rosters)>0){
			$rtr .= '<table cellspacing="1" cellpadding="5" class="tab_brdr">
			<tr><td class="tab_head2">'.$lang['enmy'].'</td><td class="tab_head2">'.$lang['date'].'</td></tr>';
			foreach($rosters as $ky=>$vl){
				if($vl['idc1']!=$_SESSION['dl_config']['idc']) $id = 1; else $id = 2;
				$rtr .= '<tr><td class="content1">'.show_flag($vl['ctry'.$id]).' <a href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=clans&amp;id='.$vl['idc'.$id].'">'.$vl['c'.$id.'name'].'</a></td><td class="content2"><a href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=team_roster&amp;id='.$ky.'">'.show_date($vl['date'],1).'</a></td></tr>';
			}
			$rtr .= '</table>';
		}else $rtr .= $lang['roster_empty'].'<br/>';
	}
	$rtr .= '<br/>'.content_line('B');
	return $rtr;
}

?>
