<?php

# SCHEDULE
	
Function schedule($dts,$idg,$login){
	global $alang;
	if(!$idg) $idg = 'P';
	if($idg!='P' && $idg!='N'){
		$qry = 'SELECT gname FROM '.KML_PREFIX.'_group WHERE league='.LEAGUE.' AND idg='.(int)$idg;
		$gname = mysql_result(query(__FILE__,__FUNCTION__,__LINE__,$qry,0), 0, 'gname');
	}elseif($idg == "P"){
		$gname = $alang['playoff'];
		$qry = 'SELECT idpt, name FROM '.KML_PREFIX.'_ptable WHERE league='.LEAGUE.' AND ids='.IDS.' ORDER BY idpt';
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		if(mysql_num_rows($rsl)>0){
			while($row=mysql_fetch_assoc($rsl)){
				foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
				$ptables[$row['idpt']] = $row['name'];
			}
		}else{
			echo $alang['fadd_ptable'];
			exit;
		}
	}
	elseif($idg=='N') $gname = $alang['other_match'];
	option_head($alang['schedule'].' '.$alang['for'].' '.$gname);
	if($dts['opt']==$alang['add']){
		$mqry1 = 'INSERT INTO '.KML_PREFIX.'_match_map(idm, map, played) VALUES';
		if($dts['map']){
			$mqry2 = '';
			foreach($dts['map'] AS $vl){
				if($vl){
					if($mqry2) $mqry2 .= ', ';
					$mqry2 .= '(#idm, "'.$vl.'", 0)';
				}
			}
		}
		for($i=0;$i<20;$i++){
			if($dts['clan1'][$i] && $dts['clan2'][$i] && $dts['clan1'][$i]!=$dts['clan2'][$i]){
				if($dts['mround']) $dts['round'][$i] = $dts['mround'];
				if($idg=='P' || $idg=='N') $iidg = 'NULL'; else $iidg = '"'.(int)$idg.'"';
				$qry = 'INSERT INTO '.KML_PREFIX.'_match(league, ids, judge, idg, pos, rnd, idpt, idc1, idc2, type) VALUES('.LEAGUE.', '.IDS.', '.(int)$dts['judge'][$i].', '.$iidg.', ';
				if($idg=='P') $qry .= '"'.(int)$dts['position'][$i].'", '; else $qry .= 'NULL, ';
				if($idg!='N') $qry .= '"'.(int)$dts['round'][$i].'", '; else $qry .= 'NULL, ';
				if($idg!='N') $qry .= '"'.(int)$dts['idpt'].'", '; else $qry .= '0, ';
				$qry .= (int)$dts['clan1'][$i].', '.(int)$dts['clan2'][$i].', ';
				if($idg=='N') $qry .= SQL('%s', $dts['type'][$i]); else $qry .= 'NULL';
				$qry .= ')';
				query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
				$mid = mysql_insert_id();
				if($mqry2) query(__FILE__,__FUNCTION__,__LINE__,$mqry1.str_replace('#idm', $mid, $mqry2),0);
				$j++;
			}
		}
		if($j>0) println($alang['add_schedule1']); else println($alang['add_schedule2']);
		print('<br/><br/>');
	}
	$judges[0] = '';
	if(LEAGUE_TYPE=='D'){
		$qry = 'SELECT u.login AS tag, c.idc FROM '.KML_PREFIX.'_in AS c, '.KML_PREFIX.'_users AS u';
		if($idg!='P' && $idg!='N') $qry .= ', '.KML_PREFIX.'_table AS t';
		$qry .= ' WHERE c.league='.LEAGUE.' AND c.idc=u.iduser';
		if($idg!='P' && $idg!='N') $qry .= ' AND u.iduser=t.idc AND t.idg='.(int)$idg;
	}else{
		$qry = 'SELECT c.idc, c.tag FROM '.KML_PREFIX.'_clan AS c, '.KML_PREFIX.'_in AS x ';
		if($idg!='P' && $idg!='N') $qry .= ', '.KML_PREFIX.'_table AS t';
		$qry .= ' WHERE x.idc=c.idc AND x.league='.LEAGUE.' ';
		if($idg!='P' && $idg!='N') $qry .= ' AND c.idc=t.idc AND t.idg='.(int)$idg;
	}
	$qry .= ' ORDER BY tag';
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	if(mysql_num_rows($rsl)>0){
		$clans[0] = '';
		while($row=mysql_fetch_array($rsl)){
			foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
			$clans[$row['idc']] = $row['tag'];
		}
	}
	$qry = 'SELECT u.login, u.iduser FROM '.KML_PREFIX.'_users AS u, '.KML_PREFIX.'_grants AS g WHERE g.iduser=u.iduser AND g.service='.MAIN_ID.' ORDER BY u.login';
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	while($row=mysql_fetch_array($rsl)){
		foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
		$judges[$row['iduser']] = $row['login'];
	}
	if(count($clans)>0){
		println('<form method="post" action="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=schedule"><input type="hidden" name="idg" value="'.$idg.'">
		<div class="hd">'.$alang['maps'].' <input type="button" class="addItem" value="+" onclick="javascript:maps(\'maps\', \'\')"></div>
		<div id="maps">');
		if(isset($dts['map'])){
			foreach($dts['map'] as $ky=>$vl){
				println('<script type="text/javascript">
				maps(\'maps\', \''.$vl.'\');
				</script>');
			}
		}
		print('</div><br/>
		<span class="hd">'.$alang['round_for_all'].':</span> <input type="text" name="mround" size="5" maxlength="2">');
		if(isset($ptables)) println(' <span class="hd">'.$alang['poff_table'].':</span> <select name="idpt">'.array_assoc($ptables,(int)$dts['idpt']).'</select>');
		print('<table align="center">
		<tr><td class="hd">'.$alang['clan'].' 1</td><td class="hd">'.$alang['clan'].' 2</td><td class="hd">'.$alang['referee'].'</td>');
		if($idg!='N') print('<td class="hd">'.$alang['round'].'</td>');
		if($idg=='P') print('<td class="hd">'.$alang['position'].'</td>');
		if($idg=='N') print('<td class="hd">'.$alang['type'].'</td>');
		println('</tr>');
		for($i=0;$i<15;$i++){
			print('<tr><td><select name="clan1[]">'.array_assoc($clans,'').'</select></td><td><select name="clan2[]">'.array_assoc($clans,'').'</select></td><td><select name="judge[]">'.array_assoc($judges,'').'</td>');
			if($idg!='N') print('<td><input type="text" name="round[]" size="5" maxlength="2"></td>');
			if($idg=='P') print('<td><input type="text" name="position[]" size="5" maxlength="2"></td>');
			if($idg=='N') print('<td><input type="text" name="type[]" size="20" maxlength="20"></td>');
			println('</tr>');
		}
		println('<tr><td colspan="5" align="right"><input type="submit" name="opt" value="'.$alang['add'].'"></td></tr>
		</form>
		</table>');
	}else print($alang['group_err']);
}

Function auto_scheduler($dts){
	global $alang;
	if($dts['idg']){
		$qry = 'SELECT gname FROM '.KML_PREFIX.'_group WHERE league='.LEAGUE.' AND idg='.(int)$dts['idg'];
		$gname = mysql_result(query(__FILE__,__FUNCTION__,__LINE__,$qry,0), 0, 'gname');
		option_head($alang['auto_schedule'].' '.$alang['for'].' '.$gname);
	}else option_head($alang['auto_schedule']);
	
	$qry = 'SELECT idg, gname, gphase FROM '.KML_PREFIX.'_group WHERE league='.LEAGUE.' AND ids='.IDS;
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	while($row=mysql_fetch_assoc($rsl)){
		foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
		$grupy[$row['idg']] = $alang['gphase'].': '.$row['gphase'].' | '.$alang['group'].': '.$row['gname'];
	}
	if(!is_array($grupy)) print($alang['groups_req']);
	else{
		print('<div style="text-align: left;">
		<form action="admin.php?'.KML_LINK_SL.'op=aschedule" method="post">
		&nbsp; <select name="idg">'.array_assoc($grupy, $dts['idg']).'</select> <span class="bold">'.$alang['maps_limit'].':</span> <input type="text" style="width: 30px;" maxlength="2" name="maps_limit" value="'.$dts['maps_limit'].'"> <input type="submit" value="'.$alang['show'].'">
		</div>');
	}
	if($dts['idg']){
		if(LEAGUE_TYPE=='D') $qry = 'SELECT t.idc, c.login AS cname, c.login AS tag FROM '.KML_PREFIX.'_table AS t, '.KML_PREFIX.'_users AS c WHERE t.league='.LEAGUE.' AND t.idc=c.iduser AND t.idg='.$dts['idg'];
		else $qry = 'SELECT c.idc, c.cname, c.tag FROM '.KML_PREFIX.'_table AS t, '.KML_PREFIX.'_clan AS c WHERE t.league='.LEAGUE.' AND t.idc=c.idc AND t.idg='.(int)$dts['idg'];
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		while($row=mysql_fetch_assoc($rsl)){
			foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
			$teamNames[$row['idc']] = $row['tag'];
			$teamNames2[] = array($row['idc'],$row['cname'],$row['tag']);
		}
		$tms = count($teamNames2);

		for($i=0;$i<$tms;$i++){
			$teams[$i+1] = $teamNames2[$i][0];
		}

		if($tms%2==0){
			$meczeKolejka = $tms/2;
		}else{
			$teams[$tms] = '0';
			$teamNames2[$tms] = array('0','','');
			$tms += 1;
			$meczeKolejka = $tms/2;
		}
		$iloscKolejek = $meczeKolejka*2-1;

		print('<table cellpadding="10"><tr><td valign="top"><div class="header">'.$alang['settings'].'</div><br/>
		<div valign="top">'.$alang['rounds'].': '.$iloscKolejek.'<br/>'.$alang['match_per_round'].': '.$meczeKolejka.'</div>
		</td><td valign="top"><div class="header">'.ucfirst(strtolower($alang['clans'])).'</div><br/>');
		for($i=0;$i<$tms;$i++){
			if($teamNames2[$i][0]>0){
				if(LEAGUE_TYPE=='T') $css = ' float: left;';
				print('<div style="width: 50px; font-weight: bold;'.$css.'">'.$teamNames2[$i][2].'</div>');
				if(LEAGUE_TYPE=='T') print('<div>'.$teamNames2[$i][1].'</div>');
			}
		}
		print('</td></tr>
		</table>');

		/*
		$actMatches - tabela po usunieciu meczy juz rozegranych
		$tmpMatches - tabela po usunieciu meczy ktore nie moga byc juz rozegrane w danej kolejce
		$schedule - tabela z terminarzem
		*/

	   print('<table cellpadding="10"><tr>');
	   $actMatches = $allMatches;
	   for($j=0;$j<$iloscKolejek;$j++){
			if($j%5==0 && $j!=0) print('</tr><tr>');
			#pierwszy mecz kolejki
			print('<td style="vertical-align: top;">
			<div class="header">'.$alang['round'].' '.($j+1).'</div>');
			$wylosowanyMecz = ($j+1).'-'.$tms;
			$inf = explode('-',$wylosowanyMecz);
			#nazwy druzyn pokazowe ~
			$tmID1 = $teamNames2[$j][0];
			$tmID2 = $teamNames2[$tms-1][0];
			$tmNM1 = $teamNames2[$j][2];
			$tmNM2 = $teamNames2[$tms-1][2];
			if($tmID1==0) $input = $tmNM2.' - '.$alang['break'];
			else if($tmID2==0) $input = $tmNM1.' - '.$alang['break'];
			else $input = $tmNM1.' vs '.$tmNM2.'<input type="hidden" name="schedule['.$j.'][0]" value="'.$tmID1.'-'.$tmID2.'">';
			print($input.'<br/>');
			#okreslenie kto z kim gra w kolejnych meczach
			for($i=0;$i<$meczeKolejka-1;$i++){
				$p1 = ($j+1)+(2*$i+1);
				if($p1>=$tms) $p1 = $p1-$iloscKolejek;
				$p2 = ($j+1)-(2*$i+1);
				if($p2<1) $p2 = $p2+$iloscKolejek;
				#wyswietlanie meczy w zaleznosci od druzyn
				$tmID1 = $teamNames2[$p1-1][0];
				$tmID2 = $teamNames2[$p2-1][0];
				$tmNM1 = $teamNames2[$p1-1][2];
				$tmNM2 = $teamNames2[$p2-1][2];
				if($tmID1==0) $input = $tmNM2.' - '.$alang['break'];
				else if($tmID2==0) $input = $tmNM1.' - '.$alang['break'];
				else $input = $tmNM1.' vs '.$tmNM2.'<input type="hidden" name="schedule['.$j.']['.($i+1).']" value="'.$tmID1.'-'.$tmID2.'">';
				print($input.'<br/>');
			}
			print('<div class="hd">'.$alang['maps'].':</div>');
			for($z=1;$z<$dts['maps_limit']+1;$z++){
				print('<div style="float: left; width: 25px;">'.$z.':</div> <input type="text" size="8" maxlength="10" name="map['.$j.']['.$z.']" value="'.$dts['map'][$j][$z].'"><br/>');
			}			
			print('</td>');
		}
		print('</tr></table>
		<div style="text-align: left;"><input type="submit" name="opt" value="'.$alang['add'].'"></div>
		</form>
		<div style="text-align: left;">');
		if($dts['opt']){
			$mqry1 = 'INSERT INTO '.KML_PREFIX.'_match_map(idm, map, played) VALUES';
			foreach($dts['schedule'] AS $ky=>$vl){
				echo '<div class="bold">'.$alang['round'].' '.($ky+1).'</div>';
				$mqry2 = '';
				foreach($dts['map'][$ky] AS $mk=>$mv){
					if($mv){
						if($mqry2) $mqry2 .= ', ';
						$mqry2 .= SQL('(#idm, %s, 0)', $mv);
					}
				}
				foreach($vl AS $ky2=>$vl2){
					$clan = explode('-', $vl2);
					$qry = 'SELECT idm FROM '.KML_PREFIX.'_match WHERE league='.LEAGUE.' AND ids='.IDS.' AND idg='.(int)$dts['idg'].' AND (idc1='.$clan[0].' OR idc2='.$clan[0].') AND (idc1='.$clan[1].' OR idc2='.$clan[1].')';
					$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
					if(mysql_num_rows($rsl)<1){
						$qry = 'INSERT INTO '.KML_PREFIX.'_match(league, ids, idg, rnd, idc1, idc2) VALUES('.LEAGUE.', '.IDS.', '.(int)$dts['idg'].', '.($ky+1).', '.$clan[0].', '.$clan[1].')';
						if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) print($alang['added'].': '.$teamNames[$clan[0]].' vs '.$teamNames[$clan[1]].'<br/>');
						//dodanie map
						$mid = mysql_insert_id();
						if($mqry2) query(__FILE__,__FUNCTION__,__LINE__,$mqry1.str_replace('#idm', $mid, $mqry2),0);
					}else print($alang['already_played_max'].': '.$teamNames[$clan[0]].' vs '.$teamNames[$clan[1]].'<br/>');
				}
			}
		}
		print('</div>');
	}
}

#group rounds description

Function rounds_description($dts){
	global $alang;
	$qry = 'SELECT gname, idg, gphase FROM '.KML_PREFIX.'_group WHERE league='.LEAGUE.' AND ids='.IDS.' GROUP BY idg ORDER BY gphase DESC, gname ASC';
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	while($row = mysql_fetch_array($rsl)){
		foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
		$groups[$row['idg']] = $alang['gphase'].': '.$row['gphase'].' | '.ucfirst($alang['group']).': '.$row['gname'];
	}
	if($dts['idgd']){
		$qry = 'SELECT idg, rnd, descr FROM '.KML_PREFIX.'_dround WHERE league='.LEAGUE.' AND idgd='.(int)$dts['idgd'].' AND ids='.IDS;
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		if(mysql_num_rows($rsl)!=1) die($alang['sure']);
		$row = mysql_fetch_array($rsl);
		foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
		$inf = ' - '.$alang['round'].' : '.$row['rnd'].' - '.$row['descr'];
	}
	if($inf) option_head($alang['rdesc'].$inf); else option_head($alang['rdesc']);
	if(count($groups)==0){
		print($alang['groups_req']);
		return 0;
	}
	println('<table cellspacing="10"><tr><td valign="top">
	<form method="post" action="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=dround"><input type="hidden" name="idgd" value="'.$dts['idgd'].'">');
	switch($dts['opt']){
		case $alang['edit']:{
			println('<table>
			<tr><td class="hd">'.ucfirst($alang['group']).':</td><td><select name="idg">'.array_assoc($groups,$row['idg']).'</select></td></tr>
			<tr><td class="hd">'.$alang['round'].':</td><td><input type="text" name="rnd" maxlength="2" size="5" value="'.$row['rnd'].'"></td></tr>
			<tr><td class="hd">'.$alang['descr'].':</td><td><input type="text" name="descr" maxlength="30" value="'.$row['descr'].'"></td></tr>
			<tr><td colspan="2" align="right"><input type="submit" name="opt" value="'.$alang['delete'].'"> <input type="reset" value="'.$alang['reset'].'"> <input type="submit" name="opt" value="'.$alang['save'].'"></td></tr>
			</table>');
			break;
		}
		case $alang['delete']:{
			action_info('x', $alang['rdesc']);
			break;
		}
		case $alang['yes']:{
			$qry = 'DELETE FROM '.KML_PREFIX.'_dround WHERE league='.LEAGUE.' AND idgd="'.(int)$dts['idgd'].'" AND ids='.IDS;
			if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) action_info('r', $alang['rdesc']);
			break;
		}
		case $alang['add']:{
			if($dts['all']){
				$sqry = 'SELECT gphase FROM '.KML_PREFIX.'_group WHERE league='.LEAGUE.' AND idg='.(int)$dts['idg'];
				$gphase = mysql_result(query(__FILE__,__FUNCTION__,__LINE__,$sqry,0),0);
				$qry = SQL('INSERT INTO '.KML_PREFIX.'_dround(league, ids, idg, rnd, descr) SELECT "'.LEAGUE.'", "'.IDS.'", idg, %d, %s FROM '.KML_PREFIX.'_group WHERE league='.LEAGUE.' AND gphase='.$gphase.' AND ids='.IDS, $dts['rnd'], $dts['descr']);
				if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) action_info('a', $alang['rdesc'].$alang['rdesc_add_full']);
			}else{
				$qry = SQL('INSERT INTO '.KML_PREFIX.'_dround(league, ids, idg, rnd, descr) VALUES('.LEAGUE.', '.IDS.', %d, %d, %s)', $dts['idg'], $dts['rnd'], $dts['descr']);
				if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) action_info('a', $alang['rdesc']);
			}
			break;
		}
		case $alang['save']:{
			$qry = SQL('UPDATE '.KML_PREFIX.'_dround SET rnd=%d, idg=%d, descr=%s WHERE league='.LEAGUE.' AND idgd=%d', $dts['rnd'], $dts['idg'], $dts['descr'], $dts['idgd']);
			if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) action_info('s', $alang['rdesc']);
			break;
		}
		default:{
			println('<table>
			<tr><td class="hd">'.ucfirst($alang['group']).':</td><td><select name="idg">'.array_assoc($groups,'').'</select></td></tr>
			<tr><td class="hd">'.$alang['round'].':</td><td><input type="text" name="rnd" maxlength="2" size="5"></td></tr>
			<tr><td class="hd">'.$alang['descr'].':</td><td><input type="text" name="descr" maxlength="30"></td></tr>
			<tr><td class="hd" colspan="2">'.$alang['dround_all'].': <input type="checkbox" name="all"></td></tr>
			<tr><td colspan="2" align="right"><input type="submit" name="opt" value="'.$alang['add'].'"></td></tr>
			</table>');
		}
	}
	println('</form>
	</td><td valign="top">
	<form method="post" action="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=dround"><select name="idgd" size="10">');
	$qry = 'SELECT idgd, idg, rnd, descr FROM '.KML_PREFIX.'_dround WHERE league='.LEAGUE.' AND ids='.IDS.' ORDER BY idg DESC, rnd DESC';
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	while($row=mysql_fetch_array($rsl)){
		foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
		print('<option value="'.$row['idgd'].'"');
		if($row['idgd']==$dts['idgd']) print(' selected');
		println('>'.$groups[$row['idg']].' | '.$alang['round'].': '.$row['rnd'].' | '.$row['descr']);
	}
	println('</select><br/>
	<input type="submit" name="opt" value="'.$alang['delete'].'"> <input type="submit" name="opt" value="'.$alang['edit'].'">
	</form>
	</td></tr></table>');
}

?>
