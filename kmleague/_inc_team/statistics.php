<?php

# STATYSTYKI

Function league_stats(){
	global $lang;
	$rtr = content_line('T',$lang['lg_stats']);
	$rtr .= '<td align="center">';
	$wo = 0;
	$qry = 'SELECT COUNT(idm), SUM(frags1+frags2) AS frags FROM '.KML_PREFIX.'_match WHERE league='.LEAGUE.' AND (win1>0 OR win2>0 OR draw>0) AND ids='.IDS;
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	$matches = mysql_result($rsl,0,0);
	$frags = mysql_result($rsl,0,1);
	if($matches>0){
		$qry = 'SELECT COUNT(idmm) FROM '.KML_PREFIX.'_match_map AS x, '.KML_PREFIX.'_match AS m WHERE m.idm=x.idm AND m.league='.LEAGUE.' AND x.played=1 AND m.ids='.IDS;
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		$maps = mysql_result($rsl,0);
		$qry = 'SELECT COUNT(idmm) FROM '.KML_PREFIX.'_match_map AS x, '.KML_PREFIX.'_match AS m WHERE m.idm=x.idm AND m.league='.LEAGUE.' AND x.wo>0 AND m.ids='.IDS;
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		$wo = mysql_result($rsl,0);
		$qry = 'SELECT count(u.iduser) AS mtches, u.iduser, u.login, u.country FROM '.KML_PREFIX.'_match AS m, '.KML_PREFIX.'_users AS u WHERE m.judge=u.iduser AND m.league='.LEAGUE.' AND (m.win1>0 OR m.win2>0 OR m.draw>0) AND m.ids='.IDS.' GROUP BY u.iduser ORDER BY mtches DESC, u.login';
		$jrsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		$qry = 'SELECT COUNT(x.map) AS mmps, x.map FROM '.KML_PREFIX.'_match_map AS x, '.KML_PREFIX.'_match AS m WHERE m.idm=x.idm AND m.league='.LEAGUE.' AND m.ids='.IDS.' AND x.played=1 GROUP BY x.map ORDER BY mmps DESC, x.map';
		$mrsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		$rtr .= '<table cellspacing="10">
		<tr><td valign="top">
		<table cellspacing="1" cellpadding="5" class="tab_brdr">
		<tr><td colspan="2" class="tab_head3">'.$lang['stats'].'</td></tr>
		<tr><td class="content2">'.$lang['matches'].':</td><td class="content1">'.$matches.'</td></tr>
		<tr><td class="content2">'.$lang['maps'].':</td><td class="content1">'.$maps.'</td></tr>
		<tr><td class="content2">w/o:</td><td class="content1">'.$wo.'</td></tr>
		<tr><td class="content2">'.$lang['frags2'].':</td><td class="content1">'.$frags.'</td></tr>
		<tr><td class="content2">'.$lang['m_frg'].':</td><td class="content1">'.round($frags/$matches,2).'</td></tr>
		<tr><td class="content2">'.$lang['m_frg2'].':</td><td class="content1">'.round($frags/$maps,2).'</td></tr>
		</table>
		</td><td valign="top">
		<table cellspacing="1" cellpadding="5" class="tab_brdr">
		<tr><td colspan="5" class="tab_head3">'.$lang['maps'].'</td></tr>';
		while($row=mysql_fetch_assoc($mrsl)){
			foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
			$prct = round(($row['mmps']/$maps)*100,2);
			$rtr .= '<tr><td class="content2">'.$row['map'].':</td><td class="content1"><img alt="vote" src="'.SKIN.'img/vote.gif" height="11" width="'.floor($prct).'" /><img alt="bb" src="'.DIR.'_img/bb.gif" height="11" width="'.(100-floor($prct)).'" /></td><td class="content2" align="right">'.$row['mmps'].'</td><td class="content1">'.$prct.' %</td></tr>';
		}
		$rtr .= '</table>
		</td><td valign="top">';
		$rtr .= '<table cellspacing="1" cellpadding="5" class="tab_brdr">
		<tr><td class="tab_head3">'.$lang['referee'].'</td><td class="tab_head3">'.$lang['matches'].'</td></tr>';
		while($row=mysql_fetch_assoc($jrsl)){
			foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
			$rtr .= '<tr><td class="content2"><a class="link" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=users&amp;id='.$row['iduser'].'">'.show_flag($row['country']).' '.$row['login'].'</a></td><td class="content1" align="right">'.$row['mtches'].'</td></tr>';
		}
		$rtr .= '</table>';
		$rtr .= '</td></tr>
		</table>';
	}else $rtr .= '<br/>'.$lang['no_matches'].'<br/><br/>';
	$rtr .= content_line('B');
	return $rtr;
}

Function players_stats($sort='eff',$type='desc'){
	global $lang;
	$rtr = content_line('T',$lang['pls_stats']);
	$rtr .= '<td>';
	if($sort!='eff' && $sort!='nick' && $sort!='maps' && $sort!='frags' && $sort!='deaths' && $sort!='m_frg2' && $sort!='m_dts2' && $sort!='cname') $sort = 'skill';
	if($type!='desc' && $type!='asc') $type = 'desc';
	$order = array('skill'=>$lang['skill'], 'eff'=>$lang['m_eff'], 'nick'=>$lang['nick'], 'maps'=>$lang['maps'], 'frags'=>$lang['frags'], 'deaths'=>$lang['deaths'], 'm_frg2'=>$lang['m_frg2'], 'm_dts2'=>$lang['m_dts2']);
	if(PLAYER_TYPE=='T') $order['cname'] = $lang['clan'];
	$ordert = array('desc'=>$lang['desc'], 'asc'=>$lang['asc']);
	$rtr .= '<div align="right"><form action="'.$_SERVER['PHP_SELF'].'" method="get">'.KML_LINK_SLF.'<input type="hidden" name="op" value="pstats" /><span class="bold">'.$lang['sort'].':</span> <select name="sort">'.array_assoc($order,$sort).'</select> <select name="type">'.array_assoc($ordert,$type).'</select> <input type="submit" value="'.$lang['show'].'" /></form></div><br/>';

	if(PLAYER_TYPE=='T'){
		$qry = 'SELECT IF(SUM(s.frags)>=0,(SUM(s.frags)/(SUM(s.frags)+SUM(s.deaths))*100), (SUM(s.frags)-SUM(s.deaths))) AS eff, IF(SUM(s.frags)>=0,((SUM(s.frags)/(SUM(s.frags)+SUM(s.deaths))*100)*(1+(COUNT(s.idmm)/50))),(SUM(s.frags)-SUM(s.deaths))) AS skill, (SUM(s.frags)-SUM(s.deaths)) AS net, (SUM(s.frags)/COUNT(s.idmm)) AS m_frg2, (SUM(s.deaths)/COUNT(s.idmm)) AS m_dts2, c.idc, c.country, c.cname, COUNT(s.idmm) AS maps, SUM(s.frags) AS frags, SUM(s.deaths) AS deaths, p.pname AS nick FROM '.KML_PREFIX.'_players_stats AS s, '.KML_PREFIX.'_clan AS c, '.KML_PREFIX.'_match AS m, '.KML_PREFIX.'_player AS p WHERE s.league='.LEAGUE.' AND s.ids="'.IDS.'" AND m.idm=s.idm AND p.idp=s.idp AND p.idc=c.idc AND m.type IS NULL GROUP BY p.pname, p.idc ORDER BY `'.$sort.'` '.$type.', skill DESC';
		$td = '<td class="tab_head2">'.$lang['clan'].'</td>';
	}elseif(PLAYER_TYPE=='X'){
		$qry = 'SELECT IF(SUM(s.frags)>=0,(SUM(s.frags)/(SUM(s.frags)+SUM(s.deaths))*100), (SUM(s.frags)-SUM(s.deaths))) AS eff, IF(SUM(s.frags)>=0,((SUM(s.frags)/(SUM(s.frags)+SUM(s.deaths))*100)*(1+(COUNT(s.idmm)/50))),(SUM(s.frags)-SUM(s.deaths))) AS skill, (SUM(s.frags)-SUM(s.deaths)) AS net, (SUM(s.frags)/COUNT(s.idmm)) AS m_frg2, (SUM(s.deaths)/COUNT(s.idmm)) AS m_dts2, COUNT(s.idmm) AS maps, SUM(s.frags) AS frags, SUM(s.deaths) AS deaths, p.pname AS nick FROM '.KML_PREFIX.'_players_stats AS s, '.KML_PREFIX.'_player AS p WHERE s.league='.LEAGUE.' AND s.ids="'.IDS.'" AND p.idp=s.idp GROUP BY p.pname, p.idc ORDER BY `'.$sort.'` '.$type.', skill DESC';
	}
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	if(mysql_num_rows($rsl)>0){
		$rtr .= '<table cellspacing="1" cellpadding="5" class="tab_brdr">
		<tr><td></td><td class="tab_head2">'.$lang['nick'].'</td><td class="tab_head3">'.$lang['skill'].'</td><td class="tab_head3">'.$lang['eff'].'</td><td class="tab_head3">'.$lang['maps'].'</td><td class="tab_head3">'.$lang['frags'].'</td><td class="tab_head3">'.$lang['deaths'].'</td><td class="tab_head3">'.$lang['net'].'</td><td class="tab_head2">'.$lang['m_frg2'].'</td><td class="tab_head2">'.$lang['m_dts2'].'</td>'.$td.'</tr>';
		while($row=mysql_fetch_assoc($rsl)){
			foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
			if($row['eff']){
				$i++;
				if(PLAYER_TYPE=='T') $clan = '<td><a class="link" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=clans&amp;id='.$row['idc'].'">'.show_flag($row['country']).' '.$row['cname'].'</a></td>';
				if($row['skill']==$skill) $j = ''; else $j = $i;
				if($row['eff']<=0) $row['eff'] = 0;
				if($row['skill']<=0) $row['skill'] = 0;
				$rtr .= '<tr class="content2"><td class="bold">'.$j.'</td><td class="content1">'.$row['nick'].'</td><td align="right">'.round($row['skill'],3).'</td><td align="right">'.round($row['eff'],3).' %</td><td align="right">'.$row['maps'].'</td><td align="right">'.$row['frags'].'</td><td align="right">'.$row['deaths'].'</td><td align="right">'.($row['net']).'</td><td align="right">'.round($row['m_frg2'],2).'</td><td align="right">'.round($row['m_dts2'],2).'</td>'.$clan.'</tr>';
				$skill = $row['skill'];
			}
		}
		mysql_free_result($rsl);
		$rtr .= '</table>';
		$qry = 'SELECT m.map, SUM(frags) AS frags, COUNT(s.idmm) AS maps FROM '.KML_PREFIX.'_players_stats AS s, '.KML_PREFIX.'_match_map AS m, '.KML_PREFIX.'_match AS x WHERE s.idmm=m.idmm AND s.league='.LEAGUE.' AND s.ids='.IDS.' AND m.played=1 AND x.idm=s.idm AND x.type IS NULL GROUP BY m.map';
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		$rtr .= '<table cellspacing="1" cellpadding="5" class="tab_brdr">
		<tr><td class="tab_head3">'.$lang['map'].'</td><td class="tab_head3">'.$lang['number'].'</td><td class="tab_head3">'.$lang['frags'].'</td><td class="tab_head2">'.$lang['m_frg2'].'</td><td class="tab_head2">'.$lang['bply'].'</td></tr>';
		while($row=mysql_fetch_assoc($rsl)){
			foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
			if(PLAYER_TYPE=='T'){
				$pqry = 'SELECT (SUM(s.frags)/(SUM(s.frags)+(s.deaths))*100) AS eff, c.idc, c.country, c.tag, p.pname FROM '.KML_PREFIX.'_players_stats AS s, '.KML_PREFIX.'_player AS p, '.KML_PREFIX.'_match AS m, '.KML_PREFIX.'_match_map AS x, '.KML_PREFIX.'_clan AS c WHERE x.idmm=s.idmm AND s.league='.LEAGUE.' AND s.idm=m.idm AND s.ids='.IDS.' AND m.type IS NULL AND x.map="'.$row['map'].'" AND (s.frags!=0 OR s.deaths!=0) AND p.idc=c.idc AND p.idp=s.idp GROUP BY p.pname ORDER BY eff DESC LIMIT 0,1';
			}elseif(PLAYER_TYPE=='X'){
				$pqry = 'SELECT (SUM(s.frags)/(SUM(s.frags)+(s.deaths))*100) AS eff, p.pname FROM '.KML_PREFIX.'_match_map AS x, '.KML_PREFIX.'_players_stats AS s, '.KML_PREFIX.'_player AS p, '.KML_PREFIX.'_match AS m WHERE s.league='.LEAGUE.' AND s.idm=m.idm AND s.ids='.IDS.' AND x.idmm=s.idmm AND x.map="'.$row['map'].'" AND (s.frags!=0 OR s.deaths!=0) AND p.idp=s.idp GROUP BY p.pname ORDER BY eff DESC LIMIT 0,1';
			}
			$prsl = query(__FILE__,__FUNCTION__,__LINE__,$pqry,0);
			if(mysql_num_rows($prsl)>0){
				$prow = mysql_fetch_assoc($prsl);
				foreach($prow as $ky=>$vl) $prow[$ky] = intoBrowser($vl);
				if(PLAYER_TYPE=='T'){
					$clan = '<a class="link" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=clans&amp;id='.$prow['idc'].'">'.show_flag($prow['country']).' '.$prow['tag'].'</a> ';
				}
				$best = $clan.$prow['pname'];
			}else $best = '&#8212;&#8212;&#8212;';
			$rtr .= '<tr class="content2"><td class="tab_head2">'.$row['map'].'</td><td align="right">'.$row['maps'].'</td><td align="right">'.$row['frags'].'</td><td align="right">'.round($row['frags']/$row['maps'],2).'</td><td class="content1">'.$best.'</td></tr>';
		}
		$rtr .= '</table>';
	}else $rtr .= $lang['no_players_stats'].'<br/><br/>';
	$rtr .= content_line('B');
	return $rtr;
}


Function clans_stats(){
	global $lang;
	$qry = 'SELECT idc1, idc2 FROM '.KML_PREFIX.'_match AS m, '.KML_PREFIX.'_match_map AS x WHERE m.league='.LEAGUE.' AND m.idm=x.idm AND m.ids='.IDS.' AND x.played=1 AND x.wo=0';
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	$rtr = content_line('T',$lang['cls_stats']);
	$rtr .= '<td>';
	if(mysql_num_rows($rsl)>0){
		$played_maps = array();
		while($row=mysql_fetch_assoc($rsl)){
			++$played_maps[$row['idc1']];
			++$played_maps[$row['idc2']];
		}
		if(LEAGUE_TYPE=='D') $qry = 'SELECT m.idm, m.idc1, c1.login AS cnm1, c1.country AS c1ctry, m.idc2, c2.login AS cnm2, c2.country AS c2ctry, m.frags1, m.frags2 FROM '.KML_PREFIX.'_users AS c1, '.KML_PREFIX.'_users AS c2, '.KML_PREFIX.'_match AS m WHERE m.league='.LEAGUE.' AND m.idc1=c1.iduser AND m.idc2=c2.iduser AND (m.win1>0 OR m.win2>0 OR m.draw>0) AND m.type IS NULL AND m.ids='.IDS;
		else $qry = 'SELECT m.idm, m.idc1, c1.cname AS cnm1, c1.country AS c1ctry, m.idc2, c2.cname AS cnm2, c2.country AS c2ctry, m.frags1, m.frags2 FROM '.KML_PREFIX.'_clan AS c1, '.KML_PREFIX.'_clan AS c2, '.KML_PREFIX.'_match AS m WHERE m.league='.LEAGUE.' AND m.idc1=c1.idc AND m.idc2=c2.idc AND (m.win1>0 OR m.win2>0 OR m.draw>0) AND m.type IS NULL AND m.ids='.IDS;
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		while($row=mysql_fetch_assoc($rsl)){
			foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
			$frags1 = $stats[$row['idc1']][1]+$row['frags1'];
			$deaths1 = $stats[$row['idc1']][2]+$row['frags2'];
			if(($frags1+$deaths1)>0) $eff1 = ($frags1/($frags1+$deaths1))*100;
			else $eff1 = 0;
			$frags2 = $stats[$row['idc2']][1]+$row['frags2'];
			$deaths2 = $stats[$row['idc2']][2]+$row['frags1'];
			if(($frags2+$deaths2)>0) $eff2 = ($frags2/($frags2+$deaths2))*100;
			else $eff2 = 0;
			$matches1 = $stats[$row['idc1']][6]+1;
			$matches2 = $stats[$row['idc2']][6]+1;
			$stats[$row['idc1']] = array($eff1, $frags1, $deaths1, 0, $row['c1ctry'], $row['cnm1'], $matches1, 0, 0, ($frags1-$deaths1));
			$stats[$row['idc2']] = array($eff2, $frags2, $deaths2, 0, $row['c2ctry'], $row['cnm2'], $matches2, 0, 0, ($frags2-$deaths2));
		}
		foreach($stats as $ky=>$vl){
			$mps = $played_maps[$ky];
			if($mps>0){
				$stats[$ky][3] = $mps;
				$stats[$ky][7] = $stats[$ky][1]/$mps;
				$stats[$ky][8] = $stats[$ky][2]/$mps;
			}
		}
		$rtr .= '<table cellspacing="1" cellpadding="5" class="tab_brdr">
		<tr><td></td><td class="tab_head2">'.$lang['clan'].'</td><td class="tab_head3">'.$lang['eff'].'</td><td class="tab_head3">'.$lang['matches'].'</td><td class="tab_head3">'.$lang['maps'].'</td><td class="tab_head3">'.$lang['frags2'].'</td><td class="tab_head3">'.$lang['deaths2'].'</td><td class="tab_head3">'.$lang['net'].'</td><td class="tab_head2">'.$lang['m_frg2'].'</td><td class="tab_head2">'.$lang['m_dts2'].'</td><td class="tab_head2">'.ucfirst(strtolower($lang['details'])).'</td></tr>';
		function cmp2 ($a, $b) { 
		   if ($a[0] == $b[0]) return 0; 
		   return ($a[0] > $b[0]) ? -1 : 1; 
		}
		uasort($stats,'cmp2');
		foreach($stats as $key=>$other){
			$i++;
			if($other[0]==$eff) $j = ''; else $j = $i;
			if($other[0]>100) $other[0] = 100;
			if($other[0]<0) $other[0] = 0;
			if($other[2]<0) $other[2] = 0;
			if($other[1]<0) $other[1] = 0;
			if($other[7]<0) $other[7] = 0;
			if($other[8]<0) $other[8] = 0;
			$rtr .= '<tr class="content2"><td class="bold">'.$j.'</td><td class="content1"><a class="link" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=clans&amp;id='.$key.'">'.show_flag($other[4]).' '.$other[5].'</a></td><td align="right">'.round($other[0],2).' %</td><td align="right">'.$other[6].'</td><td align="right">'.$other[3].'</td><td align="right">'.$other[1].'</td><td align="right">'.$other[2].'</td><td align="right">'.$other[9].'</td><td align="right">'.round($other[7]).'</td><td align="right">'.round($other[8]).'</td><td class="content2" align="center"><a class="tab_link2" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=clans&amp;id='.$key.'&amp;so=sts">&raquo;</a></td></tr>';
			$eff = $other[0];
		}
		$rtr .= '</table>
		<table cellspacing="1" cellpadding="5" class="tab_brdr">
		<tr><td></td><td class="tab_head3">'.$lang['most'].'</td><td class="tab_head3">'.$lang['least'].'</td></tr>';
		$all = count($stats)-1;
		function cmp5 ($a, $b) { 
		   if ($a[6] == $b[6]) return 0; 
		   return ($a[6] > $b[6]) ? -1 : 1; 
		}
		uasort($stats,'cmp5');
		$array = array_keys($stats);
		$rtr .= '<tr class="content1"><td class="tab_head2">'.$lang['matches'].'</td><td><a class="link" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=clans&amp;id='.$array[0].'">'.show_flag($stats[$array[0]][4]).' '.$stats[$array[0]][5].'</a> ('.$stats[$array[0]][6].')</td><td><a class="link" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=clans&amp;id='.$array[$all].'">'.show_flag($stats[$array[$all]][4]).' '.$stats[$array[$all]][5].'</a> ('.$stats[$array[$all]][6].')</td></tr>';
		function cmp6 ($a, $b) { 
		   if ($a[3] == $b[3]) return 0; 
		   return ($a[3] > $b[3]) ? -1 : 1; 
		}
		uasort($stats,'cmp6');
		$array = array_keys($stats);
		$rtr .= '<tr class="content1"><td class="tab_head2">'.$lang['maps'].'</td><td><a class="link" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=clans&amp;id='.$array[0].'">'.show_flag($stats[$array[0]][4]).' '.$stats[$array[0]][5].'</a> ('.$stats[$array[0]][3].')</td><td><a class="link" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=clans&amp;id='.$array[$all].'">'.show_flag($stats[$array[$all]][4]).' '.$stats[$array[$all]][5].'</a> ('.$stats[$array[$all]][3].')</td></tr>';
		function cmp3 ($a, $b) { 
		   if ($a[1] == $b[1]) return 0; 
		   return ($a[1] > $b[1]) ? -1 : 1; 
		}
		uasort($stats,'cmp3');
		$array = array_keys($stats);
		$rtr .= '<tr class="content1"><td class="tab_head2">'.$lang['frags2'].'</td><td><a class="link" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=clans&amp;id='.$array[0].'">'.show_flag($stats[$array[0]][4]).' '.$stats[$array[0]][5].'</a> ('.$stats[$array[0]][1].')</td><td><a class="link" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=clans&amp;id='.$array[$all].'">'.show_flag($stats[$array[$all]][4]).' '.$stats[$array[$all]][5].'</a> ('.$stats[$array[$all]][1].')</td></tr>';
		function cmp4 ($a, $b) { 
		   if ($a[2] == $b[2]) return 0; 
		   return ($a[2] > $b[2]) ? -1 : 1; 
		}
		uasort($stats,'cmp4');
		$array = array_keys($stats);
		$rtr .= '<tr class="content1"><td class="tab_head2">'.$lang['deaths2'].'</td><td><a class="link" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=clans&amp;id='.$array[0].'">'.show_flag($stats[$array[0]][4]).' '.$stats[$array[0]][5].'</a> ('.$stats[$array[0]][2].')</td><td><a class="link" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=clans&amp;id='.$array[$all].'">'.show_flag($stats[$array[$all]][4]).' '.$stats[$array[$all]][5].'</a> ('.$stats[$array[$all]][2].')</td></tr>';
		function cmp7 ($a, $b) { 
		   if ($a[9] == $b[9]) return 0; 
		   return ($a[9] > $b[9]) ? -1 : 1; 
		}
		uasort($stats,'cmp7');
		$array = array_keys($stats);
		$rtr .= '<tr class="content1"><td class="tab_head2">'.$lang['net'].'</td><td><a class="link" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=clans&amp;id='.$array[0].'">'.show_flag($stats[$array[0]][4]).' '.$stats[$array[0]][5].'</a> ('.$stats[$array[0]][9].')</td><td><a class="link" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=clans&amp;id='.$array[$all].'">'.show_flag($stats[$array[$all]][4]).' '.$stats[$array[$all]][5].'</a> ('.$stats[$array[$all]][9].')</td></tr>';
		function cmp8 ($a, $b) { 
		   if ($a[7] == $b[7]) return 0; 
		   return ($a[7] > $b[7]) ? -1 : 1; 
		}
		uasort($stats,'cmp8');
		$array = array_keys($stats);
		$rtr .= '<tr><td class="tab_head2">'.$lang['m_frg2'].'</td><td class="content1"><a class="link" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=clans&amp;id='.$array[0].'">'.show_flag($stats[$array[0]][4]).' '.$stats[$array[0]][5].'</a> ('.round($stats[$array[0]][7]).')</td><td class="content1"><a class="link" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=clans&amp;id='.$array[$all].'">'.show_flag($stats[$array[$all]][4]).' '.$stats[$array[$all]][5].'</a> ('.round($stats[$array[$all]][7]).')</td></tr>';
		function cmp9 ($a, $b) { 
		   if ($a[8] == $b[8]) return 0; 
		   return ($a[8] > $b[8]) ? -1 : 1; 
		}
		uasort($stats,'cmp9');
		$array = array_keys($stats);
		$rtr .= '<tr><td class="tab_head2">'.$lang['m_dts2'].'</td><td class="content1"><a class="link" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=clans&amp;id='.$array[0].'">'.show_flag($stats[$array[0]][4]).' '.$stats[$array[0]][5].'</a> ('.round($stats[$array[0]][8]).')</td><td class="content1"><a class="link" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=clans&amp;id='.$array[$all].'">'.show_flag($stats[$array[$all]][4]).' '.$stats[$array[$all]][5].'</a> ('.round($stats[$array[$all]][8]).')</td></tr>';
		$rtr .= '</table>';

	}else $rtr .= $lang['no_matches'].'<br/><br/>';
	$rtr .= content_line('B');
	return $rtr;
}

?>
