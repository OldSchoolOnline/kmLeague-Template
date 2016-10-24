<?php

# SCREENS

Function screen($dts){
	global $alang;
	if($dts['idn']){
		$qry = 'SELECT map, hash, name, idm FROM '.KML_PREFIX.'_screen WHERE league='.LEAGUE.' AND idn='.(int)$dts['idn'];
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		if(mysql_num_rows($rsl)!=1) die($alang['sure']);
		$row = mysql_fetch_array($rsl);
		foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
		$file = 'screen/'.$row['map'].$row['hash'].$row['name'].'.jpg';
		$inf = show_date($row['name'], 2).' | '.$row['map'];
	}
	if($inf) option_head($alang['screen_edit'].$inf); else option_head($alang['new_screen']);
	println('<table cellspacing="10"><tr><td valign="top">');
	if(LEAGUE_TYPE=='D') $mqry = 'SELECT c1.login AS cnm1, c2.login AS cnm2, m.idm, m.date, m.idc1, m.idc2 FROM '.KML_PREFIX.'_match AS m, '.KML_PREFIX.'_users AS c1, '.KML_PREFIX.'_users AS c2 WHERE m.league='.LEAGUE.' AND m.idc1=c1.iduser AND m.idc2=c2.iduser AND m.ids='.IDS.' AND m.date>0 ORDER BY m.idm DESC';
	else $mqry = 'SELECT c1.tag AS cnm1, c2.tag AS cnm2, m.idm, m.date, m.idc1, m.idc2 FROM '.KML_PREFIX.'_match AS m, '.KML_PREFIX.'_clan AS c1, '.KML_PREFIX.'_clan AS c2 WHERE m.league='.LEAGUE.' AND m.idc1=c1.idc AND m.idc2=c2.idc AND m.ids='.IDS.' AND m.date>0 ORDER BY m.idm DESC';
	$mrsl = query(__FILE__,__FUNCTION__,__LINE__,$mqry,0);
	if(mysql_num_rows($mrsl)<1) die($alang['no_matches']);
	while($mrow = mysql_fetch_assoc($mrsl)){
		foreach($mrow as $ky=>$vl) $mrow[$ky] = intoBrowser($vl);
		$matches[$mrow['idm']] = show_date($mrow['date'], 1).' | '.$mrow['cnm1'].' vs '.$mrow['cnm2'];
	}
	println('<form action="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=screen" method="post" enctype="multipart/form-data">');
	if($dts['idn']){
		println('<div class="header"><a target="_blank" href="'.$file.'">'.$alang['preview'].'</a><input type="hidden" name="idn" value="'.$dts['idn'].'"/></div>');
	}
	switch($dts['opt']){
		case $alang['edit']:{
			println('<table>
			<tr><td class="hd">'.$alang['match'].':</td><td><select name="idm">'.array_assoc($matches,$row['idm']).'</select></td></tr>
			<tr><td class="hd">'.$alang['map'].':</td><td><input type="text" name="map" value="'.$row['map'].'" maxlength="10"/></td></tr>
			<tr><td colspan="2" align="right"><input type="submit" name="opt" value="'.$alang['delete'].'"/> <input type="submit" name="opt" value="'.$alang['save'].'"/></td></tr>
			</table>');
			break;
		}
		case $alang['delete']:{
			action_info('x', $alang['screen']);
			break;
		}
		case $alang['yes']:{
			unlink($file);
			$qry = 'DELETE FROM '.KML_PREFIX.'_screen WHERE league='.LEAGUE.' AND idn='.(int)$dts['idn'];
			if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) action_info('r', $alang['screen']);
			break;
		}
		case $alang['save']:{
			if(!file_exists($file)) action_info('f',$file);
			else{
				$rename2 = 'screen/'.$dts['map'].$row['hash'].$row['name'].'.jpg';
				rename($file,$rename2);
				$qry = SQL('UPDATE '.KML_PREFIX.'_screen SET idm=%d, map=%s WHERE league='.LEAGUE.' AND idn=%d', $dts['idm'], $dts['map'], $dts['idn']);
				if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) action_info('s', $alang['screen']);
			}
			break;
		}
		case $alang['add']:{
			$time = time();
			$hash = substr(md5(uniqid(rand(),1)),0,3);
			$new_name = $dts['map'].$hash.$time.'.jpg';
			if($_FILES['screen']['size'] != 0){
				if(empty($dts['map'])) die($alang['screen_err1']);
				if ($_FILES['screen']['type'] != 'image/pjpeg' && $_FILES['screen']['type'] != 'image/jpeg') die($alang['screen_err2']);
				move_uploaded_file($_FILES['screen']['tmp_name'], "screen/$new_name");
				if(file_exists("screen/$new_name")){
					$qry = SQL('INSERT INTO '.KML_PREFIX.'_screen(league, idm, map, name, hash) VALUES("'.LEAGUE.'", %d, %s, "'.$time.'", %s)', $dts['idm'], $dts['map'], $hash);
					if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) action_info('a', $alang['screen']);
				}
			}
			break;
		}
		default:{
			println('<table>
			<tr><td class="hd">'.$alang['file'].':</td><td><input size="27" name="screen" type="file"></td></tr>
			<tr><td class="hd">'.$alang['match'].':</td><td><select name="idm">'.array_assoc($matches,'').'</select></td></tr>
			<tr><td class="hd">'.$alang['map'].':</td><td><input type="text" name="map" maxlength="10"></td></tr>
			<tr><td colspan="2" align="right"><input type="submit" name="opt" value="'.$alang['add'].'"></td></tr>
			</table>');
		}
	}
	println('</form>
	</td><td valign="top">
	<div class="head">'.$alang['screen_list'].'</div>');
	$qry = 'SELECT idn, name, map, idm FROM '.KML_PREFIX.'_screen WHERE league='.LEAGUE.' ORDER BY idn DESC LIMIT 0,50';
	$rslt = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	println('<form method="post" action="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=screen">
	<select size="10" name="idn">');
	while($row=mysql_fetch_array($rslt)){
		foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
		print('<option value="'.$row['idn'].'"');
		if($row['idn']==$dts['idn']) print(' selected');
		println('>idm: '.$row['idm'].' | '.show_date($row['name'], 2).' | '.$row['map']);
	}
	println('</select><br/>
	<input type="submit" name="opt" value="'.$alang['edit'].'"> <input type="submit" name="opt" value="'.$alang['delete'].'">
	</form>
	</td></tr></table>');
}

?>
