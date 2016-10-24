<?php

#HTML
Function index_header($alang='pl'){
	?>
	<!doctype html public "-//w3c//dtd html 4.0 transitional//en">
	<html>
	<head>
	<title>KMleague :: admin</title>
	<?php println('<meta http-equiv="content-type" content="text/html; charset='.ENCODING.'">'); ?>
	<meta name="author" content="KMprojekt">
	<link rel="stylesheet" type="text/css" href="css_admin.css"/>
	<link rel="stylesheet" type="text/css" href="adm/gosu.css"/>
	<script type="text/javascript" src="adm/tooltip.js"></script>
	<script type="text/javascript" src="adm/framework.js"></script>
	<script type="text/javascript" src="adm/admin.js"></script>
	<script type="text/javascript" src="adm/gosuIe5.js"></script>
	<script type="text/javascript" src="adm/gosuDropDownMenuX.js"></script>
	</head>
	<body>
	<?php
}

Function bottom(){
	?>
	</body>
	</html>
	<?php
}

Function adm_log_form(){
	print('<form method=post action="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=login">
	login: <input TYPE="text" NAME="login" SIZE="10"> pass: <input type="password" name="passw" size="10"> <input type="submit" value="LogIn"><br/><br/>
	<a href="index.php?'.KML_LINK_SL2.'">&laquo BACK</a>');
	exit;
}

Function action_info($type,$info,$xopt=''){
	global $alang;
	if($type=='x'){
		if(!$xopt) $xopt = 'opt';
		print('<div class="action_info">'.$alang['qinfo_rem'].': '.$info.'? <input type="submit" name="'.$xopt.'" value="'.$alang['yes'].'"></div>');
	}else{
		$types = array('r'=>$alang['info_rem'], 'a'=>$alang['info_add'], 's'=>$alang['info_sav'], 'f'=>$alang['file_missing'], 're'=>$alang['remove_err']);
		print('<div class="action_info">'.$types[$type].': '.$info.'.</div>');
	}
}

#BANS

Function ban($ip,$opt){
	global $alang;
	option_head($alang['banned_ips']);
	switch($opt){
		case $alang['add']:{
			$qry = SQL('INSERT INTO '.KML_PREFIX.'_bans(ip) VALUES(%s)', $ip);
			if (query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) action_info('a',$alang['ban'].' ['.$ip.']'); else println($alang['mysql_err'].' / '.$alang['adrr_aban']);
			break;
		}
		case $alang['delete']:{
			$qry = SQL('DELETE FROM '.KML_PREFIX.'_bans WHERE ip=%s', $ip);
			if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) action_info('r',$alang['ban'].' ['.$ip.']');
			break;
		}
	}
	println('<form method=post action="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=ban">
	IP: <input type="text" name="ip"/> <input type="submit" name="opt" value="'.$alang['add'].'"/> <input type="submit" name="opt" value="'.$alang['delete'].'"/>
	</form>');
	$qry = 'SELECT ip FROM '.KML_PREFIX.'_bans';
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	while($row = mysql_fetch_assoc($rsl)){
		foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
		println($row['ip'].' | <a href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=ban&ip='.$row['ip'].'&opt='.$alang['delete'].'">'.$alang['delete'].'</a><br/>');
	}
}

#USERS

Function search_user($search,$type=1){
	global $alang;
	option_head($alang['user_edit']);
	if($type==2) $lnk = 'a';
	println('<table cellpadding="10"><tr><td>
	<form method="post" action="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op='.$lnk.'search_user">
	'.$alang['userlog'].': <input type="text" name="search" value="'.$search.'"> <input type="submit" value="'.strtoupper($alang['search']).'">
	</form>
	</td><td>
	<form method="post" action="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op='.$lnk.'edit_user">
	'.$alang['userid'].': <input type="text" name="id" size="5"> <input type="submit" value="'.$alang['edit'].'">
	</form>
	</td></tr></table>');
	if(!empty($search)){
		$qry = SQL('SELECT login, iduser FROM '.KML_PREFIX.'_users WHERE login LIKE %s', '%'.$search.'%');
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		if(mysql_num_rows($rsl)>0){
			println('<div class="head">'.$alang['search_res'].'</div>');
			while($row=mysql_fetch_assoc($rsl)){
				foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
				println('<a href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op='.$lnk.'edit_user&id='.$row['iduser'].'">'.$row['login'].'</A><br/>');
			}
		}else println('<div class="head">'.$alang['no_result'].'</div>');
	}
}

Function user($id){
	global $alang;
	$id = intval($id);
	$qry = 'SELECT iduser, login, pass, mail, grants, added, lastlog, name, birth, clan, clan_www, home_www, city, avatar, info, show_mail, country, comtype1, comtype2, comm1, comm2 FROM '.KML_PREFIX.'_users WHERE iduser='.(int)$id;
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	if(mysql_num_rows($rsl)!=1) die($alang['acc_err']);
	$row = mysql_fetch_assoc($rsl);
	foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
	option_head($alang['user_edit'].': '.$row['login']);
	$rok = substr($row['birth'],0,4);
	$miesiac = substr($row['birth'],4,2);
	$dzien = substr($row['birth'],6,2);
	$portal_function = array(1=>$alang['user'], 2=>$alang['assistant'], 3=>$alang['admin'], 4=>$alang['webmaster']);
	println('<table>
	<tr><td>');
	$avatar = DIR.'_avatars/'.$row['iduser'].'.jpg';
	$avatar_img = DIR.'_avatars/'.$row['iduser'].'.jpg';
	if($row['avatar']=='1' && file_exists($avatar)) print('<IMG src="'.$avatar_img.'" alt="avatar">'); else print('<IMG src="'.SKIN.'img/no.jpg" alt="avatar">');
	println('</td><td class="head" valign="top">'.$row['login'].'</td></tr>');
	if($row['grants']>0) println('<tr><td class="hd">'.$alang['portal_function'].':</td><td>'.$portal_function[$row['grants']].'</td></tr>');
	if($row['name']) println('<tr><td class="hd">'.$alang['name_surn'].':</td><td>'.$row['name'].'</td></tr>');
	if($row['city']) println('<tr><td class="hd">'.$alang['city'].':</td><td>'.$row['city'].'</td></tr>');
	if($rok!='' && $miesiac!='' && $dzien!='') println('<tr><td class="hd">'.$alang['birth'].':</td><td>'.$dzien.'.'.$miesiac.'.'.$rok.'</td></tr>');
	if($row['show_mail']==1) println('<tr><td class="hd">E-mail:</td><td><a href="mailto:'.$row['mail'].'">'.$row['mail'].'</A></td></tr>');
	if($row['clan']==1) println('<tr><td class="hd">'.$alang['clan'].':</td><td><a target="_blank" href="'.$row['cwww'].'">'.$row['clan'].'</A></td></tr>');
	if($row['home_www']) println('<tr><td class="hd">'.$alang['homesite'].':</td><td><a target="_blank" href="'.$row['home_www'].'">'.$row['home_www'].'</A></td></tr>');
	if($row['comtype1']) println('<tr><td class="hd">'.$row['comtype1'].':</td><td class="content1">'.$row['comm1'].'</td></tr>');
	if($row['comtype2']) println('<tr><td class="hd">'.$row['comtype2'].':</td><td class="content1">'.$row['comm2'].'</td></tr>');
	println('<tr><td class="hd">'.$alang['reg_date'].':</td><td>'.show_date($row['added'],2).'</td></tr>
	<tr><td class="hd">'.$alang['last_log'].':</td><td>');
	if($row['lastlog']>0) print(show_date($row['lastlog'],2)); else print($alang['never_login']);
	println('</td></tr>');
	if($row['info']) println('<tr><td class="hd">'.$alang['other_info'].':</td><td>'.$row['info'].'</td></tr>');
	println('</table><br/>
	<table>
	<form method="post" action="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=save_prof"><input type="hidden" name="iduser" value="'.$id.'">');
	$gqry = 'SELECT iduser, wgrant, descr FROM '.KML_PREFIX.'_grants WHERE service='.MAIN_ID.' AND iduser='.$id;
	$grsl = query(__FILE__,__FUNCTION__,__LINE__,$gqry,0);
	if(mysql_num_rows($grsl)>0){
		$grow = mysql_fetch_assoc($grsl);
		foreach($grow as $ky=>$vl) $grow[$ky] = intoBrowser($vl);
	}
	for($i=0;$i<4;$i++) $uprawnienia[] = $i;
	print('<tr><td class="hd">'.$alang['grants'].': <select name="grants">'.array_norm($uprawnienia, $grow['wgrant']).'</select> '.$alang['function'].': <input type="text" name="descr" value="'.$grow['descr'].'" maxlength="30" size="20"><input type="submit" value="'.$alang['save'].'"> </td></tr>
	</form>
	</table>
	'.$alang['grants_info']);
}

Function save_prof($dts){
	global $alang;
	option_head($alang['user_edit']);
	$qry = 'SELECT iduser FROM '.KML_PREFIX.'_grants WHERE service='.MAIN_ID.' AND iduser='.(int)$dts['iduser'];
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	if($dts['grants'] > 0){
		if(mysql_num_rows($rsl) == 1){
			$qry = SQL('UPDATE '.KML_PREFIX.'_grants SET wgrant=%d, descr=%s WHERE service='.MAIN_ID.' AND iduser=%d', $dts['grants'], $dts['descr'], $dts['iduser']);
			if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) action_info('s',$alang['admin']);
		}else{
			$qry = SQL('INSERT INTO '.KML_PREFIX.'_grants(iduser, service, wgrant, descr) VALUES(%d, "'.MAIN_ID.'", %d, %s)', $dts['iduser'], $dts['grants'], $dts['descr']);
			if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) action_info('a',$alang['admin']);
		}
	}else{
		$qry = 'DELETE FROM '.KML_PREFIX.'_grants WHERE service='.MAIN_ID.' AND iduser='.(int)$dts['iduser'];
		if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) action_info('r',$alang['admin']);
	}
}

Function option_head($title){
	print('<div class="menu_head2">&nbsp;'.$title.'</div>
	<div id="main_content">');
}

?>
