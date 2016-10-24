<?php

# MATCHES

Function match($idg,$dts,$login,$get){
	global $alang;
	if($get['idm']){
		$dts['idm'] = $get['idm'];
		if($get['opx']==1) $dts['opt'] = $alang['scrs_edit'];
		if($get['opx']==2) $dts['opt'] = $alang['dscr_edit'];
	}
	if($get['opx']==3){
		$idg = 'P';
		$dts['rnd'] = $get['idr'];
		$dts['pos'] = $get['idp'];
		$dts['idpt'] = $get['idpt'];
	}
	if($dts['idm'] || $dts['id']){
		if(!$dts['idm']) $dts['idm'] = $dts['id'];
		$dts['idm'] = intval($dts['idm']);
		if(LEAGUE_TYPE=='D') $mqry = 'SELECT c1.login AS cnm1, c2.login AS cnm2, m.idm, m.server, m.idg, m.pos, m.rnd, m.date, m.judge, m.type, m.idpt, m.descr, m.points1, m.points2, m.idc1, m.idc2 FROM '.KML_PREFIX.'_match AS m, '.KML_PREFIX.'_users AS c1, '.KML_PREFIX.'_users AS c2 WHERE m.league='.LEAGUE.' AND m.idc1=c1.iduser AND m.idc2=c2.iduser AND m.idm='.$dts['idm'];
		else $mqry = 'SELECT c1.tag AS cnm1, c2.tag AS cnm2, m.idm, m.server, m.idg, m.pos, m.rnd, m.date, m.judge, m.type, m.idpt, m.descr, m.points1, m.points2, m.idc1, m.idc2 FROM '.KML_PREFIX.'_match AS m, '.KML_PREFIX.'_clan AS c1, '.KML_PREFIX.'_clan AS c2 WHERE m.league='.LEAGUE.' AND m.idc1=c1.idc AND m.idc2=c2.idc AND m.idm='.$dts['idm'];
		$mrsl = query(__FILE__,__FUNCTION__,__LINE__,$mqry,0);
		if(mysql_num_rows($mrsl)<1) die($alang['match_err2']);
		$mrow = mysql_fetch_array($mrsl);
		foreach($mrow as $ky=>$vl) $mrow[$ky] = intoBrowser($vl);
		if($mrow['idg']==NULL && $mrow['pos']==NULL && $mrow['rnd']==NULL) $mrow['idg'] = 'N'; elseif($mrow['idg']==NULL) $mrow['idg'] = 'P';
		$inf = $mrow['cnm1'].' vs '.$mrow['cnm2'];
	}
	if(!$inf) option_head(ucfirst(strtolower($alang['match_new']))); else option_head($alang['match_edit'].': '.$inf);
	if($mrow['idg']) $idg = $mrow['idg'];
	if(!$idg) $idg = 'P';
	println('<form method="post" action="admin.php?'.KML_LINK_SL.'op=match" enctype="multipart/form-data" name="formula">');
	if($dts['idm'] || $dts['id']){
		println('<input type="hidden" name="idg" value="'.$mrow['idg'].'"><input type="hidden" name="idm" value="'.$dts['idm'].'">');
	}
	println('<table cellspacing="10" align="left"><tr><td valign="top">');
	print('<input type="hidden" name="idg" value="'.$idg.'">');

	switch($dts['opt']){
		case $alang['add']: check_match($idg,$dts); break;
		case $alang['approve']: add_match($dts); break;
		case $alang['delete']: action_info('x',$alang['match']); break;
		case $alang['dscr_edit']: descr_edit($mrow); break;
		case $alang['scrs_edit']: scrs_edit($mrow); break;
		case $alang['yes']: delete_match($dts); break;
		case $alang['save']: save_match($dts); break;
		case $alang['rem']:{
			if(count($dts['scr'])>0){
				foreach($dts['scr'] AS $screen){
					$qry = 'SELECT map, hash, name FROM '.KML_PREFIX.'_screen WHERE league='.LEAGUE.' AND idn='.$screen;
					$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
					$row = mysql_fetch_array($rsl);
					foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
					$name = $row['map'].$row['hash'].$row['name'].'.jpg';
					if(!unlink('screen/'.$name)) die($alang['scr_remerr']);
					$qry = 'DELETE FROM '.KML_PREFIX.'_screen WHERE league='.LEAGUE.' AND idn='.$screen;
					query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
				}
				action_info('r', $alang['screen']);
			}else println($alang['scr_remerr2']);
			break;
		}
		default: if($idg) new_match($idg,$dts,$login);
	}
	println('</form>
	</td><td valign="top">');
	if($dts['opt']==$alang['dscr_edit'] || !$dts['opt']) ubbcodes_flags();
	print('</td></tr></table>');
}

# MATCHES - SUBFUNCTIONS

Function matches_list($dts){
	global $alang;
	option_head($alang['match_list']);
	$judges[0] = '';
	$clans[0] = '';
	$qry = 'SELECT u.login, u.iduser FROM '.KML_PREFIX.'_users AS u, '.KML_PREFIX.'_grants AS g WHERE g.iduser=u.iduser AND g.service='.MAIN_ID.' ORDER BY u.login';
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	while($row=mysql_fetch_array($rsl)){
		foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
		$judges[$row['iduser']] = $row['login'];
	}
	if(LEAGUE_TYPE=='D') $qry = 'SELECT m.idc1 AS idc1, m.idc2 AS idc2, c1.login AS cnm1, c2.login AS cnm2 FROM '.KML_PREFIX.'_users AS c1, '.KML_PREFIX.'_users AS c2, '.KML_PREFIX.'_match AS m WHERE m.league='.LEAGUE.' AND m.idc1=c1.iduser AND m.idc2=c2.iduser AND m.ids='.IDS;
	else $qry = 'SELECT c1.idc AS idc1, c2.idc AS idc2, c1.tag AS cnm1, c2.tag AS cnm2 FROM '.KML_PREFIX.'_clan AS c1, '.KML_PREFIX.'_clan AS c2, '.KML_PREFIX.'_match AS m WHERE m.league='.LEAGUE.' AND m.idc1=c1.idc AND m.idc2=c2.idc AND m.ids='.IDS;
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	if(mysql_num_rows($rsl)>0){
		while($row=mysql_fetch_assoc($rsl)){
			foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
			$clans[$row['idc1']] = $row['cnm1'];
			$clans[$row['idc2']] = $row['cnm2'];
		}
		asort($clans);
		println('<form action="admin.php" name="formula">'.KML_LINK_SLF.'<input type="hidden" name="op" value="ematch">
		<table>
		<tr><td class="hd">ID:</td><td><input type="text" name="idm" value="'.$dts['idm'].'" size="3"></td><td class="hd">'.$alang['clan'].'1:</td><td><select name="idc1">'.array_assoc($clans,$dts['idc1']).'</select></td><td class="hd">'.$alang['clan'].'2:</td><td><select name="idc2">'.array_assoc($clans,$dts['idc2']).'</select></td><td class="hd">'.$alang['referee'].':</td><td><select name="idj">'.array_assoc($judges,$dts['idj']).'</select></td><td><input type="submit" value="'.$alang['search'].'"></td></tr>
		</table>
		</form>');
		$qry = 'SELECT u.login, m.idm, m.date, m.idc1, m.win1, m.win2, m.draw, m.idc2 FROM '.KML_PREFIX.'_match AS m LEFT JOIN '.KML_PREFIX.'_users AS u ON u.iduser=m.judge WHERE m.league='.LEAGUE.' AND m.ids='.IDS;
		if($dts['idc1']) $qry .= ' AND (m.idc1='.(int)$dts['idc1'].' OR m.idc2='.(int)$dts['idc1'].')';
		if($dts['idc2']) $qry .= ' AND (m.idc1='.(int)$dts['idc2'].' OR m.idc2='.(int)$dts['idc2'].')';
		if($dts['idm']) $qry .= ' AND m.idm='.(int)$dts['idm'];
		if($dts['idj']) $qry .= ' AND m.judge='.(int)$dts['idj'];
		$qry .= ' ORDER BY m.date DESC, m.idm ASC';
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		if(mysql_num_rows($rsl)>0){
			print('<table cellspacing="0" cellpadding="5" align="center">
			<tr><td class="hd">ID</td><td class="hd">'.$alang['date'].'</td><td class="hd">'.ucfirst(strtolower($alang['clans'])).'</td><td class="hd">'.$alang['score'].'</td><td class="hd">'.$alang['referee'].'</td><td class="hd">'.ucfirst(strtolower($alang['options'])).'</td></tr>');
			while($row = mysql_fetch_array($rsl)){
				foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
				if($row['date']>0) $date = show_date($row['date'], 2); else $date = '&mdash;&mdash;&mdash;';
				if($row['login']) $judge = $row['login']; else $judge = '&mdash;&mdash;&mdash;';
				println('<form method="post" action="admin.php?'.KML_LINK_SL.'op=match"><input type="hidden" name="idm" value="'.$row['idm'].'">
				<tr class="list'.(++$i%2).'"><td>'.$row['idm'].'</td><td>'.$date.'</td><td>'.$clans[$row['idc1']].' vs '.$clans[$row['idc2']].'</td><td>'.$row['win1'].':'.$row['draw'].':'.$row['win2'].'</td><td>'.$judge.'</td><td><input type="submit" class="list" name="opt" value="'.$alang['dscr_edit'].'"> <input type="submit" class="list" name="opt" value="'.$alang['scrs_edit'].'"> <input type="submit" class="list" name="opt" value="'.$alang['delete'].'"></td></tr>
				</form>');
			}
			print('</table>');
		}
	}else print($alang['no_matches']);
}

Function new_match($idg,$dts,$login){
	global $alang;
	if($idg == "P"){
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
	#daty
	$years[] = '';
	$months[] = '';
	$days[] = '';
	$hours[] = '';
	$minutes[] = '';
	for($i=2002;$i<(date('Y')+2);$i++) $years[] = $i;
	for($i=1;$i<13;$i++){
	    if(strlen($i)!=2) $j = '0'.$i; else $j = $i;
		$months[] = $j;
	}
	for($i=1;$i<32;$i++){
	    if(strlen($i)!=2) $j = '0'.$i; else $j = $i;
		$days[] = $j;
	}
	for($i=0;$i<24;$i++){
	    if(strlen($i)!=2) $j = '0'.$i; else $j = $i;
		$hours[] = $j;
	}
	for($i=0;$i<56;$i++){
		if($i%5==0){
	      if(strlen($i)!=2) $j = '0'.$i; else $j = $i;
	      $minutes[] = $j;
		}
	}
	if(!$dts['back']){
		if(!$dts['year']) $dts['year'] = date('Y');
		if(!$dts['month']) $dts['month'] = date('m');
		if(!$dts['day']) $dts['day'] = date('d');
		if(!$dts['hour']) $dts['hour'] = date('H');
		if(!$dts['minute']) $dts['minute'] = '00';
		if(!$dts['from_year']) $dts['from_year'] = date('Y');
		if(!$dts['from_month']) $dts['from_month'] = date('m');
		if(!$dts['from_day']) $dts['from_day'] = date('d');
		if(!$dts['to_year']) $dts['to_year'] = date('Y');
		if(!$dts['to_month']) $dts['to_month'] = date('m');
		if(!$dts['to_day']) $dts['to_day'] = date('d');
		if(!$dts['points2']) $dts['points2'] = '-';
		if(!$dts['points1']) $dts['points1'] = '-';
	}
#	if(!$dts['send_pm']) $dts['send_pm'] = 0;
#	if(!$dts['roster']) $dts['roster'] = 1;
	#klany
	if(LEAGUE_TYPE=='D'){
		if($idg != "P" && $idg != "N") $qry = 'SELECT c.iduser AS idc, c.login AS tag FROM '.KML_PREFIX.'_table AS t, '.KML_PREFIX.'_users AS c WHERE t.league='.LEAGUE.' AND c.iduser=t.idc AND t.idg='.(int)$idg.' GROUP BY t.idc ORDER BY tag';
		else $qry = 'SELECT u.login AS tag, x.idc FROM '.KML_PREFIX.'_users AS u, '.KML_PREFIX.'_in AS x WHERE x.league='.LEAGUE.' AND x.idc=u.iduser ORDER BY tag';
	}else{
		if($idg != "P" && $idg != "N") $qry = 'SELECT c.idc, c.tag FROM '.KML_PREFIX.'_table AS t, '.KML_PREFIX.'_clan AS c WHERE t.league='.LEAGUE.' AND c.idc=t.idc AND t.idg='.(int)$idg.' GROUP BY t.idc ORDER BY c.tag';
		else $qry = 'SELECT c.tag, c.idc FROM '.KML_PREFIX.'_clan AS c, '.KML_PREFIX.'_in AS x WHERE x.league='.LEAGUE.' AND c.idc=x.idc ORDER BY c.tag';
	}
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	if(mysql_num_rows($rsl)<2) die($alang['group_err']);
	while($row=mysql_fetch_array($rsl)){
		foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
		$clans[$row['idc']] = $row['tag'];
	}
	#sedziowie
	$judges[0] = '';
	$qry = 'SELECT u.login, u.iduser FROM '.KML_PREFIX.'_users AS u, '.KML_PREFIX.'_grants AS g WHERE g.iduser=u.iduser AND g.service='.MAIN_ID.' ORDER BY u.login';
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	while($row=mysql_fetch_array($rsl)){
		foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
		$judges[$row['iduser']] = $row['login'];
	}
	$map_list = getArray('SELECT sname FROM '.KML_PREFIX.'_maps WHERE league='.LEAGUE.' ORDER BY `sname`', 'N');
	$map_list = $map_list[0];
	$servers = getArray('SELECT `sname` FROM '.KML_PREFIX.'_servers WHERE league='.LEAGUE.' ORDER BY `sname`', 'N');
	$servers = $servers[0];
	if(count($servers)>0) $servers_list = 1;
	println('<div class="head">'.$alang['descr'].'</div>
	<table>
	<tr><td class="hd">'.$alang['referee'].':</td><td><select name="judge">'.array_assoc($judges,$dts['judge']).'</select></td></tr>
	<tr><td class="hd">'.$alang['date'].':</td><td><select name="year">'.array_norm($years,$dts['year']).'</select>-<select name="month">'.array_norm($months,$dts['month']).'</select>-<select name="day">'.array_norm($days,$dts['day']).'</select> <select name="hour">'.array_norm($hours,$dts['hour']).'</select>:<select name="minute">'.array_norm($minutes,$dts['minute']).'</select></td></tr>');
	/*<tr><td class="hd">'.$alang['date_from'].':</td><td><select name="from_year">'.array_norm($years,$dts['from_year']).'</select>-<select name="from_month">'.array_norm($months,$dts['from_month']).'</select>-<select name="from_day">'.array_norm($days,$dts['from_day']).'</select></td></tr>
	<tr><td class="hd">'.$alang['date_to'].':</td><td><select name="to_year">'.array_norm($years,$dts['to_year']).'</select>-<select name="to_month">'.array_norm($months,$dts['to_month']).'</select>-<select name="to_day">'.array_norm($days,$dts['to_day']).'</select></td></tr>*/
	print('<tr><td class="hd">'.$alang['server'].':</td><td>');
	if($servers_list==1) print('<select name="server"><option></option>'.array_norm($servers,$dts['server']).'</select>');
	else print('<input type="text" name="server" value="'.$dts['server'].'" size="25" maxlength="30">');
	println('</td></tr>');
	if($idg == "N") println('<tr><td class="hd">'.$alang['type'].':</td><td><input type="text" name="type" value="'.$dts['type'].'" size="25" maxlength="20"></td></tr>');
	if($idg != "N") println('<tr><td class="hd">'.$alang['round'].':</td><td><input type="text" name="rnd" value="'.$dts['rnd'].'" size="3" maxlength="2"></td></tr>');
	if($idg == "P") println('<tr><td class="hd">'.$alang['position'].':</td><td><input type="text" name="pos" value="'.$dts['pos'].'" size="5" maxlength="4"></td></tr>');
	if(isset($ptables)) println('<tr><td class="hd">'.$alang['poff_table'].':</td><td><select name="idpt">'.array_assoc($ptables,$dts['idpt']).'</select></td></tr>');
	$yesno = array('1'=>$alang['yes'], 0=>$alang['no']);
#	println('<tr><td class="hd">'.$alang['send_pm'].':</td><td>'.radio_list('send_pm',$yesno,$dts['send_pm'],'').'</td></tr>
#	<tr><td class="hd">'.$alang['team_roster'].':</td><td>'.radio_list('roster',$yesno,$dts['roster'],'').'</td></tr>
	println('<tr><td valign="top" class="bold">'.$alang['descr'].':</td><td><textarea name="descr" rows="10" cols="63" maxlength="65000">'.$dts['descr'].'</textarea></td></tr>	
	</table>
	<br/><div class="head">'.ucfirst(strtolower($alang['clans'])).'</div>
	<table align="center">
	<tr><td></td><td align="right" class="bold">'.$alang['clan'].' 1</td><td class="hd2">vs</td><td class="hd">'.$alang['clan'].' 2</td></tr>
	<tr><td class="hd">'.ucfirst(strtolower($alang['clans'])).':</td><td align="right"><select name="idc1">'.array_assoc($clans,$dts['idc1']).'</select></td><td align="center">vs</td><td><select name="idc2">'.array_assoc($clans,$dts['idc2']).'</select></td></tr>');
	if($idg != "N" && $idg != "P") println('<tr><td class="hd">'.$alang['points'].':</td><td align="right"><input type="text" name="points1" value="'.$dts['points1'].'" maxlength="2" size="5"></td><td align="center">:</td><td><input type="text" name="points2" value="'.$dts['points2'].'" maxlength="2" size="5"></td></tr>');
	println('</table><br/>
	<div class="head">'.$alang['score'].'</div>
	<div style="float: left; width: 30px;"><input type="button" class="addItem" value="+" onclick="javascript:matches(\'matches\', \'\', \'-\', \'-\', \'\', \'\', \'\');" /></div><div style="float: left; width: 56px;" class="hd2">'.$alang['map'].'</div><div style="float: left; width: 69px;" class="hd2">'.$alang['clan'].' 1<br/>'.$alang['frags'].'</div><div style="float: left; width: 69px;" class="hd2">'.$alang['clan'].' 2<br/>'.$alang['frags'].'</div><div style="float: left; width: 150px;" class="hd2">'.$alang['screens'].'</div><br class="clear"/>
	<div id="matches">');
	?>
		<script type="text/javascript">
		<!--
		var mms = 0;
		function matches(which, map, clfg1, clfg2, screen, idmm, details_in){
			var details = new Array(6);
			var lngth = details.length; 
			for (i=0;i<lngth;i++){
				if(details_in[i]) details[i] = details_in[i]; else details[i] = '-';
			}
			++mms;
			var stringas = '';
			var dir = 'tmp_screen';
			if(idmm) dir = 'screen';
			if(idmm) stringas += '<input type="hidden" name="maps['+mms+'][idmm]" value="'+idmm+'">';
			stringas += '<div style="float: left; width: 20px;" class="hd">'+mms+'</div><div style="float: left; width: 85px;">';

			<?php
				if(is_array($map_list)){
					print('stringas += \'<select style="width: 80px;" id="mlist\'+mms+\'" name="maps[\'+mms+\'][name]"><option></option>'.array_norm($map_list,'').'</select>\'');
				}else{
			?>

			stringas += '<input type="text" name="maps['+mms+'][name]" style="width: 80px;" maxlength="20" value="'+map+'">';

			<?php
				}
			?>

			stringas += '</div><div style="float: left; width: 69px;"><input type="text" name="maps['+mms+'][cl1_fg]" style="width: 63px;" maxlength="5" value="'+clfg1+'"></div><div style="float: left; width: 69px;"><input type="text" name="maps['+mms+'][cl2_fg]" style="width: 63px;" maxlength="5" value="'+clfg2+'"></div><div style="float: left;"><input size="15" name="maps['+mms+'][screen]" type="file">';
			if(screen) stringas += '<input type="hidden" name="scr_file['+mms+']" value="'+screen+'"> <a target="_blank" href="'+dir+'/'+screen+'"><img alt="screen" src="adm/file.gif"/></a>';
			stringas += '</div><br class="clear"/>';

			<?php
			//dodatkowe info
			if(SCORE_DETAILS=='Y'){
				for($i=0;$i<3;$i++){
					print("\n".'stringas += \'<div style="float: left; width: 105px;">'.$alang[($i+1).'_round'].'</div><div style="float: left; width: 69px;"><input type="text" name="maps[\'+mms+\'][cl1_fg_d'.($i+1).'st]" style="width: 63px;" maxlength="5" value="\'+details['.($i*2).']+\'"></div><div style="float: left; width: 69px;"><input type="text" name="maps[\'+mms+\'][cl2_fg_d'.($i+1).'st]" style="width: 63px;" maxlength="5" value="\'+details['.($i*2+1).']+\'"></div><br class="clear"/>\';'."\n");
				}
				print("\n".'stringas += \'<br class="clear"/>\';'."\n");
			}
			?>

			element = document.getElementById(which);
			nowyelement = document.createElement('div');
			idek = 'mms'+mms;
			nowyelement.id = idek;
			element.appendChild(nowyelement);
			nowyelement.innerHTML += stringas;

			<?php
		if($map_list){
			?>

			if(map!=""){
				element = document.getElementById('mlist'+mms);
				allelems = element.length;
				for(i=0;i<allelems;i++){
					if(element.options[i].text==map){
						element.options[i].selected = true;
					}
				}
			}

			<?php
		}
			?>

		}
		-->
		</script>
		<?php
	if($dts['maps']){
		foreach($dts['maps'] as $ky=>$vl){
			$screen = '';
			if($vl['name']){
				if($dts['scr_file'][$ky]) $screen = $dts['scr_file'][$ky];
				println('<script type="text/javascript">');
				$score_details = 'Array(';
				if(SCORE_DETAILS=='Y'){
					$el = 0;
					for($i=0;$i<3;$i++){
						if(++$el!=1) $score_details .= ', ';
						$score_details .= '\''.$vl['cl1_fg_d'.($i+1).'st'].'\', \''.$vl['cl2_fg_d'.($i+1).'st'].'\'';
					}
				}
				$score_details .= ')';
				println('matches(\'matches\', \''.$vl['name'].'\', \''.$vl['cl1_fg'].'\', \''.$vl['cl2_fg'].'\', \''.$screen.'\', \'\', '.$score_details.');
				</script>');
			}
		}
	}
	println('</div><br/>
	<input class="addItem" type="submit" name="opt" value="'.$alang['add'].'">');
}

Function check_match($idg,$dts){
	global $alang;
	if($idg == "P"){
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
	if($dts['idc1']==$dts['idc2']){
		$form_err = 1;
		println($alang['match_err1']);
	}
	if($dts['points1']>125 || $dts['points2']>125){
		$form_err = 1;
		println($alang['match_err3']);
	}
	$clan1w = 0;
	$clandw = 0;
	$clan2w = 0;
	$frags1 = 0;
	$frags2 = 0;
	$time = time();
	if(is_array($dts['maps'])){
		foreach($dts['maps'] as $ky=>$vl){
			if($vl['name']){
				//upload do tmp_screen
				if($_FILES['maps']['size'][$ky]['screen']>0){
					if($_FILES['maps']['type'][$ky]['screen'] != 'image/pjpeg' && $_FILES['maps']['type'][$ky]['screen'] != 'image/jpeg') die($alang['screen_err2']);
					##############################################
					### dodac zabezpieczenie przed wylosowaniem tego samego hasha
					$hash = substr(uniqid(rand(),1),0,3);
					$new_name = $hash.$time.'.jpg';
					move_uploaded_file($_FILES['maps']['tmp_name'][$ky]['screen'], "tmp_screen/$new_name");
					chmod("tmp_screen/$new_name", 0755);
					if(file_exists("tmp_screen/$new_name")){
						println('<input type="hidden" name="scr_file['.$ky.']" value="'.$new_name.'">');
					}
				}elseif($dts['scr_file'][$ky]){
					#w przypadku jesli screeny wczesniej zostaly wrzucone
					println('<input type="hidden" name="scr_file['.$ky.']" value="'.$dts['scr_file'][$ky].'">');
				}
				if($vl['name']) $map2[] = trim($vl['name']);
				if($vl['cl1_fg']!='-' && $vl['cl2_fg']!='-'){
					$frags1 += $vl['cl1_fg'];
					$frags2 += $vl['cl2_fg'];
					if(SCORE=='M'){
						if($vl['cl1_fg']>$vl['cl2_fg']) ++$clan1w; elseif($vl['cl1_fg']<$vl['cl2_fg']) ++$clan2w; else ++$clandw;
					}
				}
			}
		}
	}
	if(SCORE=='R'){
		$clan1w = $frags1;
		$clan2w = $frags2;
	}
	println('<table cellspacing="1" cellpadding="3">');
	if($dts['judge']){
		$qry = 'SELECT login FROM '.KML_PREFIX.'_users WHERE iduser='.(int)$dts['judge'];
		$result = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		$row = mysql_fetch_array($result);
		foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
		println('<tr><td class="hd">'.$alang['referee'].':</td><td>'.$row['login'].'</td></tr>');
	}
	if($dts['year'] && $dts['month'] && $dts['day']) println('<tr><td class="hd">'.$alang['date'].':</td><td>'.$dts['year'].'-'.$dts['month'].'-'.$dts['day'].' '.$dts['hour'].':'.$dts['minute'].'</td></tr>');
	if($dts['from_year'] && $dts['from_month'] && $dts['from_day']) println('<tr><td class="hd">'.$alang['date_from'].':</td><td>'.$dts['from_year'].'-'.$dts['from_month'].'-'.$dts['from_day'].'</td></tr>');
	if($dts['to_year'] && $dts['to_month'] && $dts['to_day']) println('<tr><td class="hd">'.$alang['date_to'].':</td><td>'.$dts['to_year'].'-'.$dts['to_month'].'-'.$dts['to_day'].'</td></tr>');

	println('<tr><td class="hd">'.$alang['server'].':</td><td>'.$dts['server'].'</td></tr>');
	if($idg == "N") println('<tr><td class="hd">'.$alang['type'].':</td><td>'.$dts['type'].'</td></tr>');
	if($idg != "N") println('<tr><td class="hd">'.$alang['round'].':</td><td>'.$dts['rnd'].'</td></tr>');
	if($idg == "P") println('<tr><td class="hd">'.$alang['position'].':</td><td>'.$dts['pos'].'</td></tr>');
	$yesno = array('1'=>$alang['yes'], 0=>$alang['no']);
#	println('<tr><td class="hd">'.$alang['send_pm'].':</td><td>'.$yesno[$dts['send_pm']].'</td></tr>
#	<tr><td class="hd">'.$alang['team_roster'].':</td><td>'.$yesno[$dts['roster']].'</td></tr>');
	if(isset($ptables) && $dts['idpt']) println('<tr><td class="hd">'.$alang['poff_table'].':</td><td>'.$ptables[$dts['idpt']].'</td></tr>');
	println('</table>');
	if(LEAGUE_TYPE=='D') $qry = 'SELECT login AS tag FROM '.KML_PREFIX.'_users WHERE iduser='.(int)$dts['idc1'];
	else $qry = 'SELECT tag FROM '.KML_PREFIX.'_clan WHERE idc='.(int)$dts['idc1'];
	$result = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	$row = mysql_fetch_array($result);
	foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
	if(LEAGUE_TYPE=='D') $cqry = 'SELECT login AS tag FROM '.KML_PREFIX.'_users WHERE iduser='.(int)$dts['idc2'];
	else $cqry = 'SELECT tag FROM '.KML_PREFIX.'_clan WHERE idc='.(int)$dts['idc2'];
	$crsl = query(__FILE__,__FUNCTION__,__LINE__,$cqry,0);
	$crow = mysql_fetch_array($crsl);
	foreach($crow as $ky=>$vl) $crow[$ky] = intoBrowser($vl);
	println('<br/><table cellspacing="1" cellpadding="3">
	<tr><td colspan="2" class="hd2">'.$row['tag'].' vs '.$crow['tag'].'</td></tr>');
	if($idg != "N" && $idg != "P"){
		if($dts['points2']=='-' && $dts['points1']=='-'){
			$pqry = 'SELECT points1, points2, score1, score2 FROM '.KML_PREFIX.'_points WHERE ((score1='.$clan1w.' AND draw='.$clandw.' AND score2='.$clan2w.') OR (score2='.$clan1w.' AND draw='.$clandw.' AND score1='.$clan2w.')) AND league='.LEAGUE;
			$prsl = query(__FILE__,__FUNCTION__,__LINE__,$pqry,0);
			if(mysql_num_rows($prsl)>0){
				$prow = mysql_fetch_assoc($prsl);
				if($prow['score1']==$clan1w && $prow['score2']==$clan2w){
					$dts['points1'] = $prow['points1'];
					$dts['points2'] = $prow['points2'];
				}else{
					$dts['points2'] = $prow['points1'];
					$dts['points1'] = $prow['points2'];
				}
			}
		}
		println('<tr><td class="hd">'.$alang['points'].':</td><td>'.$dts['points1'].' : '.$dts['points2'].'</td></tr>');
	}
	if(SCORE=='M'){
		println('<tr><td class="hd">'.$alang['score'].':</td><td>'.$clan1w.' : '.$clandw.' : '.$clan2w.'</td></tr>
		<tr><td class="hd">'.$alang['frags'].':</td><td>'.$frags1.' : '.$frags2.'</td></tr>');
	}elseif(SCORE=='R'){
		println('<tr><td class="hd">'.$alang['score'].':</td><td>'.$clan1w.' : '.$clan2w.'</td></tr>');
	}
	println('</table>
	<br/>
	<table cellspacing="1" cellpadding="5">');
	if(count($map2)>0){
		println('<tr><td class="hd">'.$alang['match'].'</td><td class="hd2">'.$alang['map'].'</td><td class="hd2">'.$row['tag'].' : '.$crow['tag'].'</td></tr>');
		foreach($dts['maps'] as $ky=>$vl){
			if($vl['name']){
				$wo = '';
				if($vl['cl1_fg']=='w') $wo = '(w/o '.$alang['for'].' '.$crow['tag'].')'; elseif($vl['cl2_fg']=='w') $wo = '(w/o '.$alang['for'].' '.$row['tag'].')';
				println('<tr><td class="hd">'.(++$i).'<td>'.$vl['name'].'<input type="hidden" name="maps['.$ky.'][name]" value="'.$vl['name'].'"></td><td align="center">'.$vl['cl1_fg'].' : '.$vl['cl2_fg'].' '.$wo.'<input type="hidden" name="maps['.$ky.'][cl1_fg]" value="'.$vl['cl1_fg'].'"><input type="hidden" name="maps['.$ky.'][cl2_fg]" value="'.$vl['cl2_fg'].'"><br/>');
				if(SCORE_DETAILS=='Y'){
					for($i=0;$i<3;$i++){
						print($alang[($i+1).'_round'].' -> '.$vl['cl1_fg_d'.($i+1).'st'].':'.$vl['cl2_fg_d'.($i+1).'st'].' <input type="hidden" name="maps['.$ky.'][cl1_fg_d'.($i+1).'st]" value="'.$vl['cl1_fg_d'.($i+1).'st'].'"> <input type="hidden" name="maps['.$ky.'][cl2_fg_d'.($i+1).'st]" value="'.$vl['cl2_fg_d'.($i+1).'st'].'"><br/>');
					}
				}
				println('</td></tr>');
			}
		}
	}
	println('</table>
	<table width="400"><tr><td>'.ubcode_add($dts['descr']).'</td></tr></table>
	<div align="right"><input type="submit" name="back" value="'.$alang['back'].'"> ');
	if($form_err != 1) print('<input type="submit" name="opt" value="'.$alang['approve'].'">');
	println('</div>');
	#dane do dodania meczu i powrotu do tworzenia meczu
	if($dts['year'] && $dts['month'] && $dts['day']) $matchTime = mktime($dts['hour'], $dts['minute'], 0, $dts['month'], $dts['day'], $dts['year']);
	if($dts['from_year'] && $dts['from_month'] && $dts['from_day']) $date_from = mktime(0, 0, 0, $dts['from_month'], $dts['from_day'], $dts['from_year']);
	if($dts['to_year'] && $dts['to_month'] && $dts['to_day']) $date_to = mktime(0, 0, 0, $dts['to_month'], $dts['to_day'], $dts['to_year']);
	println('<input type="hidden" name="judge" value="'.$dts['judge'].'"><input type="hidden" name="idpt" value="'.$dts['idpt'].'"><input type="hidden" name="idc1" value="'.$dts['idc1'].'"><input type="hidden" name="idc2" value="'.$dts['idc2'].'"><input type="hidden" name="date" value="'.(int)$matchTime.'"><input type="hidden" name="points1" value="'.$dts['points1'].'"><input type="hidden" name="points2" value="'.$dts['points2'].'"><input type="hidden" name="frags1" value="'.$frags1.'"><input type="hidden" name="frags2" value="'.$frags2.'"><input type="hidden" name="clan1w" value="'.$clan1w.'"><input type="hidden" name="clandw" value="'.$clandw.'"><input type="hidden" name="clan2w" value="'.$clan2w.'"><input type="hidden" name="descr" value="'.$dts['descr'].'"><input type="hidden" name="idg" value="'.$idg.'"><input type="hidden" name="type" value="'.$dts['type'].'"><input type="hidden" name="pos" value="'.$dts['pos'].'"><input type="hidden" name="rnd" value="'.$dts['rnd'].'">
	<input type="hidden" name="hour" value="'.$dts['hour'].'"><input type="hidden" name="minute" value="'.$dts['minute'].'"><input type="hidden" name="day" value="'.$dts['day'].'"><input type="hidden" name="month" value="'.$dts['month'].'"><input type="hidden" name="year" value="'.$dts['year'].'"><input type="hidden" name="server" value="'.$dts['server'].'"><input type="hidden" name="send_pm" value="'.$dts['send_pm'].'"><input type="hidden" name="from_day" value="'.$dts['from_day'].'"><input type="hidden" name="from_month" value="'.$dts['from_month'].'"><input type="hidden" name="from_year" value="'.$dts['from_year'].'"><input type="hidden" name="to_day" value="'.$dts['to_day'].'"><input type="hidden" name="to_month" value="'.$dts['to_month'].'"><input type="hidden" name="to_year" value="'.$dts['to_year'].'"><input type="hidden" name="roster" value="'.$dts['roster'].'"><input type="hidden" name="date_from" value="'.$date_from.'"><input type="hidden" name="date_to" value="'.$date_to.'">');
}

Function add_match($dts){
	global $alang;
	if($dts['idc1']==$dts['idc2']) die($alang['match_err1']);
	if($dts['points1']>125 || $dts['points2']>125) die($alang['match_err3']);
	if($dts['idg'] != "P" && $dts['idg'] != "N"){
		table_upd_add($dts);
# dodanie meczu w zaleznosci od typu meczu
# ids, judge, date, date_from, date_to, roster, idg, pos, rnd, idc1, idc2, type, idpt, points1, points2, frags1, frags2, win1, draw, win2, descr, server 
		$qry = SQL('INSERT INTO '.KML_PREFIX.'_match(league, ids, judge, date, date_from, date_to, roster, idg, pos, rnd, idc1, idc2, idpt, points1, points2, frags1, frags2, win1, draw, win2, descr, server) VALUES('.LEAGUE.', "'.IDS.'", %d, %d, %d, %s, %d, %d, NULL, %d, %d, %d, "0", %d, %d, %d, %d, %d, %d, %d, %m, %s)', $dts['judge'], $dts['date'], $dts['date_from'], $dts['date_to'], $dts['roster'], $dts['idg'], $dts['rnd'], $dts['idc1'], $dts['idc2'], $dts['points1'], $dts['points2'], $dts['frags1'], $dts['frags2'], $dts['clan1w'], $dts['clandw'], $dts['clan2w'], $dts['descr'], $dts['server']);
	}else{
		if($dts['idg'] == "N") $qry = SQL('INSERT INTO '.KML_PREFIX.'_match(league, ids, judge, date, date_from, date_to, roster, idc1, idc2, type, idpt, points1, points2, frags1, frags2, win1, draw, win2, descr, server) VALUES('.LEAGUE.', "'.IDS.'", %d, %d, %d, %d, %s, %d, %d, %s, "0", %d, %d, %d, %d, %d, %d, %d, %m, %s)', $dts['judge'], $dts['date'], $dts['date_from'], $dts['date_to'], $dts['roster'], $dts['idc1'], $dts['idc2'], $dts['type'], $dts['points1'], $dts['points2'], $dts['frags1'], $dts['frags2'], $dts['clan1w'], $dts['clandw'], $dts['clan2w'], $dts['descr'], $dts['server']);
		if($dts['idg'] == "P") $qry = SQL('INSERT INTO '.KML_PREFIX.'_match(league, ids, judge, date, date_from, date_to, roster, pos, rnd, idc1, idc2, idpt, points1, points2, frags1, frags2, win1, draw, win2, descr, server) VALUES('.LEAGUE.', "'.IDS.'", %d, %d, %d, %d, %s, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %m, %s)', $dts['judge'], $dts['date'], $dts['date_from'], $dts['date_to'], $dts['roster'], $dts['pos'], $dts['rnd'], $dts['idc1'], $dts['idc2'], $dts['idpt'], $dts['points1'], $dts['points2'], $dts['frags1'], $dts['frags2'], $dts['clan1w'], $dts['clandw'], $dts['clan2w'], $dts['descr'], $dts['server']);
	}
	if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) action_info('a',strtolower($alang['match']));
	$idm = mysql_insert_id();
	//sent PM to CL
	/*
	if($dts['send_pm']==1){
		$qry1 = 'SELECT cname FROM '.KML_PREFIX.'_clan WHERE idc='.$dts['idc1'];
		$rsl1 = query(__FILE__,__FUNCTION__,__LINE__,$qry1,0);
		$row1 = mysql_fetch_assoc($rsl1);
		$qry2 = 'SELECT cname FROM '.KML_PREFIX.'_clan WHERE idc='.$dts['idc2'];
		$rsl2 = query(__FILE__,__FUNCTION__,__LINE__,$qry2,0);
		$row2 = mysql_fetch_assoc($rsl2);
		$title = $alang['pm_new_match_title'];
		$content = str_replace('{{idc1n}}', $row1['cname'], $alang['pm_new_match_content']);
		$content = str_replace('{{idc2n}}', $row2['cname'], $content);
		$content = str_replace('{{idc1}}', $dts['idc1'], $content);
		$content = str_replace('{{idc2}}', $dts['idc2'], $content);
		$content = str_replace('{{idm}}', $idm, $content);
		$cls = get_all_cl($dts['idc1']) + get_all_cl($dts['idc2']);
		foreach($cls as $vl) send_pm($vl,$_SESSION['dl_login'],$title,$content,1);
		print(str_replace('{{amount}}', count($cls), $alang['pm_sent_msgs']));
	}*/
#dodanie map
	$maps = array();
	if(is_array($dts['maps'])){
		foreach($dts['maps'] as $ky=>$vl){
			if($vl['name']){
				$wo = 0;
				if($vl['cl1_fg']=='w') $wo = $dts['idc2']; elseif($vl['cl2_fg']=='w') $wo = $dts['idc1'];
				if($vl['cl1_fg']!='-' && $vl['cl2_fg']!='-') $ply = 1; else $ply = 0;
				$qry = SQL('INSERT INTO '.KML_PREFIX.'_match_map(idm, map, frags1, frags2, wo, played) VALUES('.$idm.', %s, %d, %d, %d, "'.$ply.'")', $vl['name'], $vl['cl1_fg'], $vl['cl2_fg'], $wo);
				if(!query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) die('error');
				$mid = mysql_insert_id();
				if($wo==0) $maps[$mid] = $vl['name'];
				if(SCORE_DETAILS=='Y'){
					$qry = 'DELETE FROM '.KML_PREFIX.'_match_score_details WHERE idmm='.(int)$mid;
					query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
					for($i=1;$i<4;$i++){
						if($vl['cl2_fg_d'.$i.'st']!='-' && $vl['cl1_fg_d'.$i.'st']!='-'){
							$qry = 'INSERT INTO '.KML_PREFIX.'_match_score_details(idmm, period, frags1, frags2) VALUES('.(int)$mid.', '.$i.', '.(int)$vl['cl1_fg_d'.$i.'st'].', '.(int)$vl['cl2_fg_d'.$i.'st'].')';
							query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
						}
					}
				}
	#dodanie screena z danej mapy jesli takowy jest na serwerze
				$screen = $dts['scr_file'][$ky];
				$old_name = 'tmp_screen/'.$screen;
				if($screen && file_exists($old_name)){
					$new_name = 'screen/'.$screen;
					if(!rename($old_name,$new_name)) die($alang['scr_moveerr']);
					$hash = substr($screen, 0, 3);
					$xtime = substr($screen, 3, -4);
					$qry = SQL('INSERT INTO '.KML_PREFIX.'_screen(league, idm, idmm, name, hash) VALUES('.LEAGUE.', '.$idm.', %s, %d, %s)', $mid, $xtime, $hash);
					if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) action_info('a', $alang['screen']);
				}
			}
		}
	}

#statystyki graczy jesli rodzaj T i jest jakas mapa
	if(count($maps)>0 && LEAGUE_TYPE=='T'){
		print('</form><form method="post" action="admin.php?'.KML_LINK_SL.'op=nstat"><input type="hidden" name="idm" value="'.$idm.'">
		<div class="head"><br/>'.$alang['pl_stat'].'</div><br/>');
		if(PLAYER_TYPE=='X'){
			$head = $alang['nick'];
			$qry = 'SELECT p.idp, p.pname FROM '.KML_PREFIX.'_player AS p, '.KML_PREFIX.'_player_in AS x WHERE x.idp=p.idp AND x.league='.LEAGUE.' ORDER BY p.pname ASC';
			$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
			while($row = mysql_fetch_array($rsl)){
				foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
				$players[] = array($row['idp'],$row['pname']);
			}
		}elseif(PLAYER_TYPE=='T'){
			$head = $alang['clan'].' | '.$alang['nick'];
			$qry = 'SELECT p.idp, c.tag, p.pname FROM '.KML_PREFIX.'_player AS p, '.KML_PREFIX.'_clan AS c, '.KML_PREFIX.'_player_in AS x WHERE x.idp=p.idp AND x.league='.LEAGUE.' AND (p.idc='.(int)$dts['idc1'].' OR p.idc='.(int)$dts['idc2'].') AND p.idc=c.idc ORDER BY c.tag ASC, p.pname ASC';
			$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
			while($row = mysql_fetch_array($rsl)){
				foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
				$players[] = array($row['idp'],$row['tag'].' | '.$row['pname']);
			}
		}

		?>
		<script type="text/javascript">
		<!--
		var pps = 0;
		function players_stats(which,mapid,idps,player,frags,deaths){
			++pps;
			element = document.getElementById(which);
			nowyelement = document.createElement('div');
			idek = 'pps'+pps;
			nowyelement.id = idek;
			element.appendChild(nowyelement);
			nowyelement.innerHTML += '<div style="float: left; padding: 2px; width: 200px;"><input type="hidden" name="pstats['+pps+'][mapid]" value="'+mapid+'"><input type="hidden" name="pstats['+pps+'][idps]" value="'+idps+'"><select id="list'+idek+'" style="width: 190px;" name="pstats['+pps+'][idp]"><option value="0"></option><?php
			if(is_array($players)){
				foreach($players AS $ply){
					print('<option value="'.$ply[0].'">'.$ply[1].'</option>');
				}
			}
			?></select></div><div style="float: left; padding: 2px; width: 50px;"><input type="text" name="pstats['+pps+'][frags]" maxlength="5" size="5" value="'+frags+'"></div><div style="float: left; padding: 2px; width: 50px;"><input type="text" name="pstats['+pps+'][deaths]" maxlength="5" size="5" value="'+deaths+'"></div><br class="clear"/>';
			if(player!=0){
				element = document.getElementById('list'+idek);
				allelems = element.length;
				for(i=0;i<allelems;i++){
					if(element.options[i].value==player){
						element.options[i].selected = true;
					}
				}
			}
		}
		-->
		</script>
		<?php
		foreach($maps as $ky=>$vl){
			$idek = 'mid'.$ky;
			print('<div class="blockHead" style="width: 400px;" onclick="flip(\''.$idek.'\');">'.$alang['map'].': '.$vl.'</div>
			<div style="display: block;" id="'.$idek.'">
			<div style="float: left; width: 200px;" class="hd"><input type="button" class="button" value="+" onclick="javascript:players_stats(\''.$idek.'\', \''.$ky.'\', \'\', \'\', \'\', \'\');" /> '.$head.'</div><div style="float: left; width: 50px;" class="hd">'.$alang['frags'].'</div><div style="float: left;" class="hd">'.$alang['deaths'].'</div><br class="clear"/>');
			print('</div>');
		}
		println('<tr><td align="right" colspan="3"><input type="submit" name="opt" value="'.$alang['add'].'"></td></tr>
		</table>');
	}
	print('</form>');
}

Function new_ply_stats($dts){
	global $alang;
	option_head($alang['pl_stat']);
	foreach($dts['pstats'] as $ky=>$vl){
		if($vl['idp']){
			$qry = 'INSERT INTO '.KML_PREFIX.'_players_stats(league, ids, idp, idm, idmm, frags, deaths) VALUES('.LEAGUE.', '.IDS.', '.(int)$vl['idp'].', '.(int)$dts['idm'].', '.(int)$vl['mapid'].', '.(int)$vl['frags'].', '.(int)$vl['deaths'].')';
			if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) ++$k;
		}
	}
	action_info('a', $alang['pl_stat'].' ('.$k.')');
}

Function descr_edit($mrow){
	global $alang;
	if($mrow['idg'] == "P"){
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
	#data
	$years[] = '';
	$months[] = '';
	$days[] = '';
	$hours[] = '';
	$minutes[] = '';
	for($i=2002;$i<(date('Y')+2);$i++) $years[] = $i;
	for($i=1;$i<13;$i++){
	    if(strlen($i)!=2) $j = '0'.$i; else $j = $i;
		$months[] = $j;
	}
	for($i=1;$i<32;$i++){
	    if(strlen($i)!=2) $j = '0'.$i; else $j = $i;
		$days[] = $j;
	}
	for($i=0;$i<24;$i++){
	    if(strlen($i)!=2) $j = '0'.$i; else $j = $i;
		$hours[] = $j;
	}
	for($i=0;$i<56;$i++){
		if($i%5==0){
	      if(strlen($i)!=2) $j = '0'.$i; else $j = $i;
	      $minutes[] = $j;
		}
	}
	if($mrow['date']>0){
		$dts['year'] = date('Y',$mrow['date']);
		$dts['month'] = date('m',$mrow['date']);
		$dts['day'] = date('d',$mrow['date']);
		$dts['hour'] = date('H',$mrow['date']);
		$dts['minute'] = date('i',$mrow['date']);
	}
	$servers = getArray('SELECT `sname` FROM '.KML_PREFIX.'_servers WHERE league='.LEAGUE.' ORDER BY `sname`', 'N');
	$servers = $servers[0];
	if(count($servers)>0) $servers_list = 1;
	if($mrow['server'] && $servers_list==1){
		if(!in_array($mrow['server'],$servers)) $servers[] = $mrow['server'];
	}
	$judges[0] = '';
	$qry = 'SELECT u.login, u.iduser FROM '.KML_PREFIX.'_users AS u, '.KML_PREFIX.'_grants AS g WHERE g.iduser=u.iduser AND g.service='.MAIN_ID.' ORDER BY u.login';
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	while($row=mysql_fetch_array($rsl)){
		foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
		$judges[$row['iduser']] = $row['login'];
	}
	println('<div class="head">'.$alang['descr'].'</div><br/>
	<table>
	<tr><td class="hd">'.$alang['referee'].':</td><td><select name="judge">'.array_assoc($judges,$mrow['judge']).'</select></td></tr>
	<tr><td class="hd">'.$alang['date'].':</td><td><select name="year">'.array_norm($years,$dts['year']).'</select>-<select name="month">'.array_norm($months,$dts['month']).'</select>-<select name="day">'.array_norm($days,$dts['day']).'</select> <select name="hour">'.array_norm($hours,$dts['hour']).'</select>:<select name="minute">'.array_norm($minutes,$dts['minute']).'</select></td></tr>');
	print('<tr><td class="hd">'.$alang['server'].':</td><td>');
	if($servers_list==1) print('<select name="server"><option></option>'.array_norm($servers,$mrow['server']).'</select>');
	else print('<input type="text" name="server" value="'.$mrow['server'].'" size="25" maxlength="30">');
	println('</td></tr>');
	if($mrow['type']==NULL) println('<tr><td class="hd">'.$alang['round'].':</td><td><input type="text" name="rnd" value="'.$mrow['rnd'].'" size="5" maxlength="2"></td></tr>');
	if($mrow['idg']=='N') println('<tr><td class="hd">'.$alang['type'].':</td><td><input type="text" name="type" value="'.$mrow['type'].'" size="20" maxlength="20"></td></tr>');
	if($mrow['idg']=='P') println('<tr><td class="hd">'.$alang['position'].':</td><td><input type="text" name="pos" value="'.$mrow['pos'].'" size="5" maxlength="4"></td></tr>');
	if(isset($ptables)) println('<tr><td class="hd">'.$alang['poff_table'].':</td><td><select name="idpt">'.array_assoc($ptables,$mrow['idpt']).'</select></td></tr>');
	println('<tr><td valign="top" class="hd">'.$alang['descr'].':</td><td><textarea name="descr" rows="10" cols="63" maxlength="65000">'.ubcode_rem($mrow['descr']).'</textarea><input type="hidden" name="triger" value="O"></td></tr>
	</table>');
	$sqry = 'SELECT idn, idmm, map, name, hash FROM '.KML_PREFIX.'_screen WHERE league='.LEAGUE.' AND idm='.$mrow['idm'];
	$srslt = query(__FILE__,__FUNCTION__,__LINE__,$sqry,0);
	if(mysql_num_rows($srslt)>0){
		print('<div class="blockHead" onclick="flip(\'screens\');">'.$alang['screens'].'</div>
		<div class="block" id="screens">
		<table cellspacing="3">');
		while($srow = mysql_fetch_array($srslt)){
			foreach($srow as $ky=>$vl) $srow[$ky] = intoBrowser($vl);
			$sname = $srow['map'].$srow['hash'].$srow['name'];
			println('<tr><td><input type="checkbox" name="scr[]" value="'.$srow['idn'].'"> <a href="screen/'.$sname.'.jpg" target="_blank">'.$srow['map'].'</a></td><td class="blockElement" onClick=dodaj(\'[SCREEN="'.$sname.'"]\')><span class="bold">&nbsp; &nbsp; '.strtolower($alang['add']).' &nbsp; &nbsp;</span></td></tr>');
		}
		println('<tr><td colspan="2" align="right"><input type="submit" name="opt" value="'.$alang['rem'].'"></td></tr>
		</table>
		</div>');
	}
	print('<table>
	<tr><td width="450">');

	if($mrow['descr']){
		print('<div class="blockHead" onclick="flip(\'dscr_view\');">'.$alang['dscr_view'].'</div>
		<div class="block" id="dscr_view">'.$mrow['descr']);
		if(mysql_num_rows($srslt)>0 && !eregi('src="screen/',$mrow['descr'].'<br/>')){
			mysql_data_seek($srslt,0);
			while($srow = mysql_fetch_array($srslt)){
				foreach($srow as $ky=>$vl) $srow[$ky] = intoBrowser($vl);
				println('<br/><div align="center"><table cellspacing=2 cellpadding=0 bgcolor=black><tr><td><img border=0 src="screen/'.$srow['map'].$srow['hash'].$srow['name'].'.jpg"></td></tr></table></div>');
			}
		}
		println('</div><br/>');
	}
	print('</td></tr>
	</table>');
	$maps = array();
	$pqry = 'SELECT idmm, map, wo FROM '.KML_PREFIX.'_match_map WHERE idm='.$mrow['idm'];
	$prsl = query(__FILE__,__FUNCTION__,__LINE__,$pqry,0);
	while($prow=mysql_fetch_assoc($prsl)) if($prow['wo']==0){
		foreach($prow as $ky=>$vl) $prow[$ky] = intoBrowser($vl);
		$maps[$prow['idmm']] = $prow['map'];
	}
#statystyki graczy jesli sa jakies mapy i try druzynowy
	if(count($maps)>0 && LEAGUE_TYPE=='T'){
		if(PLAYER_TYPE=='X'){
			$head = $alang['nick'];
			$qry = 'SELECT p.idp, p.pname FROM '.KML_PREFIX.'_player AS p, '.KML_PREFIX.'_player_in AS x WHERE x.idp=p.idp AND x.league='.LEAGUE.' ORDER BY p.pname ASC';
			$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
			while($row = mysql_fetch_array($rsl)){
				foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
				$players[] = array($row['idp'],$row['pname']);
			}
		}elseif(PLAYER_TYPE=='T'){
			$head = $alang['clan'].' | '.$alang['nick'];
			$qry = 'SELECT p.idp, c.tag, p.pname FROM '.KML_PREFIX.'_player AS p, '.KML_PREFIX.'_clan AS c, '.KML_PREFIX.'_player_in AS x WHERE x.idp=p.idp AND x.league='.LEAGUE.' AND (p.idc='.(int)$mrow['idc1'].' OR p.idc='.(int)$mrow['idc2'].') AND p.idc=c.idc ORDER BY c.tag ASC, p.pname ASC';
			$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
			while($row = mysql_fetch_array($rsl)){
				foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
				$players[] = array($row['idp'],$row['tag'].' | '.$row['pname']);
			}
		}
		if($players){
			println('<div class="head">'.$alang['pl_stat'].'</div><br/>');
			?>
			<script type="text/javascript">
			<!--
			var pps = 0;
			function players_stats(which,mapid,idps,player,frags,deaths){
				++pps;
				element = document.getElementById(which);
				nowyelement = document.createElement('div');
				idek = 'pps'+pps;
				nowyelement.id = idek;
				element.appendChild(nowyelement);
				nowyelement.innerHTML += '<div style="float: left; padding: 2px; width: 200px;"><input type="hidden" name="pstats['+pps+'][mapid]" value="'+mapid+'"><input type="hidden" name="pstats['+pps+'][idps]" value="'+idps+'"><select id="list'+idek+'" style="width: 190px;" name="pstats['+pps+'][idp]"><option value="0"></option><?php
				foreach($players AS $ply){
					print('<option value="'.$ply[0].'">'.$ply[1].'</option>');
				}
				?></select></div><div style="float: left; padding: 2px; width: 50px;"><input type="text" name="pstats['+pps+'][frags]" maxlength="5" size="5" value="'+frags+'"></div><div style="float: left; padding: 2px; width: 50px;"><input type="text" name="pstats['+pps+'][deaths]" maxlength="5" size="5" value="'+deaths+'"></div><br class="clear"/>';
				if(player!=0){
					element = document.getElementById('list'+idek);
					allelems = element.length;
					for(i=0;i<allelems;i++){
						if(element.options[i].value==player){
							element.options[i].selected = true;
						}
					}
				}
			}
			-->
			</script>
			<?php
			$qry = 'SELECT idps, idmm, idp, frags, deaths FROM '.KML_PREFIX.'_players_stats WHERE league='.LEAGUE.' AND idm='.$mrow['idm'];
			$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
			if(mysql_num_rows($rsl)>0){
				while($row=mysql_fetch_assoc($rsl)){
					$stats[$row['idmm']][] = array('idp'=>$row['idp'], 'idps'=>$row['idps'], 'frags'=>$row['frags'], 'deaths'=>$row['deaths']);
				}
			}
			foreach($maps as $ky=>$vl){
				$idek = 'mid'.$ky;
				print('<div class="blockHead" style="width: 400px;" onclick="flip(\''.$idek.'\');">'.$alang['map'].': '.$vl.'</div>
				<div style="display: block;" id="'.$idek.'">
				<div style="float: left; width: 200px;" class="hd"><input type="button" class="button" value="+" onclick="javascript:players_stats(\''.$idek.'\', \''.$ky.'\', \'\', \'\', \'\', \'\');" /> '.$head.'</div><div style="float: left; width: 50px;" class="hd">'.$alang['frags'].'</div><div style="float: left;" class="hd">'.$alang['deaths'].'</div><br class="clear"/>');
				if(count($stats[$ky])>0){
					foreach($stats[$ky] as $sky=>$svl){
						println('<script type="text/javascript">
						<!--
						players_stats(\''.$idek.'\', \''.$ky.'\',\''.$svl['idps'].'\',\''.$svl['idp'].'\',\''.$svl['frags'].'\',\''.$svl['deaths'].'\');
						-->
						</script>');
					}
				}
				print('</div>');
			}
		}else print($alang['no_players']);
	}
	println('<div align="right"><input type="submit" name="opt" value="'.$alang['save'].'"> <input type="submit" name="opt" value="'.$alang['delete'].'"></div>');
}

Function scrs_edit($mrow){
	global $alang;
	#klany
	if(LEAGUE_TYPE=='D'){
		if($mrow['idg']>0) $qry = 'SELECT t.idc, c.login AS tag FROM '.KML_PREFIX.'_table AS t, '.KML_PREFIX.'_users AS c WHERE t.league='.LEAGUE.' AND c.iduser=t.idc AND t.idg='.$mrow['idg'].' GROUP BY t.idc ORDER BY tag';
		else $qry = 'SELECT u.login AS tag, x.idc FROM '.KML_PREFIX.'_users AS u, '.KML_PREFIX.'_in AS x WHERE x.league='.LEAGUE.' AND x.idc=u.iduser ORDER BY tag';
	}else{
		if($mrow['idg']>0) $qry = 'SELECT c.idc, c.tag FROM '.KML_PREFIX.'_table AS t, '.KML_PREFIX.'_clan AS c WHERE t.league='.LEAGUE.' AND c.idc=t.idc AND t.idg='.$mrow['idg'].' GROUP BY t.idc ORDER BY c.tag';
		else $qry = 'SELECT c.tag, c.idc FROM '.KML_PREFIX.'_clan AS c, '.KML_PREFIX.'_in AS x WHERE x.league='.LEAGUE.' AND x.idc=c.idc ORDER BY tag';
	}
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	if(mysql_num_rows($rsl)<2) die($alang['group_err']);
	while($row=mysql_fetch_array($rsl)){
		foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
		$clans[$row['idc']] = $row['tag'];
	}
	$sqry = 'SELECT idmm, map, frags1, frags2, wo, played FROM '.KML_PREFIX.'_match_map WHERE idm='.$mrow['idm'];
	$srsl = query(__FILE__,__FUNCTION__,__LINE__,$sqry,0);
	if(mysql_num_rows($srsl)>0){
		while($srow=mysql_fetch_assoc($srsl)){
			foreach($srow as $ky=>$vl) $srow[$ky] = intoBrowser($vl);
			if($srow['played']!=1){
				$srow['frags1'] = '-';
				$srow['frags2'] = '-';
			}else $playedMap = 1;
			$addedMaps[] = array('idmm'=>$srow['idmm'], 'map'=>$srow['map'], 'frags1'=>$srow['frags1'], frags2=>$srow['frags2'], 'wo'=>$srow['wo'], 'played'=>$srow['played']);
		}
	}
	if($mrow['points1']==0 && $mrow['points2']==0 && $playedMap!=1){
		$mrow['points1'] = '-';
		$mrow['points2'] = '-';
	}
	println('<div class="head">'.ucfirst(strtolower($alang['clans'])).'</div>
	<div align="center">
	<table>
	<tr><td>&nbsp;</td><td class="hd2">'.$alang['clan'].' 1</td><td>&nbsp;</td><td class="hd2">'.$alang['clan'].' 2</td></tr>
	<tr><td class="hd">'.ucfirst(strtolower($alang['clans'])).':</td><td><select name="idc1">'.array_assoc($clans,$mrow['idc1']).'</select></td><td>vs</td><td><select name="idc2">'.array_assoc($clans,$mrow['idc2']).'</select></td></tr>');
	if($mrow['idg']>0) println('<tr><td class="hd">'.$alang['points'].':</td><td align="right"><input type="text" name="points1" value="'.$mrow['points1'].'" maxlength="2" size="5"></td><td align="center">:</td><td><input type="text" name="points2" value="'.$mrow['points2'].'" maxlength="2" size="5"></td></tr>');
	println('</table>
	</div>
	<div class="head">'.$alang['score'].'</div>
	<div style="float: left; width: 30px;"><input type="button" class="addItem" value="+" onclick="javascript:matches(\'matches\', \'\', \'-\', \'-\', \'\', \'\', \'\');" /></div><div style="float: left; width: 80px;" class="hd2">'.$alang['map'].'</div><div style="float: left; width: 69px;" class="hd2">'.$alang['clan'].' 1<br/>'.$alang['frags'].'</div><div style="float: left; width: 69px;" class="hd2">'.$alang['clan'].' 2<br/>'.$alang['frags'].'</div><div style="float: left; width: 160px;" class="hd2">'.$alang['screens'].'</div><br class="clear"/>');
	print('<div id="matches">');

	$xqry = 'SELECT idmm, map, name, hash FROM '.KML_PREFIX.'_screen WHERE idm='.$mrow['idm'];
	$xrsl = query(__FILE__,__FUNCTION__,__LINE__,$xqry,0);
	if(mysql_num_rows($xrsl)>0){
		while($xrow=mysql_fetch_assoc($xrsl)){
			foreach($xrow as $ky=>$vl) $xrow[$ky] = intoBrowser($vl);
			$screens[$xrow['idmm']] = $xrow['map'].$xrow['hash'].$xrow['name'].'.jpg';
		}
	}
	$map_list = getArray('SELECT sname FROM '.KML_PREFIX.'_maps WHERE league='.LEAGUE.' ORDER BY `sname`', 'N');
	$map_list = $map_list[0];
?>
		<script type="text/javascript">
		<!--
		var mms = 0;
		function matches(which, map, clfg1, clfg2, screen, idmm, details_in){
			var details = new Array(6);
			var lngth = details.length; 
			for (i=0;i<lngth;i++){
				if(details_in[i]) details[i] = details_in[i]; else details[i] = '-';
			}
			++mms;
			var stringas = '';
			var dir = 'tmp_screen';
			if(idmm) dir = 'screen';
			if(idmm) stringas += '<input type="hidden" name="maps['+mms+'][idmm]" value="'+idmm+'">';
			stringas += '<div style="float: left; width: 20px;" class="hd">'+mms+'</div><div style="float: left; width: 85px;">';

			<?php
				if(is_array($map_list)){
					print('stringas += \'<select style="width: 80px;" id="mlist\'+mms+\'" name="maps[\'+mms+\'][name]"><option></option>'.array_norm($map_list,'').'</select>\'');
				}else{
			?>

			stringas += '<input type="text" name="maps['+mms+'][name]" style="width: 80px;" maxlength="20" value="'+map+'">';

			<?php
				}
			?>

			stringas += '</div><div style="float: left; width: 69px;"><input type="text" name="maps['+mms+'][cl1_fg]" style="width: 63px;" maxlength="5" value="'+clfg1+'"></div><div style="float: left; width: 69px;"><input type="text" name="maps['+mms+'][cl2_fg]" style="width: 63px;" maxlength="5" value="'+clfg2+'"></div><div style="float: left;"><input size="15" name="maps['+mms+'][screen]" type="file">';
			if(screen) stringas += '<input type="hidden" name="scr_file['+mms+']" value="'+screen+'"> <a target="_blank" href="'+dir+'/'+screen+'"><img alt="screen" src="adm/file.gif"/></a>';
			stringas += '</div><br class="clear"/>';

			<?php
			//dodatkowe info
			if(SCORE_DETAILS=='Y'){
				for($i=0;$i<3;$i++){
					print("\n".'stringas += \'<div style="float: left; width: 105px;">'.$alang[($i+1).'_round'].'</div><div style="float: left; width: 69px;"><input type="text" name="maps[\'+mms+\'][cl1_fg_d'.($i+1).'st]" style="width: 63px;" maxlength="5" value="\'+details['.($i*2).']+\'"></div><div style="float: left; width: 69px;"><input type="text" name="maps[\'+mms+\'][cl2_fg_d'.($i+1).'st]" style="width: 63px;" maxlength="5" value="\'+details['.($i*2+1).']+\'"></div><br class="clear"/><br/>\';'."\n");
				}
			}
			?>

			element = document.getElementById(which);
			nowyelement = document.createElement('div');
			idek = 'mms'+mms;
			nowyelement.id = idek;
			element.appendChild(nowyelement);
			nowyelement.innerHTML += stringas;

			<?php
		if($map_list){
			?>

			if(map!=""){
				element = document.getElementById('mlist'+mms);
				allelems = element.length;
				for(i=0;i<allelems;i++){
					if(element.options[i].text==map){
						element.options[i].selected = true;
					}
				}
			}

			<?php
		}
			?>

		}
		-->
		</script>
		<?php
	if(is_array($addedMaps)>0){
		foreach($addedMaps as $amk=>$srow){
			if($srow['wo']==$mrow['idc1']){ $srow['frags1'] = 'w'; $srow['frags2'] = 'o';}elseif($srow['wo']==$mrow['idc2']){ $srow['frags1'] = 'o'; $srow['frags2'] = 'w';}
			println('<script type="text/javascript">');
			$score_details = 'Array(';
			if(SCORE_DETAILS=='Y'){
				$dqry = 'SELECT period, frags1, frags2 FROM '.KML_PREFIX.'_match_score_details WHERE idmm='.$srow['idmm'].' ORDER BY period';
				$drsl = query(__FILE__,__FUNCTION__,__LINE__,$dqry,0);
				if(mysql_num_rows($drsl)>0){
					$el = 0;
					#print('var details_score = new 
					while($drow=mysql_fetch_assoc($drsl)){
						if(++$el!=1) $score_details .= ', ';
						$score_details .= '\''.$drow['frags1'].'\', \''.$drow['frags2'].'\'';
					}
				}
			}
			$score_details .= ')';
			println('matches(\'matches\', \''.$srow['map'].'\', \''.$srow['frags1'].'\', \''.$srow['frags2'].'\', \''.$screens[$srow['idmm']].'\', \''.$srow['idmm'].'\', '.$score_details.');
			</script>');
		}
	}
	/*
	if(mysql_num_rows($srsl)>0){
		while($srow=mysql_fetch_assoc($srsl)){
			if($srow['played']!=1){
				$srow['frags1'] = '-';
				$srow['frags2'] = '-';
			}
			if($srow['wo']==$mrow['idc1']){ $srow['frags1'] = 'w'; $srow['frags2'] = 'o';}elseif($srow['wo']==$mrow['idc2']){ $srow['frags1'] = 'o'; $srow['frags2'] = 'w';}
			println('<script type="text/javascript">');
			if(SCORE_DETAILS=='Y'){
				$dqry = 'SELECT period, frags1, frags2 FROM '.KML_PREFIX.'_match_score_details WHERE idmm='.$srow['idmm'].' ORDER BY period';
				$drsl = query(__FILE__,__FUNCTION__,__LINE__,$dqry,0);
				$score_details = '\'\'';
				if(mysql_num_rows($drsl)>0){
					$el = 0;
					#print('var details_score = new 
					$score_details = 'Array(';
					while($drow=mysql_fetch_assoc($drsl)){
						if(++$el!=1) $score_details .= ', ';
						$score_details .= '\''.$drow['frags1'].'\', \''.$drow['frags2'].'\'';
					}
					$score_details .= ')';
				}
			}
			println('matches(\'matches\', \''.$srow['map'].'\', \''.$srow['frags1'].'\', \''.$srow['frags2'].'\', \''.$screens[$srow['idmm']].'\', \''.$srow['idmm'].'\', '.$score_details.');
			</script>');
		}
	}*/
	print('</div>
	<input type="hidden" name="triger" value="S"><input class="addItem" type="submit" name="opt" value="'.$alang['save'].'"> <input class="addItem" type="submit" name="opt" value="'.$alang['delete'].'">');
}

Function delete_match($dts){
	global $alang;
	#usuniecie screenow
	$qry = 'SELECT idn, name, map, hash FROM '.KML_PREFIX.'_screen WHERE league='.LEAGUE.' AND idm='.(int)$dts['idm'];
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	while($row=mysql_fetch_array($rsl)){
		foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
		$file = 'screen/'.$row['map'].$row['hash'].$row['name'].'.jpg';
		@unlink($file);
	}
	#usuniecie komentarzy do meczu
	$qry = 'DELETE FROM '.KML_PREFIX.'_comments WHERE type='.WARS_ID.' AND iditem='.(int)$dts['idm'];
	if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) println($alang['coms_deleted'].'<br/>');
	#usuniecie meczu
	$qry = 'DELETE FROM '.KML_PREFIX.'_screen WHERE league='.LEAGUE.' AND idm='.(int)$dts['idm'];
	if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) action_info('r', $alang['screen']);
	#pobranie danych o meczu
	$qry = 'SELECT idg, pos, rnd FROM '.KML_PREFIX.'_match WHERE league='.LEAGUE.' AND idm='.(int)$dts['idm'];
	$result = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	$row = mysql_fetch_array($result);
	if(!$row['idg'] && !$row['pos'] && !$row['rnd']){
		$dts['idg'] = 'N';
	}elseif(!$row['idg']){
		$dts['idg'] = 'P';
	}
	#usuniecie danych o meczu z tabel
	if($dts['idg'] != 'P' && $dts['idg'] != 'N') tab_upd_del($dts['idm']);
	#usuniecie meczu
	$qry = 'DELETE FROM '.KML_PREFIX.'_match WHERE league='.LEAGUE.' AND idm='.(int)$dts['idm'];
	query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	$dqry = 'SELECT idmm FROM '.KML_PREFIX.'_match_map WHERE idm='.(int)$dts['idm'];
	$drsl = query(__FILE__,__FUNCTION__,__LINE__,$dqry,0);
	while($drow=mysql_fetch_assoc($drsl)){
		$qry = 'DELETE FROM '.KML_PREFIX.'_match_score_details WHERE idmm='.$drow['idmm'];
		query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	}
	$qry = 'DELETE FROM '.KML_PREFIX.'_match_map WHERE idm='.(int)$dts['idm'];
	query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	action_info('r', $alang['match']);
}

Function save_match($dts){
	global $alang;
	if($dts['triger']=='O'){
		if($dts['year'] && $dts['month'] && $dts['day']) $matchTime = mktime($dts['hour'], $dts['minute'], 0, $dts['month'], $dts['day'], $dts['year']);
		if($dts['idg']=="N") $qry = SQL('UPDATE '.KML_PREFIX.'_match SET judge=%d, date=%d, type=%s, descr=%m, server=%s WHERE league='.LEAGUE.' AND idm=%d', $dts['judge'], (int)$matchTime, $dts['type'], $dts['descr'], $dts['server'], $dts['idm']);
		elseif($dts['idg']=="P") $qry = SQL('UPDATE '.KML_PREFIX.'_match SET judge=%d, date=%d, idpt=%d, pos=%d, rnd=%d, descr=%m, server=%s WHERE league='.LEAGUE.' AND idm=%d', $dts['judge'], (int)$matchTime, $dts['idpt'], $dts['pos'], $dts['rnd'], $dts['descr'], $dts['server'], $dts['idm']);
		else $qry = SQL('UPDATE '.KML_PREFIX.'_match SET judge=%d, rnd=%d, server=%s, date=%d, descr=%m WHERE league='.LEAGUE.' AND idm=%d', $dts['judge'], $dts['rnd'], $dts['server'], (int)$matchTime, $dts['descr'], $dts['idm']);
		if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) action_info('s',strtolower($alang['match']));
#statystyki graczy
		if(isset($dts['pstats'])){
			foreach($dts['pstats'] as $ky=>$vl){
				if($vl['idp'] || $vl['idps']){
					if($vl['idp']){
						if(!$vl['idps']) $qry = 'INSERT INTO '.KML_PREFIX.'_players_stats(league, ids, idp, idm, idmm, frags, deaths) VALUES('.LEAGUE.', '.IDS.', '.(int)$vl['idp'].', "'.(int)$dts['idm'].'", "'.(int)$vl['mapid'].'", "'.(int)$vl['frags'].'", "'.(int)$vl['deaths'].'")';
						else $qry = 'UPDATE '.KML_PREFIX.'_players_stats SET idp="'.(int)$vl['idp'].'", frags="'.(int)$vl['frags'].'", deaths="'.(int)$vl['deaths'].'" WHERE idps='.(int)$vl['idps'];
					}elseif(!$vl['idp'] && $vl['idps']) $qry = 'DELETE FROM '.KML_PREFIX.'_players_stats WHERE idps='.(int)$vl['idps'];
					if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0))++$k;
				}
			}
		}
		if($k>0) action_info('s', $alang['pl_stat']);
	}elseif($dts['triger']=='S'){
		if($dts['idc1']==$dts['idc2']) die($alang['match_err1']);
		if($dts['points1']>125 || $dts['points2']>125) die($alang['match_err3']);
		#usuniecie danych o meczu z tabel
		if($dts['idg'] != 'P' && $dts['idg'] != 'N') tab_upd_del($dts['idm']);
		$clan1w = 0;
		$clandw = 0;
		$clan2w = 0;
		$frags1 = 0;
		$frags2 = 0;
		$time = time();
#dodanie/aktualizacja wynikow i screenow
		if(is_array($dts['maps'])){
			foreach($dts['maps'] as $ky=>$vl){
				if($vl['name']){
					$wo = 0;
					if($vl['cl1_fg']=='w') $wo = $dts['idc2']; elseif($vl['cl2_fg']=='w') $wo = $dts['idc1'];
					if($vl['cl1_fg']!='-' && $vl['cl2_fg']!='-') $ply = 1; else $ply = 0;
					if($vl['idmm']){
						$qry = SQL('UPDATE '.KML_PREFIX.'_match_map SET map=%s, frags1=%d, frags2=%d, wo=%d, played="'.$ply.'" WHERE idmm=%d', $vl['name'], $vl['cl1_fg'], $vl['cl2_fg'], $wo, $vl['idmm']);
						$mid = $vl['idmm'];
					}else $qry = SQL('INSERT INTO '.KML_PREFIX.'_match_map(idm, map, frags1, frags2, wo, played) VALUES(%d, %s, %d, %d, %d, "'.$ply.'")', $dts['idm'], $vl['name'], $vl['cl1_fg'], $vl['cl2_fg'], $wo);
					if(!query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) die('error');
					if(!$vl['idmm']) $mid = mysql_insert_id();
					//dodanie rozszerzonych wynikow 
					if(SCORE_DETAILS=='Y'){
						$qry = 'DELETE FROM '.KML_PREFIX.'_match_score_details WHERE idmm='.(int)$mid;
						query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
						for($i=1;$i<4;$i++){
							if($vl['cl1_fg_d'.$i.'st']!='-' || $vl['cl2_fg_d'.$i.'st']!='-'){
								$qry = 'INSERT INTO '.KML_PREFIX.'_match_score_details(idmm, period, frags1, frags2) VALUES('.(int)$mid.', '.$i.', '.(int)$vl['cl1_fg_d'.$i.'st'].', '.(int)$vl['cl2_fg_d'.$i.'st'].')';
								query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
							}
						}
					}
					if($wo==0) $maps[$mid] = $vl['name'];
		#dodanie screena z danej mapy
					if($_FILES['maps']['size'][$ky]['screen']>0){
						if($_FILES['maps']['type'][$ky]['screen'] != 'image/pjpeg' && $_FILES['maps']['type'][$ky]['screen'] != 'image/jpeg') die($alang['screen_err2']);
						##############################################
						### dodac zabezpieczenie przed wylosowaniem tego samego hasha
						$hash = substr(uniqid(rand(),1),0,3);
						$new_name = $hash.$time.'.jpg';
						move_uploaded_file($_FILES['maps']['tmp_name'][$ky]['screen'], "screen/$new_name");
						chmod("screen/$new_name", 0755);
						if(file_exists("screen/$new_name")){
							$qry = 'INSERT INTO '.KML_PREFIX.'_screen(league, idm, idmm, name, hash) VALUES('.LEAGUE.', '.(int)$dts['idm'].', '.(int)$mid.', '.(int)$time.', "'.$hash.'")';
							if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) action_info('a', $alang['screen']);
						}
					}
					if($vl['name']) $map2[] = trim($vl['name']);
					if($vl['cl1_fg']!='-' && $vl['cl2_fg']!='-'){
						$frags1 += $vl['cl1_fg'];
						$frags2 += $vl['cl2_fg'];
						if(SCORE=='M'){
							if($vl['cl1_fg']>$vl['cl2_fg']) ++$clan1w; elseif($vl['cl1_fg']<$vl['cl2_fg']) ++$clan2w; else ++$clandw;
						}
					}
				}elseif(!$vl['name'] && $vl['idmm']){
					#usuniecie mapy i zwiazanych z nia innych rzeczy
					$qry = 'DELETE FROM '.KML_PREFIX.'_match_map WHERE idmm='.(int)$vl['idmm'];
					query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
					$qry = 'DELETE FROM '.KML_PREFIX.'_players_stats WHERE idmm='.(int)$vl['idmm'];
					query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
					##################################################
					#### zrobic usuwanie screenow i demek
				}
			}
		}
		if($dts['points2']=='-' && $dts['points1']=='-'){
			$pqry = 'SELECT points1, points2, score1, score2 FROM '.KML_PREFIX.'_points WHERE ((score1='.$clan1w.' AND draw='.$clandw.' AND score2='.$clan2w.') OR (score2='.$clan1w.' AND draw='.$clandw.' AND score1='.$clan2w.')) AND league='.LEAGUE;
			$prsl = query(__FILE__,__FUNCTION__,__LINE__,$pqry,0);
			if(mysql_num_rows($prsl)>0){
				$prow = mysql_fetch_assoc($prsl);
				if($prow['score1']==$clan1w && $prow['score2']==$clan2w){
					$dts['points1'] = $prow['points1'];
					$dts['points2'] = $prow['points2'];
				}else{
					$dts['points2'] = $prow['points1'];
					$dts['points1'] = $prow['points2'];
				}
			}
		}
		if(SCORE=='R'){
			$clan1w = $frags1;
			$clan2w = $frags2;
		}
		#update meczu
		$qry = 'UPDATE '.KML_PREFIX.'_match SET idc1='.(int)$dts['idc1'].', idc2='.(int)$dts['idc2'].', points1="'.(int)$dts['points1'].'", points2="'.(int)$dts['points2'].'", frags1="'.(int)$frags1.'", frags2="'.(int)$frags2.'", win1="'.(int)$clan1w.'", draw="'.(int)$clandw.'", win2="'.(int)$clan2w.'" WHERE league='.LEAGUE.' AND idm='.(int)$dts['idm'];
		if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) action_info('s', $alang['match']);
		$dts['clan1w'] = $clan1w;
		$dts['clan2w'] = $clan2w;
		$dts['clandw'] = $clandw;
		$dts['frags1'] = $frags1;
		$dts['frags2'] = $frags2;
		#update tabel
		if($dts['idg'] != 'P' && $dts['idg'] != 'N') table_upd_add($dts);
	}
}

# TABLES UPDATES

Function tab_upd_del($idm){
	global $alang;
#pobranie info o meczu
	$qry = 'SELECT win1, draw, win2, idc1, idc2, idg, points1, frags1, points2, frags2 FROM '.KML_PREFIX.'_match WHERE league='.LEAGUE.' AND idm='.(int)$idm;
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	$row = mysql_fetch_array($rsl);
#pobranie ID tabeli dla klanu
	$tqry = 'SELECT idt FROM '.KML_PREFIX.'_table WHERE league='.LEAGUE.' AND idc='.$row['idc1'].' AND idg='.$row['idg'];
	$idt1 = mysql_result(query(__FILE__,__FUNCTION__,__LINE__,$tqry,0), 0, 'idt');
	$tqry = 'SELECT idt FROM '.KML_PREFIX.'_table WHERE league='.LEAGUE.' AND idc='.$row['idc2'].' AND idg='.$row['idg'];
	$idt2 = mysql_result(query(__FILE__,__FUNCTION__,__LINE__,$tqry,0), 0, 'idt');
#usuniecie kar
	$wqry = 'SELECT wo FROM '.KML_PREFIX.'_match_map WHERE idm='.(int)$idm;
	$wrsl = query(__FILE__,__FUNCTION__,__LINE__,$wqry,0);
	while($wrow=mysql_fetch_assoc($wrsl)){
		if($wrow['wo']==$row['idc1']) ++$f1;
		if($wrow['wo']==$row['idc2']) ++$f2;
	}
	if($f1>0) del_penalty($idt1,$f1,$table);
	if($f2>0) del_penalty($idt2,$f2,$table);
	$wins = 0;
	$lost = 0;
	$draw = 0;
	if($row['win1'] || $row['draw'] || $row['win2']){
		if($row['win1']>$row['win2']) ++$wins; elseif($row['win1']<$row['win2']) ++$lost; else ++$draw;
	}
	$cqry = 'SELECT points, frags, deaths, wins, lost, draw, map_win, map_lost, map_draw FROM '.KML_PREFIX.'_table WHERE league='.LEAGUE.' AND idt='.$idt1;
	$crsl = query(__FILE__,__FUNCTION__,__LINE__,$cqry,0);
	$crow = mysql_fetch_array($crsl);
	$uqry = 'UPDATE '.KML_PREFIX.'_table SET points="'.($crow['points']-$row['points1']).'", frags="'.($crow['frags']-$row['frags1']).'", deaths="'.($crow['deaths']-$row['frags2']).'", wins="'.($crow['wins']-$wins).'", draw="'.($crow['draw']-$draw).'", lost="'.($crow['lost']-$lost).'", map_win="'.($crow['map_win']-$row['win1']).'", map_lost="'.($crow['map_lost']-$row['win2']).'", map_draw="'.($crow['map_draw']-$row['draw']).'" WHERE league='.LEAGUE.' AND idt='.$idt1;
	if(query(__FILE__,__FUNCTION__,__LINE__,$uqry,0)) println($alang['match_saved1']);
	$cqry = 'SELECT points, frags, deaths, wins, lost, draw, map_win, map_lost, map_draw FROM '.KML_PREFIX.'_table WHERE league='.LEAGUE.' AND idt='.$idt2;
	$crsl = query(__FILE__,__FUNCTION__,__LINE__,$cqry,0);
	$crow = mysql_fetch_array($crsl);
	$uqry = 'UPDATE '.KML_PREFIX.'_table SET points="'.($crow['points']-$row['points2']).'", frags="'.($crow['frags']-$row['frags2']).'", deaths="'.($crow['deaths']-$row['frags1']).'", wins="'.($crow['wins']-$lost).'", draw="'.($crow['draw']-$draw).'", lost="'.($crow['lost']-$wins).'", map_win="'.($crow['map_win']-$row['win2']).'", map_lost="'.($crow['map_lost']-$row['win1']).'", map_draw="'.($crow['map_draw']-$row['draw']).'" WHERE league='.LEAGUE.' AND idt='.$idt2;
	if(query(__FILE__,__FUNCTION__,__LINE__,$uqry,0)) println($alang['match_saved2']);
}

Function table_upd_add($dts){
	global $alang;
#pobranie ID tabeli dla klanu
	$tqry = 'SELECT idt FROM '.KML_PREFIX.'_table WHERE league='.LEAGUE.' AND idc='.(int)$dts['idc1'].' AND idg='.(int)$dts['idg'];
	$idt1 = mysql_result(query(__FILE__,__FUNCTION__,__LINE__,$tqry,0), 0, 'idt');
	$tqry = 'SELECT idt FROM '.KML_PREFIX.'_table WHERE league='.LEAGUE.' AND idc='.(int)$dts['idc2'].' AND idg='.(int)$dts['idg'];
	$idt2 = mysql_result(query(__FILE__,__FUNCTION__,__LINE__,$tqry,0), 0, 'idt');
#dodanie kary
	if(is_array($dts['maps'])){
		foreach($dts['maps'] as $ky=>$vl){
			if($vl['name']){
				$wo = 0;
				if($vl['cl1_fg']=='w') ++$f2; elseif($vl['cl2_fg']=='w') ++$f1;
			}
		}
	}
	if($f1>0) add_penalty($idt1,$dts['idc1'],$f1);
	if($f2>0) add_penalty($idt2,$dts['idc2'],$f2);
#aktualizacja tabel
	$wins = 0;
	$lost = 0;
	$draw = 0;
	if($dts['clan1w'] || $dts['clan2w'] || $dts['clandw']){
		if($dts['clan1w']>$dts['clan2w']) ++$wins; elseif($dts['clan1w']<$dts['clan2w']) ++$lost; else ++$draw;
	}
	$qry = 'SELECT points, frags, deaths, wins, draw, lost, map_win, map_lost, map_draw FROM '.KML_PREFIX.'_table WHERE league='.LEAGUE.' AND idt='.$idt1;
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	$row = mysql_fetch_array($rsl);
	$qry = 'UPDATE '.KML_PREFIX.'_table SET points="'.($row['points']+(int)$dts['points1']).'", frags="'.($row['frags']+(int)$dts['frags1']).'", deaths="'.($row['deaths']+(int)$dts['frags2']).'", wins="'.($row['wins']+$wins).'", draw="'.($row['draw']+$draw).'", lost="'.($row['lost']+$lost).'", map_win="'.($row['map_win']+(int)$dts['clan1w']).'", map_draw="'.($row['map_draw']+(int)$dts['clandw']).'", map_lost="'.($row['map_lost']+(int)$dts['clan2w']).'" WHERE league='.LEAGUE.' AND idt='.$idt1;
	if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) println($alang['tab_upd1']);
	$qry = 'SELECT points, frags, deaths, wins, draw, lost, map_win, map_lost, map_draw FROM '.KML_PREFIX.'_table WHERE league='.LEAGUE.' AND idt='.$idt2;
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	$row = mysql_fetch_array($rsl);
	$qry = 'UPDATE '.KML_PREFIX.'_table SET points="'.($row['points']+(int)$dts['points2']).'", frags="'.($row['frags']+(int)$dts['frags2']).'", deaths="'.($row['deaths']+(int)$dts['frags1']).'", wins="'.($row['wins']+$lost).'", draw="'.($row['draw']+$draw).'", lost="'.($row['lost']+$wins).'", map_win="'.($row['map_win']+(int)$dts['clan2w']).'", map_draw="'.($row['map_draw']+(int)$dts['clandw']).'", map_lost='.($row['map_lost']+(int)$dts['clan1w']).' WHERE league='.LEAGUE.' AND idt='.$idt2;
	if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) println($alang['tab_upd2']);
}

?>
