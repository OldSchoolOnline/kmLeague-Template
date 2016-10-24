<?php

#DEMOS

Function demos($dts){
	global $alang;
	//demosquad demos
	if(strlen(DEMOSQUAD)>10){
		if($dts['idd']){
			$qry = 'SELECT idd, idm, idd_ds FROM '.KML_PREFIX.'_demo WHERE league='.LEAGUE.' AND idd='.(int)$dts['idd'];
			$rslt = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
			if(mysql_num_rows($rslt)!=1) die($alang['sure']);
			$row = mysql_fetch_array($rslt);
			$inf = ' #ID: '.$row['idd'];
		}
		if(!$inf) option_head($alang['new_demo']); else option_head($alang['edit_demo'].$inf);
		println('<table cellspacing="10"><tr><td valign="top">');
		println('<form action="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=demos" method="post">');
		if(LEAGUE_TYPE=='D') $qry = 'SELECT c1.login AS cnm1, c2.login AS cnm2, m.idm, m.date, m.idc1, m.idc2 FROM '.KML_PREFIX.'_match AS m, '.KML_PREFIX.'_users AS c1, '.KML_PREFIX.'_users AS c2 WHERE m.league='.LEAGUE.' AND m.idc1=c1.iduser AND m.idc2=c2.iduser AND m.ids='.IDS.' AND m.date>0 ORDER BY m.date DESC, m.idm DESC';
		else $qry = 'SELECT c1.tag AS cnm1, c2.tag AS cnm2, m.idm, m.date, m.idc1, m.idc2 FROM '.KML_PREFIX.'_match AS m, '.KML_PREFIX.'_clan AS c1, '.KML_PREFIX.'_clan AS c2 WHERE m.league='.LEAGUE.' AND m.idc1=c1.idc AND m.idc2=c2.idc AND m.ids='.IDS.' AND m.date>0 ORDER BY m.date DESC, m.idm DESC';
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		if(mysql_num_rows($rsl)<1) die($alang['no_matches']);
		if($dts['idd']){
			println('<input type="hidden" name="idd" value="'.$dts['idd'].'">');
		}
		switch($dts['opt']){
			case $alang['yes']:{
				$qry = 'DELETE FROM '.KML_PREFIX.'_demo WHERE league='.LEAGUE.' AND idd='.(int)$dts['idd'];
				if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) action_info('r',$alang['demo']); else println(__FILE__.__LINE__.$alang['mysql_err']);
				break;
			}
			case $alang['save']:{
				$qry = 'UPDATE '.KML_PREFIX.'_demo SET idm='.(int)$dts['idm'].', idd_ds="'.(int)$dts['idd_ds'].'" WHERE league='.LEAGUE.' AND idd='.(int)$dts['idd'];
				if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) action_info('s',$alang['demo']); else println(__FILE__.__LINE__.$alang['mysql_err']);
				break;
			}
			case $alang['delete']:{
				action_info('x',$alang['demo']);
				break;
			}
			case $alang['edit']:{
				println('<table>');
				println('<tr><td><b>'.$alang['match'].':</b></td><td><select name="idm">');
				while($mrow = mysql_fetch_assoc($rsl)){
					foreach($mrow as $ky=>$vl) $mrow[$ky] = intoBrowser($vl);
					print('<option value='.$mrow['idm']);
					if($mrow['idm']==$row['idm']) print(' selected');
					println('>'.show_date($mrow['date'], 1).' | '.$mrow['cnm1'].' vs '.$mrow['cnm2']);
				}
				println('</select></td></tr>');
				println('<tr><td><b>'.$alang['ds_idd'].':</b></td><td><input type=text value="'.$row['idd_ds'].'" name="idd_ds" maxlength="15"></td></tr>');
				println('<tr><td colspan="2" align="right"><input type="submit" name="opt" value="'.$alang['save'].'"> <input type="submit" name="opt" value="'.$alang['delete'].'"></td></tr>');
				println('</table>');
				break;
			}
			case $alang['add']:{
				$qry = 'INSERT INTO '.KML_PREFIX.'_demo(league, idm, idd_ds) VALUES('.LEAGUE.', "'.(int)$dts['idm'].'", "'.(int)$dts['idd_ds'].'")';
				if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) action_info('a',$alang['demo']); else println(__FILE__.__LINE__.$alang['mysql_err']);
			}
			default:{
				println('<table>');
				print('<tr><td><b>'.$alang['match'].':</b></td><td><select name="idm">');
				while($mrow = mysql_fetch_array($rsl)){
					foreach($mrow as $ky=>$vl) $mrow[$ky] = intoBrowser($vl);
					print('<option value='.$mrow['idm'].'>'.show_date($mrow['date'], 2).' | '.$mrow['cnm1'].' vs '.$mrow['cnm2']);
				}
				println('</select></td></tr>');
				println('<tr><td><b>'.$alang['ds_idd'].':</b></td><td><input type="text" name="idd_ds" maxlength="15"></td></tr>');
				println('<tr><td colspan="2" align="right"><input type="submit" name="opt" value="'.$alang['add'].'"></td></tr>');
				println('</table>');
			}
		}
		println('</form>');
		println('</td><td valign="top">');
		println('<div class="head">'.$alang['demos_list'].'</div>');
		$qry = 'SELECT l.idd, CONCAT_WS(" vs ",d.team1,d.team2) AS name, d.map, d.pov, d.date FROM '.KML_PREFIX.'_demo AS l, ds_demos AS d WHERE l.league='.LEAGUE.' AND d.iddemo=l.idd_ds ORDER BY l.idd DESC LIMIT 0,50';
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		println('<form method="post" action="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=demos">');
		println('<select size="10" name="idd">');
		while($row=mysql_fetch_assoc($rsl)){
			foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
			print('<option value="'.$row['idd'].'"');
			if($row['idd']==$dts['idd']) print(' selected');
			print('>'.show_date($row['date'], 1).' | '.$row['name'].' | '.$row['map'].' | '.$row['pov']);
		}
		println('</select><br/><input type="submit" name="opt" value="'.$alang['edit'].'"> <input type="submit" name="opt" value="'.$alang['delete'].'">');
		println('</form>');
		println('</td></tr></table>');
	//normal demos
	}else{
		if($dts['idd']){
			if(LEAGUE_TYPE=='D') $qry = 'SELECT d.idd, d.pov, d.map, d.time, d.link, d.extension, c1.login AS cnm1, c2.login AS cnm2, m.idm, m.idc1, m.idc2 FROM '.KML_PREFIX.'_match AS m, '.KML_PREFIX.'_users AS c1, '.KML_PREFIX.'_users AS c2, '.KML_PREFIX.'_demo_sa AS d WHERE m.league='.LEAGUE.' AND m.idc1=c1.iduser AND m.idc2=c2.iduser AND m.idm=d.idm AND d.idd='.(int)$dts['idd'];
			else $qry = 'SELECT d.idd, d.pov, d.map, d.time, d.link, d.extension, c1.tag AS cnm1, c2.tag AS cnm2, m.idm, m.idc1, m.idc2 FROM '.KML_PREFIX.'_match AS m, '.KML_PREFIX.'_clan AS c1, '.KML_PREFIX.'_clan AS c2, '.KML_PREFIX.'_demo_sa AS d WHERE m.league='.LEAGUE.' AND m.idc1=c1.idc AND m.idc2=c2.idc AND m.idm=d.idm AND d.idd='.(int)$dts['idd'];
			$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
			if(mysql_num_rows($rsl)!=1) die($alang['sure']);
			$row = mysql_fetch_array($rsl);
			foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
			if($row['link']) $link = $row['link']; else $link = 'demos/'.$row['time'].'_'.$row['cnm1'].'_vs_'.$row['cnm2'].'_'.$row['map'].'_'.$row['pov'].'.'.$row['extension'];
			$inf = ' #ID: '.$row['idd'];
		}
		if(!$inf) option_head($alang['new_demo']); else option_head($alang['edit_demo'].$inf);
		println('<table cellspacing="10"><tr><td valign="top">
		<form action="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=demos" method="post" enctype="multipart/form-data">');
		if(LEAGUE_TYPE=='D') $mqry = 'SELECT c1.login AS cnm1, c2.login AS cnm2, m.idm, m.date, m.idc1, m.idc2 FROM '.KML_PREFIX.'_match AS m, '.KML_PREFIX.'_users AS c1, '.KML_PREFIX.'_users AS c2 WHERE m.league='.LEAGUE.' AND m.idc1=c1.iduser AND m.idc2=c2.iduser AND m.ids='.IDS.' AND m.date>0 ORDER BY m.date DESC';
		else $mqry = 'SELECT c1.tag AS cnm1, c2.tag AS cnm2, m.idm, m.date, m.idc1, m.idc2 FROM '.KML_PREFIX.'_match AS m, '.KML_PREFIX.'_clan AS c1, '.KML_PREFIX.'_clan AS c2 WHERE m.league='.LEAGUE.' AND m.idc1=c1.idc AND m.idc2=c2.idc AND m.ids='.IDS.' AND m.date>0 ORDER BY m.date DESC';
		$mrsl = query(__FILE__,__FUNCTION__,__LINE__,$mqry,0);
		if(mysql_num_rows($mrsl)<1) die($alang['no_matches']);
		while($mrow = mysql_fetch_assoc($mrsl)){
			foreach($mrow as $ky=>$vl) $mrow[$ky] = intoBrowser($vl);
			$matches[$mrow['idm']] = show_date($mrow['date'],1).' | '.$mrow['cnm1'].' vs '.$mrow['cnm2'];
		}
		$time = time();
		if($dts['idd']){
			println('<input type="hidden" name="idd" value="'.$dts['idd'].'"></div>');
		}
		switch($dts['opt']){
			case $alang['add']:{
				if($_FILES['demo']['size']>0){
					$allow_ext = array('zip', 'rar');
					$ext = strtolower(substr($_FILES['demo']['name'],-3));
					if(!in_array($ext,$allow_ext)) die($alang['sure']);
					if(LEAGUE_TYPE=='D') $qry = 'SELECT c1.login AS cnm1, c2.login AS cnm2 FROM '.KML_PREFIX.'_match AS m, '.KML_PREFIX.'_users AS c1, '.KML_PREFIX.'_users AS c2 WHERE m.league='.LEAGUE.' AND m.idc1=c1.iduser AND m.idc2=c2.iduser AND m.idm='.(int)$dts['idm'];
					else $qry = 'SELECT c1.tag AS cnm1, c2.tag AS cnm2 FROM '.KML_PREFIX.'_match AS m, '.KML_PREFIX.'_clan AS c1, '.KML_PREFIX.'_clan AS c2 WHERE m.league='.LEAGUE.' AND m.idc1=c1.idc AND m.idc2=c2.idc AND m.idm='.(int)$dts['idm'];
					$rsl =  query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
					$row = mysql_fetch_assoc($rsl);
					foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
					$new_name = 'demos/'.$time.'_'.$row['cnm1'].'_vs_'.$row['cnm2'].'_'.$dts['map'].'_'.$dts['pov'].'.'.$ext;
					move_uploaded_file($_FILES['demo']['tmp_name'], $new_name);
					chmod($new_name, KML_CHMOD);
					$dts['link'] = '';
				}
				$qry = SQL('INSERT INTO '.KML_PREFIX.'_demo_sa(league, idm, link, map, pov, time, extension) VALUES('.LEAGUE.', %d, %s, %s, %s, "'.$time.'", "'.$ext.'")', $dts['idm'], $dts['link'], $dts['map'], $dts['pov']);
				if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) action_info('a',$alang['demo']);
				break;
			}
			case $alang['yes']:{
				if(!$row['link']){
					if(unlink($link)) action_info('r',$alang['file']); else action_info('re',$alang['file']);
				}
				$qry = 'DELETE FROM '.KML_PREFIX.'_demo_sa WHERE idd='.(int)$dts['idd'];
				if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) action_info('r',$alang['demo']);
				break;
			}
			case $alang['save']:{
				if($_FILES['demo']['size']>0){
					if(!$row['link']) if(unlink($link)) action_info('r',$alang['old_file']); else action_info('re',$alang['old_file']);
					$allow_ext = array('rar', 'zip');
					$ext = strtolower(substr($_FILES['demo']['name'],-3));
					if(!in_array($ext,$allow_ext)) die($alang['sure']);
					if(LEAGUE_TYPE=='D') $qry = 'SELECT c1.login AS cnm1, c2.login AS cnm2 FROM '.KML_PREFIX.'_match AS m, '.KML_PREFIX.'_users AS c1, '.KML_PREFIX.'_users AS c2 WHERE m.league='.LEAGUE.' AND m.idc1=c1.iduser and m.idc2=c2.iduser and m.idm='.(int)$dts['idm'];
					else $qry = 'SELECT c1.tag AS cnm1, c2.tag AS cnm2 FROM '.KML_PREFIX.'_match AS m, '.KML_PREFIX.'_clan AS c1, '.KML_PREFIX.'_clan AS c2 WHERE m.league='.LEAGUE.' AND m.idc1=c1.idc and m.idc2=c2.idc and m.idm='.(int)$dts['idm'];
					$rsl =  query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
					$row = mysql_fetch_assoc($rsl);
					foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
					$new_name = 'demos/'.$time.'_'.$row['cnm1'].'_vs_'.$row['cnm2'].'_'.$dts['map'].'_'.$dts['pov'].'.'.$ext;
					move_uploaded_file($_FILES['demo']['tmp_name'], $new_name);
					$dts['link'] = '';
				}elseif(!$dts['link']){
					$new_name = 'demos/'.$time.'_'.$row['cnm1'].'_vs_'.$row['cnm2'].'_'.$dts['map'].'_'.$dts['pov'].'.'.$row['extension'];
					rename($link,$new_name);
				}
				$qry = SQL('UPDATE '.KML_PREFIX.'_demo_sa SET idm=%d, link=%s, map=%s, pov=%s, time='.$time, $dts['idm'], $dts['link'], $dts['map'], $dts['pov']);
				if($ext) $qry .= ', extension="'.$ext.'"';
				$qry .= ' WHERE idd='.(int)$dts['idd'];
				if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) action_info('s',$alang['old_file']);
				break;
			}
			case $alang['delete']:{
				action_info('x',$alang['demo']);
				break;
			}
			case $alang['edit']:{
				println('<table>
				<tr><td><b>'.$alang['match'].':</b></td><td><select name="idm">'.array_assoc($matches,$row['idm']).'</select></td></tr>
				<tr><td><b>'.$alang['file'].':</b></td><td><input type="file" name="demo"></td></tr>
				<tr><td><b>'.$alang['link'].':</b></td><td><input type="text" value="'.$row['link'].'" name="link" maxlength="100" size="40"></td></tr>
				<tr><td><b>'.$alang['pov'].':</b></td><td><input type="text" value="'.$row['pov'].'" name="pov" maxlength="30"></td></tr>
				<tr><td><b>'.$alang['map'].':</b></td><td><input type="text" value="'.$row['map'].'" name="map" maxlength="15"></td></tr>
				<tr><td colspan="2" align="right"><input type="submit" name="opt" value="'.$alang['save'].'"> <input type="submit" name="opt" value="'.$alang['delete'].'"></td></tr>
				</table>');
				break;
			}
			default:{
				println('<table>
				<tr><td><b>'.$alang['match'].':</b></td><td><select name="idm">'.array_assoc($matches,'').'</select></td></tr>
				<tr><td><b>'.$alang['file'].':</b></td><td><input type="file" name="demo"></td></tr>
				<tr><td><b>'.$alang['link'].':</b></td><td><input type="text" name="link" maxlength="100" size="40"></td></tr>
				<tr><td><b>'.$alang['pov'].':</b></td><td><input type="text" name="pov" maxlength="30"></td></tr>
				<tr><td><b>'.$alang['map'].':</b></td><td><input type="text" name="map" maxlength="15"></td></tr>
				<tr><td colspan="2" align="right"><input type="submit" name="opt" value="'.$alang['add'].'"></td></tr>
				</table>');
			}
		}
		println('</form>
		</td><td valign=top>
		<div class="head">'.$alang['demos_list'].'</div>');
		$qry = 'SELECT d.idd, d.time, d.map, d.pov from '.KML_PREFIX.'_demo_sa AS d WHERE d.league='.LEAGUE.' ORDER BY d.time DESC LIMIT 0,30';
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		println('<form method="post" action="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=demos">
		<select size="10" name="idd">');
		while($row=mysql_fetch_assoc($rsl)){
			foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
			print('<option value="'.$row['idd'].'"');
			if($row['idd']==$dts['idd']) print(' selected');
			println('>'.show_date($row['time'],1).' | '.$row['map'].' | '.$row['pov']);
		}
		println('</select><br/>
		<input type="submit" name="opt" value="'.$alang['edit'].'"> <input type="submit" name="opt" value="'.$alang['delete'].'">
		</form>
		</td></tr></table>');
	}
}

?>
