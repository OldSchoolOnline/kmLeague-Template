<?php

Function show_profile($id){
	global $lang;
	global $countries;
	$id = intval($id);
	$qry = 'SELECT iduser, login, mail, grants, added, lastlog, name, birth, comtype1, comm1, comtype2, comm2, clan, clan_www, home_www, city, avatar, info, show_mail, country FROM '.KML_PREFIX.'_users WHERE iduser='.$id;
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	if(mysql_num_rows($rsl)!=1) return;
	$row = mysql_fetch_assoc($rsl);
	foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
	if($_GET['op']!='clans'){
		$rtr .= content_line('T',$lang['profile'].': '.$row['login']);
		$rtr .= '<td valign="top" align="center">';
	}
	$rtr .= '<br/>';
	$rok = substr($row['birth'],0,4);
	$miesiac = substr($row['birth'],4,2);
	$dzien = substr($row['birth'],6,2);
	if($_SESSION['dl_grants']['main']>0) $rtr .= '<a class="menu" href="index.php?'.KML_LINK_SL.'op=pm&amp;id=3&amp;nick='.$row['login'].'">'.$lang['send_mess'].'</a><br/><br/>';
	$rtr .= '<table class="tab_brdr" cellspacing="1" cellpadding="5" width="300">';
	$rtr .= '<tr><td class="avatar">';
	$avatar = DIR.'_avatars/'.$row['iduser'].'.jpg';
	if($row['avatar']=='1' && file_exists($avatar)) $rtr .= '<img alt="avatar" src="'.$avatar.'" />'; else $rtr .= '<img alt="avatar" src="'.SKIN.'img/no.jpg" />';
	$rtr .= '</td><td class="tab_head3" valign="middle">';
	if($row['name']!='') $rtr .= $row['name'];
	if($dl_grants>2 && $row['grants']<5){
		$rtr .= '<form method="post" action="admin.php?'.KML_LINK_SL.'op=edit_user&amp;id='.$row['iduser'].'"><input type="submit" value="'.$lang['edit'].'" /></form>';
	}
	$rtr .= '</td></tr>';
	if($row['grants']>1){
		$rtr .= '<tr><td class="content3">'.$lang['function'].':</td><td class="content1">';
		if($row['grants']==4){
			$rtr .= 'webmaster';
		}else{
			if($row['grants']==2) $rtr .= $lang['assistant'];
			if($row['grants']==3) $rtr .= $lang['prof_admin'];
		}
		$rtr .= '</td></tr>';
	}
	$rtr .= '<tr><td class="content3">'.$lang['city'].':</td><td class="content1">'.$row['city'].'</td></tr>
	<tr><td class="content3">'.$lang['country'].':</td><td class="content1">'.$countries[$row['country']].' <img alt="'.$lang['flag'].'" src="'.DIR.'_country/'.$row['country'].'.gif" /></td></tr>';
	if($rok || $dzien || $miesiac){
		if($rok>0) $data = $rok; else $data = 'XXXX';
		$data .= '-';
		if($miesiac>0) $data .= $miesiac; else $data .= 'XX';
		$data .= '-';
		if($dzien>0)$data .= $dzien; else $data .= 'XX';
	}
	if($data) $rtr .= '<tr><td class="content3">'.$lang['birth'].':</td><td class="content1">'.$data.'</td></tr>';
	if(($_SESSION['dl_grants']['main']>0 && $row['show_mail']=='R') || ($row['show_mail']=='Y' && $_SESSION['dl_grants']['main']>0)) $rtr .= '<tr><td class="content3">E-Mail:</td><td class="content1"><a class="link" href="mailto:'.$row['mail'].'">'.$row['mail'].'</a></td></tr>';
	elseif($row['show_mail']=='Y'){
		$row['mail'] = str_replace('@', '[at]', $row['mail']);
		$rtr .= '<tr><td class="content3">E-Mail:</td><td class="content1">'.$row['mail'].'</td></tr>';
	}
	if($row['clan']){
		$rtr .= '<tr><td class="content3">'.$lang['prof_clan'].':</td><td class="content1">';
		if($row['clan_www']) $rtr .= '<a class="link" target="_blank" href="'.$row['clan_www'].'">';
		$rtr .= $row['clan'];
		if($row['clan_www']) $rtr .= '</a>';
		$rtr .= '</td></tr>';
	}
	if($row['comtype1']) $rtr .= '<tr><td class="content3">'.$row['comtype1'].':</td><td class="content1">'.$row['comm1'].'</td></tr>';
	if($row['comtype2']) $rtr .= '<tr><td class="content3">'.$row['comtype2'].':</td><td class="content1">'.$row['comm2'].'</td></tr>';
	if($row['home_www']) $rtr .= '<tr><td class="content3">'.$lang['homesite'].':</td><td class="content1"><a class="link" target="_blank" href="'.$row['home_www'].'">'.$row['home_www'].'</a></td></tr>';
	$rtr .= '<tr><td class="content3">'.$lang['reg_date'].':</td><td class="content1">'.show_date($row['added'], 2).'</td></tr>
	<tr><td class="content3">'.$lang['last_log'].':</td><td class="content1">';
	if($row['lastlog']>0) $rtr .= show_date($row['lastlog'], 2); else $rtr .= $lang['never_login'];
	$rtr .= '</td></tr>';
	$cqry = 'SELECT COUNT(idcomment) FROM '.KML_PREFIX.'_comments WHERE iduser="'.$row['iduser'].'"';
	$all_comments = mysql_result(query(__FILE__,__FUNCTION__,__LINE__,$cqry,0),0);
	if($all_comments>0){
		$aqry = 'SELECT COUNT(idcomment) FROM '.KML_PREFIX.'_comments';
		$comments = mysql_result(query(__FILE__,__FUNCTION__,__LINE__,$aqry,0), 0);
		$prc = round(($all_comments/$comments)*100,2);
		$pct = ' ('.$prc.'% '.$lang['of_all'].')';
	}else $comments = 0;
	$rtr .= '<tr><td class="content3">'.ucfirst($lang['comments']).':</td><td class="content1">'.$all_comments.$pct.'</td></tr>';
	if($row['info']) $rtr .= '<tr><td class="content3">'.$lang['other_info'].':</td><td class="content1">'.$row['info'].'</td></tr>';
	$rtr .= '</table><br/>';
	if($_GET['op']!='clans'){
		$rtr .= content_line('B');
	}
	return $rtr;
}

Function users($get){
	global $lang;
	global $countries;
	$id = intval($get['id']);
	if(!isset($get['p'])) $get['p'] = -1;
	$p = intval($get['p']);
	if($id>0) $rtr = show_profile($id);
	else{
		$show = 50;
		$limit = $show*$p;
		$params = array('login'=>strtolower($lang['login']), 'name'=>strtolower($lang['name']), 'clan'=>strtolower($lang['prof_clan']), 'city'=>strtolower($lang['city']));
		if($get['p1'] || $get['p3']){
			if($get['p1']){
				if(!array_key_exists($get['p2'],$params)) die($lang['sure']);
				$where .= SQL(' `'.$get['p2'].'` LIKE %s', '%'.$get['p1'].'%');
			}
			if($get['p3']){
				if($get['p1']) $where .= ' AND';
				$where .= ' country='.(int)$get['p3'];
			}
		}
		$sqry = 'SELECT COUNT(iduser) FROM '.KML_PREFIX.'_users';
		if(!empty($where)) $sqry .= ' WHERE'.$where;
		$all = mysql_result(query(__FILE__,__FUNCTION__,__LINE__,$sqry,0), 0);
		if($p>=0) $order = 'login ASC'; else{ $order = 'iduser DESC'; $limit = 0; }
		$qry = 'SELECT login, clan_www, country, clan, show_mail, mail, home_www, grants, iduser FROM '.KML_PREFIX.'_users';
		if(!empty($where)) $qry .= ' WHERE'.$where;
		$qry .= ' ORDER BY '.$order.' LIMIT '.$limit.','.$show;
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		$rtr = content_line('T',$lang['users'].': '.$all);
		$rtr .= '<td valign="top"><br/>';
		$rtr .= '<div align="right"><form action="'.$_SERVER['PHP_SELF'].'" method="get">'.KML_LINK_SLF.'<input type="hidden" name="op" value="users" /> <span class="bold">'.$lang['search'].':</span> <input type="text" name="p1" value="'.$get['p1'].'"/> <select name="p2">'.array_assoc($params,$get['p2']).'</select> <span class="bold">'.$lang['country'].':</span> <select name="p3"><option value="0">---</option>'.array_assoc($countries,$get['p3']).'</select> <input type="submit" value="'.$lang['show'].'" /></form></div><br/>';
		$pages = ceil($all/$show);
		$rtr .= pages('',$pages,$p,'op=users&amp;p1='.$get['p1'].'&amp;p2='.$get['p2'].'&amp;p3='.$get['p3'],7,$lang['newest'],'-1');
		$rtr .= '<table class="tab_brdr" cellspacing="1" cellpadding="5">
		<tr><td class="tab_head3">'.$lang['nick'].'</td><td class="tab_head3">'.$lang['prof_clan'].'</td><td class="tab_head3">E-mail</td><td class="tab_head3">'.$lang['website'].'</td>';
		if($_SESSION['dl_grants']['main']>2) $rtr .= '<td class="tab_head3">'.$lang['prof_admin'].'</td>';
		$rtr .= '</tr>';
		while ($row = mysql_fetch_assoc($rsl)){
			foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
			$rtr .= '<tr class="content1"><td class="content3"><a class="tab_link2" href="index.php?'.KML_LINK_SL.'op=users&amp;id='.$row['iduser'].'"><img alt="'.$lang['flag'].'" title="'.$countries[$row['country']].'" src="'.DIR.'_country/'.$row['country'].'.gif" border="0" /> '.$row['login'].'</a></td><td align="center">';
			if($row['clan']){
				if($row['clan_www']) $rtr .= '<a class="link" target="_blank" href="'.$row['clan_www'].'">';
				$rtr .= $row['clan'];
				if($row['clan_www']) $rtr .= '</a>';
			}else $rtr .= '&#8212;&#8212;&#8212;';
			$rtr .= '</td><td align="center">';
			if($row['show_mail']=='Y' || ($dl_grants>0 && $row['show_mail']=='R')) $rtr .= '<a class="link" href="mailto:'.$row['mail'].'">E-MAIL</a>';
			else $rtr .= '&#8212;&#8212;&#8212;';
			$rtr .= '</td><td align="center">';
			if($row['home_www']) $rtr .= '<a class="link" target="_blank" href="'.$row['home_www'].'">WWW</a>';
			else $rtr .= '&#8212;&#8212;&#8212;';
			$rtr .= '</td>';
			if($_SESSION['dl_grants']['main']>2 && $row['grants']<5) $rtr .= '<td align="center"><a class="link" href="admin.php?'.KML_LINK_SL.'op=edit_user&amp;id='.$row['iduser'].'">'.$lang['edit'].'</a></td>';
			$rtr .= '</tr>';
		}
		$rtr .= '</table><br/>';
		$rtr .= pages('',$pages,$p,'op=users&amp;p1='.$get['p1'].'&amp;p2='.$get['p2'].'&amp;p3='.$get['p3'],7,$lang['newest'],'-1');
		$rtr .= '<br/>';
		$rtr .= content_line('B');
	}
	return $rtr;
}

Function profile($dts,$login){
	global $lang;
	global $countries;
	$rtr .= content_line('T',$lang['prof_edit']);
	$rtr .= '<td valign="top">';
	if($dts['show_mail']){
		$rtr .= '<table cellspacing="1" cellpadding="5" class="tab_brdr">
		<tr><td class="content1" align="center">';
		if($dts['new_pass'] && $dts['new_pass2'] && $dts['old_pass']){
			if ($dts['new_pass'] != $dts['new_pass2']) $err_pass=1;
			if (strlen($dts['new_pass']) < 5) $err_passl=1;
			if ($err_passl==1 || $err_pass==1) $rtr .= $lang['pass_err'];
			else{
				$qry = SQL('SELECT iduser, pass, login FROM '.KML_PREFIX.'_users WHERE pass=%s AND iduser=%d', md5($dts['old_pass']), $login);
				$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
				if(mysql_num_rows($rsl)>0){
					$lrow = mysql_fetch_assoc($rsl);
					foreach($lrow as $ky=>$vl) $lrow[$ky] = intoBrowser($vl);
					if (eregi($lrow['login'],$dts['new_pass'])) $err_passc=1;
					if ($err_passc==1) $rtr .= $lang['pass_err'];
					else{
						if(FORUM) pass_forum($login,md5($dts['new_pass']));
						$qry = SQL('UPDATE '.KML_PREFIX.'_users SET pass=%s WHERE pass=%s AND iduser=%d', md5($dts['new_pass']), md5($dts['old_pass']), $login);
						if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) $rtr .= $lang['pass_sav'].' ';
						
					}
				}else $rtr .= $lang['pass_err2'];
			}
		}
		if($dts['home_www']!='' && !eregi("^http://",$dts['home_www'])) $dts['home_www'] = 'http://'.$dts['home_www'];
		if($dts['clan_www']!='' && !eregi("^http://",$dts['clan_www'])) $dts['clan_www'] = 'http://'.$dts['clan_www'];
		if(!$dts['country']) $dts['country'] = 52;
		if(strlen($dts['rok'])!=4) $dts['rok'] = '0000';
		if(strlen($dts['miesiac'])!=2) $dts['miesiac'] = '00';
		if(strlen($dts['dzien'])!=2) $dts['dzien'] = '00';
		$birth = $dts['rok'].$dts['miesiac'].$dts['dzien'];
		$dts['info'] = substr($dts['info'],0,250);
		if($dts['timem']!=0 || $dts['timeg']!=0){
			$timezone = ($dts['timeg']*60) + $dts['timem'];
			if($dts['sign'] == '-') $timezone *= -1;
		}else $timezone = 0;
		$qry = SQL('UPDATE '.KML_PREFIX.'_users SET comtype1=%s, comm1=%s, comtype2=%s, comm2=%s, name=%s, city=%s, country=%d,birth=%s, clan=%s, clan_www=%s, home_www=%s, info=%s, show_mail=%s, timezone=%d, send_pm_mail=%d WHERE iduser=%d', $dts['comtype1'], $dts['comm1'], $dts['comtype2'], $dts['comm2'], $dts['name'], $dts['city'], $dts['country'], $birth, $dts['clan'], $dts['clan_www'], $dts['home_www'], $dts['info'], $dts['show_mail'], $timezone, $dts['send_pm_mail'], $login);
		if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) $rtr .= $lang['info_sav'];
		$rtr .= '</td></tr></table><br/>';
	}
	$qry = 'SELECT send_pm_mail, mail, timezone, login, name, birth, comtype1, comm1, comtype2, comm2, clan, clan_www, home_www, city, info, show_mail, country FROM '.KML_PREFIX.'_users WHERE iduser='.(int)$login;
	$lata[0] = '-';
	for($i=1930;$i<date('Y')-5;$i++){
		$lata[$i] = $i;
	}
	$miesiace[0] = '-';
	for($i=1;$i<13;$i++){
		if(strlen($i)==1) $j = '0'.$i; else $j=$i;
		$miesiace[$j] = $j;
	}
	$dni[0] = '-';
	for($i=1;$i<32;$i++){
		if(strlen($i)==1) $j = '0'.$i; else $j=$i;
		$dni[$j] = $j;
	}
	for($i=0;$i<15;$i++) $timegs[] = $i;
	$plusminus = array('+', '-');
	$timems = array('00', '15', '30', '45');
	$mail_status = array('Y'=>$lang['mail_all'], 'R'=>$lang['mail_reg'], 'N'=>$lang['mail_hide']);
	$yesno = array(1=>$lang['yes'], 0=>$lang['no']);
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	$row = mysql_fetch_assoc($rsl);
	foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
	if($row['birth']){
		$rok = substr($row['birth'],0,4);
		$miesiac = substr($row['birth'],4,2);
		$dzien = substr($row['birth'],6,2);
	}
	if($row['timezone']){
		if($row['timezone']<0){
			$sign = '-';
			$row['timezone'] *= -1;
		}
		$timeg = floor($row['timezone']/60);
		$timem = $row['timezone']%60;
	}else{
		$timeg = 0;
		$timem = '00';
	}
	//echo $timeg.' '.$timem;
	$rtr .= '<form method="post" action="index.php?'.KML_LINK_SL.'op=profile">
	<table class="tab_brdr" cellspacing="1" cellpadding="5">
	<tr><td class="content3">'.$lang['name_surn'].':</td><td class="content1"><input type="text" name="name" value="'.$row['name'].'" maxlength="50" size="30" /></td></tr>
	<tr><td class="content3">'.$lang['old_pass'].'*:</td><td class="content1"><input type="password" name="old_pass" maxlength="20" size="30" /></td></tr>
	<tr><td class="content3">'.$lang['new_pass'].'*:</td><td class="content1"><input type="password" name="new_pass" maxlength="20" size="30" /></td></tr>
	<tr><td class="content3">'.$lang['rep_password'].'*:</td><td class="content1"><input type="password" name="new_pass2" maxlength="20" size="30" /></td></tr>
	<tr><td class="content3">'.$lang['mail'].':</td><td class="content1">'.$row['mail'].'</td></tr>
	<tr><td colspan=2 class="content3">'.$lang['mail_info'].' '.radio_list('show_mail',$mail_status,$row['show_mail'],' ').'</td></tr>
	<tr><td colspan=2 class="content3">'.$lang['send_pm_mail'].': '.radio_list('send_pm_mail',$yesno,$row['send_pm_mail'],' ').'</td></tr>
	<tr><td class="content3">'.$lang['timezone'].':</td><td class="content1"><select name="sign">'.array_norm($plusminus,$sign).'</select> <select name="timeg">'.array_norm($timegs,$timeg).'</select>:<select name="timem">'.array_norm($timems,$timem).'</select></td></tr>
	<tr><td class="content3">'.$lang['prof_clan'].':</td><td class="content1"><input type="text" name="clan" value="'.$row['clan'].'" maxlength="20" size="30" /></td></tr>
	<tr><td class="content3">'.$lang['clan_site'].':</td><td class="content1"><input type="text" name="clan_www" value="'.$row['clan_www'].'" maxlength=40 size="30" /></td></tr>
	<tr><td class="content3">'.$lang['country'].':</td><td class="content1"><select name="country">'.array_assoc($countries,$row['country']).'</select></td></tr>
	<tr><td class="content3">'.$lang['city'].':</td><td class="content1"><input type="text" name="city" value="'.$row['city'].'" maxlength="40" size="30" /></td></tr>
	<tr><td class="content3">'.$lang['birth'].'('.$lang['birth_info'].'):</td><td class="content1"><select name="rok">'.array_assoc($lata,$rok).'</select>-<select name="miesiac">'.array_assoc($miesiace,$miesiac).'</select>-<select name="dzien">'.array_assoc($dni,$dzien).'</select></td></tr>
	<tr><td class="content3">'.$lang['comtype'].'1:</td><td class="content1"><input type="text" name="comtype1" value="'.$row['comtype1'].'" maxlength="10" size="10" /></td></tr>
	<tr><td class="content3">'.$lang['comm'].'1:</td><td class="content1"><input type="text" name="comm1" value="'.$row['comm1'].'" maxlength="30" size="30" /></td></tr>
	<tr><td class="content3">'.$lang['comtype'].'2:</td><td class="content1"><input type="text" name="comtype2" value="'.$row['comtype2'].'" maxlength="10" size="10" /></td></tr>
	<tr><td class="content3">'.$lang['comm'].'2:</td><td class="content1"><input type="text" name="comm2" value="'.$row['comm2'].'" maxlength="30" size="30" /></td></tr>
	<tr><td class="content3">'.$lang['homesite'].':</td><td class="content1"><input type="text" name="home_www" value="'.$row['home_www'].'" maxlength="40" size="30" /></td></tr>
	<tr><td class="content3">'.$lang['other_info'].':</td><td class="content1"><textarea name="info" rows="5" cols="30">'.$row['info'].'</textarea></td></tr>
	<tr><td class="content3" colspan=2 align="right"><input type="submit" value="'.$lang['save'].'" /> &nbsp; <input type="reset" value="'.$lang['reset'].'" /></td></tr>
	</table>
	</form><br/>
	<div class="note"><span class="bold">'.$lang['notes'].':</span> '.$lang['pass_notes'].'</div><br/>';
	$rtr .= content_line('B');
	return $rtr;
}

Function avatar($login,$avatar=''){
	global $lang;
	$rtr = content_line('T',$lang['prof_photo']);
	$rtr .= '<td valign="top">
	<table class="tab_brdr" cellspacing="1" cellpadding="5">
	<tr><td class="tab_head3">'.$lang['announc'].'</td><td class="tab_head3">'.$lang['current_photo'].'</td></tr>
	<tr><td class="content2" align="center">';
	if($avatar!=''){
		$qry = SQL('UPDATE '.KML_PREFIX.'_users SET avatar=%s WHERE iduser=%d', $avatar, $login);
		if($avatar==1) $fstatus = $lang['photo_vis']; else $fstatus = $lang['photo_hid'];
		if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) $rtr .= $fstatus.'<br/>';
			if ($_FILES['avatar_file']['size'] != 0){
				$size = getimagesize($_FILES['avatar_file']['tmp_name']);
				$width = $size[0];
				$height = $size[1];
				$res = explode(':', KML_AVATAR_RES);
				if((int)$res[0]==0 || (int)$res[1]==0){ $res[0] = 60; $res[1] = 60;}
				if ($width>$res[0] || $height>$res[1]) $status .= $lang['photo_info1'];
				if ($_FILES['avatar_file']['type'] != 'image/pjpeg' && $_FILES['avatar_file']['type'] != 'image/jpeg') $status .= $lang['photo_info2'];
				if ($_FILES['avatar_file']['size'] > KML_AVATAR_SIZE) $status .= $lang['photo_info3'];
				if(empty($status)){
					$new_name = DIR.'_avatars/'.$login.'.jpg';
					move_uploaded_file($_FILES['avatar_file']['tmp_name'], $new_name);
					chmod($new_name, 0755);
					$status = $lang['photo_info4'];
				}
				$rtr .= $status;
			}
	}
	$rtr .= '</td><td bgcolor="black" align="center">';
	$avatars = DIR.'_avatars/'.$login.'.jpg';
	if(file_exists($avatars)) $rtr .= '<img alt="avatar" src="'.$avatars.'" />'; else $rtr .= '<img alt="avatar" src="'.SKIN.'img/no.jpg" />';
	$qry = 'SELECT avatar FROM '.KML_PREFIX.'_users WHERE iduser='.(int)$login;
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	$row = mysql_fetch_assoc($rsl);
	foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
	$rtr .= '</td></tr></table><br/>';
	$rtr .= content_line('M',$lang['set_ch'].'/'.$lang['upload']);
	$rtr .= '<br/><form method="post" enctype="multipart/form-data" action="index.php?'.KML_LINK_SL.'op=avatar">
	<table class="tab_brdr" cellspacing="1" cellpadding="5">
	<tr class="content1"><td class="content3">'.$lang['photo_ask'].' ?</td><td><select name="avatar"><option value="1">'.$lang['yes'].'<option ';
	if($row['avatar']==0) $rtr .= 'selected ';
	$rtr .= 'value=0>'.$lang['no'].'</select>';
	$rtr .= '</td></tr>
	<tr class="content1"><td class="content3">'.$lang['file'].':</td><td><input size="27" name="avatar_file" type="file" /></td></tr>
	<tr class="content3"><td align="right" colspan="2"><input type="submit" value="'.$lang['submit'].'/'.$lang['save'].'" /></td></tr>
	</table>
	</form>
	<br/><div class="note"><span class="bold">'.$lang['notes'].':</span> '.$lang['photo_info1'].$lang['photo_info2'].$lang['photo_info3'].'</div><br/>';
	$rtr .= content_line('B');
	return $rtr;
}
###do zrobienia
/*
Function user_comments($user_id,$dl_grants){
	$qry = 'SELECT iduser, login FROM '.KML_PREFIX.'_users WHERE iduser='.(int)$user_id;
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	if(mysql_num_rows($rsl)!=1) die('yy jasne..');
	$row = mysql_fetch_assoc($rsl);
	foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
	println('<table width="97%" cellspacing="1" cellpadding="2" border="0" bgcolor="black" bordercolor="black" rules="none">');
	println('<tr><td align="right" bgcolor="black" class="tabh"><span class="bold">Komentarze do newsów u¿ytkownika <a class="menu" href="index.php?'.KML_LINK_SL.'op=users&amp;id='.$row['iduser'].'">'.$row['login'].'</a></span> &nbsp;</td></tr>');
	println('<tr><td class="content1" align="center"><br/>');
	println('<table class="tab_brdr" cellspacing=1 cellpadding=4 width="90%">');
	$qry = 'SELECT n.title, c.date, c.idnews, c.idcomment, c.comment, c.ip FROM '.KML_PREFIX.'_comments AS c, '.KML_PREFIX.'_news AS n WHERE c.iduser='.(int)$user_id.' AND c.idnews=n.idnews ORDER BY c.idcomment DESC LIMIT 0,30';
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	$all= mysql_num_rows($rsl);
	if ($all==0) println('<tr><td class="content3">U¿ytkownik nie odda³ jeszcze ¿adnego komentarza.</td></tr>');
	for($i=0;$i<$all;$i++){
		$row = mysql_fetch_assoc($rsl);
		foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
		print('<tr class="tab_head"><td width="50%">News: <a class="menu" href="index.php?'.KML_LINK_SL.'op=com&amp;id='.$row['idnews'].'">'.$row['title'].'</a></td><td width="50%" align="right">'.show_date($row['date'], 2));
		if($dl_grants>1) print(' <a class="link" href="admin.php?'.KML_LINK_SL.'op=comment&amp;id='.$row['idcomment'].'">ED. KOM.</a>');
		println('</td></tr>');
		println('<tr><td class="komentarz" colspan="2">'.$row['comment'].'</td></tr>');
	}
	println('</table>');
	println('<br/>');
	println('<tr><td align="right" bgcolor="black" class="tabh"><b><a class="link" href="#TOP">^^^</A></b> &nbsp;</td></tr>');
	println('</td></tr></table>');
}
*/

Function watchers_log($login){
	$qry = 'DELETE FROM '.KML_PREFIX.'_watchers WHERE time<"'.(time()-300).'"';
	query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	if($login==0){
		$qry = 'SELECT ip FROM '.KML_PREFIX.'_watchers WHERE service="'.MAIN_ID.'" AND ip="'.$_SERVER['REMOTE_ADDR'].'"';
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		if(mysql_num_rows($rsl)==1) $qry = 'UPDATE '.KML_PREFIX.'_watchers SET time="'.time().'" WHERE service='.MAIN_ID.' AND ip="'.$_SERVER['REMOTE_ADDR'].'"';
		else $qry = 'INSERT INTO '.KML_PREFIX.'_watchers(time, ip, iduser, service) VALUES("'.time().'", "'.$_SERVER['REMOTE_ADDR'].'", "'.(int)$login.'", "'.MAIN_ID.'")';
		query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	}else{
		$qry = 'SELECT COUNT(time) FROM '.KML_PREFIX.'_watchers WHERE service="'.MAIN_ID.'" AND iduser='.(int)$login;
		$all = mysql_result(query(__FILE__,__FUNCTION__,__LINE__,$qry,0),0);
		if($all==1) $qry = 'UPDATE '.KML_PREFIX.'_watchers SET time="'.time().'" WHERE service="'.MAIN_ID.'" AND iduser='.(int)$login;
		else{
			$qry = 'SELECT COUNT(time) FROM '.KML_PREFIX.'_watchers WHERE ip="'.$_SERVER['REMOTE_ADDR'].'"';
			$exist = mysql_result(query(__FILE__,__FUNCTION__,__LINE__,$qry,0),0);
			$qry = 'SELECT COUNT(time) FROM '.KML_PREFIX.'_watchers WHERE ip="'.$_SERVER['REMOTE_ADDR'].'" AND iduser='.(int)$login;
			$done = mysql_result(query(__FILE__,__FUNCTION__,__LINE__,$qry,0),0);
			if($done<1){
				if($exist>0){
					$qry = 'UPDATE '.KML_PREFIX.'_watchers SET iduser="'.(int)$login.'" WHERE service="'.MAIN_ID.'" AND ip="'.$_SERVER['REMOTE_ADDR'].'"';
					query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
				}else{
					$qry = 'INSERT INTO '.KML_PREFIX.'_watchers(time, ip, iduser, service) VALUES("'.time().'", "'.$_SERVER['REMOTE_ADDR'].'", "'.(int)$login.'", "'.MAIN_ID.'")';
					query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
				}
			}else{
				$qry = 'UPDATE '.KML_PREFIX.'_watchers SET service="'.MAIN_ID.'", time="'.time().'" WHERE iduser="'.(int)$login.'" AND ip="'.$_SERVER['REMOTE_ADDR'].'"';
				query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
			}
		}
	}
}

Function serv_squad(){
	global $lang;
	global $countries;
	$rtr = content_line('T',$lang['admin_team']);
	$rtr .= '<td valign="top">';
	$qry = 'SELECT g.descr, u.iduser, u.avatar, u.country, u.login, u.show_mail, u.mail FROM '.KML_PREFIX.'_users AS u, '.KML_PREFIX.'_grants AS g WHERE g.service="'.MAIN_ID.'" AND u.iduser=g.iduser ORDER BY u.login';
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	$rtr .= '<table cellpadding="1" cellspacing="0" align="center">';
	while($row = mysql_fetch_assoc($rsl)){
		foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
		if(++$i%2 != 0) $rtr .= '<tr><td>';
		$rtr .= '<table class="tab_brdr" cellspacing="1" cellpadding="5" width="320">
		<tr><td colspan="3" align="right" class="tab_head2"><a class="tab_link2" href="index.php?'.KML_LINK_SL.'op=users&amp;id='.$row['iduser'].'">'.$row['login'].' <img alt="'.$lang['flag'].'" title="'.$countries[$row['country']].'" src="'.DIR.'_country/'.$row['country'].'.gif" border="0" /></a></td></tr>
		<tr><td rowspan="2" align="center" width="70" class="avatar"><a href="index.php?'.KML_LINK_SL.'op=users&amp;id='.$row['iduser'].'">';
		$avatar = DIR.'_avatars/'.$row['iduser'].'.jpg';
		if($row['avatar']=='1' && file_exists($avatar)) $rtr .= '<img alt="avatar" src="'.$avatar.'" />'; else $rtr .= '<img alt="avatar" src="'.SKIN.'img/no.jpg" />';
		$rtr .= '</a></td><td class="tab_head2" width="40">MAIL:</td><td class="content1">';
		if($row['show_mail']=='Y' || $_SESSION['dl_grants']['main']>0 && $row['show_mail']=='R') $rtr .= '<a class="link" href="mailto:'.$row['mail'].'">'.$row['mail'].'</a>'; else $rtr .= '&#8212;&#8212;&#8212;';
		$rtr .= '</td></tr>
		<tr><td class="tab_head2">'.$lang['function'].':</td><td class="content1">';
		if($row['descr']) $rtr .= $row['descr']; else $rtr .= '&#8212;&#8212;&#8212;';
		$rtr .= '</td></tr>
		</table>';
		if($i%2!=0) $rtr .= '</td><td>';
		if($i%2==0 || $i==mysql_num_rows($rsl)) $rtr .= '</td></tr>';
	}
	$rtr .= '</table>'.content_line('B');
	return $rtr;
}

?>
