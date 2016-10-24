<?php

# SEASON

Function season($post,$id,$opt){
	global $alang;
	$basics['table'] = KML_PREFIX.'_season';
	$basics['op'] = 'season&amp;'.KML_LINK_SL2;
	$basics['opt'] = $opt;
	$basics['key'] = 'ids';
	$basics['id'] = $id;
	$basics['query_add'] = ', league';
	$basics['query_add_value'] = ', '.LEAGUE;
	$basics['list_query'] = 'SELECT ids, `sname` FROM '.KML_PREFIX.'_season WHERE league='.LEAGUE;
	$basics['list_query_add'] = ' ORDER BY ids DESC';
	$basics['header'] = $alang['seasons'];
	$basics['header_edit'] = $alang['seasons'];
	$basics['list_items'] = array($alang['name']);
	$basics['list_values'] = array('{sname}');
	$basics['list_size'] = '300:400';
	$basics['editonly'] = 1;
	$basics['inf_del_query'] = $alang['qinfo_rem'].': '.$alang['season'];
	$basics['inf_del'] = $alang['info_rem'].': '.$alang['season'];
	$basics['inf_add'] = $alang['info_add'].': '.$alang['season'];
	$basics['inf_upd'] = $alang['info_sav'].': '.$alang['season'];

	$fields['sname'] = array('head'=>$alang['name'], 'req'=>'Y', 'param1'=>'200', 'param2'=>'20');
	$fields['descr'] = array('head'=>$alang['descr'], 'param1'=>'200', 'param2'=>'50');
	echo data_form($post,$get,$fields,$basics);
}

# GROUPS
Function groups($post,$id,$opt){
	global $alang;
	for($i=1;$i<21;$i++) $phases[] = $i;
	$basics['table'] = KML_PREFIX.'_group';
	$basics['op'] = 'group&amp;'.KML_LINK_SL2;
	$basics['opt'] = $opt;
	$basics['key'] = 'idg';
	$basics['id'] = $id;
	$basics['query_add'] = ',league, ids';
	$basics['query_add_value'] = ', '.LEAGUE.', '.IDS;
	$basics['query_req'] = 'league='.LEAGUE.' AND ids='.IDS;
	$basics['list_query'] = 'SELECT idg, gphase, gname FROM '.KML_PREFIX.'_group WHERE league='.LEAGUE.' AND ids='.IDS.' ORDER BY gphase DESC, gname';
	$basics['header'] = $alang['new_group'];
	$basics['header_edit'] = $alang['edit_group'];
	$basics['header_list'] = '';
	$basics['list_items'] = array($alang['name'], ucfirst(strtolower($alang['round'])));
	$basics['list_values'] = array('{gname}', '{gphase}');
	$basics['inf_del_query'] = $alang['qinfo_rem'].': '.$alang['group'];
	$basics['inf_del'] = $alang['info_rem'].': '.$alang['group'];
	$basics['inf_add'] = $alang['info_add'].': '.$alang['group'];
	$basics['inf_upd'] = $alang['info_sav'].': '.$alang['group'];
	$fields['gname'] = array('head'=>$alang['name'], 'param1'=>200, 'param2'=>20, 'req'=>'Y');
	$fields['gphase'] = array('type'=>'S', 'head'=>ucfirst(strtolower($alang['round'])), 'values'=>$phases, 'req'=>'Y', 'param2'=>'N');
	if($post['opt3'] || $post['opt4'] || $opt==3 || $opt==4){
		$qry = 'SELECT idt FROM '.KML_PREFIX.'_table WHERE league='.LEAGUE.' AND idg='.(int)$id;
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		if(mysql_num_rows($rsl)>0){
			echo '<div class="infoAction">'.$alang['group_delerr'].'</div>';
			unset($post['opt3'],$post['opt4'],$basics['opt']);
		}
	}
	echo data_form($post,$get,$fields,$basics);
}

Function menu_rounds(){
	global $alang;
	$qry = 'SELECT gphase FROM '.KML_PREFIX.'_group WHERE league='.LEAGUE.' AND ids='.IDS.' GROUP BY gphase';
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	while($row = mysql_fetch_array($rsl)){
		$r .= '<a class="menu_link" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=groups&amp;id='.$row['gphase'].'">'.strtoupper($alang['round']).' '.$row['gphase'].'</a><br/>';
	}
	return $r;
}

Function clans_groups($dts,$id){
	global $alang;
	option_head($alang['tables_round'].' '.$id);
	if(LEAGUE_TYPE=='D') $qry ='SELECT x.idc, u.login AS tag, u.login AS cname FROM '.KML_PREFIX.'_users AS u, '.KML_PREFIX.'_in AS x WHERE x.league='.LEAGUE.' AND x.idc=u.iduser ORDER BY tag';
	else $qry ='SELECT c.idc, c.tag, c.cname FROM '.KML_PREFIX.'_clan AS c, '.KML_PREFIX.'_in AS x WHERE x.league='.LEAGUE.' AND x.idc=c.idc ORDER BY c.tag';
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	if(mysql_num_rows($rsl)<1) die($alang['grp_err1']);
	$gqry = 'SELECT idg, gname FROM '.KML_PREFIX.'_group WHERE league='.LEAGUE.' AND ids='.IDS.' AND gphase='.(int)$id;
	$grsl = query(__FILE__,__FUNCTION__,__LINE__,$gqry,0);
	if(mysql_num_rows($grsl)<1) die($alang['grp_err2']);
	$groups[0] = '';
	while($grow=mysql_fetch_assoc($grsl)){
		foreach($grow as $ky=>$vl) $grow[$ky] = intoBrowser($vl);
		$groups[$grow['idg']] = $grow['gname'];
	}
	$tqry = 'SELECT idg, gname FROM '.KML_PREFIX.'_group WHERE league='.LEAGUE.' AND gphase='.(int)$id;
	$trsl = query(__FILE__,__FUNCTION__,__LINE__,$tqry,0);
	while($trow=mysql_fetch_array($trsl)){
		foreach($trow as $ky=>$vl) $trow[$ky] = intoBrowser($vl);
		$tab[$trow['idg']] = $trow['gname'];
	}
	if($dts['opt']==$alang['save']){
		$all = count($dts['name'])+1;
		for($i=0;$i<$all;$i++){
			if($dts['idg'][$i]!=$dts['oidg'][$i]){
				if($dts['idt'][$i]){
					if(!empty($dts['idg'][$i])){
						$err = check_clangp($dts['idt'][$i], $dts['idc'][$i], $dts['idg'][$i]);
						if(!$err){
							$qry = 'UPDATE '.KML_PREFIX.'_table SET idg="'.(int)$dts['idg'][$i].'" WHERE league='.LEAGUE.' AND idt='.(int)$dts['idt'][$i];
							$status = '<span class="hd">'.$dts['name'][$i].'</span> '.$alang['clan_move'].' <span class="hd">'.$tab[$dts['idg'][$i]].'</span>.';
						}else print($err);
					}else{
						$err = check_clangp($dts['idt'][$i], $dts['idc'][$i], $dts['idg'][$i]);
						if(!$err){
							$qry = 'DELETE FROM '.KML_PREFIX.'_table WHERE league='.LEAGUE.' AND idt='.(int)$dts['idt'][$i];
							$status = '<span class="hd">'.$dts['name'][$i].'</span> '.$alang['clan_ngrp'];
						}else print($err);
					}
				}else{
					if(!empty($dts['idg'][$i])){
						$qry = 'INSERT INTO '.KML_PREFIX.'_table(league, idc, idg) VALUES('.LEAGUE.', '.(int)$dts['idc'][$i].', "'.(int)$dts['idg'][$i].'")';
						$status = '<span class="hd">'.$dts['name'][$i].'</span> '.$alang['clan_agrp'].' <span class="hd">'.$tab[$dts['idg'][$i]].'</span>.';
					}
				}
				if($qry) if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) println($status.'<br/>');
				if($err) print('<span class="hd">'.$dts['name'][$i].':</span> '.$err.'<br/>');
			}
			$qry = '';
			$err = '';
		}
	}else{
		println('<table cellpadding="4">
		<form method="post" action="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=groups"><input type="hidden" name="id" value="'.$id.'">
		<tr><td class="hd">'.$alang['clan'].'</td><td class="hd">'.ucfirst($alang['group']).'</td></tr><tr>');
		while($row=mysql_fetch_array($rsl)){
			foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
			if((++$i-1)%2==0 && $i!=1) print('</tr><tr>');
			$cqry = 'SELECT t.idg, t.idt FROM '.KML_PREFIX.'_table AS t, '.KML_PREFIX.'_group AS g WHERE t.league='.LEAGUE.' AND t.idg=g.idg AND g.gphase='.(int)$id.' AND t.idc='.$row['idc'].' AND g.ids='.IDS;
			$crsl = query(__FILE__,__FUNCTION__,__LINE__,$cqry,0);
			$crow = mysql_fetch_array($crsl);
			println('<td>'.$row['tag'].' | '.$row['cname'].'</td><td><select name="idg['.$i.']">'.array_assoc($groups,$crow['idg']).'</select>
			<input type="hidden" name="idc['.$i.']" value="'.$row['idc'].'"><input type="hidden" name="name['.$i.']" value="'.$row['tag'].' | '.$row['cname'].'"><input type="hidden" name="idt['.$i.']" value="'.$crow['idt'].'"><input type="hidden" name="oidg['.$i.']" value="'.$crow['idg'].'"></td>');
		}
		println('</tr><tr><td colspan="2" align="center"><input type="submit" name="opt" value="'.$alang['save'].'"></td></tr>
		</form>
		</table>');
	}
}

Function check_clangp($idt,$idc,$nidg){
	global $alang;
	$qry = 'SELECT idg FROM '.KML_PREFIX.'_table WHERE league='.LEAGUE.' AND idt='.(int)$idt;
	$idg = mysql_result(query(__FILE__,__FUNCTION__,__LINE__,$qry,0), 0, 'idg');
	if($nidg!=$idg){
		$qry = 'SELECT idm FROM '.KML_PREFIX.'_match WHERE league='.LEAGUE.' AND (idc1="'.(int)$idc.'" OR idc1="'.(int)$idc.'") AND idg='.(int)$idg;
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		if(mysql_num_rows($rsl)>0) return $alang['del_matchb'];
		$qry = 'SELECT idy FROM '.KML_PREFIX.'_penalty WHERE league='.LEAGUE.' AND idt="'.(int)$idt.'"';
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		if(mysql_num_rows($rsl)>0) return $alang['del_matchb2'];
	}
}

# GROUP FIXER

Function table_fixer($idg){
	global $alang;
	option_head($alang['table_fix']);
	if($idg){
		$qry = 'SELECT gname FROM '.KML_PREFIX.'_group WHERE league='.LEAGUE.' AND idg='.(int)$idg;
		$info = mysql_result(query(__FILE__,__FUNCTION__,__LINE__,$qry,0), 0, 'gname');
		println('<div class="header">'.$info.'</div>');
		$qry = 'SELECT idc FROM '.KML_PREFIX.'_table WHERE league='.LEAGUE.' AND idg='.(int)$idg;
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		while($row=mysql_fetch_array($rsl)){
			foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
			$qry = 'SELECT idc1, idc2, frags1, frags2, points1, points2, win1, win2, draw FROM '.KML_PREFIX.'_match WHERE league='.LEAGUE.' AND ids='.IDS.' AND idg='.(int)$idg.' AND (win1>0 OR draw>0 OR win2>0) AND (idc1='.$row['idc'].' OR idc2='.$row['idc'].')';
			$srsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
			$frags = 0;
			$deaths = 0;
			$points = 0;
			$map_win = 0;
			$map_draw = 0;
			$map_lost = 0;
			$win = 0;
			$lost = 0;
			$draw = 0;
			while($srow=mysql_fetch_array($srsl)){
				if($row['idc']==$srow['idc1']){
					$frags += $srow['frags1'];
					$deaths += $srow['frags2'];
					$points += $srow['points1'];
					$map_win += $srow['win1'];
					$map_draw += $srow['draw'];
					$map_lost += $srow['win2'];
					if($srow['win1']>$srow['win2']) ++$win; elseif($srow['win1']<$srow['win2']) ++$lost; else ++$draw;
				}elseif($row['idc']==$srow['idc2']){
					$frags += $srow['frags2'];
					$deaths += $srow['frags1'];
					$points += $srow['points2'];
					$map_win += $srow['win2'];
					$map_draw += $srow['draw'];
					$map_lost += $srow['win1'];
					if($srow['win2']>$srow['win1']) ++$win; elseif($srow['win2']<$srow['win1']) ++$lost; else ++$draw;
				}
			}
			$tqry = 'SELECT idt FROM '.KML_PREFIX.'_table WHERE league='.LEAGUE.' AND idg='.(int)$idg.' AND idc='.$row['idc'];
			$idt = mysql_result(query(__FILE__,__FUNCTION__,__LINE__,$tqry,0), 0, 'idt');
			$pqry = 'SELECT SUM(frags) AS frags, SUM(points) AS points FROM '.KML_PREFIX.'_penalty WHERE league='.LEAGUE.' AND idt='.$idt;
			$prsl = query(__FILE__,__FUNCTION__,__LINE__,$pqry,0);
			$prow = mysql_fetch_array($prsl);
			$qry = 'UPDATE '.KML_PREFIX.'_table SET points="'.($points-$prow['points']).'", frags="'.$frags.'", deaths="'.($deaths+$prow['frags']).'", wins="'.$win.'", lost="'.$lost.'", map_win="'.$map_win.'", map_draw="'.$map_draw.'", map_lost="'.$map_lost.'" WHERE league='.LEAGUE.' AND idt='.$idt;
			query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		}
		println($alang['tabl_fixed'].'<br/><br/>');
	}
	println('<form method="post" action="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=tabfix">');
	$qry = 'SELECT idg, gname FROM '.KML_PREFIX.'_group WHERE league='.LEAGUE.' AND ids='.IDS;
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	print('<select name="idg">');
	while($row=mysql_fetch_array($rsl)){
		foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
		print('<option value="'.$row['idg'].'"');
		if($row['idg']==$idg) print(' selected');
		print('>'.$row['gname']);
	}
	println('</select> <input type="submit" value="'.$alang['fix'].'">
	</form>');
}

?>
