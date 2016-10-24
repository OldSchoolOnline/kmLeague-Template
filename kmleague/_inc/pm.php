<?php

Function mark_message($idm,$idu){
	$qry = 'UPDATE '.KML_PREFIX.'_pm SET showed="Y" WHERE idpm='.(int)$idm.' AND iduser='.(int)$idu;
	query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
}

Function check_mess($login){
	$qry = 'SELECT idpm FROM '.KML_PREFIX.'_pm WHERE iduser='.(int)$login.' AND showed="N" ORDER BY idpm DESC';
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	if(mysql_num_rows($rsl)>0){
		$row = mysql_fetch_assoc($rsl);
		return '<a class="menu" href="index.php?'.KML_LINK_SL.'op=spm&amp;id='.$row['idpm'].'"><img alt="nmsg" title="'.$lang['msg_new_message'].'" src="'.DIR.'_img/msg.gif" border="0"></a>';
	}
}

Function pmm($login){
	global $lang;
	$qry = 'SELECT COUNT(idpm) AS count FROM '.KML_PREFIX.'_pm WHERE iduser='.(int)$login.' AND showed="N"';
	$mess1 = mysql_result(query(__FILE__,__FUNCTION__,__LINE__,$qry,0), 0, 'count');
	$qry = 'SELECT COUNT(idpm) AS count FROM '.KML_PREFIX.'_pm WHERE iduser='.(int)$login.' AND showed="Y"';
	$mess2 = mysql_result(query(__FILE__,__FUNCTION__,__LINE__,$qry,0), 0, 'count');
	$rtr = '<td align="center"><a  class="menu" href="index.php?'.KML_LINK_SL.'op=pm&amp;id=1">'.$lang['new_mess'].' ('.$mess1.')</a> | <a  class="menu" href="index.php?'.KML_LINK_SL.'op=pm&amp;id=2">'.$lang['read'].' ('.$mess2.')</a> | <a  class="menu" href="index.php?'.KML_LINK_SL.'op=pm&amp;id=3">'.$lang['send_mess'].'</a></td>
	<td class="BoxCright"></td></tr>
	<tr><td class="BoxCleft"></td>';
	return $rtr;
}

Function pm($login,$id,$dts=''){
	global $lang;
	$id = intval($id);
	$rtr = content_line('T','Messenger');
	$rtr .= pmm($login);
	$rtr .= '<td valign="top">';
	if($id==1){
		$rtr .= '<table class="tab_brdr" cellspacing="1" cellpadding="5" width="500">
		<tr><td class="tab_head3">'.ucfirst($lang['title']).'</td><td class="tab_head3">'.$lang['date'].'</td><td class="tab_head3">'.$lang['author'].'</td></tr>';
		$qry = 'SELECT p.idpm, p.title, u.login, p.idauthor, p.date FROM '.KML_PREFIX.'_pm AS p, '.KML_PREFIX.'_users AS u WHERE p.idauthor=u.iduser AND p.iduser='.(int)$login.' AND p.showed="N" ORDER BY p.idpm DESC';
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		while($row = mysql_fetch_assoc($rsl)){
			foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
			$rtr .= '<tr class="content2"><td class="tab_con2"><a class="link" href="index.php?'.KML_LINK_SL.'op=spm&amp;id='.$row['idpm'].'">'.$row['title'].'</td><td align="center">'.show_date($row['date'], 1).'</td><td align="center"><a class="link" href="index.php?'.KML_LINK_SL.'op=users&amp;id='.$row['idauthor'].'">'.$row['login'].'</td></tr>';
		}
		$rtr .= '</table>
		<br/><a class="menu" href="index.php?'.KML_LINK_SL.'op=dpm&amp;id=n">'.$lang['dell_all_mess'].'</a><br/>';
	}elseif($id==2){
		$rtr .= '<table class="tab_brdr" cellspacing="1" cellpadding="5" width="500">
		<tr><td class="tab_head3">'.ucfirst($lang['title']).'</td><td class="tab_head3">'.$lang['date'].'</td><td class="tab_head3">'.$lang['author'].'</td></tr>';
		$qry = 'SELECT p.idpm, p.title, u.login, p.idauthor, p.date FROM '.KML_PREFIX.'_pm AS p, '.KML_PREFIX.'_users AS u where p.idauthor=u.iduser AND p.iduser='.(int)$login.' AND p.showed="Y" ORDER BY p.idpm DESC';
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		while($row = mysql_fetch_assoc($rsl)){
			foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
			$rtr .= '<tr class="content2"><td class="tab_con2"><a class="link" href="index.php?'.KML_LINK_SL.'op=spm&amp;id='.$row['idpm'].'">'.$row['title'].'</td><td align="center">'.show_date($row['date'], 1).'</td><td align="center"><a class="link" href="index.php?'.KML_LINK_SL.'op=users&amp;id='.$row['idauthor'].'">'.$row['login'].'</td></tr>';
		}
		$rtr .= '</table>
		<br/><a class="menu" href="index.php?'.KML_LINK_SL.'op=dpm&amp;id=r">'.$lang['dell_all_mess'].'</a><br/>';
	}elseif($id==3){
		$qry = 'SELECT login FROM '.KML_PREFIX.'_users WHERE grants>0 ORDER BY login';
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		if($dts['title']) $title = 're: '.$dts['title'];
		if($dts['content']) $content = '] '.post_edit($dts['content']);
		$rtr .= '<form method="post" action="index.php?'.KML_LINK_SL.'op=pms">
		<table class="tab_brdr" cellspacing="1" cellpadding="5">
		<tr><td class="tab_head2">'.$lang['receiver'].':</td><td class="content1">';
		if(mysql_num_rows($rsl)<301){
			$users = getArray($rsl);
			$rtr .= '<select name="user">'.array_norm($users[0],$dts['nick']).'</select>';
		}else $rtr .= '<input type="text" name="user" maxlength="15" size="30" value="'.$dts['nick'].'" />';
		$rtr .= '</td></tr>
		<tr><td class="tab_head2">'.ucfirst($lang['title']).':</td><td class="content1"><input type="text" name="title" maxlength="30" size="30" value="'.$title.'" /></td></tr>
		<tr><td class="tab_head2" valign="top">'.ucfirst($lang['content']).':</td><td class="content1"><textarea cols="50" rows="6" name="content">'.$content.'</textarea></td></tr>
		<tr><td class="content2" colspan="2" align="right"><input type="submit" value="'.$lang['submit'].'" /></td></tr>
		</table>
		</form>';
	}
	$rtr .= '<br/>';
	$rtr .= content_line('B');
	return $rtr;
}

Function pms($login,$dts){
	global $lang;
	$rtr = content_line('T','Messenger');
	$rtr .= pmm($login);
	$rtr .= '<td valign="top" align="center"><br/>';
	if(!$dts['title'] || !$dts['content']) $status = $lang['pm_err1'];
	if(!$dts['user']) $status .= $lang['pm_err2'];
	if(!$status){
		$qry = SQL('SELECT iduser FROM '.KML_PREFIX.'_users WHERE login=%s', $dts['user']);
		$receiver = @mysql_result(query(__FILE__,__FUNCTION__,__LINE__,$qry,0), 0, 'iduser');
		if($receiver>0){
			if(send_pm($receiver,$login,$dts['title'],$dts['content'])) $rtr .= $lang['mess_send'];
		}else $rtr .= $lang['pm_err3'];
	}else $rtr .= $status;
	$rtr .= '<br/><br/>';
	$rtr .= content_line('B');
	return $rtr;
}

Function spm($login,$id){
	global $lang;
	$id = intval($id);
	$qry = 'SELECT p.idpm, p.title, u.login, p.content, p.idauthor, p.date FROM '.KML_PREFIX.'_pm AS p, '.KML_PREFIX.'_users AS u WHERE p.idauthor=u.iduser AND p.iduser='.(int)$login.' AND p.idpm="'.(int)$id.'"';
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	if(mysql_num_rows($rsl)!=1) die($lang['sure']);
	$row = mysql_fetch_assoc($rsl);
	foreach($row as $ky=>$vl){
		if($ky!='content') $row[$ky] = intoBrowser($vl);
	}
	$rtr = content_line('T','Messenger');
	$rtr .= pmm($login);
	$rtr .= '<td valign="top">
	<table class="tab_brdr" cellspacing="1" cellpadding="5" width="500">
	<tr><td class="tab_head2">'.$row['title'].'</td><td align="right" class="tab_head2">'.show_date($row['date'], 2).' | <a class="tab_link2" href="index.php?'.KML_LINK_SL.'op=users&amp;id='.$row['idauthor'].'">'.$row['login'].'</a></td></tr>
	<tr><td colspan=2 class="comment"><br/>'.stripslashes($row['content']).'<br/><br/></td></tr>
	<tr class="content2">
	<td align="right" width="50%"><form method="post" action="index.php?'.KML_LINK_SL.'op=dpm"><input type="hidden" name="id" value="'.$row['idpm'].'" /><input type="submit" value="'.$lang['delete'].'" /></form></td>
	<td align="left" width="50%"><form method="post" action="index.php?'.KML_LINK_SL.'op=pm&amp;id=3"><input type="hidden" name="nick" value="'.$row['login'].'" /><input type="hidden" name="content" value="'.intoBrowser($row['content']).'" /><input type="hidden" name="title" value="'.$row['title'].'" /><input type="submit" value="'.$lang['replay'].'" /></form></td>
	</tr>
	</table>
	<br/>';
	$rtr .= content_line('B');
	return $rtr;
}

Function dpm($login,$id){
	global $lang;
	if($id == 'r'){
		$qry = 'DELETE FROM '.KML_PREFIX.'_pm WHERE iduser='.(int)$login.' AND showed="Y"';
		if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) $status = $lang['pm_dr'];
	}elseif($id == 'n'){
		$qry = 'DELETE FROM '.KML_PREFIX.'_pm WHERE iduser='.(int)$login.' AND showed="N"';
		if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) $status = $lang['pm_dn'];
	}else{
		$qry = 'DELETE FROM '.KML_PREFIX.'_pm WHERE idpm='.(int)$id.' AND iduser='.(int)$login;
		if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) $status = $lang['pm_d'];
	}
	$rtr = content_line('T','Messenger');
	$rtr .= pmm($login);
	$rtr .= '<td valign="top" align="center"><br/>'.$status.'<br/><br/>';
	$rtr .= content_line('B');
	return $rtr;
}

?>
