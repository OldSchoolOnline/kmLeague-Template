<?php

Function form_reg(){
	global $lang;
	if(banned($_SERVER['REMOTE_ADDR'])){
		$rtr = $lang['ban'];
		return $rtr;
	}
	$qry = 'DELETE FROM '.KML_PREFIX.'_users_auth WHERE added<'.(time()-86400*KML_CLEAN_REG_PAS);
	query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	$qry = 'DELETE FROM '.KML_PREFIX.'_users_pass WHERE added<'.(time()-86400*KML_CLEAN_REG_PAS);
	query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	$rtr = content_line('T',$lang['register']);
	$rtr .= '<td>';
	if(KML_REGISTRATION==0) $rtr .= $lang['registration_off'];
	else{
		$rtr .= '<form method="post" action="index.php?'.KML_LINK_SL.'op=add_user">
		<table class="tab_brdr" cellspacing="1" cellpadding="5">
		<tr><td class="tab_head2">'.$lang['login'].' (3-20)</td><td class="content1"><input type="text" name="nlogin" maxlength="20" size="30" /></td></tr>
		<tr><td class="tab_head2">'.$lang['password'].' (6-20)</td><td class="content1"><input type="password" name="npassw" maxlength="20" size="30" /></td></tr>
		<tr class="tab_con"><td class="tab_head2">'.$lang['rep_password'].' (6-20)</td><td class="content1"><input type="password" name="npassw2" maxlength="20" size="30" /></td></tr>
		<tr class="tab_con"><td class="tab_head2">E-mail (6-30)</td><td class="content1"><input type="text" name="nmail" maxlength="30" size="30" /></td></tr>
		<tr><td colspan="2" align="right" class="content2"><input type="submit" value="'.$lang['submit'].'" /></td></tr>
		</table>
		</form>
		<br/><div class="note"><span class="bold">'.$lang['notes'].':</span> '.$lang['reg_info'];
		if(KML_ACCOUNT_AUTH=='on') $rtr .= $lang['reg_info2'];
		$rtr .= '</div><br/>';
	}
	$rtr .= content_line('B');
	return $rtr;
}

Function add_user($dts){
	global $lang;
	$rtr = content_line('T',$lang['register']);
	$rtr .= '<td>';
	if(banned($_SERVER['REMOTE_ADDR'])){
		$rtr .= $lang['ban'];
		return $rtr;
	}
	if(KML_REGISTRATION==0){
		$rtr .= $lang['registration_off'];
		return $rtr;
	}
	$qry = SQL('SELECT login FROM '.KML_PREFIX.'_users WHERE login=%s OR mail=%s', $dts['nlogin'], $dts['nmail']);
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	if(mysql_num_rows($rsl)>0) $err_login2 = 1;
	$qry = SQL('SELECT login FROM '.KML_PREFIX.'_users_auth WHERE login=%s OR mail=%s', $dts['nlogin'], $dts['nmail']);
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	if(mysql_num_rows($rsl)>0) $err_login2 = 1;
	if(@eregi($dts['nlogin'],$dts['npassw'])) $err_passc = 1;
	if(strlen($dts['nlogin'])<3) $err_login = 1;
	if($dts['npassw'] != $dts['npassw2']) $err_pass = 1;
	if(strlen($dts['npassw'])<6) $err_passl=1;
	if($dts['nmail'] =="" || !eregi("^[0-9a-zA-Z -_.]+@[0-9a-zA-Z -]+\.[0-9a-zA-Z -_.]+$",$dts['nmail'])) $err_mail = 1;
	if($err_login==1 || $err_pass==1 || $err_passl==1 || $err_mail==1 || $err_passc==1 || $err_login2==1){
		if($err_mail==1) $rtr .= $lang['ereg_mail'];
		if($err_login==1) $rtr .= $lang['ereg_login'];
		if($err_login2==1) $rtr .= $lang['ereg_login2'];
		if($err_pass==1) $rtr .= $lang['ereg_pass'];
		if($err_passl==1) $rtr .= $lang['ereg_pass2'];
		if($err_passc==1) $rtr .= $lang['ereg_pass3'];
		$error_msg = 1;
	}
	if($error_msg!=1){
		if(KML_ACCOUNT_AUTH=='on'){
			$string = md5(uniqid(""));
			$qry = SQL('INSERT INTO '.KML_PREFIX.'_users_auth(login, pass, mail, added, auth) VALUES(%s, %s, %s, "'.time().'", "'.$string.'")', $dts['nlogin'], md5($dts['npassw']), $dts['nmail']);
			if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) $rtr .= $lang['reg_add2'];
			$mail_content = $lang['login'].': '.$dts['nlogin']."\n".$lang['password'].': '.$dts['npassw']."\n\n".$lang['reg_mail']."\n\n".ADDRESS.'index.php?op=auth&stg='.$string;
			$title = $lang['new_account'].' '.ADDRESS;
			@mail($dts['nmail'], $title, $mail_content, 'From: '.KML_ADMIN_MAIL);
		}else{
			$id = create_account_kml($dts['npassw'],$dts['nlogin'],$dts['nmail']);
			$rtr .= $lang['reg_add'];
			$mail_content = $lang['login'].': '.$dts['login']."\n".$lang['password'].': '.$dts['passw'];
			$title = $lang['new_account'].' '.ADDRESS;
			@mail($dts['mail'], $title, $mail_content, 'From: '.KML_ADMIN_MAIL);
			welcome_pm($id);
			if(FORUM) reg_forum($id);
		}
	}
	$rtr .= '<br/><br/>'.content_line('B');
	return $rtr;
}

Function forgot_pass($mail){
	global $lang;
	$rtr = content_line('T',$lang['fpass']);
	$rtr .= '<td align="center">';
	if($mail){
		$qry = SQL('SELECT iduser, login FROM '.KML_PREFIX.'_users WHERE mail=%s', $mail);
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		if(mysql_num_rows($rsl)==1){
			$row = mysql_fetch_assoc($rsl);
			foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
			$qry = 'SELECT iduser FROM '.KML_PREFIX.'_users_pass WHERE iduser='.$row['iduser'];
			$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
			if(mysql_num_rows($rsl)>0) $rtr .= $lang['pass_sent'];
			else{
				$string = md5(uniqid(""));
				$newpass = substr(md5(uniqid("")),0,15);
				$qry = SQL('INSERT INTO '.KML_PREFIX.'_users_pass(iduser, pass, added, auth) VALUES("'.$row['iduser'].'", "'.md5($newpass).'", "'.time().'", "'.$string.'")');
				if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) $rtr .= $lang['npass'];
				$mail_content = $lang['login'].': '.$row['login']."\n".$lang['new_pass'].': '.$newpass."\n\n".$lang['passc_mail']."\n\n".ADDRESS.'index.php?op=npass&stg='.$string;
				$title = $lang['pass_change'].' '.ADDRESS;
				@mail($mail, $title, $mail_content, 'From: '.KML_ADMIN_MAIL);
				$rtr .= '<br/><br/>';
			}
		}else $rtr .= $lang['mail_err'];
	}else{
		$rtr .= '<table class="tab_brdr" cellspacing="1" cellpadding="5">
		<form method="post" action="index.php?'.KML_LINK_SL.'op=fpass">
		<tr><td class="tab_head3">'.$lang['ins_mail'].'</td></tr>
		<tr><td class="content1" align="center"><input type="text" name="mail" maxlength="30" size="30"> &nbsp; <input type="submit" value="'.$lang['submit'].'"></td></tr>
		</form>
		</table><br/>';
	}
	$rtr .= content_line('B');
	return $rtr;
}

Function authorize_pass($dts){
	global $lang;
	if(!$dts['stg']){
		$rtr = $lang['sure'];
		return $rtr;
	}
	$rtr = content_line('T',$lang['fpass']);
	$rtr .= '<td align="center">';
	$qry = SQL('SELECT iduser, pass FROM '.KML_PREFIX.'_users_pass WHERE auth=%s', $dts['stg']);
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	if(mysql_num_rows($rsl)==1){
		$row = mysql_fetch_assoc($rsl);
		$qry = 'UPDATE '.KML_PREFIX.'_users SET pass="'.$row['pass'].'" WHERE iduser="'.$row['iduser'].'"';
		if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) $rtr .= $lang['pass_app'];
		$qry = SQL('DELETE FROM '.KML_PREFIX.'_users_pass WHERE auth=%s', $dts['stg']);
		query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		if(FORUM) pass_forum($row['iduser'],$row['pass']);
	}else $rtr .= $lang['pass_already_auth'];
	$rtr .= content_line('B');
	return $rtr;
}

Function authorize($dts){
	global $lang;
	if(!$dts['stg']){
		$rtr .= $lang['sure'];
		return $rtr;
	}
	if(KML_REGISTRATION==0){
		$rtr .= $lang['registration_off'];
		return $rtr;
	}
	$rtr = content_line('T',$lang['authorize']);
	$rtr .= '<td align="center">';
	$qry = SQL('SELECT login, pass, mail FROM '.KML_PREFIX.'_users_auth WHERE auth=%s', $dts['stg']);
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	if(mysql_num_rows($rsl)==1){
		$row = mysql_fetch_assoc($rsl);
		foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
		$id = create_account_kml($row['pass'],$row['login'],$row['mail'],0);
		$rtr .= $lang['reg_app'];
		$id = mysql_insert_id();
		welcome_pm($id);
		if(FORUM) reg_forum($id);
		$qry = SQL('DELETE FROM '.KML_PREFIX.'_users_auth WHERE auth=%s', $dts['stg']);
		query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	}else $rtr .= $lang['account_already_auth'];
	$rtr .= content_line('B');
	return $rtr;
}

Function welcome_pm($id){
	global $lang;
	$msg = $lang['welcome_msg'];
	$title = $lang['welcome_title'];
	send_pm($id,1,$title,$msg,0);
}

?>
