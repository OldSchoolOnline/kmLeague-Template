<?php

Function gr($id){
	global $lang;
	$id = intval($id);
	$qry = 'SELECT x.idc, MIN(a.grade) AS grade FROM '.KML_PREFIX.'_clan_award AS x, '.KML_PREFIX.'_award AS a WHERE a.ida=x.ida AND x.league='.LEAGUE.' GROUP BY x.idc';
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	while($row=mysql_fetch_assoc($rsl)) $awards[$row['idc']] = $row['grade'];
	$qry = 'SELECT gname FROM '.KML_PREFIX.'_group WHERE league='.LEAGUE.' AND idg='.$id;
	$group = mysql_result(query(__FILE__,__FUNCTION__,__LINE__,$qry,0), 0, 'gname');
	$rtr = content_line('T',$group);
	$rtr .= '<td>';
	if(LEAGUE_TYPE=='D') $qry = 'SELECT (t.frags/(t.frags+t.deaths)*100) AS eff, c.iduser AS idc, c.country, c.login AS cname, t.points, t.frags, t.deaths, t.wins, t.draw, t.lost, t.map_win, t.map_lost, t.map_draw FROM '.KML_PREFIX.'_table AS t, '.KML_PREFIX.'_users AS c WHERE t.league='.LEAGUE.' AND t.idc=c.iduser AND t.idg="'.$id.'" ORDER BY t.points DESC, eff DESC, c.login ASC';
	else $qry = 'SELECT (t.frags/(t.frags+t.deaths)*100) AS eff, c.idc, c.country, c.cname, t.points, t.frags, t.deaths, t.wins, t.draw, t.lost, t.map_win, t.map_lost, t.map_draw FROM '.KML_PREFIX.'_table AS t, '.KML_PREFIX.'_clan AS c WHERE t.league='.LEAGUE.' AND t.idc=c.idc AND t.idg="'.$id.'" ORDER BY t.points DESC, eff DESC, c.cname ASC';
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	if(mysql_num_rows($rsl)==0) $rtr .= $lang['group_err'];
	else{
		$rtr .= '<table cellspacing="1" cellpadding="5" class="tab_brdr">
		<tr><td>&nbsp;</td><td class="tab_head2">'.$lang['clan'].'</td><td class="tab_head2">'.$lang['points'].'</td><td class="tab_head2">'.$lang['wars'].'</td><td class="tab_head2">'.$lang['wins'].'</td><td class="tab_head2">'.$lang['lost'].'</td><td class="tab_head2">'.$lang['frags2'].'</td><td class="tab_head2">'.$lang['deaths2'].'</td><td class="tab_head2">'.$lang['frg_m'].'</td><td class="tab_head2">'.$lang['dts_m'].'</td><td class="tab_head3">'.$lang['net'].'</td><td class="tab_head3">'.$lang['eff'].'</td></tr>';
		while($row=mysql_fetch_assoc($rsl)){
			foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
			$games = $row['wins']+$row['draw']+$row['lost'];
			if($games==0){
				$frg_match = 0;
				$dth_match = 0;
			}else{
				$frg_match = $row['frags']/$games;
				$dth_match = $row['deaths']/$games;
			}
			if(!$row['eff']) $row['eff'] = 0;
			++$i;
			if($row['points']!=$points) $j = $i;
			elseif($row['points']==$points && $row['eff']!=$eff) $j = $i;
			if($awards[$row['idc']]) $ainf = ' <a href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=clans&amp;id='.$row['idc'].'&amp;so=awa"><img alt="'.$lang['awards'].'" title="'.$lang['level'].': '.$awards[$row['idc']].'" src="'.DIR.'_img/award'.$awards[$row['idc']].'.gif"/></a>'; else $ainf = '';
			$rtr .= '<tr class="content2"><td class="bold">'.$j.'</td><td class="content1"><a class="link" href="index.php?'.KML_LINK_SL.'op=clans&amp;id='.$row['idc'].'">'.show_flag($row['country']).' '.$row['cname'].'</a>'.$ainf.'</td><td align="right">'.$row['points'].'</td><td align="right">'.($row['wins']+$row['draw']+$row['lost']).'</td><td align="right">'.$row['wins'].' ('.$row['map_win'].')</td><td align="right">'.$row['lost'].' ('.$row['map_lost'].')</td><td align="right">'.$row['frags'].'</td><td align="right">'.$row['deaths'].'</td><td align="right">'.round($frg_match).'</td><td align="right">'.round($dth_match).'</td><td align="right">'.($row['frags']-$row['deaths']).'</td><td align="right">'.round($row['eff']).' %</td></tr>';
			$points = $row['points'];
			$eff = $row['eff'];
		}
		$rtr .= '</table>
		<div align="right"><br/>'.$lang['table_info'].'</div><br/>';
		$rtr .= match_list($id);
	}
	$rtr .= content_line('B');
	return $rtr;
}

#play off tree functions
Function black($count){
	for($i=0;$i<$count;$i++) $rtr .= '<tr><td class="content2" height="34">&nbsp;</td></tr>';
	return $rtr;
}

Function black2($link=''){
	global $lang;
	#for($i=0;$i<$count;$i++){
		if($link) $inf = '<a href="admin.php?'.KML_LINK_SL.'op=match&amp;opx=3&amp;'.$link.'"><img title="'.$lang['add'].'" alt="new" src="adm/new.gif"/></a>'; else $inf = '+++';
		$rtr .= '<tr><td class="content1" align="center" height="34">'.$inf.'</td></tr>';
	#}
	return $rtr;
}

Function poff_single_match($row){
	$row['cnm2'] = $row['cnm2'];
	$row['cnm1'] = $row['cnm1'];
	if($row['win1']>$row['win2']) $win = $row['cnm1'].' '.show_flag($row['ctry1']);
	elseif($row['win1']<$row['win2']) $win = $row['cnm2'].' '.show_flag($row['ctry2']);
	else $win = '';
	$rtr = '<tr><td class="content2" style="height: 34px; width: 138px;" align="center"><a class="link" href="index.php?'.KML_LINK_SL.'op=clans&amp;id='.$row['idc1'].'">'.$row['cnm1'].'</a> vs <a class="link" href="index.php?'.KML_LINK_SL.'op=clans&amp;id='.$row['idc2'].'">'.$row['cnm2'].'</a><br/><a class="link" href="index.php?'.KML_LINK_SL.'op=descr&amp;id='.$row['idm'].'">'.$row['win1'].':'.$row['win2'].' '.$win.'</a></td></tr>';
	return $rtr;
}

Function play_offs($idpt){
	$idpt = intval($idpt);
	$qry = 'SELECT name, ptype, teams, place FROM '.KML_PREFIX.'_ptable WHERE league='.LEAGUE.' AND idpt='.$idpt;
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	$row = mysql_fetch_assoc($rsl);
	foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
	$rtr .= content_line('T',$row['name']);
	$rtr .= '<td align="center">';
	if($row['ptype']=='F') $rtr .= play_offs2($idpt,$row['teams'],$row['place']);
	else $rtr .= play_offs1($idpt,$row['teams']);
	$rtr .= content_line('B');
	return $rtr;
}

Function play_offs1($idpt,$allteams){
	global $lang;
#	$allteams = 128;
	$all = $allteams/2;
	$rounds = ceil((log($all)/log(2))+1);
	#double eliminations query
	$dqry = 'SELECT idm FROM '.KML_PREFIX.'_match WHERE league='.LEAGUE.' AND pos<0 AND ids='.IDS.' AND idpt='.$idpt;
	$drsl = query(__FILE__,__FUNCTION__,__LINE__,$dqry,0);
	if(mysql_num_rows($drsl)>0) $double = 1;
	$mnum = $allteams;
	if($double==1){
		$rtr .= ' &raquo; <a class="linkC" href="#wb">'.$lang['wbrck'].'</a>  &laquo;  &raquo; <a class="linkC" href="#final">'.$lang['poff_final'].'</a>  &laquo;  &raquo; <a class="linkC" href="#lb">'.$lang['lbrck'].'</a> &laquo; ';
		$rtr .= content_line('M',$lang['wbrck'].'<a name="wb"></a>');
		$lnum = $mnum/4;
	}
//rysowanie drabinki
#/*
	$rtr .= '<table cellspacing="0" cellpadding="1" class="tab_brdr">
	<tr>';
	for($i=0;$i<$rounds;$i++){
		$mnum /= 2;
		$c = 0;
		$rtr .= '<td align="center" valign="top" style="width: 140px;" class="tab_head1">';
		$rtr .= '1/'.$mnum;
		$rtr .= '<table cellspacing="1" cellpadding="3" class="playoffMtable">';
		if(LEAGUE_TYPE=='D') $qry = 'SELECT m.idm, m.win2, m.win1, m.idc1, m.pos, m.idc2, c1.login AS cnm1, c1.country AS ctry1, c2.login AS cnm2, c2.country AS ctry2 FROM '.KML_PREFIX.'_match AS m, '.KML_PREFIX.'_users AS c1, '.KML_PREFIX.'_users AS c2 WHERE m.league='.LEAGUE.' AND m.idc1=c1.iduser AND m.idc2=c2.iduser AND m.rnd="'.($i+1).'" AND m.pos>0 AND m.ids='.IDS.' AND m.idpt='.$idpt.' ORDER BY m.pos, m.idm';
		else $qry = 'SELECT m.idm, m.win2, m.win1, m.idc1, m.pos, m.idc2, c1.tag AS cnm1, c1.country AS ctry1, c2.tag AS cnm2, c2.country AS ctry2 FROM '.KML_PREFIX.'_match AS m, '.KML_PREFIX.'_clan AS c1, '.KML_PREFIX.'_clan AS c2 WHERE m.league='.LEAGUE.' AND m.idc1=c1.idc AND m.idc2=c2.idc AND m.rnd="'.($i+1).'" AND m.pos>0 AND m.ids='.IDS.' AND m.idpt='.$idpt.' ORDER BY m.pos, m.idm';
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		$matches = array();
		if($i==0){
			while($row = mysql_fetch_assoc($rsl)){
				foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
				++$c;
				++$c2;
				$rtr .= poff_single_match($row);
				if($c2!=$mnum){
					++$c;
					$rtr .= black(1);
				}
			}
			if(($all*2)-1-$c>0){
				for($k=0;$k<(($all*2)-1-$c)/2;$k++){
					if($k!=0) $rtr .= black(1);
					if($_SESSION['dl_grants'][MAIN_ID]>0) $link = 'idr='.($i+1).'&amp;idp='.($k+$c2+1).'&amp;idpt='.$idpt;
					$rtr .= black2($link);
				}
			}
		}else{
			if($i == 1){
				$first_black = 1;
				$second_black = 3;
			}else{
				$first_black = $second_black;
				$second_black = ($second_black*2)+1;
			}
			$rtr .= black($first_black);
			//mecze
			while($row = mysql_fetch_assoc($rsl)){
				foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
				$matches[$row['pos']] = poff_single_match($row);
			}
			for($j=1;$j<$mnum+1;$j++){
				if($matches[$j]) $rtr .= $matches[$j]; else{
					if($_SESSION['dl_grants'][MAIN_ID]>0) $link = 'idr='.($i+1).'&amp;idp='.$j.'&amp;idpt='.$idpt;
					$rtr .= black2($link);
				}
				if($j != $mnum) $rtr .= black($second_black);
			}
			$rtr .= black($first_black);
		}
		$rtr .= '</table>
		</td>';
	}
	$rtr .= '</tr>
	</table>';
#*/
	if($double==1){
		$rtr .= content_line('M',$lang['poff_final'].'<a name="final"></a>');
		if(LEAGUE_TYPE=='D') $qry = 'SELECT m.idm, m.win1, m.win2, c1.login AS cnm1, c1.country AS ctry1, c2.login AS cnm2, c2.country AS ctry2, m.idc2, m.idc1 FROM '.KML_PREFIX.'_match AS m, '.KML_PREFIX.'_users AS c1, '.KML_PREFIX.'_users AS c2 WHERE m.league='.LEAGUE.' AND m.idc1=c1.iduser AND m.idc2=c2.iduser AND m.rnd=0 AND m.pos=0 AND m.ids='.IDS.' AND m.idpt='.$idpt;
		else $qry = 'SELECT m.idm, m.win1, m.win2, c1.tag AS cnm1, c1.country AS ctry1, c2.tag AS cnm2, c2.country AS ctry2, m.idc2, m.idc1 FROM '.KML_PREFIX.'_match AS m, '.KML_PREFIX.'_clan AS c1, '.KML_PREFIX.'_clan AS c2 WHERE m.league='.LEAGUE.' AND m.idc1=c1.idc AND m.idc2=c2.idc AND m.rnd=0 AND m.pos=0 AND m.ids='.IDS.' AND m.idpt='.$idpt;
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		$rtr .= '<table cellspacing="2" cellpadding="2" class="tab_brdr">';
		if(mysql_num_rows($rsl)==1){
			$row = mysql_fetch_array($rsl);
			foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
			$rtr .= poff_single_match($row);
		}else{
			if($_SESSION['dl_grants'][MAIN_ID]>0) $link = 'idr=0&amp;idp=0&amp;idpt='.$idpt;
			$rtr .= black2($link);
		}
		$rtr .= '</table>';
		$rtr .= content_line('M',$lang['lbrck'].'<a name="lb"></a>');
		//rysowanie drabinki
		//loser brackets

		$rounds = ($rounds-1)*2;

		$rtr .= '<table cellspacing="0" cellpadding="1" class="tab_brdr">
		<tr class="tab_dcon">';
		for($i=0;$i<$rounds;$i++){
			$rtr .= '<td align="center" width="140" class="tab_head1">';

			if($i==1 || $i==0){
				$ok;
			}elseif($i%2==0){
				++$ddl;
				$lnum /= 2;
			}

			if($i!=0){
				if($dbl%2==0) ++$bbl;
			}

			$rtr .= '1/'.$lnum;
			$rtr .= '<table cellspacing="1" cellpadding="3" class="playoffMtable">';
			if(LEAGUE_TYPE=='D') $qry = 'SELECT m.idm, m.pos, m.win1, m.win2, c1.login AS cnm1, c1.country AS ctry1, c2.login AS cnm2, c2.country AS ctry2, m.idc2, m.idc1 FROM '.KML_PREFIX.'_match AS m, '.KML_PREFIX.'_users AS c1, '.KML_PREFIX.'_users AS c2 WHERE m.league='.LEAGUE.' AND m.idc1=c1.iduser AND m.idc2=c2.iduser AND m.rnd='.($i+1).' AND m.pos<0 AND m.ids='.IDS.' AND m.idpt='.$idpt.' ORDER BY m.pos, m.idm ASC';
			else $qry = 'SELECT m.idm, m.pos, m.win1, m.win2, c1.tag AS cnm1, c1.country AS ctry1, c2.tag AS cnm2, c2.country AS ctry2, m.idc2, m.idc1 FROM '.KML_PREFIX.'_match AS m, '.KML_PREFIX.'_clan AS c1, '.KML_PREFIX.'_clan AS c2 WHERE m.league='.LEAGUE.' AND m.idc1=c1.idc AND m.idc2=c2.idc AND m.rnd='.($i+1).' AND m.pos<0 AND m.ids='.IDS.' AND m.idpt='.$idpt.' ORDER BY m.pos, m.idm ASC';
			$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
			if($i == 1 || $i == 0){
				$first_black = 0;
				$second_black = 1;
			}else{
				if($dbl%2==0){
					$first_black = $second_black;
					$second_black = $second_black*2+1;
				}
			}
			$rtr .= black($first_black);
			//mecze
			$matches = '';
			while($row = mysql_fetch_array($rsl)){
				foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
				$matches[$row['pos']] = poff_single_match($row);
			}

			for($j=1;$j<$lnum+1;$j++){
				if($matches['-'.$j]) $rtr .= $matches['-'.$j]; else{
					if($_SESSION['dl_grants'][MAIN_ID]>0) $link = 'idr='.($i+1).'&amp;idp=-'.$j.'&amp;idpt='.$idpt;
					$rtr .= black2($link);
				}
				#sprawdzic sprawdzic sprawdzic sprawdzic  sprawdzic sprawdzic sprawdzic
				if($i==1 || $i==0){
					if($j < $lnum) $rtr .= black($second_black);
				}else if($j != $lnum) $rtr .= black($second_black);
			}
			$rtr .= black($first_black);
			++$dbl;
			$rtr .= '</table>';
			$rtr .= '</td>';
		}
		$rtr .= '</tr>';
		$rtr .= '</table>';
	}
	return $rtr;
}

Function black3($dscr){
	if($dscr) $rtr .= '<tr><td class="tab_head2" height="20" align="right">'.$dscr.'</td></tr>';
	else $rtr .= '<tr><td height="20">&nbsp;</td></tr>';
	return $rtr;
}

Function play_offs2($idpt,$allteams,$aplace){
	global $lang;
	$all = $allteams/2;
	$rounds = ceil((log($all)/log(2))+1);
//rysowanie drabinki
	$rtr .= '<table cellspacing="0" cellpadding="1" class="tab_brdr">
	<tr>';
	for($i=0;$i<$rounds;$i++){
		$rtr .= '<td align="center" width="140" class="tab_head1">';
		#naglowki z info o jakie miejsca sa mecze
		$places = $allteams/pow(2,$i);
		$lastplace = $places;
		$rndDscr = ($aplace).'-'.($places+$aplace-1);
		$divede = $places/2;
		#naglowki z info co za runda
		if($i!=$rounds-1) $rtr .= $lang['round'].' '.($i+1); else $rtr .= $lang['final'];
		#$rtr .= ' + '.$divede.' - '.$i.' | '.$allteams;
		$rtr .= '<table cellspacing="1" cellpadding="3" class="playoffMtable">';
		if(LEAGUE_TYPE=='D') $qry = 'SELECT m.idm, m.win2, m.win1, m.idc1, m.pos, m.idc2, c1.login AS cnm1, c1.country AS ctry1, c2.login AS cnm2, c2.country AS ctry2 FROM '.KML_PREFIX.'_match AS m, '.KML_PREFIX.'_users AS c1, '.KML_PREFIX.'_users AS c2 WHERE m.league='.LEAGUE.' AND m.idc1=c1.iduser AND m.idc2=c2.iduser AND m.rnd="'.($i+1).'" AND m.pos>0 AND m.ids='.IDS.' AND m.idpt='.$idpt.' ORDER BY m.pos, m.idm';
		else $qry = 'SELECT m.idm, m.win2, m.win1, m.idc1, m.pos, m.idc2, c1.tag AS cnm1, c1.country AS ctry1, c2.tag AS cnm2, c2.country AS ctry2 FROM '.KML_PREFIX.'_match AS m, '.KML_PREFIX.'_clan AS c1, '.KML_PREFIX.'_clan AS c2 WHERE m.league='.LEAGUE.' AND m.idc1=c1.idc AND m.idc2=c2.idc AND m.rnd="'.($i+1).'" AND m.pos>0 AND m.ids='.IDS.' AND m.idpt='.$idpt.' ORDER BY m.pos, m.idm';
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		$matches = '';
		$rtr .= black3($rndDscr);
		//mecze
		while($row = mysql_fetch_assoc($rsl)){
			foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
			$matches[$row['pos']] = poff_single_match($row);
		}
		for($j=1;$j<$all+1;$j++){
			if($matches[$j]) $rtr .= $matches[$j]; else{
				if($_SESSION['dl_grants'][MAIN_ID]>0) $link = 'idr='.($i+1).'&amp;idp='.$j.'&amp;idpt='.$idpt;
				$rtr .= black2($link);
			}
			if($j != $all){
				if($j%$divede==0){
					$lplace = $lastplace+$divede*2;
					$inf = ($lastplace+1+$aplace-1).'-'.($lplace+$aplace-1);
					$lastplace = $lplace;
				}else $inf = '';
				$rtr .= black3($inf);
			}
		}
		$rtr .= '</table>
		</td>';
	}
	$rtr .= '</tr>
	</table>';
	return $rtr;
}



Function special_table($idc){
	global $lang;
	global $countries;
	if($idc>0){
		if(LEAGUE_TYPE=='D') $qry = 'SELECT u.login AS cname FROM '.KML_PREFIX.'_users AS u, '.KML_PREFIX.'_special_table AS x WHERE x.idc=u.iduser AND x.ids='.IDS.' AND x.league='.LEAGUE.' AND u.iduser='.(int)$idc;
		else $qry = 'SELECT c.cname FROM '.KML_PREFIX.'_clan AS c, '.KML_PREFIX.'_special_table AS x WHERE x.idc=c.idc AND x.ids='.IDS.' AND x.league='.LEAGUE.' AND c.idc='.(int)$idc;
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		if(mysql_num_rows($rsl)==0) return $lang['sure'];
		$row = mysql_fetch_assoc($rsl);
		foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
		$add = ': '.$row['cname'];
	}
	$rtr = content_line('T',$lang['special_table'].$add);
	$rtr .= '<td align="center"><br/>';
	if($idc>0){
		if(LEAGUE_TYPE=='D') $qry = 'SELECT x.date, x.points, x.description FROM '.KML_PREFIX.'_users AS u, '.KML_PREFIX.'_special_table AS x WHERE x.idc=u.iduser AND x.ids='.IDS.' AND x.league='.LEAGUE.' AND u.iduser='.(int)$idc.' ORDER BY x.date DESC';
		else $qry = 'SELECT x.date, x.points, x.description FROM '.KML_PREFIX.'_clan AS c, '.KML_PREFIX.'_special_table AS x WHERE x.idc=c.idc AND x.ids='.IDS.' AND x.league='.LEAGUE.' AND c.idc='.(int)$idc.' ORDER BY x.date DESC';
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		if(mysql_num_rows($rsl)==0) return $lang['sure'];
		$rtr .= '<table align="center" cellspacing="1" cellpadding="5" class="tab_brdr">';
		$rtr .= '<tr><td class="tab_head2">'.$lang['date'].'</td><td class="tab_head2">'.$lang['points'].'</td><td class="tab_head2">'.$lang['descr'].'</td></tr>';
		while($row=mysql_fetch_assoc($rsl)){
			foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
			$rtr .= '<tr><td class="content1">'.date('Y-m-d', $row['date']).'</td><td class="content2">'.$row['points'].'</td><td class="content2">'.$row['description'].'</td></tr>';
		}
		$rtr .= '</table>';

	}else{
		if(LEAGUE_TYPE=='D') $qry = 'SELECT u.iduser AS idc, u.login AS cname, u.country, SUM(x.points) AS sum_points FROM '.KML_PREFIX.'_users AS u, '.KML_PREFIX.'_special_table AS x WHERE x.idc=u.iduser AND x.ids='.IDS.' AND x.league='.LEAGUE.' GROUP BY x.idc ORDER BY sum_points DESC';
		else $qry = 'SELECT c.idc, c.cname, c.country, SUM(x.points) AS sum_points FROM '.KML_PREFIX.'_clan AS c, '.KML_PREFIX.'_special_table AS x WHERE x.idc=c.idc AND x.ids='.IDS.' AND x.league='.LEAGUE.' GROUP BY x.idc ORDER BY sum_points DESC';
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		if(mysql_num_rows($rsl)>0){
			$rtr .= '<table align="center" cellspacing="1" cellpadding="5" class="tab_brdr">';
			$rtr .= '<tr><td class="tab_head2"></td><td class="tab_head2">'.$lang['clan'].'</td><td class="tab_head2">'.$lang['points'].'</td><td class="tab_head2">'.ucfirst(strtolower($lang['details'])).'</td></tr>';
			while($row=mysql_fetch_assoc($rsl)){
				foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
				$rtr .= '<tr><td class="bold content1">'.(++$j).'</td><td class="content1"><a class="link" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=clans&amp;id='.$row['idc'].'">'.show_flag($row['country']).' '.$row['cname'].'</a></td><td class="content2">'.round($row['sum_points'],2).'</td><td class="content2" align="center"><a class="tab_link2" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=special_table&amp;idc='.$row['idc'].'">&raquo;</a></td></tr>';
			}
			$rtr .= '</table>';
		}else $rtr .= $lang['special_table_empty'];
	}
	$rtr .= '<br/>';
	$rtr .= content_line('B');
	return $rtr;
}

?>
