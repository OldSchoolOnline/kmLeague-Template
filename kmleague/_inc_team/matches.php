<?php

Function match_list($gr){
	global $lang;
	$rtr = content_line('M',$lang['matches']);
	if(LEAGUE_TYPE=='D') $qry = 'SELECT m.date, m.idm, m.win1, m.win2, m.draw, m.rnd, c1.login AS cnm1, c1.country AS ctry1, c2.login AS cnm2, c2.country AS ctry2 FROM '.KML_PREFIX.'_match AS m, '.KML_PREFIX.'_users AS c1, '.KML_PREFIX.'_users AS c2 WHERE m.league='.LEAGUE.' AND m.idc1=c1.iduser AND m.idc2=c2.iduser AND (m.win1>0 OR m.win2>0 OR m.draw>0) AND m.idg='.$gr.' ORDER BY m.rnd DESC, m.date DESC';
	else $qry = 'SELECT m.date, m.idm, m.win1, m.win2, m.draw, m.rnd, c1.tag AS cnm1, c1.country AS ctry1, c2.tag AS cnm2, c2.country AS ctry2 FROM '.KML_PREFIX.'_match AS m, '.KML_PREFIX.'_clan AS c1, '.KML_PREFIX.'_clan AS c2 WHERE m.league='.LEAGUE.' AND m.idc1=c1.idc AND m.idc2=c2.idc AND (m.win1>0 OR m.win2>0 OR m.draw>0) AND m.idg='.$gr.' ORDER BY m.rnd DESC, m.date DESC';
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	$rtr .= '<table cellspacing="1" cellpadding="5" class="tab_brdr" width="480">
	<tr><td class="tab_head3">'.$lang['match'].'</td><td class="tab_head3">'.$lang['date'].'</td><td class="tab_head3">'.$lang['score'].'</td><td class="tab_head3">'.ucfirst($lang['comments']).'</td></tr>';
	$rounds = array('1'=>'I', '2'=>'II', '3'=>'III', '4'=>'IV', '5'=>'V', '6'=>'VI', '7'=>'VII', '8'=>'VIII', '9'=>'IX', '10'=>'X', '11'=>'XI', '12'=>'XII', '13'=>'XIII', '14'=>'XIV', '15'=>'XV', '16'=>'XVI', '17'=>'XVII', '18'=>'XVIII', '19'=>'XIX', '20'=>'XX', '21'=>'XXI');
	while($row=mysql_fetch_assoc($rsl)){
		foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
		$com_query = 'SELECT idcomment FROM '.KML_PREFIX.'_comments WHERE type='.WARS_ID.' AND iditem='.$row['idm'];
		$com_result = query(__FILE__,__FUNCTION__,__LINE__,$com_query,0);
		$com_num = mysql_num_rows($com_result);
		if(!$com_num) $com_num=0;
		if($old2 != $row['rnd']){
			$rtr .= '<tr><td colspan="5" class="content4">'.$lang['round'].' '.$rounds[$row['rnd']].'</td></tr>';
		}
		$old2 = $row['rnd'];
		$rtr .= '<tr><td class="content2" align="center"><a class="link" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=descr&amp;id='.$row['idm'].'">'.show_flag($row['ctry1']).' '.$row['cnm1'].' vs '.$row['cnm2'].' '.show_flag($row['ctry2']).'</a></td><td class="content2">'.show_date($row['date'], 2).'</td><td class="content2">'.$row['win1'].':'.$row['win2'].'</td><td class="content2"><a class="link2" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=descr&amp;id='.$row['idm'].'#com">'.$lang['comments'].' | '.$com_num.'</a></td></tr>';
	}
	$rtr .= '</table>';
	return $rtr;
}

Function descr($id,$dl_grants,$dls_grants){
	global $lang;
	$show_scores = 1;
	$id = intval($id);
	if(LEAGUE_TYPE=='D') $qry = 'SELECT g.gname, m.idc1, c1.login AS c1name, c1.login AS c1tag, c2.login AS c2tag, c1.country AS c1ctry, m.idpt, m.server, m.idc2, c2.login AS c2name, c2.country AS c2ctry, m.idg, m.judge, m.date, m.idg, m.type, m.points1, m.points2, m.frags1, m.frags2, m.win1, m.win2, m.descr, m.server FROM '.KML_PREFIX.'_users AS c1, '.KML_PREFIX.'_users AS c2, '.KML_PREFIX.'_match AS m LEFT JOIN '.KML_PREFIX.'_group AS g USING(idg) WHERE m.league='.LEAGUE.' AND m.idc1=c1.iduser AND m.idc2=c2.iduser AND m.idm='.$id;
	else $qry = 'SELECT g.gname, m.idc1, c1.cname AS c1name, c1.tag AS c1tag, c2.tag AS c2tag, c1.country AS c1ctry, m.idpt, m.server, m.idc2, c2.cname AS c2name, c2.country AS c2ctry, m.idg, m.judge, m.date, m.idg, m.type, m.points1, m.points2, m.frags1, m.frags2, m.win1, m.win2, m.descr, m.server FROM '.KML_PREFIX.'_clan AS c1, '.KML_PREFIX.'_clan AS c2, '.KML_PREFIX.'_match AS m LEFT JOIN '.KML_PREFIX.'_group AS g USING(idg) WHERE m.league='.LEAGUE.' AND m.idc1=c1.idc AND m.idc2=c2.idc AND m.idm='.$id;
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	if(mysql_num_rows($rsl)==0) return;
	$row = mysql_fetch_assoc($rsl);
	foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
	$rtr = content_line('T',$lang['match'].': '.$row['c1tag'].' vs '.$row['c2tag']);
	$rtr .= '<td align="center">
	&raquo; <a class="linkC" href="#summary">'.$lang['summary'].'</a> &laquo; &raquo; <a class="linkC" href="#descr">'.$lang['descr'].'</a> &laquo; &raquo; <a class="linkC" href="#com">'.ucfirst($lang['comments']).'</a> &laquo; &raquo; <a class="linkC" href="#ncom">'.$lang['new_com'].'</a> &laquo;';
	if($_SESSION['dl_grants'][MAIN_ID]>0) $rtr .= ' &raquo; <a class="linkC" href="admin.php?'.KML_LINK_SL.'op=match&amp;idm='.$id.'&amp;opx=1">'.$lang['edit'].' '.strtoupper($lang['score']).'</a> &laquo; &raquo; <a class="linkC" href="admin.php?'.KML_LINK_SL.'op=match&amp;idm='.$id.'&amp;opx=2">'.$lang['edit'].' '.strtoupper($lang['descr']).'</a> &laquo;';
	$rtr .= content_line('M','<a name="summary"></a>'.$lang['summary']);
	$rtr .= '<table cellspacing="1" cellpadding="5" class="tab_brdr">
	<tr><td align="center" colspan="2" class="tab_head2"><a class="tab_link2" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=clans&amp;id='.$row['idc1'].'">'.show_flag($row['c1ctry']).' '.$row['c1name'].'</a> vs <a class="tab_link2" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=clans&amp;id='.$row['idc2'].'">'.$row['c2name'].' '.show_flag($row['c2ctry']).'</a></td></tr>';
	if($row['idg']) $type = '<a class="link" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=gr&amp;id='.$row['idg'].'">'.$row['gname'].'</a>'; elseif($row['type']) $type = $row['type']; else $type = '<a class="link" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=poff&amp;id='.$row['idpt'].'">'.$lang['playoff'].'</a>';
	if($row['judge']>0){
		$jqry = 'SELECT login, country, iduser FROM '.KML_PREFIX.'_users WHERE iduser='.$row['judge'];
		$jrsl = query(__FILE__,__FUNCTION__,__LINE__,$jqry,0);
		if(mysql_num_rows($jrsl)>0){
			$jrow = mysql_fetch_assoc($jrsl);
			foreach($jrow as $ky=>$vl) $jrow[$ky] = intoBrowser($vl);
			$judge = '<a class="link" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=users&amp;id='.$jrow['iduser'].'">'.show_flag($jrow['country']).' '.$jrow['login'].'</a>';
		}else $judge = '&mdash;&mdash;&mdash;';
	}else $judge = '&mdash;&mdash;&mdash;';
	$rtr .= '<tr><td class="tab_head2">'.$lang['type'].':</td><td class="content1">'.$type.'</td></tr>
	<tr><td class="tab_head2">'.$lang['referee'].':</td><td class="content1">'.$judge.'</td></tr>';
	if($row['server']) $rtr .= '<tr><td class="tab_head2">'.$lang['server'].':</td><td class="content1">'.$row['server'].'</td></tr>';
	if($row['date']>0) $date = show_date($row['date'], 2); else $date = '&mdash;&mdash;&mdash;';
	$rtr .= '<tr><td class="tab_head2">'.$lang['date'].':</td><td class="content1">'.$date.'</td></tr>';
#screens
	$sqry = 'SELECT idmm, map, hash, name FROM '.KML_PREFIX.'_screen WHERE league='.LEAGUE.' AND idm='.$id;
	$srsl = query(__FILE__,__FUNCTION__,__LINE__,$sqry,0);
	while($srow=mysql_fetch_assoc($srsl)){
		foreach($srow as $ky=>$vl) $srow[$ky] = intoBrowser($vl);
		$screens[$srow['idmm']] = $srow['map'].$srow['hash'].$srow['name'];
	}
#maps
	$mqry = 'SELECT idmm, map, frags1, frags2, wo, played FROM '.KML_PREFIX.'_match_map WHERE idm='.$id.' ORDER BY idmm';
	$mrsl = query(__FILE__,__FUNCTION__,__LINE__,$mqry,0);
	if(mysql_num_rows($mrsl)>0){
		while($mrow=mysql_fetch_assoc($mrsl)){
			foreach($mrow as $ky=>$vl) $mrow[$ky] = intoBrowser($vl);
			$amaps[$mrow['idmm']] = array('map'=>$mrow['map'], 'wo'=>$mrow['wo'], 'ply'=>$mrow['played']);
			if($mrow['played']==0){
				if(++$ln2>1) $xmaps .= ', ';
				$xmaps .= $mrow['map'];
				++$nply;
			}else{
				if(++$ln>1){
						$maps .= ', ';
						$score .= ', ';
				}
				if($mrow['wo']>0){
					if($mrow['map']!='w/o') $mrow['map'] .= ' (w/o)';
					$mrow['frags1'] = 'w';
					$mrow['frags2'] = 'w';
					if($mrow['wo']==$row['idc1']) $mrow['frags1'] = 'o'; else $mrow['frags2'] = 'o';
				}
				if($screens[$mrow['idmm']]) $map_inf = '<a class="link" target="_blank" href="screen/'.$screens[$mrow['idmm']].'.jpg">'.$mrow['map'].'</a>';
				else $map_inf = $mrow['map'];
				$maps .= $map_inf;
				if(++$mct!=1){
					if(SCORE_DETAILS=='N') $score_details .= ', '; else $score_details .= '<br/>';
				}
				$score_details .= $map_inf.' -> '.$mrow['frags1'].':'.$mrow['frags2'];
				if(SCORE_DETAILS=='Y'){
					$dqry = 'SELECT period, frags1, frags2  FROM '.KML_PREFIX.'_match_score_details WHERE idmm='.$mrow['idmm'].' ORDER BY period';
					$drsl = query(__FILE__,__FUNCTION__,__LINE__,$dqry,0);
					if(mysql_num_rows($drsl)>0){
						$score_details .= ' (';
						while($drow=mysql_fetch_assoc($drsl)){
							foreach($drow as $ky=>$vl) $drow[$ky] = intoBrowser($vl);
							if(++$scl!=1) $score_details .= ', ';
							$score_details .= $lang[$drow['period'].'_round_short'].' -> '.$drow['frags1'].':'.$drow['frags2'];
						}
						$score_details .= ')';
					}					
				}
			}
		}
		if(mysql_num_rows($mrsl)==$nply){
			$maps = $xmaps;
			$show_scores = 0;
		}
	}else $show_scores = 0;
	if($show_scores!=0){
		$rtr .= '<tr><td class="tab_head2">'.$lang['score'].':</td><td class="content1">'.$row['win1'].':'.$row['win2'].'</td></tr>
		<tr><td class="tab_head2">'.$lang['frags2'].':</td><td class="content1">'.$row['frags1'].':'.$row['frags2'].'</td></tr>';
	}
	if($show_scores==0) $rtr .= '<tr><td class="tab_head2">'.$lang['maps'].':</td><td class="content1">'.$maps.'</td></tr>';
	else $rtr .= '<tr><td class="tab_head2">'.$lang['score_details'].':</td><td class="content1">'.$score_details.'</td></tr>';
	if($show_scores!=0){
		$rtr .= '<tr><td class="tab_head2">'.$lang['demos'].':</td><td class="content1">';
		$rtr .= match_demos($id);
		$rtr .= '</td></tr>';
	}
	$rtr .= '</table>';
	
	if($show_scores && LEAGUE_TYPE=='T'){
		$sqry = 'SELECT idm FROM '.KML_PREFIX.'_players_stats WHERE league='.LEAGUE.' AND idm='.$id;
		$srsl = query(__FILE__,__FUNCTION__,__LINE__,$sqry,0);
		if(mysql_num_rows($srsl)>0){
			$maps = explode(',', $row['maps']);
			$rtr .= '<table cellspacing="1" cellpadding="3" class="tab_brdr">';
			if(PLAYER_TYPE=='T'){
				$cspan = ' colspan="2"';
				$rtr .= '<tr><td class="tab_head2" width="200" align="center">
				<a class="tab_link2" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=clans&amp;id='.$row['idc1'].'">'.show_flag($row['c1ctry']).' '.$row['c1name'].'</a>
				</td><td class="tab_head2" width="200" align="center">
				<a class="tab_link2" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=clans&amp;id='.$row['idc2'].'">'.show_flag($row['c2ctry']).' '.$row['c2name'].'</a>
				</td></tr>';
			}
			foreach($amaps as $ky=>$vl){
				if($vl['ply']==1 && $vl['wo']==0){
					if(PLAYER_TYPE=='T'){
						$sqry = 'SELECT p.pname, s.frags, s.deaths FROM '.KML_PREFIX.'_players_stats AS s, '.KML_PREFIX.'_player AS p WHERE s.league='.LEAGUE.' AND s.idm='.$id.' AND s.idmm="'.$ky.'" AND p.idc='.$row['idc1'].' AND p.idp=s.idp ORDER BY s.frags DESC';
					}elseif(PLAYER_TYPE=='X'){
						$sqry = 'SELECT p.pname, s.frags, s.deaths FROM '.KML_PREFIX.'_players_stats AS s, '.KML_PREFIX.'_player AS p WHERE s.league='.LEAGUE.' AND s.idm='.$id.' AND s.idmm="'.$ky.'" AND p.idp=s.idp ORDER BY s.frags DESC';
					}
					if(PLAYER_TYPE=='T' || (PLAYER_TYPE=='X' && $cmap==1)) $shead .= '<tr class="tab_head2">';
					$shead .= '<td class="tab_head3"'.$cspan.'>'.$vl['map'].'</td>';
					if(PLAYER_TYPE=='T' || (PLAYER_TYPE=='X' && count($maps)==$cmap)) $shead .= '</tr><tr>';
					if(PLAYER_TYPE=='T'){ $rtr .= $shead; $shead = '';}
					$sstats .= '<td class="content1" align="center">';
					$srsl = query(__FILE__,__FUNCTION__,__LINE__,$sqry,0);
					if(mysql_num_rows($srsl)>0){
						$sstats .= '<table cellspacing="1" cellpadding="5" class="tab_brdr">';
						while($srow=mysql_fetch_assoc($srsl)){
							foreach($srow as $ky=>$vl) $srow[$ky] = intoBrowser($vl);
							$sstats .= '<tr><td class="content1">'.$srow['pname'].'</td><td class="content2">'.$srow['frags'].':'.$srow['deaths'].'</td></tr>';
						}
						$sstats .= '</table>';
					}else $sstats .= $lang['no_info'];
					if(PLAYER_TYPE=='T'){
						$sstats .= '</td><td class="content1" align="center">';
						$sqry = 'SELECT p.pname, s.frags, s.deaths FROM '.KML_PREFIX.'_players_stats AS s, '.KML_PREFIX.'_player AS p WHERE s.league='.LEAGUE.' AND s.idm='.$id.' AND s.idmm="'.$ky.'" AND p.idc='.$row['idc2'].' AND p.idp=s.idp ORDER BY (s.frags-s.deaths) DESC';
						$srsl = query(__FILE__,__FUNCTION__,__LINE__,$sqry,0);
						if(mysql_num_rows($srsl)>0){
							$sstats .= '<table cellspacing="1" cellpadding="5" class="tab_brdr">';
							while($srow=mysql_fetch_assoc($srsl)){
								foreach($srow as $ky=>$vl) $srow[$ky] = intoBrowser($vl);
								$sstats .= '<tr><td class="content1">'.$srow['pname'].'</td><td class="content2">'.$srow['frags'].':'.$srow['deaths'].'</td></tr>';
							}
							$sstats .= '</table>';
						}else $sstats .= $lang['no_info'];
					}
					$sstats .= '</td>';
					if(PLAYER_TYPE=='T' || (PLAYER_TYPE=='X' && count($maps)==$cmap)) $sstats .= '</tr>';
					if(PLAYER_TYPE=='T'){ $rtr .= $sstats; $sstats = '';}
				}
			}
			if(PLAYER_TYPE=='X') $rtr .= $shead.$sstats;
			$rtr .= '</table>';
		}
	}
	$rtr .= content_line('M','<a name="descr"></a>'.$lang['descr']);
	$rtr .= '<div class="news">'.$row['descr'].'</div><br/>';
	if(is_array($screens) && !eregi('screen/',$row['descr'])){
		foreach($screens as $ky=>$vl) $rtr .= '<img class="screen" alt="screen" title="'.$amaps[$ky]['map'].'" src="screen/'.$vl.'.jpg" />';
	}
	$rtr .= content_line('M','<a name="com"></a>'.ucfirst($lang['comments']));
	$rtr .= comms(WARS_ID,$id,$dl_grants,$dls_grants);
	if(IDSA==IDS){
		$rtr .= content_line('M','<a name="ncom"></a>'.$lang['new_com']);
		if(banned($_SERVER['REMOTE_ADDR'])){
			$rtr .= $lang['banned'];
			return $rtr;
		}
		if($dl_grants>0) $rtr .= comment_form($id,WARS_ID);
		else $rtr .= $lang['comm_info'];
	}
	$rtr .= content_line('B');
	return $rtr;
}

Function show_schedule($qry,$rounds,$gname,$gtype,$judges){
	global $lang;
	$trounds = array('1'=>'I', '2'=>'II', '3'=>'III', '4'=>'IV', '5'=>'V', '6'=>'VI', '7'=>'VII', '8'=>'VIII', '9'=>'IX', '10'=>'X', '11'=>'XI', '12'=>'XII', '13'=>'XIII', '14'=>'XIV', '15'=>'XV', '16'=>'XVI', '17'=>'XVII', '18'=>'XVIII', '19'=>'XIX', '20'=>'XX', '21'=>'XXI');
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	$rtr = content_line('M','<a name="'.clear_string($gname).'"></a>'.$gname);
	if(mysql_num_rows($rsl)>0){
		if($gtype==2) $gshow ='<td class="tab_head3">'.$lang['type'].'</td>';
		$rtr .= '<table cellspacing="1" cellpadding="5" class="tab_brdr">
		<tr><td class="tab_head3">'.$lang['date'].'</td><td class="tab_head3">'.$lang['clans'].'</td>'.$gshow.'<td class="tab_head3">'.$lang['referee'].'</td></tr>';
		while($row = mysql_fetch_assoc($rsl)){
			foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
			if($old != $row['rnd'] && $gtype == 1){
				if($rounds[$row['idg']][$row['rnd']]) $head = $lang['round'].' '.$trounds[$row['rnd']].': '.$rounds[$row['idg']][$row['rnd']]; else $head = $lang['round'].' '.$trounds[$row['rnd']];
				$rtr .= '<tr><td colspan="4" class="tab_head3">'.$head.'</td></tr>';
			}
			$old = $row['rnd'];
			if($row['date'] == 0) $mtime = '&mdash;&mdash;&mdash;'; else $mtime = show_date($row['date'], 2);
			if($row['idg']) $type = ''; elseif($row['type']) $type = '<td>'.$row['type'].'</td>'; else $type = '<td>'.$lang['playoff'].'</td>';
			if($row['judge']>0) $judge = '<a class="link" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=users&amp;id='.$row['judge'].'">'.show_flag($judges[$row['judge']][1]).' '.$judges[$row['judge']][0].'</a>'; else $judge = '&mdash;&mdash;&mdash;';
			$rtr .= '<tr class="content2"><td><a class="tab_link" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=descr&amp;id='.$row['idm'].'">'.$mtime.'</a></td><td align="center" class="content1"><a class="link" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=clans&amp;id='.$row['idc1'].'">'.show_flag($row['c1ctry']).' '.$row['cnm1'].'</a> vs <a class="link" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=clans&amp;id='.$row['idc2'].'">'.$row['cnm2'].' '.show_flag($row['c2ctry']).'</a></td>'.$type.'<td>'.$judge.'</td></tr>';
		}
		$rtr .= '</table>';
	}else $rtr .= $lang['schedule_err1'].'<br/><br/>';
	return $rtr;
}

Function schedule(){
	global $lang;
	$qry = 'SELECT idg, rnd, descr FROM '.KML_PREFIX.'_dround WHERE league='.LEAGUE.' AND ids='.IDS;
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	while($row=mysql_fetch_assoc($rsl)){
		foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
		$rounds[$row['idg']][$row['rnd']] = $row['descr'];
	}
	$jqry = 'SELECT u.login, u.iduser, u.country FROM '.KML_PREFIX.'_users AS u, '.KML_PREFIX.'_grants AS g WHERE g.iduser=u.iduser AND g.service='.MAIN_ID;
	#echo $jqry;
	$jrsl = query(__FILE__,__FUNCTION__,__LINE__,$jqry,0);
	while($jrow=mysql_fetch_array($jrsl)){
		foreach($jrow as $ky=>$vl) $jrow[$ky] = intoBrowser($vl);
		$judges[$jrow['iduser']] = array($jrow['login'], $jrow['country']);
	}
	$rtr = content_line('T',$lang['schedule']);
	$rtr .= '<td align="center">
	&raquo; <a class="linkC" href="#'.clear_string($lang['m_other']).'">'.$lang['m_other'].'</a> &laquo; ';
	
#	$qry = 'SELECT idpt, name FROM '.KML_PREFIX.'_ptable WHERE league='.LEAGUE.' AND ids='.IDS.' ORDER BY idpt DESC';
#	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
#	while($row=mysql_fetch_assoc($rsl)) $rtr .= '&raquo; <a class="menu" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=poff&amp;id='.$row['idpt'].'">'.$row['name'].'</a><br/>';
	
	$qry = 'SELECT g.gname, g.idg FROM '.KML_PREFIX.'_group AS g, '.KML_PREFIX.'_table AS t WHERE t.league='.LEAGUE.' AND g.ids='.IDS.' AND t.idg=g.idg GROUP BY t.idg ORDER BY g.gphase DESC, g.gname';
	$rslt = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	while($row=mysql_fetch_assoc($rslt)){
		foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
		$rtr .= ' &raquo; <a class="linkC" href="#'.clear_string($row['gname']).'">'.$row['gname'].'</a> &laquo; ';
	}
	
	if(LEAGUE_TYPE=='D') $qry = 'SELECT g.gname, m.idc1, m.idm, c1.login AS cnm1, c1.country AS c1ctry, m.idc2, c2.login AS cnm2, c2.country AS c2ctry, m.idg, m.judge, m.date, m.rnd, m.idg, m.type FROM '.KML_PREFIX.'_users AS c1, '.KML_PREFIX.'_users AS c2, '.KML_PREFIX.'_match AS m LEFT JOIN '.KML_PREFIX.'_group g USING(idg) WHERE m.league='.LEAGUE.' AND m.idc1=c1.iduser AND m.idc2=c2.iduser AND (m.win1=0 AND m.win2=0 AND m.draw=0) AND m.ids='.IDS.' AND m.idg IS NULL ORDER BY m.rnd ASC, m.date DESC';
	else $qry = 'SELECT g.gname, m.idc1, m.idm, c1.cname AS cnm1, c1.country AS c1ctry, m.idc2, c2.cname AS cnm2, c2.country AS c2ctry, m.idg, m.judge, m.date, m.rnd, m.idg, m.type FROM '.KML_PREFIX.'_clan AS c1, '.KML_PREFIX.'_clan AS c2, '.KML_PREFIX.'_match AS m LEFT JOIN '.KML_PREFIX.'_group g USING(idg) WHERE m.league='.LEAGUE.' AND m.idc1=c1.idc AND m.idc2=c2.idc AND (m.win1=0 AND m.win2=0 AND m.draw=0) AND m.ids='.IDS.' AND m.idg IS NULL ORDER BY m.rnd ASC, m.date DESC';
	$rtr .= show_schedule($qry,$rounds,$lang['m_other'],2,$judges);
	@mysql_data_seek($rslt,0);
	while($row=mysql_fetch_assoc($rslt)){
		foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
		if(LEAGUE_TYPE=='D') $qry = 'SELECT g.gname, m.idc1, m.idm, c1.login AS cnm1, c1.country AS c1ctry, m.idc2, c2.login AS cnm2, c2.country AS c2ctry, m.idg, m.judge, m.date, m.rnd, m.idg, m.type FROM '.KML_PREFIX.'_users AS c1, '.KML_PREFIX.'_users AS c2, '.KML_PREFIX.'_match AS m LEFT JOIN '.KML_PREFIX.'_group AS g USING(idg) WHERE m.league='.LEAGUE.' AND m.idc1=c1.iduser AND m.idc2=c2.iduser AND (m.win1=0 AND m.win2=0 AND m.draw=0) AND m.ids='.IDS.' AND m.idg="'.$row['idg'].'" ORDER BY m.rnd ASC, m.date DESC';
		else $qry = 'SELECT g.gname, m.idc1, m.idm, c1.cname AS cnm1, c1.country AS c1ctry, m.idc2, c2.cname AS cnm2, c2.country AS c2ctry, m.idg, m.judge, m.date, m.rnd, m.idg, m.type FROM '.KML_PREFIX.'_clan AS c1, '.KML_PREFIX.'_clan AS c2, '.KML_PREFIX.'_match AS m LEFT JOIN '.KML_PREFIX.'_group AS g USING(idg) WHERE m.league='.LEAGUE.' AND m.idc1=c1.idc AND m.idc2=c2.idc AND (m.win1=0 AND m.win2=0 AND m.draw=0) AND m.ids='.IDS.' AND m.idg="'.$row['idg'].'" ORDER BY m.rnd ASC, m.date DESC';
		$rtr .= show_schedule($qry,$rounds, $row['gname'],1,$judges);
	}
	$rtr .= content_line('B');
	return $rtr;
}

Function show_all_match($qry,$gname,$gtype){
	global $lang;
	$trounds = array('1'=>'I', '2'=>'II', '3'=>'III', '4'=>'IV', '5'=>'V', '6'=>'VI', '7'=>'VII', '8'=>'VIII', '9'=>'IX', '10'=>'X', '11'=>'XI', '12'=>'XII', '13'=>'XIII', '14'=>'XIV', '15'=>'XV', '16'=>'XVI', '17'=>'XVII', '18'=>'XVIII', '19'=>'XIX', '20'=>'XX', '21'=>'XXI');
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	$rtr = content_line('M','<a name="'.clear_string($gname).'"></a>'.$gname);
	if(mysql_num_rows($rsl)>0){
		$rtr .= '<table cellspacing="1" cellpadding="5" class="tab_brdr">';
		if($gtype==2) $gshow ='<td class="tab_head3">'.$lang['type'].'</td>'; else $gshow = '';
		$rtr .= '<tr><td class="tab_head3">'.$lang['date'].'</td><td class="tab_head3">'.$lang['clans'].'</td>'.$gshow.'<td class="tab_head3">'.$lang['score'].'</td></tr>';
		while($row = mysql_fetch_assoc($rsl)){
			foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
			if($old2 != $row['rnd']){
				$rtr .= '<tr><td colspan="5" class="content4">'.$lang['round'].' '.$trounds[$row['rnd']].'</td></tr>';
			}
			$old2 = $row['rnd'];
			if(!$row['date']) $mtime = '&#8212;&#8212;&#8212;'; else $mtime = show_date($row['date'], 2);
			if($gtype==2) $type = '<td class="content1">'.$row['type'].'</td>'; else $type = '';
			$rtr .= '<tr><td class="content2">'.$mtime.'</td><td class="content1" align="center"><a class="link" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=clans&amp;id='.$row['idc1'].'">'.show_flag($row['c1ctry']).' '.$row['cnm1'].'</a> vs <a class="link" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=clans&amp;id='.$row['idc2'].'">'.$row['cnm2'].' '.show_flag($row['c2ctry']).'</a></td>'.$type.'<td class="content2" align="center"><a class="link" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=descr&amp;id='.$row['idm'].'">'.$row['win1'].':'.$row['win2'].'</a></td></tr>';
		}
		$rtr .= '</table>';
	}else $rtr .= $lang['all_matches_err'].'<br/><br/>';
	return $rtr;
}

Function all_matches(){
	global $lang;
	$rtr = content_line('T',$lang['all_matches']);
	$rtr .= '<td align="center">
	&raquo; <a class="linkC" href="#'.clear_string($lang['playoff']).'">'.$lang['playoff'].'</a> &laquo; &raquo; <a class="linkC" href="#'.clear_string($lang['m_other']).'">'.$lang['m_other'].'</a> &laquo; ';
	$qry = 'SELECT idg, gname FROM '.KML_PREFIX.'_group WHERE league='.LEAGUE.' AND ids='.IDS;
	$rslt = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	while($row=mysql_fetch_assoc($rslt)){
		foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
		$rtr .= ' &raquo; <a class="linkC" href="#'.clear_string($row['gname']).'">'.$row['gname'].'</a> &laquo; ';
	}
	if(LEAGUE_TYPE=='D') $qry = 'SELECT m.idm, u.login, u.country AS jctry, m.rnd, m.idc1, c1.login AS cnm1, c1.country AS c1ctry, m.idc2, c2.login AS cnm2, c2.country AS c2ctry, m.judge, m.date, m.win1, m.win2, m.draw FROM '.KML_PREFIX.'_users AS c1, '.KML_PREFIX.'_users AS c2, '.KML_PREFIX.'_match AS m LEFT JOIN '.KML_PREFIX.'_users AS u ON(u.iduser=m.judge) WHERE m.league='.LEAGUE.' AND m.idc1=c1.iduser AND m.idc2=c2.iduser AND (m.win1>0 OR m.win2>0 OR m.draw>0) AND m.pos IS NOT NULL AND m.ids='.IDS.' ORDER BY m.rnd DESC, m.date DESC';
	else $qry = 'SELECT m.idm, u.login, u.country AS jctry, m.rnd, m.idc1, c1.cname AS cnm1, c1.country AS c1ctry, m.idc2, c2.cname AS cnm2, c2.country AS c2ctry, m.judge, m.date, m.win1, m.win2, m.draw FROM '.KML_PREFIX.'_clan AS c1, '.KML_PREFIX.'_clan AS c2, '.KML_PREFIX.'_match AS m LEFT JOIN '.KML_PREFIX.'_users AS u ON(u.iduser=m.judge) WHERE m.league='.LEAGUE.' AND m.idc1=c1.idc AND m.idc2=c2.idc AND (m.win1>0 OR m.win2>0 OR m.draw>0) AND m.pos IS NOT NULL AND m.ids='.IDS.' ORDER BY m.rnd DESC, m.date DESC';
	$rtr .= show_all_match($qry,$lang['playoff'],3);
	if(LEAGUE_TYPE=='D') $qry = 'SELECT m.idm, u.login, u.country AS jctry, m.type, m.idc1, c1.login AS cnm1, c1.country AS c1ctry, m.idc2, c2.login AS cnm2, c2.country AS c2ctry, m.judge, m.date, m.win1, m.win2, m.draw FROM '.KML_PREFIX.'_users AS c1, '.KML_PREFIX.'_users AS c2, '.KML_PREFIX.'_match AS m LEFT JOIN '.KML_PREFIX.'_users AS u ON(u.iduser=m.judge) WHERE m.league='.LEAGUE.' AND m.idc1=c1.iduser AND m.idc2=c2.iduser AND m.type IS NOT NULL AND (m.win1>0 OR m.win2>0 OR m.draw>0) AND m.ids='.IDS.' ORDER BY m.date DESC';
	else $qry = 'SELECT m.idm, u.login, u.country AS jctry, m.type, m.idc1, c1.cname AS cnm1, c1.country AS c1ctry, m.idc2, c2.cname AS cnm2, c2.country AS c2ctry, m.judge, m.date, m.win1, m.win2, m.draw FROM '.KML_PREFIX.'_clan AS c1, '.KML_PREFIX.'_clan AS c2, '.KML_PREFIX.'_match AS m LEFT JOIN '.KML_PREFIX.'_users AS u ON(u.iduser=m.judge) WHERE m.league='.LEAGUE.' AND m.idc1=c1.idc AND m.idc2=c2.idc AND m.type IS NOT NULL AND (m.win1>0 OR m.win2>0 OR m.draw>0) AND m.ids='.IDS.' ORDER BY m.date DESC';
	$rtr .= show_all_match($qry,$lang['m_other'],2);
	@mysql_data_seek($rslt,0);
	while($row=mysql_fetch_assoc($rslt)){
		foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
		if(LEAGUE_TYPE=='D') $qry = 'SELECT m.idm, m.rnd, g.gname, m.idc1, c1.login AS cnm1, c1.country AS c1ctry, m.idc2, c2.login AS cnm2, c2.country AS c2ctry, m.idg, m.date, m.idg, m.type, m.win1, m.win2, m.draw FROM '.KML_PREFIX.'_users AS c1, '.KML_PREFIX.'_users AS c2, '.KML_PREFIX.'_match AS m LEFT JOIN '.KML_PREFIX.'_group AS g USING(idg) WHERE m.league='.LEAGUE.' AND m.idc1=c1.iduser AND m.idc2=c2.iduser AND (m.win1>0 OR m.win2>0 OR m.draw>0) AND m.idg='.$row['idg'].' AND m.ids='.IDS.' ORDER BY g.gphase ASC, m.rnd DESC, m.date DESC';
		else $qry = 'SELECT m.idm, m.rnd, g.gname, m.idc1, c1.cname AS cnm1, c1.country AS c1ctry, m.idc2, c2.cname AS cnm2, c2.country AS c2ctry, m.idg, m.date, m.idg, m.type, m.win1, m.win2, m.draw FROM '.KML_PREFIX.'_clan AS c1, '.KML_PREFIX.'_clan AS c2, '.KML_PREFIX.'_match AS m LEFT JOIN '.KML_PREFIX.'_group AS g USING(idg) WHERE m.league='.LEAGUE.' AND m.idc1=c1.idc AND m.idc2=c2.idc AND (m.win1>0 OR m.win2>0 OR m.draw>0) AND m.idg='.$row['idg'].' AND m.ids='.IDS.' ORDER BY g.gphase ASC, m.rnd DESC, m.date DESC';
		$rtr .= show_all_match($qry,$row['gname'],1);
	}
	$rtr .= content_line('B');
	return $rtr;
}

?>
