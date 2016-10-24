<?php

#PENALTIES

Function penalties($dts){
	global $alang;
	if($dts['idy']>0){
		if(LEAGUE_TYPE=='D') $qry = 'SELECT p.frags, p.points AS ppnts, p.idt, t.points AS tpnts, t.deaths, c.login AS tag FROM '.KML_PREFIX.'_penalty AS p, '.KML_PREFIX.'_table AS t, '.KML_PREFIX.'_users AS c WHERE p.league='.LEAGUE.' AND t.idt=p.idt AND t.idc=c.iduser AND p.idy='.(int)$dts['idy'];
		else $qry = 'SELECT p.frags, p.points AS ppnts, p.idt, t.points AS tpnts, t.deaths, c.tag FROM '.KML_PREFIX.'_penalty AS p, '.KML_PREFIX.'_table AS t, '.KML_PREFIX.'_clan AS c WHERE p.league='.LEAGUE.' AND t.idt=p.idt AND t.idc=c.idc AND p.idy='.(int)$dts['idy'];
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		if(mysql_num_rows($rsl)!=1) die($alang['sure']);
		$row = mysql_fetch_array($rsl);
		foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
		$inf = $alang['penalty'].' '.$alang['for'].' '.$row['tag'];
	}
	if($inf) option_head($inf); else option_head($alang['new_penalty']);
	println('<table cellspacing="10"><tr><td valign="top">
	<form action="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=penalty" method="post"><input type="hidden" name="idy" value="'.$dts['idy'].'">');
	if(LEAGUE_TYPE=='D') $sqry = 'SELECT t.idt, c.login AS tag, g.gname FROM '.KML_PREFIX.'_table AS t, '.KML_PREFIX.'_users AS c, '.KML_PREFIX.'_group AS g WHERE t.league='.LEAGUE.' AND c.iduser=t.idc AND g.idg=t.idg AND g.ids='.IDS.' ORDER BY tag, g.gname';
	else $sqry = 'SELECT t.idt, c.tag, g.gname FROM '.KML_PREFIX.'_table AS t, '.KML_PREFIX.'_clan AS c, '.KML_PREFIX.'_group AS g WHERE t.league='.LEAGUE.' AND c.idc=t.idc AND g.idg=t.idg AND g.ids='.IDS.' ORDER BY c.tag, g.gname';
	$srslt = query(__FILE__,__FUNCTION__,__LINE__,$sqry,0);
	switch($dts['opt']){
		case $alang['delete']:{
			action_info('x', $alang['penalty']);
			break;
		}
		case $alang['yes']:{
			$uqry = 'UPDATE '.KML_PREFIX.'_table SET points="'.($row['tpnts']+$row['ppnts']).'", deaths="'.($row['deaths']-$row['frags']).'" WHERE league='.LEAGUE.' AND idt='.$row['idt'];
			if(query(__FILE__,__FUNCTION__,__LINE__,$uqry,0)) action_info('s', $alang['table']);
			$dqry = 'DELETE FROM '.KML_PREFIX.'_penalty WHERE league='.LEAGUE.' AND idy='.(int)$dts['idy'];
			if(query(__FILE__,__FUNCTION__,__LINE__,$dqry,0)) action_info('r', $alang['penalty']);
			break;
		}
		case $alang['edit']:{
			println('<table>
			<tr><td class="hd">'.$alang['clan'].' | '.ucfirst($alang['group']).':</td><td><select name="idt">');
			while($srow = mysql_fetch_array($srslt)){
				foreach($srow as $ky=>$vl) $srow[$ky] = intoBrowser($vl);
				print('<option value="'.$srow['idt'].'"');
				if($srow['idt']==$row['idt']) print(' selected');
				println('>'.$srow['tag'].' | '.$srow['gname']);
			}
			println('</select></td></tr>
			<tr><td class="hd">'.$alang['penalty'].' '.strtolower($alang['frags']).':</td><td><input type=text name="frags" maxlength="5" value="'.$row['frags'].'"></td></tr>
			<tr><td class="hd">'.$alang['penalty'].' '.strtolower($alang['points']).':</td><td><input type=text name="points" maxlength="3" value="'.$row['ppnts'].'"></td></tr>
			<tr><td colspan="2" align="right"><input type="submit" name="opt" value="'.$alang['delete'].'"> <input type="submit" name="opt" value="'.$alang['save'].'"></td></tr>
			</table>');
			break;
		}
		case $alang['save']:{
			$uqry = 'UPDATE '.KML_PREFIX.'_table SET points="'.($row['tpnts']+$row['ppnts']).'", deaths="'.($row['deaths']-$row['frags']).'" WHERE league='.LEAGUE.' AND idt='.$row['idt'];
			query(__FILE__,__FUNCTION__,__LINE__,$uqry,0);
			#ponowne pobranie po updjeciu
			if(LEAGUE_TYPE=='D') $qry = 'SELECT p.frags, p.points AS ppnts, p.idt, t.points AS tpnts, t.deaths, c.login AS tag FROM '.KML_PREFIX.'_penalty AS p, '.KML_PREFIX.'_table AS t, '.KML_PREFIX.'_users AS c WHERE p.league='.LEAGUE.' AND t.idt=p.idt AND t.idc=c.iduser AND p.idy='.(int)$dts['idy'];
			else $qry = 'SELECT p.frags, p.points AS ppnts, p.idt, t.points AS tpnts, t.deaths, c.tag FROM '.KML_PREFIX.'_penalty AS p, '.KML_PREFIX.'_table AS t, '.KML_PREFIX.'_clan AS c WHERE p.league='.LEAGUE.' AND t.idt=p.idt AND t.idc=c.idc AND p.idy='.(int)$dts['idy'];
			$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
			$row = mysql_fetch_array($rsl);
			foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
			$uqry = 'UPDATE '.KML_PREFIX.'_table SET points="'.($row['tpnts']-$dts['points']).'", deaths="'.($row['deaths']+$dts['frags']).'" WHERE league='.LEAGUE.' AND idt='.$row['idt'];
			if(query(__FILE__,__FUNCTION__,__LINE__,$uqry,0)) action_info('s', $alang['table']);
			$uqry = 'UPDATE '.KML_PREFIX.'_penalty SET idt="'.$dts['idt'].'", points="'.$dts['points'].'", frags="'.$dts['frags'].'" WHERE league='.LEAGUE.' AND idy='.$dts['idy'];
			if(query(__FILE__,__FUNCTION__,__LINE__,$uqry,0)) action_info('s', $alang['penalty']);
			break;
		}
		case $alang['add']:{
			if(LEAGUE_TYPE=='D') $qry = 'SELECT t.idt, t.idc, c.login AS tag, t.idg, t.points, t.deaths FROM '.KML_PREFIX.'_table AS t, '.KML_PREFIX.'_users AS c WHERE t.league='.LEAGUE.' AND c.iduser=t.idc AND t.idt='.$dts['idt'];
			else $qry = 'SELECT t.idt, c.idc, c.tag, t.idg, t.points, t.deaths FROM '.KML_PREFIX.'_table AS t, '.KML_PREFIX.'_clan AS c WHERE t.league='.LEAGUE.' AND c.idc=t.idc AND t.idt='.$dts['idt'];
			$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
			if(mysql_num_rows($rsl)!=1) die($alang['penalty_err']);
			$row = mysql_fetch_array($rsl);
			foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
			$pqry = 'INSERT INTO '.KML_PREFIX.'_penalty(league, idt, frags, points) VALUES('.LEAGUE.', "'.$dts['idt'].'", "'.$dts['frags'].'", "'.$dts['points'].'")';
			if(query(__FILE__,__FUNCTION__,__LINE__,$pqry,0)) action_info('a', $alang['penalty'].' '.$alang['for'].' '.$row['tag']);
			$uqry = 'UPDATE '.KML_PREFIX.'_table SET points="'.($row['points']-$dts['points']).'", deaths="'.($row['deaths']+$dts['frags']).'" WHERE league='.LEAGUE.' AND idt='.$dts['idt'];
			if(query(__FILE__,__FUNCTION__,__LINE__,$uqry,0)) action_info('s', $alang['table']);
			break;
		}
		default:{
			println('<table>
			<tr><td class="hd">'.$alang['clan'].' | '.ucfirst($alang['group']).':</td><td><select name="idt">');
			while($row = mysql_fetch_assoc($srslt)){
				foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
				println('<option value="'.$row['idt'].'">'.$row['tag'].' | '.$row['gname'].'</option>');
			}
			println('</select></td></tr>
			<tr><td class="hd">'.$alang['penalty'].' '.strtolower($alang['frags']).':</td><td><input type=text name="frags" maxlength="5"></td></tr>
			<tr><td class="hd">'.$alang['penalty'].' '.strtolower($alang['points']).':</td><td><input type=text name="points" maxlength="3"></td></tr>
			<tr><td colspan="2" align="right"><input type="submit" name="opt" value="'.$alang['add'].'"></td></tr>
			</table>');
		}
	}
	println('</form>
	</td><td valign=top>
	<div class="head">'.$alang['penalties'].'</div>');
	if(LEAGUE_TYPE=='D') $qry = 'SELECT c.login AS tag, g.gname, p.idy FROM '.KML_PREFIX.'_penalty AS p, '.KML_PREFIX.'_group AS g, '.KML_PREFIX.'_users AS c, '.KML_PREFIX.'_table AS t WHERE p.league='.LEAGUE.' AND p.idt=t.idt AND t.idc=c.iduser AND t.idg=g.idg AND g.ids='.IDS.' ORDER BY idy DESC';
	else $qry = 'SELECT c.tag, g.gname, p.idy FROM '.KML_PREFIX.'_penalty AS p, '.KML_PREFIX.'_group AS g, '.KML_PREFIX.'_clan AS c, '.KML_PREFIX.'_table AS t WHERE p.league='.LEAGUE.' AND p.idt=t.idt AND t.idc=c.idc AND t.idg=g.idg AND g.ids='.IDS.' ORDER BY idy DESC';
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	println('<form method="post" action="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=penalty"><select size="10" name="idy">');
	while($row = mysql_fetch_array($rsl)){
		foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
		print('<option value="'.$row['idy'].'"');
		if($row['idy']==$dts['idy']) print(' selected');
		println('>'.$row['tag'].' | '.$row['gname']);
	}
	println('</select>
	<input type="submit" name="opt" value="'.$alang['delete'].'"> <input type="submit" name="opt" value="'.$alang['edit'].'">
	</form>
	</td></tr></table>');
}

Function penalty_standards($dts){
	global $alang;
	if($dts['idps']>0){
		$qry = 'SELECT level, frags, points FROM '.KML_PREFIX.'_pen_stand WHERE league='.LEAGUE.' AND idps='.$dts['idps'];
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		if(mysql_num_rows($rsl)!=1) die($alang['sure']);
		$row = mysql_fetch_array($rsl);
		$inf = ' - '.$alang['level'].' : '.$row['level'];
	}
	if($inf) option_head($alang['pensta'].$inf); else option_head($alang['pensta']);
	println('<table cellspacing="10"><tr><td valign="top">
	<form method="post" action="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=pensta"><input type="hidden" name="idps" value="'.$dts['idps'].'">');
	switch($dts['opt']){
		case $alang['edit']:{
			println('<table>
			<tr><td class="hd">'.$alang['level'].':</td><td><input type="text" name="level" maxlength="2" value="'.$row['level'].'"></td></tr>
			<tr><td class="hd">'.$alang['penalty'].' '.strtolower($alang['frags']).':</td><td><input type="text" name="frags" maxlength="5" value="'.$row['frags'].'"></td></tr>
			<tr><td class="hd">'.$alang['penalty'].' '.strtolower($alang['points']).':</td><td><input type="text" name="points" maxlength="3" value="'.$row['points'].'"></td></tr>
			<tr><td colspan="2" align="right"><input type="submit" name="opt" value="'.$alang['delete'].'"> <input type="reset" value="'.$alang['reset'].'"> <input type="submit" name="opt" value="'.$alang['save'].'"></td></tr>
			</table>');
			break;
		}
		case $alang['delete']:{
			action_info('x', $alang['penstax']);
			break;
		}
		case $alang['yes']:{
			$qry = 'DELETE FROM '.KML_PREFIX.'_pen_stand WHERE league='.LEAGUE.' AND idps='.$dts['idps'];
			if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) action_info('r', $alang['penstax']);
			break;
		}
		case $alang['add']:{
			$qry = 'SELECT idps FROM '.KML_PREFIX.'_pen_stand WHERE league='.LEAGUE.' AND level="'.$dts['level'].'"';
			$rsl  = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
			if(mysql_num_rows($rsl)>0) die($alang['pen_exist']);
			$qry = 'INSERT INTO '.KML_PREFIX.'_pen_stand(league, level, frags, points) VALUES('.LEAGUE.', "'.$dts['level'].'", "'.$dts['frags'].'", "'.$dts['points'].'")';
			if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) action_info('a', $alang['penstax']);
			break;
		}
		case $alang['save']:{
			if($row['level']!=$dts['level']){
				$qry = 'SELECT idps FROM '.KML_PREFIX.'_pen_stand WHERE league='.LEAGUE.' AND level="'.$dts['level'].'"';
				$rsl  = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
				if(mysql_num_rows($rsl)>0) die($alang['pen_exist']);
			}
			$qry = 'UPDATE '.KML_PREFIX.'_pen_stand SET level="'.$dts['level'].'", frags="'.$dts['frags'].'", points="'.$dts['points'].'" WHERE league='.LEAGUE.' AND idps='.$dts['idps'];
			if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) action_info('s', $alang['penstax']);
			break;
		}
		default:{
			println('<table>
			<tr><td class="hd">'.$alang['level'].':</td><td><input type="text" name="level" maxlength="2"></td></tr>
			<tr><td class="hd">'.$alang['penalty'].' '.strtolower($alang['frags']).':</td><td><input type="text" name="frags" maxlength="5"></td></tr>
			<tr><td class="hd">'.$alang['penalty'].' '.strtolower($alang['points']).':</td><td><input type="text" name="points" maxlength="3"></td></tr>
			<tr><td colspan="2" align="right"><input type="submit" name="opt" value="'.$alang['add'].'"></td></tr>
			</table>');
		}
	}
	println('</form>
	</td><td valign="top">
	<form method="post" action="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=pensta"><select name="idps" size="10">');
	$qry = 'SELECT idps, level, points, frags FROM '.KML_PREFIX.'_pen_stand WHERE league='.LEAGUE.' ORDER BY level ASC';
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	while($row=mysql_fetch_array($rsl)){
		print('<option value="'.$row['idps'].'"');
		if($row['idps']==$dts['idps']) print(' selected');
		println('>'.$alang['level'].': '.$row['level'].' | '.$alang['points'].': '.$row['points'].' | '.$alang['frags'].' :'.$row['frags']);
	}
	println('</select><br/>
	<input type="submit" name="opt" value="'.$alang['delete'].'"> <input type="submit" name="opt" value="'.$alang['edit'].'">
	</form>
	</td></tr></table>');
}

Function add_penalty($idt,$idc,$lvl){
	global $alang;
	$qry = 'SELECT walkover FROM '.KML_PREFIX.'_config WHERE league='.LEAGUE;
	$walkover = mysql_result(query(__FILE__,__FUNCTION__,__LINE__,$qry,0),0);
	if(LEAGUE_TYPE=='D') $qry = 'SELECT login AS tag FROM '.KML_PREFIX.'_users WHERE iduser='.$idc;
	else $qry = 'SELECT tag FROM '.KML_PREFIX.'_clan WHERE idc='.$idc;
	$name = mysql_result(query(__FILE__,__FUNCTION__,__LINE__,$qry,0), 0, 'tag');
	$qry = 'SELECT level, points, frags FROM '.KML_PREFIX.'_pen_stand WHERE league='.LEAGUE;
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	while($row=mysql_fetch_assoc($rsl)) $penalties[$row['level']] = array($row['frags'],$row['points']);
	if($walkover=='M'){
		#pobrac poziom ostatniego walkowera i utworzyc odpowiednie zapytanie
		$level = 0;
		$qry = 'SELECT s.level FROM '.KML_PREFIX.'_penalty AS p, '.KML_PREFIX.'_pen_stand AS s WHERE s.league='.LEAGUE.' AND p.frags=s.frags AND p.points=s.points AND p.idt='.$idt.' ORDER BY p.idy DESC LIMIT 0,1';
		$level = @mysql_result(query(__FILE__,__FUNCTION__,__LINE__,$qry,0),0);
		$lvl = $level+1;
	}
	$qry = 'INSERT INTO '.KML_PREFIX.'_penalty(league, idt, frags, points) VALUES('.LEAGUE.', "'.$idt.'", "'.$penalties[$lvl][0].'", "'.$penalties[$lvl][1].'")';
	if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) action_info('a', $alang['penalty'].' '.$name.', '.$alang['penalty'].' '.$alang['level'].': '.$lvl.' ('.$alang['frags'].': '.$penalties[$lvl][0].' | '.$alang['points'].': '.$penalties[$lvl][1].')');
	$qry = 'SELECT points, deaths FROM '.KML_PREFIX.'_table WHERE league='.LEAGUE.' AND idt='.$idt;
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	$row = mysql_fetch_assoc($rsl);
	$qry = 'UPDATE '.KML_PREFIX.'_table SET points="'.($row['points'] - $penalties[$lvl][1]).'", deaths="'.($row['deaths']+$penalties[$lvl][0]).'" WHERE league='.LEAGUE.' AND idt='.$idt;
	if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) action_info('s', $alang['table']); 
}

Function del_penalty($idt,$lvl){
	global $alang;
	$qry = 'SELECT walkover FROM '.KML_PREFIX.'_config WHERE league='.LEAGUE;
	$walkover = mysql_result(query(__FILE__,__FUNCTION__,__LINE__,$qry,0),0);
	$qry = 'SELECT level, points, frags FROM '.KML_PREFIX.'_pen_stand WHERE league='.LEAGUE;
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	while($row=mysql_fetch_assoc($rsl)) $penalties[$row['level']] = array($row['frags'],$row['points']);
	if($walkover=='M'){
		#pobrac poziom ostatniego walkowera i utworzyc odpowiednie zapytanie
		$level = 0;
		$qry = 'SELECT s.level, p.idy FROM '.KML_PREFIX.'_penalty AS p, '.KML_PREFIX.'_pen_stand AS s WHERE s.league='.LEAGUE.' AND p.frags=s.frags AND p.points=s.points AND p.idt='.$idt.' ORDER BY p.idy DESC LIMIT 0,1';
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		$row = mysql_fetch_assoc($rsl);
		$lvl = $row['level'];
		$idy = $row['idy'];
	}else{
		$qry = 'SELECT idy FROM '.KML_PREFIX.'_penalty WHERE league='.LEAGUE.' AND idt="'.$idt.'" AND points="'.$penalties[$lvl][1].'" AND frags="'.$penalties[$lvl][0].'" LIMIT 0,1';
		$idy = @mysql_result(query(__FILE__,__FUNCTION__,__LINE__,$qry,0),'idy');
	}
	$qry = 'SELECT points, deaths FROM '.KML_PREFIX.'_table WHERE league='.LEAGUE.' AND idt='.$idt;
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	$row = mysql_fetch_assoc($rsl);
	$qry = 'UPDATE '.KML_PREFIX.'_table SET points="'.($row['points'] + $penalties[$lvl][1]).'", deaths="'.($row['deaths']-$penalties[$lvl][0]).'" WHERE league='.LEAGUE.' AND idt='.$idt;
	if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) action_info('s', $alang['table']);
	if($idy){
		$qry = 'DELETE FROM '.KML_PREFIX.'_penalty WHERE league='.LEAGUE.' AND idy='.$idy;
		if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) action_info('r', $alang['penalty']);
	}
}

?>
