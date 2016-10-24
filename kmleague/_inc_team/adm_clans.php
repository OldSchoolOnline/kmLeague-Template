<?php

# CLANS

Function clan($dts,$login,$idc,$opt){
	global $alang;
	$idc = intval($idc);
	$yesno = array('Y'=>$alang['yes'], 'N'=>$alang['no']);
	if($opt) $dts['opt'.substr($opt,-1)] = 1;
	if($idc>0){
		$qry = 'SELECT c.tag, c.cname, c.activ, c.extension FROM '.KML_PREFIX.'_clan AS c, '.KML_PREFIX.'_in AS x WHERE x.idc=c.idc AND x.league='.LEAGUE.' AND c.idc='.(int)$idc;
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		if(mysql_num_rows($rsl)!=1) die($alang['sure']);
		$row = mysql_fetch_array($rsl);
		foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
		$inf = $row['cname'];
	}
	if($inf) option_head($alang['edit_clan'].': '.$inf); else option_head($alang['new_clan']);
	$qry = 'SELECT country FROM '.KML_PREFIX.'_config WHERE league='.LEAGUE;
	$country = mysql_result(query(__FILE__,__FUNCTION__,__LINE__,$qry,0),0);
	println('<table cellspacing="10"><tr><td valign="top">
	<form method="post" action="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=clan">');
	if($idc){
		println('<input type="hidden" name="idc" value="'.$idc.'">');
	}
	if($dts['opt5']){
		println('<table>
		<tr><td class="hd">'.$alang['tag'].':</td><td><input type="text" name="tag" value="'.$row['tag'].'" maxlength="15"></td></tr>
		<tr><td class="hd">'.$alang['name'].':</td><td><input type="text" name="cname" value="'.$row['cname'].'"></td></tr>
		<tr><td class="hd">'.ucfirst($alang['active']).':</td><td><select name="activ">'.array_assoc($yesno,$row['activ']).'</select></td></tr>
		<tr><td class="hd">'.$alang['wildcard'].':</td><td><input type="checkbox" name="wildcard"');
		$qry = 'SELECT idw FROM '.KML_PREFIX.'_wildcard WHERE league='.LEAGUE.' AND ids='.IDS.' AND idc='.(int)$idc;
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		if(mysql_num_rows($rsl)==1) print(' checked><input type="hidden" name="wild" value="1">'); else print('>');
		println('</td></tr>
		<tr><td colspan="2" align="right"><input name="opt3" type="submit" value="'.$alang['delete'].'"> <input name="opt2" type="submit" value="'.$alang['save'].'"></td></tr>
		</table>');
	}elseif($dts['opt2']){
			if($dts['wild']==1 && $dts['wildcard']!='on'){
				$qry = 'DELETE FROM '.KML_PREFIX.'_wildcard WHERE league='.LEAGUE.' AND ids='.IDS.' AND idc='.(int)$idc;
				query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
			}elseif($dts['wild']==0 && $dts['wildcard']=='on'){
				$qry = 'INSERT INTO '.KML_PREFIX.'_wildcard(league, ids, idc) VALUES('.LEAGUE.', "'.IDS.'", "'.(int)$idc.'")';
				query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
			}
			$qry = SQL('UPDATE '.KML_PREFIX.'_clan SET activ=%s', $dts['activ']);
			if(LEAGUE_TYPE=='T') $qry .= SQL(', tag=%s, cname=%s', $dts['tag'], $dts['cname']);
			$qry .= 'WHERE idc='.(int)$idc;
			if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) action_info('s', $alang['clan']);
	}elseif($dts['opt3']){
		if($_SESSION['dl_grants']['main']<3){
			$qry = 'SELECT idc FROM '.KML_PREFIX.'_clan WHERE idc='.(int)$idc.' AND iduser='.$login;
			$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
			if(mysql_num_rows($rsl)<1) $err = 1;
		}
		if($err == 0){
			$qry = 'SELECT idm FROM '.KML_PREFIX.'_match WHERE league='.LEAGUE.' AND idc1='.(int)$idc.' OR idc2='.(int)$idc;
			$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
			print($alang['qdel_clan'].' ? ');
			if(mysql_num_rows($rsl)>0) print('<span class="info">'.$alang['match_rem'].'</span>');
			println(' <input type="submit" name="opt4" value="'.$alang['yes'].'">');
		}else print($alang['clan_remove_err1']);
	}elseif($dts['opt4']){
		if($_SESSION['dl_grants']['main']<3){
			$qry = 'SELECT idc FROM '.KML_PREFIX.'_clan WHERE idc='.(int)$idc.' AND iduser='.$login;
			$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
			if(mysql_num_rows($rsl)<1) $err = 1;
		}
		if($err == 0){
			$qry = 'SELECT idm, idg FROM '.KML_PREFIX.'_match WHERE league='.LEAGUE.' AND ids='.IDS.' AND (idc1='.(int)$idc.' OR idc2='.(int)$idc.')';
			$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
			if(mysql_num_rows($rsl)>0){
				while($row=mysql_fetch_array($rsl)){
					if($row['idg']) tab_upd_del($row['idm']);
					$qry = 'DELETE FROM '.KML_PREFIX.'_match WHERE league='.LEAGUE.' AND idm='.$row['idm'];
					query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
				}
			}
			print($alang['sclan_del'].'<br/>');
			$qry = 'SELECT idm FROM '.KML_PREFIX.'_match WHERE league='.LEAGUE.' AND (idc1='.(int)$idc.' OR idc2='.(int)$idc.')';
			$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
			if(mysql_num_rows($rsl)>0) die($alang['clan_err']);
			$qry = 'SELECT idt FROM '.KML_PREFIX.'_table WHERE league='.LEAGUE.' AND idc='.(int)$idc;
			$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
			while($row = mysql_fetch_array($rsl)){
				$qry = 'DELETE FROM '.KML_PREFIX.'_penalty WHERE league='.LEAGUE.' AND idt='.$row['idt'];
				query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
			}
			$qry = 'DELETE FROM '.KML_PREFIX.'_table WHERE league='.LEAGUE.' AND idc='.(int)$idc;
			query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
			if(LEAGUE_TYPE=='T'){
				if($row['extension']) unlink('clan_logo/'.$idc.'.'.$row['extension']);
				$qry = 'SELECT idp FROM '.KML_PREFIX.'_player WHERE idc='.(int)$idc;
				$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
				while($row=mysql_fetch_assoc($rsl)){
					$qry = 'DELETE FROM '.KML_PREFIX.'_players_stats WHERE league='.LEAGUE.' AND idp='.$row['idp'];
					query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
				}
				$qry = 'DELETE FROM '.KML_PREFIX.'_player WHERE idc='.(int)$idc;
				query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
			}
			$qry = 'DELETE FROM '.KML_PREFIX.'_clan WHERE idc='.(int)$idc;
			if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) action_info('r', $alang['clan']);
		}else print($alang['clan_remove_err1']);
	}elseif($dts['opt1']){
		$qry = SQL('INSERT INTO '.KML_PREFIX.'_clan(cname, tag, date, activ, country, iduser) VALUES(%s, %s, "'.time().'", "Y", %d, %d)', $dts['cname'], $dts['tag'], $country, $login);
		if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) action_info('a', $alang['clan']);
		$idc = mysql_insert_id();
		$qry = 'INSERT INTO '.KML_PREFIX.'_in(league, idc) VALUES('.LEAGUE.', '.$idc.')';
		query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	}else{
		println('<table>
		<tr><td class="hd">'.$alang['tag'].':</td><td><input type="text" name="tag" maxlength="15"></td></tr>
		<tr><td class="hd">'.$alang['name'].':</td><td><input type="text" name="cname"></td></tr>');
		println('<tr><td colspan="2" align="right"><input type="submit" name="opt1" value="'.$alang['add'].'"></td></tr>
		</table>');
		$qry = 'SELECT c.cname FROM '.KML_PREFIX.'_clan AS c, '.KML_PREFIX.'_wildcard AS w WHERE w.league='.LEAGUE.' AND c.idc=w.idc AND w.ids='.IDS;
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		if(mysql_num_rows($rsl)>0){
			println('<br/><div class="header">'.$alang['clans_wild'].'</div>
			<table cellspacing="0">');
			while($row=mysql_fetch_assoc($rsl)){
				foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
				print('<tr><td>'.$row['cname'].'</td></tr>');
			}
			println('</table>');
		}
	}
	println('</form>
	</td><td valign="top">
	<div class="head">'.$alang['clan_list'].'</div><br/>
	<form method="post" action="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=clan">
	<span class="hd">ID:</span> <input type="text" name="idc" value="'.$idc.'" size="5"> <input name="opt3" type="submit" value="'.$alang['delete'].'"> <input name="opt5" type="submit" value="'.$alang['edit'].'"></form>
	<div style="width: 250px; height: 200px; overflow: auto;">');
	$qry = 'SELECT c.idc, c.tag, c.activ FROM '.KML_PREFIX.'_clan AS c, '.KML_PREFIX.'_in AS x WHERE x.idc=c.idc AND x.league='.LEAGUE.' ORDER BY c.tag';
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	print('<table cellspacing="0" cellpadding="5" style="width: 100%;">
	<tr><td class="hd">ID</td><td class="hd">'.$alang['name'].'</td><td class="hd">'.ucfirst($alang['active']).'</td><td class="hd">'.ucfirst(strtolower($alang['options'])).'</td></tr>');
	while($row = mysql_fetch_assoc($rsl)){
		foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
		$link = $_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=clan&amp;idc='.$row['idc'].'&amp;opt=';
		println('<tr class="list'.(++$i%2).'"><td>'.$row['idc'].'</td><td>'.$row['tag'].'</td><td>'.$yesno[$row['activ']].'</td><td><a href="'.$link.'5"><img style="vertical-align: bottom;" src="adm/edit.gif" alt="'.$alang['edit'].'" title="'.$alang['edit'].'"></a> <a href="'.$link.'3"><img style="vertical-align: bottom;" src="adm/delete.gif" alt="'.$alang['delete'].'" title="'.$alang['delete'].'"></a></td></tr>');
	}
	print('</table>');
	println('</div>
	</td></tr></table>');
}

Function clan_award($post,$id,$opt){
	global $alang;
	if(LEAGUE_TYPE=='D') $qry = 'SELECT x.idc, u.login AS cname FROM '.KML_PREFIX.'_users AS u, '.KML_PREFIX.'_in AS x WHERE x.idc=u.iduser AND x.league='.LEAGUE.' ORDER BY cname';
	else $qry = 'SELECT c.idc, c.cname FROM '.KML_PREFIX.'_clan AS c, '.KML_PREFIX.'_in AS x WHERE x.idc=x.idc AND x.league='.LEAGUE.' ORDER BY c.cname';
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	if(mysql_num_rows($rsl)<1) die($alang['teams_err1']);
	while($row=mysql_fetch_assoc($rsl)){
		foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
		$clans[$row['idc']] = $row['cname'];
	}
	$qry = 'SELECT ida, name FROM '.KML_PREFIX.'_award WHERE league='.LEAGUE.' ORDER BY name';
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	if(mysql_num_rows($rsl)<1) die($alang['award_err2']);
	while($row=mysql_fetch_assoc($rsl)){
		foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
		$awards[$row['ida']] = $row['name'];
	}

	$basics['table'] = KML_PREFIX.'_clan_award';
	$basics['op'] = 'caward&amp;'.KML_LINK_SL2;
	$basics['opt'] = $opt;
	$basics['key'] = 'idca';
	$basics['id'] = $id;
	$basics['query_add'] = ' ,league, ids';
	$basics['query_add_value'] = ' ,"'.LEAGUE.'", "'.IDS.'"';
	$basics['query_req'] = 'league='.LEAGUE;
	if(LEAGUE_TYPE=='D') $basics['list_query'] = 'SELECT x.idca, c.login AS cname, a.name, a.ida, a.grade, a.img, s.ids, s.sname FROM '.KML_PREFIX.'_clan_award AS x, '.KML_PREFIX.'_users AS c, '.KML_PREFIX.'_award AS a, '.KML_PREFIX.'_season AS s WHERE x.league='.LEAGUE.' AND s.ids=x.ids AND x.idc=c.iduser AND x.ida=a.ida';
	else $basics['list_query'] = 'SELECT x.idca, c.cname, a.name, a.ida, a.img, a.grade, s.ids, s.sname FROM '.KML_PREFIX.'_clan_award AS x, '.KML_PREFIX.'_clan AS c, '.KML_PREFIX.'_award AS a, '.KML_PREFIX.'_season AS s WHERE x.league='.LEAGUE.' AND s.ids=x.ids AND x.idc=c.idc AND x.ida=a.ida';
	$basics['list_query_add'] = ' ORDER BY x.ids DESC, x.idca DESC';
	$basics['header'] = $alang['clan_awards'];
	$basics['header_edit'] = $alang['clan_awards'];
	$basics['header_list'] = $alang['clan_awards'];
	$basics['list_items'] = array($alang['award'], $alang['team'], $alang['level']);
	$basics['list_values'] = array('{name}', '{cname}', '{grade}');
	$basics['list_divider'] = 'ids';
	$basics['list_divider_head'] = $alang['season'].': {sname}';
	$basics['inf_del_query'] = $alang['award2_qdel'];
	$basics['inf_del'] = $alang['info_rem'].': '.$alang['clan_awards'];
	$basics['inf_add'] = $alang['info_add'].': '.$alang['clan_awards'];
	$basics['inf_upd'] = $alang['info_sav'].': '.$alang['clan_awards'];
	$fields['idc'] = array('type'=>'S', 'head'=>ucfirst($alang['team']), 'values'=>$clans, 'req'=>'Y');
	$fields['ida'] = array('type'=>'S', 'head'=>$alang['award'], 'values'=>$awards, 'req'=>'Y');
	echo data_form($post,$get,$fields,$basics);
}
?>
