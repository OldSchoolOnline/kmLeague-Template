<?php

# INCLUDE FILES
Function incl_file($dts,$head,$hfile){
	global $alang;
	option_head(ucfirst(strtolower($head)));
	$file = 'data/'.$hfile.'_'.LEAGUE.'.htm';
	if($dts['update']){
		$data = stripslashes(ubcode_add(nl2br($dts['descr'])));
		$fp = fopen($file, 'w+');
		fwrite($fp, $data);
		fclose($fp);
		chmod($file, KML_CHMOD);
	println('<div class="action_info">'.$alang['info_saved'].'</div><br/>');
	}
	if(file_exists($file)) $data = file_get_contents($file);
	println('<table>
	<tr><td valign="top">
	<form method="post" name="formula" action="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op='.$hfile.'">
	<textarea name="descr" rows="20" cols="60">'.ubcode_rem($data,2).'</textarea>
	<br/><input type="submit" name="update" value="'.$alang['save'].'"> <input type="reset" value="'.$alang['reset'].'">
	</form>
	</td><td valign="top" style="width: 350px;">');
	ubbcodes_flags();
	print('</td></tr>
	</table>');
}

# INTERVIEWS

Function interviews($dts,$login,$grants){
	global $alang;
	if($dts['idi'] || $dts['id']){
		if(!$dts['idi']) $dts['idi'] = $dts['id'];
		$qry = 'SELECT idi, subject, date, content, visible FROM '.KML_PREFIX.'_interview WHERE league='.LEAGUE.' AND ids='.IDS.' AND idi='.(int)$dts['idi'];
		if($grants<2) $qry .= ' AND author='.(int)$login;
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		if(mysql_num_rows($rsl)!=1) die($alang['interv_err1']);
		$row = mysql_fetch_array($rsl);
		foreach($row as $ky=>$vl){
			if($ky!='content') $row[$ky] = intoBrowser($vl);
		}
		$inf = $row['subject'];
	}
	if($inf) option_head($alang['edit_interview'].': '.$inf); else option_head($alang['new_interview']);
	println('<table cellspacing="10"><tr><td valign="top">
	<form method="post" action="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=interv" id="formula">');
	if($dts['idi']) println('<input type="hidden" name="idi" value="'.$dts['idi'].'"></div>');
	switch($dts['opt']){
		case $alang['edit']:{
			println('<table>
			<tr><td class="hd">'.$alang['subject'].':</td><td><input type="text" name="subject" maxlength="50" value="'.$row['subject'].'" size="31"></td></tr>
			<tr><td class="hd">'.$alang['show'].':</td><td><select name="visible"><option value="Y">'.$alang['yes'].'<option value="N"');
			if($row['visible']=='N') print(' selected');
			println('>'.$alang['no'].'</select></td></tr>
			<tr><td valign="top" class="hd">'.$alang['content'].'</td><td><textarea name="descr" cols="50" rows="20">'.ubcode_rem($row['content']).'</textarea></td></tr>
			<tr><td align="right" colspan="2"><input type="submit" name="opt" value="'.$alang['delete'].'"> <input type="reset" value="'.$alang['reset'].'"> <input type="submit" name="opt" value="'.$alang['save'].'"></td></tr>
			</table>');
			break;
		}
		case $alang['delete']:{
			action_info('x', $alang['interview']);
			break;
		}
		case $alang['yes']:{
			$qry = 'DELETE FROM '.KML_PREFIX.'_interview WHERE league='.LEAGUE.' AND idi='.(int)$dts['idi'];
			if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) action_info('r', $alang['interview']);
			$qry = 'DELETE FROM '.KML_PREFIX.'_comments WHERE iditem='.(int)$dts['idi'].' AND type='.ARTICLE_ID;
			if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) action_info('r', $alang['comments']);
			break;
		}
		case $alang['save']:{
			$qry = SQL('UPDATE '.KML_PREFIX.'_interview SET visible=%s, subject=%s, content=%m WHERE league='.LEAGUE.' AND idi=%d'.$dts['idi'], $dts['visible'], $dts['subject'], $dts['descr']);
			if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) action_info('s', $alang['interview']);
			break;
		}
		case $alang['add']:{
			$qry = SQL('INSERT INTO '.KML_PREFIX.'_interview(league, ids, author, date, subject, content, visible) VALUES('.LEAGUE.', "'.IDS.'", %d, "'.time().'", %s, %m, %s)', $login, $dts['subject'], $dts['descr'], $dts['visible']);
			if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) action_info('a', $alang['interview']);
		}
		default:{
			println('<table>
			<tr><td class="hd">'.$alang['subject'].':</td><td><input type="text" name="subject" maxlength="50" size="31"></td></tr>
			<tr><td class="hd">'.$alang['show'].':</td><td><select name="visible"><option value="Y">'.$alang['yes'].'<option value="N">'.$alang['no'].'</select></td></tr>
			<tr><td valign="top" class="hd">'.$alang['content'].':</td><td><textarea name="descr" cols="50" rows="20"></textarea></td></tr>
			<tr><td align="right" colspan="2"><input type="submit" name="opt" value="'.$alang['add'].'"></td></tr>
			</table>');
		}
	}
	println('</form>
	</td><td valign=top>
	<div class="head">'.$alang['interviews'].'</div><br/>
	<form method="post" action="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=interv">
	<select name="idi" size="10">');
	$qry = 'SELECT u.iduser, u.login, i.idi, i.subject FROM '.KML_PREFIX.'_interview AS i, '.KML_PREFIX.'_users AS u WHERE i.league='.LEAGUE.' AND i.ids='.IDS.' AND u.iduser=i.author';
	if($grants<2) $qry .= ' AND author='.(int)$login;
	$qry .= ' ORDER BY i.idi DESC LIMIT 0,30';
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	while($row = mysql_fetch_array($rsl)){
		foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
		print('<option value="'.$row['idi'].'"');
		if($row['idi']==$dts['idi']) print(' selected');
		println('>'.$row['subject'].' | '.$row['login']);
	}
	println('</select><br/>
	<span class="hd">ID:</span> <input type="text" size="5" name="id" value="'.$dts['idi'].'"> <input type="submit" name="opt" value="'.$alang['delete'].'"> <input type="submit" name="opt" value="'.$alang['edit'].'">
	</form>');
	ubbcodes_flags();
	print('</td></tr></table>');
}

# USERS

Function league_user($id){
	global $alang;
	println('<br/><br/>
	<form method="post" action="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=save_leader"><input type="hidden" name="iduser" value='.$id.'>');
	if(LEAGUE_TYPE=='D'){
		$qry = 'SELECT idc FROM '.KML_PREFIX.'_in WHERE league='.LEAGUE.' AND idc='.(int)$id;
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		if(mysql_num_rows($rsl)<1) print('<input type="submit" name="opt5" value="'.$alang['add_player'].'">');
		else print('<input type="submit" name="opt6" value="'.$alang['rem_player'].'">');
	}elseif(LEAGUE_TYPE=='T'){
		$qry = 'SELECT idc FROM '.KML_PREFIX.'_clan WHERE iduser='.(int)$id;
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		$row = mysql_fetch_array($rsl);
		print('<div class="hd">'.$alang['clan'].': <select name="idc"><option value="0">'.$alang['cant_edit']);
		$cqry = 'SELECT c.idc, c.tag FROM '.KML_PREFIX.'_clan AS c, '.KML_PREFIX.'_in AS x WHERE x.idc=c.idc AND x.league='.LEAGUE.' ORDER BY c.tag';
		$crsl = query(__FILE__,__FUNCTION__,__LINE__,$cqry,0);
		while($crow = mysql_fetch_array($crsl)){
			foreach($crow as $ky=>$vl) $crow[$ky] = intoBrowser($vl);
			print('<option value="'.$crow['idc'].'"');
			if($crow['idc']==$row['idc']) print(' selected');
			println('>'.$crow['tag']);
		}
		println('</select><input type="hidden" name="old_idc" value="'.$row['idc'].'"/> <input type="submit" name="opt1" value="'.$alang['save'].'"></div>');
	}
	println('</form>');
}

Function save_leader($dts){
	global $alang;
	$dts['old_idc'] = intval($dts['old_idc']);
	$dts['idc'] = intval($dts['idc']);
	$dts['iduser'] = intval($dts['iduser']);
	if($dts['opt1']){
		option_head($alang['cl_edit']);
		if($dts['old_idc']>0 && $dts['idc']==0){
			$qry = 'UPDATE '.KML_PREFIX.'_clan SET iduser=0 WHERE iduser='.$dts['iduser'].' AND idc='.$dts['old_idc'];
			if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) action_info('r', $alang['cl']);
		}elseif($dts['idc']>0){
			$qry = 'UPDATE '.KML_PREFIX.'_clan SET iduser='.$dts['iduser'].' WHERE idc='.$dts['idc'];
			if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) action_info('a', $alang['cl']);
		}
	}elseif($dts['opt5']){
		option_head($alang['players']);
		$qry = 'INSERT INTO '.KML_PREFIX.'_in(league, idc) VALUES('.LEAGUE.', "'.(int)$dts['iduser'].'")';
		if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) action_info('a', $alang['user']);
	}elseif($dts['opt6']){
		option_head($alang['players']);
		$qry = 'DELETE FROM '.KML_PREFIX.'_in WHERE league='.LEAGUE.' AND idc='.(int)$dts['iduser'];
		if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) action_info('r', $alang['user']);
	}
}

Function ip_search($dts){
	global $alang;
	option_head($alang['player_search']);
	println('<form action="'.$_SERVER['PHP_SELF'].'" method="get">'.KML_LINK_SLF.'<input type="hidden" name="op" value="ips">
	'.$alang['nick'].': <input type="text" name="name" value="'.$dts['name'].'"> '.$alang['ip'].': <input type="text" name="ip" value="'.$dts['ip'].'"> <input type="submit" value="'.$alang['search'].'">
	</form>');
	if($dts['name'] || $dts['ip']){
		$qry = 'SELECT p.pname, p.ident, c.idc, c.tag FROM '.KML_PREFIX.'_clan AS c, '.KML_PREFIX.'_player AS p, '.KML_PREFIX.'_player_in AS x, '.KML_PREFIX.'_in AS z WHERE z.idc=c.idc AND x.idp=p.idp AND x.league='.LEAGUE.' AND z.league='.LEAGUE.' AND p.idc=c.idc';
		if($dts['name']) $qry .= SQL(' AND p.pname LIKE %s', '%'.$dts['name'].'%');
		if($dts['ip']) $qry .= SQL(' AND p.ident LIKE %s', '%'.$dts['ip'].'%');
		$qry .= ' ORDER BY p.pname, p.idp';
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		if(mysql_num_rows($rsl)>0){
			println('<table>');
			while($row=mysql_fetch_assoc($rsl)){
				foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
				println('<tr><td>'.$row['pname'].' | '.$row['ip'].' | <a target="_blank" href="index.php?'.KML_LINK_SL.'op=clans&amp;id='.$row['idc'].'&amp;so=sqd">'.$row['tag'].'</a></td></tr>');
			}
			println('</table>');
		}
	}
}

#PLAYOFF TABLES
Function playoff_tables($post,$id,$opt){
	global $alang;
	$types = array('S'=>$alang['standard'], 'F'=>$alang['full']);
	for($i=1;$i<8;$i++) $tms[] = pow(2,$i);
	for($i=1;$i<129;$i++) $tms2[] = $i;
	$basics['table'] = KML_PREFIX.'_ptable';
	$basics['op'] = 'ptable&amp;'.KML_LINK_SL2;
	$basics['opt'] = $opt;
	$basics['key'] = 'idpt';
	$basics['id'] = $id;
	$basics['query_add'] = ', league, ids ';
	$basics['query_add_value'] = ', '.LEAGUE.', '.IDS;
	$basics['list_query'] = 'SELECT idpt, name FROM '.KML_PREFIX.'_ptable WHERE league='.LEAGUE.' AND ids='.IDS;
	$basics['list_query_add'] = ' ORDER BY idpt ASC';
	$basics['header'] = $alang['poff_table'];
	$basics['header_edit'] = $alang['poff_table'];
	$basics['list_items'] = array($alang['name']);
	$basics['list_values'] = array('{name}');
	$basics['list_size'] = '300:400';
	$basics['inf_del_query'] = $alang['qinfo_rem'].': '.$alang['poff_table'];
	$basics['inf_del'] = $alang['info_rem'].': '.$alang['poff_table'];
	$basics['inf_add'] = $alang['info_add'].': '.$alang['poff_table'];
	$basics['inf_upd'] = $alang['info_sav'].': '.$alang['poff_table'];
	$fields['name'] = array('head'=>$alang['name'], 'req'=>'Y', 'param1'=>'200', 'param2'=>'30');
	$fields['teams'] = array('type'=>'S', 'head'=>$alang['teams_no'], 'values'=>$tms, 'param2'=>'N');
	$fields['place'] = array('type'=>'S', 'head'=>$alang['best_place'], 'values'=>$tms2, 'param2'=>'N');
	$fields['ptype'] = array('type'=>'S', 'head'=>$alang['type'], 'values'=>$types, 'param2'=>'A');
	echo data_form($post,$get,$fields,$basics);
}

Function menu_builder($post,$id,$opt){
	global $alang;
	$targets = array('S'=>$alang['same_window'], 'N'=>$alang['new_window']);
	$yesno = array('Y'=>$alang['yes'], 'N'=>$alang['no']);
	$basics['table'] = KML_PREFIX.'_menu';
	$basics['op'] = 'menu_builder&amp;'.KML_LINK_SL2;
	$basics['opt'] = $opt;
	$basics['key'] = 'idml';
	$basics['id'] = $id;
	$basics['query_add'] = ', league';
	$basics['query_add_value'] = ', '.LEAGUE;
	$basics['list_query'] = 'SELECT `idml`, `head`, `head_lang`, `column`, `row` FROM '.KML_PREFIX.'_menu WHERE `league`='.LEAGUE;
	$basics['list_query_add'] = ' ORDER BY `column` ASC, `row` ASC, `head_lang` ASC, `head` ASC';
	$basics['tabinfos'] = array('alang'=>$alang);
	$basics['header'] = $alang['menu_item'];
	$basics['header_edit'] = $alang['menu_item'];
	$basics['list_items'] = array($alang['name'], $alang['menu_col'], $alang['menu_row']);
	$basics['list_values'] = array('{head} [alang;head_lang]', '{column}', '{row}');
	$basics['list_size'] = '300:400';
	$basics['inf_del_query'] = $alang['qinfo_rem'].': '.$alang['menu_item'];
	$basics['inf_del'] = $alang['info_rem'].': '.$alang['menu_item'];
	$basics['inf_add'] = $alang['info_add'].': '.$alang['menu_item'];
	$basics['inf_upd'] = $alang['info_sav'].': '.$alang['menu_item'];
	$fields['visible'] = array('type'=>'R', 'head'=>$alang['visible'], 'values'=>$yesno, 'default'=>'Y', 'param2'=>' ');
	$fields['privilages_global'] = array('type'=>'N', 'head'=>$alang['privilages_global'], 'param1'=>'4');
	$fields['privilages_local'] = array('type'=>'N', 'head'=>$alang['privilages_local'], 'param1'=>'4');
	$fields['head'] = array('head'=>$alang['name'], 'param1'=>'150', 'param2'=>'30');
	$fields['head_lang'] = array('head'=>$alang['head_lang'], 'param1'=>'150', 'param2'=>'30');
	$fields['link'] = array('head'=>$alang['link'], 'param1'=>'200', 'param2'=>'80');
	$fields['target'] = array('type'=>'S', 'head'=>$alang['target'], 'values'=>$targets, 'param2'=>'A');
	$fields['local'] = array('type'=>'R', 'head'=>$alang['local_link'], 'values'=>$yesno, 'default'=>'Y', 'param2'=>' ');
	$fields['column'] = array('type'=>'N', 'head'=>$alang['menu_col'], 'param1'=>'4');
	$fields['row'] = array('type'=>'N', 'head'=>$alang['menu_row'], 'param1'=>'4');
	echo data_form($post,$get,$fields,$basics);
}

#cl / players mailer

Function mailer($dts){
	global $alang;
	$qry = 'SELECT g.gname, g.idg, g.gphase FROM '.KML_PREFIX.'_group AS g WHERE g.league='.LEAGUE.' AND g.ids='.IDS.' GROUP BY g.idg ORDER BY g.gphase DESC, g.gname ASC';
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	$groups[] = '';
	while($row = mysql_fetch_array($rsl)){
		foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
		$groups[$row['idg']] = $alang['gphase'].': '.$row['gphase'].' | '.ucfirst($alang['group']).': '.$row['gname'];
	}
	option_head($alang['mailer']);
	println('<form method="post" action="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=mailer" name="formula">
	<table cellspacing="10"><tr><td valign="top">
		<table>
			<tr><td class="hd">'.ucfirst($alang['group']).':</td><td><select name="idg">'.array_assoc($groups,$dts['idg']).'</select></td></tr>
			<tr><td class="hd">'.ucfirst($alang['title']).':</td><td><input type="text" name="title" value="'.$dts['title'].'"></td></tr>
			<tr><td class="hd" valign="top">'.$alang['content'].':</td><td><textarea name="descr" rows="10" cols="50">'.$dts['descr'].'</textarea></td></tr>
			<tr><td colspan="2" align="right"><input type="submit" name="opt1" value="'.$alang['submit'].'"></td></tr>
		</table>');
	//ubbcodes_flags();
	print('</td><td valign="top">');
	#repeat
	#who will get info
	if($dts['opt2'] || $dts['opt1']){
		if($dts['idg']){
			$tidg = ', '.KML_PREFIX.'_table AS t, '.KML_PREFIX.'_group AS g';
			$cidg = ' AND t.idc=c.idc AND t.idg='.(int)$dts['idg'].' AND g.idg=t.idg AND g.ids='.IDS;
		}
		if(LEAGUE_TYPE=='T'){
			$qry = 'SELECT u.login, u.mail, c.cname, c.iduser, c.idc FROM '.KML_PREFIX.'_clan AS c, '.KML_PREFIX.'_in AS x, '.KML_PREFIX.'_users AS u'.$tidg.' WHERE x.idc=c.idc AND x.league='.LEAGUE.' AND c.iduser=u.iduser'.$cidg.' GROUP BY u.iduser ORDER BY c.idc, u.login';
			$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
			while($row=mysql_fetch_assoc($rsl)){
				foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
				$cls[$row['iduser']] = array('login'=>$row['login'], 'mail'=>$row['mail'], 'cname'=>$row['cname'], 'idc'=>$row['idc']);
			}
			$qry = 'SELECT u.login, u.mail, c.cname, c.iduser, c.idc FROM '.KML_PREFIX.'_player AS p, '.KML_PREFIX.'_clan AS c, '.KML_PREFIX.'_in AS x, '.KML_PREFIX.'_users AS u'.$tidg.' WHERE p.approve_user="Y" AND (p.function="C" OR p.function="W") AND c.idc=p.idc AND x.idc=c.idc AND x.league='.LEAGUE.' AND p.iduser=u.iduser'.$cidg.' GROUP BY u.iduser ORDER BY c.idc, u.login';
			$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
			while($row=mysql_fetch_assoc($rsl)){
				foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
				$cls[$row['iduser']] = array('login'=>$row['login'], 'mail'=>$row['mail'], 'cname'=>$row['cname'], 'idc'=>$row['idc']);
			}
		}elseif(LEAGUE_TYPE=='D'){
			$qry = 'SELECT u.login, u.mail, u.iduser FROM '.KML_PREFIX.'_in AS c, '.KML_PREFIX.'_users AS u'.$tidg.' WHERE c.league='.LEAGUE.' AND c.idc=u.iduser'.$cidg.' ORDER BY c.idc, u.login';
			$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
			while($row=mysql_fetch_assoc($rsl)){
				foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
				$cls[$row['iduser']] = array('login'=>$row['login'], 'mail'=>$row['mail']);
			}
		}
	}
	if($dts['opt2']){
		$uqry = 'SELECT mail, login FROM '.KML_PREFIX.'_users WHERE iduser='.(int)$_SESSION['dl_login'];
		$ursl = query(__FILE__,__FUNCTION__,__LINE__,$uqry,0);
		$urow = mysql_fetch_assoc($ursl);
		foreach($urow as $ky=>$vl) $urow[$ky] = intoBrowser($vl);
		$header = 'From: '.$urow['login'].' <'.$urow['mail'].'>';
		if(is_array($cls)){
			foreach($cls as $ky=>$row){
				echo $row['mail'].'|'.$dts['title'].'|'.$dts['descr'].'|'.$header.'<br/>';
				#if(!mail($row['mail'],$dts['title'],$dts['descr'], $header)) print($alang['mailer_err'].$row['mail']);
			}
			println($alang['mailer_sent']);
		}
	}else if($dts['opt1']){
		print('<div class="hd">'.$dts['title'].'</div>
		<div style="width: 300px;">'.ubcode_add($dts['descr']).'</div>');
		$all = 0;
		if(is_array($cls)){
			$all = count($cls);
			print('<div class="blockHead" onclick="flip(\'receivers\');">'.$alang['receivers'].' ('.$all.')</div>
			<div class="block" id="receivers">');
			foreach($cls as $ky=>$row){
				if($idc != $row['idc'] && LEAGUE_TYPE!='D') print('<div class="hd">'.$row['cname'].'</div>');
				print($row['login'].' - '.$row['mail'].'<br/>');
				$idc = $row['idc'];
			}
			print('</div>
			<div align="right"><input type="submit" name="opt2" value="'.$alang['approve'].'"></div>');
		}
	}
	println('</form>
	</td></tr></table>');
}

Function awards($post,$id,$opt){
	global $alang;
	$lvl = array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15);
	$ext = array('jpg', 'jpeg', 'gif');
	$basics['table'] = KML_PREFIX.'_award';
	$basics['op'] = 'award&amp;'.KML_LINK_SL2;
	$basics['opt'] = $opt;
	$basics['key'] = 'ida';
	$basics['id'] = $id;
	$basics['query_add'] = ', league';
	$basics['query_add_value'] = ', '.LEAGUE;
	$basics['list_query'] = 'SELECT ida, name, grade FROM '.KML_PREFIX.'_award WHERE league='.LEAGUE;
	$basics['list_query_add'] = ' ORDER BY grade ASC, ida DESC';
	$basics['header'] = $alang['award'];
	$basics['header_edit'] = $alang['award'];
	$basics['list_items'] = array($alang['name'], $alang['grade']);
	$basics['list_values'] = array('{name}', '{grade}');
	$basics['list_size'] = '300:400';
	$basics['inf_del_query'] = $alang['qinfo_rem'].': '.$alang['award'];
	$basics['inf_del'] = $alang['info_rem'].': '.$alang['award'];
	$basics['inf_add'] = $alang['info_add'].': '.$alang['award'];
	$basics['inf_upd'] = $alang['info_sav'].': '.$alang['award'];
	$fields['name'] = array('head'=>$alang['name'], 'req'=>'Y', 'param1'=>'200', 'param2'=>'30');
	$fields['img'] = array('type'=>'F', 'head'=>$alang['file'], 'values'=>$ext, 'param2'=>array('bigl'=>DIR.'awards/', 'mini'=>0, 'name'=>'I'), 'values'=>$files);
	$fields['grade'] = array('type'=>'S', 'head'=>$alang['level'], 'values'=>$lvl, 'param2'=>'N');
	echo data_form($post,$get,$fields,$basics);
#*/
}

Function signups($dts){
	global $alang;
	option_head($alang['signup']);
	if($dts['opt1']){
		foreach($dts['idc'] as $ky=>$vl){
			if($vl=='Y'){
				if(LEAGUE_TYPE=='D') $qry = 'DELETE FROM '.KML_PREFIX.'_signup WHERE iduser='.(int)$ky;
				elseif(LEAGUE_TYPE=='T') $qry = 'DELETE FROM '.KML_PREFIX.'_signup WHERE idc='.(int)$ky;
				query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
			}
		}
		print($alang['signup_inf2']);
	}
	if($dts['opt0']){
		foreach($dts['idc'] as $ky=>$vl){
			if($vl=='Y' && $dts['inl'][$ky]!='Y'){
				$qry = 'INSERT INTO '.KML_PREFIX.'_in(idc, league) VALUES('.(int)$ky.', '.LEAGUE.')';
				query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
			}
		}
		print($alang['signup_inf3']);
	}
	$qry = 'SELECT idc FROM '.KML_PREFIX.'_in WHERE league='.LEAGUE;
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	while($row=mysql_fetch_assoc($rsl)) $in[$row['idc']] = 1;
	if(LEAGUE_TYPE=='D') $qry = 'SELECT c.login AS cname, c.iduser AS idc FROM '.KML_PREFIX.'_users AS c, '.KML_PREFIX.'_signup AS s WHERE s.league='.LEAGUE.' AND c.iduser=s.iduser ORDER BY cname, idc';
	else $qry = 'SELECT c.cname, c.idc FROM '.KML_PREFIX.'_clan AS c, '.KML_PREFIX.'_signup AS s WHERE s.league='.LEAGUE.' AND c.idc=s.idc ORDER BY c.cname, c.idc';
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	if(mysql_num_rows($rsl)>0){
		print('<form action="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=signup" method="post">
		<table><tr>');
		$k = -1;
		while($row=mysql_fetch_assoc($rsl)){
			foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
			if(++$k%4==0 && $k!=0) print('</tr><tr>');
			if($in[$row['idc']]==1){
				$ins = '*';
				$inl = '<input type="hidden" name="inl['.$row['idc'].']" value="Y"/>';
			}else{
				$ins = '';
				$inl = '';
			}
			print('<td>'.$inl.'<input class="brd0" type="checkbox" name="idc['.$row['idc'].']" value="Y"/><a href="index.php?'.KML_LINK_SL.'op=clans&amp;id='.$row['idc'].'&amp;so=sqd">'.$ins.$row['cname'].'</a></td>');
		}
		print('</tr>
			<tr><td colspan="4"><input class="brd0" type="checkbox" onclick="checkUncheckAll(this);"/> '.$alang['check_uncheck_all'].'</td></tr>
		<tr><td colspan="4"><input type="submit" name="opt0" value="'.strtoupper($alang['approve']).'"> &nbsp; &nbsp; <input type="submit" name="opt1" value="'.$alang['rem'].'"></td></tr>
		</table>
		</form>
		');
	}
	print($alang['signup_inf1']);
}

Function league_in($dts){
	global $alang;
	option_head($alang['league_in']);
	if($dts['opt1']){
		foreach($dts['idc'] as $ky=>$vl){
			if($vl=='Y'){
				$qry = 'DELETE FROM '.KML_PREFIX.'_in WHERE idc='.(int)$ky.' AND league='.LEAGUE;
				query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
			}
		}
		print($alang['inleague_inf1']);
	}
	if($dts['opt0']){
		foreach($dts['idc'] as $ky=>$vl){
			if($vl=='Y' && $dts['inl'][$ky]!='Y'){
				$qry = 'INSERT INTO '.KML_PREFIX.'_in(idc, league) VALUES('.(int)$ky.', '.LEAGUE.')';
				query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
			}
		}
		print($alang['signup_inf3']);
	}
	$qry = 'SELECT idc FROM '.KML_PREFIX.'_in WHERE league='.LEAGUE;
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	while($row=mysql_fetch_assoc($rsl)) $in[$row['idc']] = 1;
	$qry = 'SELECT cname, idc FROM '.KML_PREFIX.'_clan ORDER BY cname, idc';
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	if(mysql_num_rows($rsl)>0){
		print('<form action="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=inleague" method="post">
		<table><tr>');
		$k = -1;
		while($row=mysql_fetch_assoc($rsl)){
			foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
			if(++$k%4==0 && $k!=0) print('</tr><tr>');
			if($in[$row['idc']]==1){
				$ins = '*';
				$inl = '<input type="hidden" name="inl['.$row['idc'].']" value="Y"/>';
			}else{
				$ins = '';
				$inl = '';
			}
			print('<td>'.$inl.'<input class="brd0" type="checkbox" name="idc['.$row['idc'].']" value="Y"/><a href="index.php?'.KML_LINK_SL.'op=clans&amp;id='.$row['idc'].'&amp;so=sqd">'.$ins.$row['cname'].'</a></td>');
		}
		print('</tr>
		<tr><td colspan="4"><input class="brd0" type="checkbox" onclick="checkUncheckAll(this);"/> '.$alang['check_uncheck_all'].'</td></tr>
		<tr><td colspan="4"><input type="submit" name="opt0" value="'.strtoupper($alang['add']).'"> &nbsp; &nbsp; <input type="submit" name="opt1" value="'.$alang['rem'].'"></td></tr>
		</table>
		</form>
		');
	}
	print($alang['signup_inf1']);
}

Function map_list($post,$id,$opt){
	global $alang;
	$basics['table'] = KML_PREFIX.'_maps';
	$basics['op'] = 'map_list&amp;'.KML_LINK_SL2;
	$basics['opt'] = $opt;
	$basics['key'] = 'idmp';
	$basics['id'] = $id;
	$basics['query_add'] = ', league';
	$basics['query_add_value'] = ', '.LEAGUE;
	$basics['list_query'] = 'SELECT idmp, sname, fname FROM '.KML_PREFIX.'_maps WHERE league='.LEAGUE;
	$basics['list_query_add'] = ' ORDER BY sname';
	$basics['header'] = $alang['map_list'];
	$basics['header_edit'] = $alang['map_list'];
	$basics['list_items'] = array($alang['map']);
	$basics['list_values'] = array('{sname} - {fname}');
	$basics['list_size'] = '300:400';
	$basics['inf_del_query'] = $alang['qinfo_rem'].': '.$alang['map'];
	$basics['inf_del'] = $alang['info_rem'].': '.$alang['map'];
	$basics['inf_add'] = $alang['info_add'].': '.$alang['map'];
	$basics['inf_upd'] = $alang['info_sav'].': '.$alang['map'];
	$basics['search_form'] = 0;
	$basics['search_form_style'] = 4;

	$fields['sname'] = array('head'=>$alang['name'], 'req'=>'Y', 'param1'=>'200', 'param2'=>'20');#, 'search'=>1, 'search_type'=>'S', 'search_values'=>'SELECT sname FROM '.KML_PREFIX.'_maps WHERE league='.LEAGUE.' ORDER BY sname');
	$fields['fname'] = array('type'=>'I', 'head'=>$alang['fname'], 'param1'=>'200', 'param2'=>'50');
#	$files = array('jpg', 'jpeg');
#	$fields['screen'] = array('type'=>'F', 'head'=>$alang['screen'], 'param1'=>'30', 'param2'=>array('bigl'=>'maps/big/', 'smll'=>'maps/small/', 'smld'=>'150:100', 'ratio'=>'1', 'mini'=>1, 'descr'=>0), 'values'=>$files);
	echo data_form($post,$get,$fields,$basics,1);
}

Function servers_list($post,$id,$opt){
	global $alang;
	$basics['table'] = KML_PREFIX.'_servers';
	$basics['op'] = 'servers&amp;'.KML_LINK_SL2;
	$basics['opt'] = $opt;
	$basics['key'] = 'idse';
	$basics['id'] = $id;
	$basics['query_add'] = ', league';
	$basics['query_add_value'] = ', '.LEAGUE;
	$basics['list_query'] = 'SELECT idse, sname, address FROM '.KML_PREFIX.'_servers WHERE league='.LEAGUE;
	$basics['list_query_add'] = ' ORDER BY sname';
	$basics['header'] = $alang['server_list'];
	$basics['header_edit'] = $alang['server_list'];
	$basics['list_items'] = array($alang['name']);
	$basics['list_values'] = array('{sname}');
	$basics['list_size'] = '300:400';
	$basics['inf_del_query'] = $alang['qinfo_rem'].': '.$alang['server'];
	$basics['inf_del'] = $alang['info_rem'].': '.$alang['server'];
	$basics['inf_add'] = $alang['info_add'].': '.$alang['server'];
	$basics['inf_upd'] = $alang['info_sav'].': '.$alang['server'];
	$fields['sname'] = array('head'=>$alang['name'], 'req'=>'Y', 'param1'=>'200', 'param2'=>'50');
	$fields['address'] = array('head'=>$alang['server_address'], 'req'=>'Y', 'param1'=>'200', 'param2'=>'50');
	echo data_form($post,$get,$fields,$basics,1);
}

Function leagues($post,$id,$opt){
	global $alang;
	$basics['table'] = KML_PREFIX.'_config';
	$basics['op'] = 'leagues&amp;'.KML_LINK_SL2;
	$basics['opt'] = $opt;
	$basics['key'] = 'league';
	$basics['id'] = $id;
#	$basics['outputinfo'] = 'F';
	$basics['list_query'] = 'SELECT league, work_name, default_lang FROM '.KML_PREFIX.'_config ORDER BY league ASC';
	$basics['header'] = $alang['leagues'];
	$basics['header_edit'] = $alang['leagues'];
	$basics['list_items'] = array($alang['name'], $alang['lang']);
	$basics['list_values'] = array('{work_name}', '{default_lang}');
	$basics['list_size'] = '300:400';
	$basics['inf_del_query'] = $alang['qinfo_rem'].': '.$alang['league'];
	$basics['inf_del'] = $alang['info_rem'].': '.$alang['league'];
	$basics['inf_add'] = $alang['info_add'].': '.$alang['league'];
	$basics['inf_upd'] = $alang['info_sav'].': '.$alang['league'];
	$basics['output'] = 'F';
	$langs = get_langs();
	$league_types = array('D'=>$alang['duel'],'T'=>$alang['team']);
	$players_types = array('T'=>$alang['player_strict_link'], 'X'=>$alang['player_free_link']);
	$walkover = array('S'=>$alang['single'], 'M'=>$alang['summ']);
	$yesno = array('0'=>$alang['yes'], '1'=>$alang['no']);
	$yesno2 = array('Y'=>$alang['yes'], 'N'=>$alang['no']);
	$score_types = array('M'=>$alang['score_maps'], 'R'=>$alang['score_rounds']);
	global $forum_types;
	$fields['work_name'] = array('type'=>'I', 'head'=>$alang['name'], 'req'=>'Y', 'param1'=>'200', 'param2'=>'20');
	$fields['visible'] = array('type'=>'R', 'head'=>$alang['visible'], 'req'=>'Y', 'param1'=>' ', 'values'=>$yesno2);
	$fields['default_lang'] = array('type'=>'S', 'head'=>$alang['lang'], 'req'=>'Y', 'values'=>$langs, 'param2'=>'N');
	$fields['main_id'] = array('type'=>'I', 'head'=>$alang['main_id'], 'req'=>'Y', 'param1'=>'30', 'param2'=>'5');
	$fields['wars_id'] = array('type'=>'I', 'head'=>$alang['wars_id'], 'req'=>'Y', 'param1'=>'30', 'param2'=>'5');
	$fields['article_id'] = array('type'=>'I', 'head'=>$alang['article_id'], 'req'=>'Y', 'param1'=>'30', 'param2'=>'5');
//	$fields['roster_id'] = array('type'=>'I', 'head'=>$alang['roster_id'], 'req'=>'Y', 'param1'=>'30', 'param2'=>'5');
	$fields['league_type'] = array('type'=>'R', 'head'=>$alang['league_type'], 'req'=>'Y', 'values'=>$league_types, 'default'=>'T', 'param1'=>' ');
	$fields['player_type'] = array('type'=>'R', 'head'=>$alang['player_type'], 'req'=>'Y', 'values'=>$players_types, 'default'=>'T', 'param1'=>'<br/>');
	$fields['score'] = array('type'=>'R', 'head'=>$alang['score_type'], 'req'=>'Y', 'values'=>$score_types, 'default'=>'M', 'param1'=>'<br/>');
	$fields['score_details'] = array('type'=>'R', 'head'=>$alang['score_details'], 'values'=>$yesno2, 'default'=>'N', 'param1'=>' ');
	$fields['address'] = array('type'=>'I', 'head'=>$alang['league_address'], 'req'=>'Y', 'param1'=>'200', 'param2'=>'80');
	$fields['irc_server'] = array('type'=>'I', 'head'=>$alang['irc_server'], 'param1'=>'200', 'param2'=>'30');
#	$fields['user_news'] = array('type'=>'R', 'head'=>$alang['user_news'], 'param1'=>' ', 'values'=>$yesno2, 'default'=>'N');
	$fields['user_clans'] = array('type'=>'S', 'head'=>$alang['user_clans'], 'values'=>$yesno2, 'param2'=>'A');
	$fields['walkover'] = array('type'=>'S', 'head'=>$alang['walkovers'], 'values'=>$walkover, 'param2'=>'A');
	$fields['display_head'] = array('type'=>'H', 'head'=>$alang['display_head']);
	//get skins
	$handler = opendir('skin/');
	while($dir=readdir($handler)){
		if(file_exists('skin/'.$dir.'/tmpl/layout_functions.php')) $skins[] = 'skin/'.$dir.'/';
	}
	$fields['skin'] = array('type'=>'S', 'head'=>$alang['skin'], 'req'=>'Y', 'values'=>$skins, 'default'=>'skin/default/', 'param2'=>'N');
	$fields['no_flags'] = array('type'=>'R', 'head'=>$alang['no_flags'], 'values'=>$yesno, 'default'=>'0', 'param1'=>' ');
	$fields['poll_live_time'] = array('type'=>'I', 'head'=>$alang['poll_live_time'], 'req'=>'Y', 'param1'=>'30', 'param2'=>'5', 'descr'=>$alang['poll_live_time_title'], 'default'=>'2');
	$fields['news_limit_show'] = array('type'=>'I', 'head'=>$alang['news_limit_show'], 'req'=>'Y', 'param1'=>'30', 'param2'=>'5', 'default'=>'5');
	$fields['short_latest_news'] = array('type'=>'I', 'head'=>$alang['short_latest_news'], 'req'=>'Y', 'param1'=>'30', 'param2'=>'5', 'default'=>'8');
	$fields['short_latest_matches'] = array('type'=>'I', 'head'=>$alang['short_latest_matches'], 'req'=>'Y', 'param1'=>'30', 'param2'=>'5', 'default'=>'6');

	$fields['forum_head'] = array('type'=>'H', 'head'=>$alang['forum_head']);
	$fields['forum'] = array('type'=>'S', 'head'=>$alang['forum_type'], 'values'=>$forum_types, 'param2'=>'N');
	$fields['forum_prefix'] = array('type'=>'I', 'head'=>$alang['forum_prefix'], 'param1'=>'100', 'param2'=>'20', 'default'=>'prefix');
	if($post['address']){
		if(!ereg('(.*)/$', $post['address'])) $post['address'] .= '/';
	}
	if($post['skin']){
		if(!ereg('(.*)/$', $post['skin'])) $post['skin'] .= '/';
	}
	$info = data_form($post,$get,$fields,$basics);
	echo $info['content'];
	if($post['opt1'] && $info['id']){
	if($post['league_type']=='D') $status_pstats = 'N'; else $status_pstats = 'Y';
		$qry = 'INSERT INTO `league_menu`(league, visible, privilages_global, privilages_local, head, head_lang, `link`, `target`, `local`, `column`, `row`) VALUES('.$info['id'].', "Y", 0, 0, "", "news", "index.php?op=news", "S", "Y", 0, 1),('.$info['id'].', "Y", 0, 0, "", "rules", "index.php?op=rules", "S", "Y", 0, 2),('.$info['id'].', "Y", 1, 0, "", "sign", "index.php?op=sign", "S", "Y", 0, 4),('.$info['id'].', "Y", 0, 0, "", "clans", "index.php?op=clans", "S", "Y", 0, 3),('.$info['id'].', "Y", 0, 0, "", "awards", "index.php?op=awards", "S", "Y", 0, 5),('.$info['id'].', "Y", 0, 0, "", "schedule", "index.php?op=schedule", "S", "Y", 0, 6),('.$info['id'].', "Y", 0, 0, "", "all_matches", "index.php?op=all_matches", "S", "Y", 0, 8), ('.$info['id'].', "Y", 0, 0, "", "cls_stats", "index.php?op=cstats", "S", "Y", 0, 7), ('.$info['id'].', "'.$status_pstats.'", 0, 0, "", "pls_stats", "index.php?op=pstats", "S", "Y", 0, 9), ('.$info['id'].', "Y", 0, 0, "", "lg_stats", "index.php?op=lstats", "S", "Y", 0, 10), ('.$info['id'].', "Y", 0, 0, "", "interviews", "index.php?op=inter", "S", "Y", 0, 14), ('.$info['id'].', "Y", 0, 0, "", "polls", "index.php?op=polls", "S", "Y", 0, 13), ('.$info['id'].', "Y", 0, 0, "", "admin_team", "index.php?op=crew", "S", "Y", 0, 15), ('.$info['id'].', "Y", 0, 0, "", "users", "index.php?op=users", "S", "Y", 0, 16)';
		query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	}
}

Function league_config($post,$id,$opt){
	global $alang;
	global $countries;
	$basics['table'] = KML_PREFIX.'_config';
	$basics['op'] = 'cfg&amp;'.KML_LINK_SL2;
	$basics['opt'] = $opt;
	$basics['key'] = 'league';
	$basics['id'] = $id;
	$basics['header'] = $alang['config'];
	$basics['header_edit'] = $alang['config'];
	$basics['single_item'] = LEAGUE;
	$basics['inf_upd'] = $alang['info_sav'].': '.$alang['config'];

	$signup = array('H'=>$alang['hidden'], 'E'=>$alang['enable'], 'D'=>$alang['disable']);
	$yesno = array('Y'=>$alang['yes'], 'N'=>$alang['no']);

	$fields['signup'] = array('type'=>'S', 'head'=>$alang['signup'], 'values'=>$signup, 'param2'=>'A');
	$fields['signup_dlimit'] = array('type'=>'D', 'head'=>$alang['signup_dlimit'], 'param1'=>'D', 'param2'=>array('Y'=>date('Y').':'.(date('Y')+1)));
	$fields['signup_tlimit'] = array('type'=>'N', 'head'=>$alang['signup_tlimit'], 'param1'=>'4');
//	$fields['dispute_limit_before'] = array('type'=>'N', 'head'=>$alang['dispute_limit_before'], 'param1'=>'4');
//	$fields['dispute_limit_after'] = array('type'=>'N', 'head'=>$alang['dispute_limit_after'], 'param1'=>'4');
	$fields['country'] = array('type'=>'S', 'head'=>$alang['def_country'], 'values'=>$countries, 'param2'=>'A');
	$fields['name'] = array('head'=>$alang['lg_name'], 'req'=>'Y', 'param1'=>'200', 'param2'=>'50');
	if(LEAGUE_TYPE=='T'){
		$fields['max_players'] = array('type'=>'N', 'head'=>$alang['max_players'], 'param1'=>'4');
		$fields['transfers'] = array('type'=>'S', 'head'=>$alang['transfers'], 'values'=>$yesno, 'param2'=>'A');
		$fields['block_ident'] = array('type'=>'S', 'head'=>$alang['block_ident'], 'values'=>$yesno, 'param2'=>'A');
	}
	$files = array('jpg', 'jpeg');
	echo data_form($post,$get,$fields,$basics,1);
}

Function point_system($post,$id,$opt){
	global $alang;
	$basics['table'] = KML_PREFIX.'_points';
	$basics['op'] = 'point_system&amp;'.KML_LINK_SL2;
	$basics['opt'] = $opt;
	$basics['key'] = 'idps';
	$basics['id'] = $id;
	$basics['query_add'] = ', league';
	$basics['query_add_value'] = ', '.LEAGUE;
	$basics['list_query'] = 'SELECT idps, points1, points2, score1, draw, score2 FROM '.KML_PREFIX.'_points WHERE league='.LEAGUE;
	$basics['list_query_add'] = ' ORDER BY score1, score2';
	$basics['header'] = $alang['points_standards'];
	$basics['header_edit'] = $alang['points_standards'];
	$basics['list_items'] = array($alang['scores'], $alang['points']);
	$basics['list_values'] = array('{score1}:{draw}:{score2}', '{points1}:{points2}');
	$basics['list_size'] = '300:400';
	$basics['inf_del_query'] = $alang['qinfo_rem'].': '.$alang['points_standard'];
	$basics['inf_del'] = $alang['info_rem'].': '.$alang['points_standard'];
	$basics['inf_add'] = $alang['info_add'].': '.$alang['points_standard'];
	$basics['inf_upd'] = $alang['info_sav'].': '.$alang['points_standard'];

	$fields['score1'] = array('type'=>'N', 'head'=>$alang['prule_score1'], 'param1'=>'4');
	$fields['draw'] = array('type'=>'N', 'head'=>ucfirst($alang['draws']), 'param1'=>'4');
	$fields['score2'] = array('type'=>'N', 'head'=>$alang['prule_score2'], 'param1'=>'4');
	$fields['points1'] = array('type'=>'N', 'head'=>$alang['prule_points1'], 'param1'=>'4');
	$fields['points2'] = array('type'=>'N', 'head'=>$alang['prule_points2'], 'param1'=>'4');
	echo data_form($post,$get,$fields,$basics);
}

Function special_table($post,$id,$opt){
	global $alang;
	if(LEAGUE_TYPE=='D') $qry = 'SELECT u.login AS tag, x.idc FROM '.KML_PREFIX.'_users AS u, '.KML_PREFIX.'_in AS x WHERE x.league='.LEAGUE.' AND x.idc=u.iduser ORDER BY tag';
	else $qry = 'SELECT c.tag, c.idc FROM '.KML_PREFIX.'_clan AS c, '.KML_PREFIX.'_in AS x WHERE x.league='.LEAGUE.' AND x.idc=c.idc ORDER BY tag';
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	while($row=mysql_fetch_assoc($rsl)) $teams[$row['idc']] = $row['tag'];
	$basics['table'] = KML_PREFIX.'_special_table';
	$basics['op'] = 'special_table&amp;'.KML_LINK_SL2;
	$basics['opt'] = $opt;
	$basics['key'] = 'idst';
	$basics['id'] = $id;
	$basics['query_add'] = ', league, ids ';
	$basics['query_add_value'] = ', '.LEAGUE.', '.IDS;
	$basics['list_query'] = 'SELECT idst, points, `date`, `idc` FROM '.KML_PREFIX.'_special_table WHERE league='.LEAGUE;
	$basics['list_query_add'] = ' ORDER BY idst ASC';
	$basics['header'] = $alang['special_table'];
	$basics['header_edit'] = $alang['special_table'];
	$basics['list_items'] = array($alang['clan'],$alang['date'],$alang['points']);
	$basics['list_values'] = array('[teams;idc]', '^date^', '{points}');
	$basics['list_size'] = '300:400';
	$basics['inf_del_query'] = $alang['qinfo_rem'].': '.$alang['specialtab_item'];
	$basics['inf_del'] = $alang['info_rem'].': '.$alang['specialtab_item'];
	$basics['inf_add'] = $alang['info_add'].': '.$alang['specialtab_item'];
	$basics['inf_upd'] = $alang['info_sav'].': '.$alang['specialtab_item'];
	$basics['tabinfos'] = array('teams'=>$teams);
	$fields['idc'] = array('type'=>'S', 'head'=>$alang['clan'], 'values'=>$teams, 'param2'=>'A');
	$fields['date'] = array('type'=>'D', 'head'=>$alang['date'], 'param1'=>'D', 'req'=>'Y', 'param2'=>array('Y'=>(date('Y')-1).':'.(date('Y')+1)));
	$fields['points'] = array('type'=>'N', 'head'=>$alang['points'], 'param1'=>'5.2', 'req'=>'Y');
	$fields['description'] = array('head'=>$alang['descr'], 'req'=>'Y', 'param1'=>'200', 'param2'=>'50');
	echo data_form($post,$get,$fields,$basics);
}

?>
