<?php

Function news($p=0,$limit=5){
	global $lang;
	$qry = 'SELECT n.idn, n.date, n.title, n.title_enc, n.content, n.encode, n.content_enc, u.login, n.iduser FROM '.KML_PREFIX.'_users AS u, '.KML_PREFIX.'_news AS n WHERE n.iduser=u.iduser AND n.service="'.MAIN_ID.'" AND n.visible="Y" ORDER BY n.idn DESC LIMIT '.(int)($p*$limit).','.(int)$limit;
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	$layout = file_get_contents(SKIN.'tmpl/item_news.html');
	while($row = mysql_fetch_assoc($rsl)){
		foreach($row as $ky=>$vl){
			if($ky!='content' && $ky!='content_enc') $row[$ky] = intoBrowser($vl);
		}
		if($_SESSION['dl_config']['lang']==$row['encode'] && $row['content_enc'] && $row['title_enc']){
			$row['content'] = $row['content_enc'];
			$row['title'] = $row['title_enc'];
		}
		$qry = 'SELECT COUNT(idcomment) FROM '.KML_PREFIX.'_comments WHERE `type`="'.MAIN_ID.'" AND iditem='.$row['idn'];
		$coms = mysql_result(query(__FILE__,__FUNCTION__,__LINE__,$qry,0),0);
		$tmplayout = $layout;
		$lay['news_more'] = '';
		$lay['news_more_amp'] = '';
		$lay['news_title'] = $row['title'];
		if($_SESSION['dl_grants'][MAIN_ID]>1 || $_SESSION['dl_grants'][MAIN_ID]==1 && $row['iduser']==$_SESSION['dl_login']) $lay['news_title'] .= ' <a href="admin.php?'.KML_LINK_SL.'op=news&amp;idn='.$row['idn'].'"><img title="'.$lang['edit'].'" alt="edit" src="adm/edit.gif"/></a>';
		$lay['news_date'] = show_date($row['date'], 2);
		$lay['news_author'] = '<a class="news" href="index.php?'.KML_LINK_SL.'op=users&amp;id='.$row['iduser'].'">'.$row['login'].'</a>';
		$inf = explode('%%', stripslashes($row['content']));
		$lay['news_content'] = $inf[0];
		$lay['news_link'] = 'index.php?'.KML_LINK_SL.'op=com&amp;id='.$row['idn'];
		if(strlen($inf[1])>0){
			$lay['news_more'] = $lang['more'];
			$lay['news_more_amp'] = ' &amp; ';
		}
		$lay['news_comments'] = $lang['comments'];
		$lay['news_comments_num'] = $coms;
		$lay['skin'] = SKIN;
		foreach($lay as $ky=>$vl) $tmplayout = str_replace('{'.$ky.'}', $vl, $tmplayout);
		$rtr .= $tmplayout;
	}
	$qry = 'SELECT COUNT(idn) FROM '.KML_PREFIX.'_news WHERE service="'.MAIN_ID.'" AND visible="Y"';
	$all = ceil(mysql_result(query(__FILE__,__FUNCTION__,__LINE__,$qry,0),0)/$limit);
	if(!$p) $p = 0;
	$rtr .= pages($lang['archive'].': ',$all,(int)$p,KML_LINK_SL.'op=news',7);
	return $rtr;
}

Function comments($id,$main_grants,$serv_grants){
	global $lang;
	$id = intval($id);
	$qry = 'SELECT n.date, n.title, n.content, u.login, n.idn, n.iduser, n.title_enc, n.content_enc, n.encode FROM '.KML_PREFIX.'_news AS n, '.KML_PREFIX.'_users AS u WHERE n.service="'.MAIN_ID.'" AND n.visible="Y" AND n.idn="'.$id.'" AND n.iduser=u.iduser';
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	if(mysql_num_rows($rsl)!=1) die($lang['sure']);
	$row = mysql_fetch_assoc($rsl);
	foreach($row as $ky=>$vl){
		if($ky!='content' && $ky!='content_enc') $row[$ky] = intoBrowser($vl);
	}
	if($_SESSION['dl_config']['lang']==$row['encode'] && $row['content_enc'] && $row['title_enc']){
		$row['content'] = $row['content_enc'];
		$row['title'] = $row['title_enc'];
	}
	$title = '<table cellspacing="0" cellpadding="0" style="width: 100%"><tr style="width: 100%"><td class="news_head1">'.$row['title'].'</td><td class="news_head2">'.show_date($row['date'], 2).' | <a class="news" href="index.php?'.KML_LINK_SL.'op=users&amp;id='.$row['iduser'].'">'.$row['login'].'</a></td></tr></table>';
	$rtr .= content_line('T',$title);
	$rtr .= '<td class="news" valign="top">';
	$inf = str_replace('%%', '', stripslashes($row['content']));
	$rtr .= $inf;
	$rtr .= content_line('M',ucfirst($lang['comments']));
	$rtr .= comms(MAIN_ID,$row['idn'],$main_grants,$serv_grants);
	$rtr .= content_line('M',$lang['new_com']);
	if(banned($_SERVER['REMOTE_ADDR'])){
		$rtr .= $lang['banned'];
		return $rtr;
	}
	if($main_grants>0) $rtr .= comment_form($id,MAIN_ID);
	else $rtr .= $lang['comm_info'];
	$rtr .= content_line('B');
	return $rtr;
}

Function comms($id,$idi,$main_grants,$serv_grants,$vote=0,$vote_display='',$edLink='ecomm'){
	global $lang;
	$rtr = '<table class="tab_brdr" cellspacing="1" cellpadding="3" width="90%">';
	if($vote==0) $qry = 'SELECT c.edit, c.date, c.iduser, c.idcomment, c.comment, c.ip, u.login, u.avatar, u.grants FROM '.KML_PREFIX.'_comments AS c, '.KML_PREFIX.'_users AS u WHERE c.type="'.$id.'" AND c.iditem="'.$idi.'" AND c.iduser=u.iduser ORDER BY c.idcomment DESC';
	if($vote==1) $qry = 'SELECT c.date, c.iduser, c.idcomment, c.comment, c.ip, u.login, u.avatar, u.grants, n.vote FROM '.KML_PREFIX.'_users AS u, '.KML_PREFIX.'_comments AS c LEFT JOIN '.KML_PREFIX.'_votes AS n USING(idcomment) WHERE c.type='.$id.' AND c.iditem='.$idi.' AND c.iduser=u.iduser ORDER BY c.idcomment DESC';
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	$all = mysql_num_rows($rsl);
	if($all==0) $rtr .= '<tr><td class="content2" align="center">'.$lang['num_com'].'</td></tr>';
	for($i=0;$i<$all;$i++){
		$row = mysql_fetch_assoc($rsl);
		foreach($row as $ky=>$vl){
			if($ky!='comment') $row[$ky] = intoBrowser($vl);
		}
		$rtr .= '<tr><td rowspan="2" valign="top" width="62" class="avatar"><a class="nick" href="index.php?'.KML_LINK_SL.'op=users&amp;id='.$row['iduser'].'&amp;">'.$row['login'].'<br/>';
		$avatar = DIR.'_avatars/'.$row['iduser'].'.jpg';
		if($row['avatar']=='1' && file_exists($avatar)) $rtr .= '<img src="'.$avatar.'" alt="avatar" />'; else $rtr .= '<img src="'.SKIN.'img/no.jpg" alt="avatar" />';
		$rtr .= '</a></td><td class="content2" align="right">'.show_date($row['date'], 2);
		if($serv_grants>1) $rtr .= ' <img src="'.DIR.'_img/ip.gif" alt="ip" title="'.$row['ip'].'" />';
		if($row['date']>(time()-60*60*24*7) && ($serv_grants>1 || $row['iduser']==$_SESSION['dl_login'])){
			$rtr .= ' | <a href="index.php?'.KML_LINK_SL.'op='.$edLink.'&amp;idc='.$row['idcomment'].'&amp;league='.LEAGUE.'&amp;season='.IDS.'">'.$lang['edit'].'</a>';
		}elseif($serv_grants>1) $rtr .= ' | <a class="link" href="admin.php?'.KML_LINK_SL.'op=comment&amp;id='.$row['idcomment'].'"><img alt="ekom" src="'.DIR.'_img/edk.gif"/></a>';
		if($main_grants>2 && $row['grants'] < 4) $rtr .= ' | <a href="'.DIR.'admin.php?'.KML_LINK_SL.'op=edit_user&amp;id='.$row['iduser'].'"><img alt="eusr" src="'.DIR.'_img/edu2.gif" border="0" /></a>';
		$rtr .= '&nbsp;</td></tr>
		<tr><td class="comment">'.$row['comment'];
		if($row['vote']) $rtr .= ' '.str_replace('{vote}',$row['vote'],$vote_display);
		if($row['edit']>0) $rtr .= '<div class="italic"><br/>'.$lang['edited'].': '.show_date($row['edit'],2).'</div>';
		$rtr .= '</td></tr>';
	}
	$rtr .= '</table>';
	return $rtr;
}

Function add_com($dts,$login){
	global $lang;
	if($dts['comment']){
		if(banned($_SERVER['REMOTE_ADDR'])){
			$rtr .= $lang['banned'].'<br/>('.$lang['comm_wait'].')';
			return $rtr;
		}
		if($login < 1){
			$rtr .= $lang['acc_block'].'<br/>('.$lang['comm_wait'].')';
			return $rtr;
		}
		if($dts['opt2']){
			$qry = SQL('UPDATE '.KML_PREFIX.'_comments SET edit="'.time().'", comment=%x, ip="'.$_SERVER['REMOTE_ADDR'].'" WHERE idcomment=%d', $dts['comment'], $dts['idc']);
			if($_SESSION['dl_grants'][MAIN_ID]<1) $qry .= SQL(' AND iduser=%d', $login);
			$komm .= $lang['comm_edited'];
		}elseif($dts['opt3']){
			$qry = SQL('DELETE FROM '.KML_PREFIX.'_votes WHERE idcomment=%d', $dts['idc']);
			if($_SESSION['dl_grants'][MAIN_ID]<1) $qry .= SQL(' AND iduser=%d', $login);
			query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
			$qry = SQL('DELETE FROM '.KML_PREFIX.'_comments WHERE idcomment=%d', $dts['idc']);
			if($_SESSION['dl_grants'][MAIN_ID]<1) $qry .= SQL(' AND iduser=%d', $login);
			$komm .= $lang['comm_deleted'];
		}else{
			$qry = SQL('INSERT INTO '.KML_PREFIX.'_comments(iduser, date, comment, ip, iditem, type) VALUES(%d, "'.time().'", %x, "'.$_SERVER['REMOTE_ADDR'].'", %d, %d)', $login, $dts['comment'], $dts['id'], $dts['item']);
			$komm .= $lang['comm_added'];
		}
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		if($dts['vote']>0){
			if($dts['opt2']){
				$qry = 'SELECT iduser, iditem, type FROM '.KML_PREFIX.'_comments WHERE idcomment="'.(int)$dts['idc'].'"';
				$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
				$row = mysql_fetch_assoc($rsl);
				$com_id = $dts['idc'];
				$login = $row['iduser'];
				$dts['id'] = $row['iditem'];
				$dts['item'] = $row['type'];
			}else $com_id = mysql_insert_id();
			$qry = 'SELECT iditem FROM '.KML_PREFIX.'_votes WHERE iduser='.(int)$login.' AND iditem='.(int)$dts['id'].' AND type='.(int)$dts['item'];
			$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
			if(mysql_num_rows($rsl)>0){
				$qry = 'UPDATE '.KML_PREFIX.'_votes SET vote='.(int)$dts['vote'].', idcomment='.(int)$com_id.' WHERE iduser='.(int)$login.' AND iditem='.(int)$dts['id'].' AND `type`='.(int)$dts['item'];
				query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
				$komm .= ' '.$lang['voice_ch'].'.';
			}else{
				$qry = 'INSERT INTO '.KML_PREFIX.'_votes(idcomment, iditem, type, iduser, vote) VALUES("'.(int)$com_id.'", "'.(int)$dts['id'].'", "'.(int)$dts['item'].'", "'.(int)$login.'", "'.(int)$dts['vote'].'")';
				query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
				$komm .= ' '.$lang['voice_add'].'.';
			}
			$rsl = 1;
		}
		if($rsl==1){
			$rtr .= $komm.'<br/>('.$lang['comm_wait'].')';
		}else $rtr .= $lang['comm_wait'];
	}else $rtr .= $lang['comm_emp'].'<br/>('.$lang['comm_wait'].')';
	return $rtr;
}

Function comment_form($id,$idi,$inf='',$vote=0,$vote_head='',$vote_array=''){
	global $lang;
	if($inf['idc']) $options = '<input type="submit" name="opt3" value="'.$lang['delete'].'" /> <input type="submit" name="opt2" value="'.$lang['save'].'" />';
	else $options = '<input type="submit" name="opt1" value="'.$lang['add'].'" />';
	$rtr .= '<table cellpadding="10"><tr><td>
	<table class="tab_brdr" cellpadding="3" cellspacing="1">
	<tr class="content2">
		<td><a href="javascript:smiles(\':arg:\')"><img alt="emo" src="'.DIR.'_emo/argue.gif" border="0" /></a></td>
		<td><a href="javascript:smiles(\':D\')"><img alt="emo" src="'.DIR.'_emo/blaugh.gif" border="0" /></a></td>
		<td><a href="javascript:smiles(\':cool:\')"><img alt="emo" src="'.DIR.'_emo/cool.gif" border="0" /></a></td>
		<td><a href="javascript:smiles(\':[\')"><img alt="emo" src="'.DIR.'_emo/cry.gif" border="0" /></a></td>
	</tr>
	<tr class="content2">
		<td><a href="javascript:smiles(\':dir:\')"><img alt="emo" src="'.DIR.'_emo/director.gif" border="0" /></a></td>
		<td><a href="javascript:smiles(\':dis:\')"><img alt="emo" src="'.DIR.'_emo/dis.gif" border="0" /></a></td>
		<td><a href="javascript:smiles(\':o\')"><img alt="emo" src="'.DIR.'_emo/eek.gif" border="0" /></a></td>
		<td><a href="javascript:smiles(\':???:\')"><img alt="emo" src="'.DIR.'_emo/he.gif" border="0" /></a></td>
	</tr>
	<tr class="content2">
		<td><a href="javascript:smiles(\':help:\')"><img alt="emo" src="'.DIR.'_emo/help.gif" border="0" /></a></td>
		<td><a href="javascript:smiles(\':kali:\')"><img alt="emo" src="'.DIR.'_emo/kali.gif" border="0" /></a></td>
		<td><a href="javascript:smiles(\':*\')"><img alt="emo" src="'.DIR.'_emo/kiss.gif" border="0" /></a></td>
		<td><a href="javascript:smiles(\':!!!:\')"><img alt="emo" src="'.DIR.'_emo/mad.gif" border="0" /></a></td>
	</tr>
	<tr class="content2">
		<td><a href="javascript:smiles(\':nono:\')"><img alt="emo" src="'.DIR.'_emo/nono.gif" border="0" /></a></td>
		<td><a href="javascript:smiles(\':puke:\')"><img alt="emo" src="'.DIR.'_emo/puke.gif" border="0" /></a></td>
		<td><a href="javascript:smiles(\':rotfl:\')"><img alt="emo" src="'.DIR.'_emo/rotfl.gif" border="0" /></a></td>
		<td><a href="javascript:smiles(\':(\')"><img alt="emo" src="'.DIR.'_emo/sad.gif" border="0" /></a></td>
	</tr>
	<tr class="content2">
		<td><a href="javascript:smiles(\':S\')"><img alt="emo" src="'.DIR.'_emo/sleepy.gif" border="0" /></a></td>
		<td><a href="javascript:smiles(\':)\')"><img alt="emo" src="'.DIR.'_emo/smile.gif" border="0" /></a></td>
		<td><a href="javascript:smiles(\':P\')"><img alt="emo" src="'.DIR.'_emo/tongue.gif" border="0" /></a></td>
		<td><a href="javascript:smiles(\';)\')"><img alt="emo" src="'.DIR.'_emo/wink.gif" border="0" /></a></td>
	</tr></table>
	</td><td>
	<form method="post" name="new_com" action="index.php?'.KML_LINK_SL.'op=add_com"><input type="hidden" name="item" value="'.$idi.'" /><input type="hidden" name="idc" value="'.$inf['idc'].'" /><input type="hidden" name="id" value="'.$id.'" /><input type="hidden" name="link" value="'.str_replace('&', '&amp;', $_SERVER['HTTP_REFERER']).'" />
	<table class="tab_brdr" cellspacing="1" cellpadding="4">
	<tr><td class="content2" valign="top"><textarea name="comment" rows="8" cols="50">'.post_edit($inf['comment']).'</textarea></td></tr>
	<tr><td class="content2" align="right">';
	if($vote==1) $rtr .= $vote_head.': <select name="vote">'.array_assoc($vote_array,$inf['vote']).'</select> ';
	$rtr .= $options.'</td></tr>
	</table>
	</form>
	</td></tr>
	</table>';
	return $rtr;
}

Function edit_comment($idc,$grants,$login,$vote=0,$vote_head='',$vote_array=''){
	global $lang;
	$rtr = content_line('T',$lang['edit_comment']);
	$rtr .= '<td align="center">';
	$qry = 'SELECT idcomment AS idc, comment FROM '.KML_PREFIX.'_comments WHERE date>'.(time()-60*60*24*7).' AND idcomment="'.(int)$idc.'"';
	if($grants==0) $qry .= ' AND iduser='.(int)$login;
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	if(mysql_num_rows($rsl)!=1) die($lang['sure']);
	$row = mysql_fetch_assoc($rsl);
	$rtr .= comment_form('','',$row,$vote,$vote_head,$vote_array);
	$rtr .= content_line('B');
	return $rtr;
}

Function add_news($dts,$grants,$login){
	global $lang;
	$rtr = content_line('T',$lang['usr_news']);
	$rtr .= '<td valign="top"><br/>';
	if($grants<1) $rtr .= $lang['comm_info'];
	else{
		$rtr .= '<form action="index.php?'.KML_LINK_SL.'op=anews" name="formula" method="post">';
		switch($dts['opt']){
			case strtoupper($lang['approve']):{
				foreach($dts as $ky=>$vl) $dts[$ky] = trim(str_replace('\'', '"', $vl));
				$qry = SQL('INSERT INTO '.KML_PREFIX.'_news(iduser, date, title, content, visible, service) VALUES(%d, "'.time().'", %s, %x, "X", "'.MAIN_ID.'")', $login, $dts['title'], $dts['descr']);
				if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) $rtr .= $lang['news_added'].'<br/><br/>';
				break;
			}
			case $lang['add']:{
				foreach($dts as $ky=>$vl) $dts[$ky] = trim(str_replace('"', '\'', $vl));
				if(!$dts['title'] || !$dts['descr']) $rtr .= $lang['news_err'];
				else{
					$rtr .= '<input type="hidden" name="title" value="'.$dts['title'].'"/><input type="hidden" name="descr" value="'.$dts['descr'].'"/>
					<table class="tab_brdr" cellspacing="1" cellpadding="5" width="500">
				<tr><td class="content3">'.ucfirst($lang['title']).':</td><td class="content1">'.$dts['title'].'</td></tr>
				<tr><td class="content3" valign="top" width="60">'.ucfirst($lang['content']).':</td><td class="content1">'.stripslashes(ubcode_add(trim(str_replace('\'', '"', $dts['descr'])))).'</td></tr>
				<tr><td class="content3" colspan="2" align="right"><input type="submit" name="opt" value="'.$lang['edit'].'"/> <input type="submit" name="opt" value="'.strtoupper($lang['approve']).'"/></td></tr>
				</table>';
				}
				break;
			}
			default:{
				foreach($dts as $ky=>$vl) $dts[$ky] = trim(str_replace('\'', '"', $vl));
				$rtr .= '<table class="tab_brdr" cellspacing="1" cellpadding="5">
				<tr><td class="content3">'.ucfirst($lang['title']).':</td><td class="content1"><input type="text" name="title" size="30" maxlength="40" value="'.$dts['title'].'"/></td></tr>
				<tr><td class="content3" valign="top">'.ucfirst($lang['content']).':</td><td class="content1"><textarea name="descr" cols="50" rows="12" maxlength="65000">'.$dts['descr'].'</textarea></td></tr>
				<tr><td class="content3" colspan="2" align="right"><input type="submit" name="opt" value="'.$lang['add'].'"/></td></tr>
				</table>';
				$rtr .= ubbcodes_flags2(2);
			}
		}
		$rtr .= '</form>';
	}
	$rtr .= content_line('B');
	return $rtr;
}

?>
