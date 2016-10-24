<?php
# NEWS

Function news($dts,$gidn){
	global $alang;
	$encode = get_langs();
	$visible = array('Y'=>strtolower($alang['yes']), 'N'=>strtolower($alang['no']), 'X'=>$alang['to_approve']);
	$inf = $alang['news'];
	if($gidn){
		$dts['idn'] = $gidn;
		$dts['opt'] = $alang['edit'];
	}
	if($dts['idn'] || $dts['id']){
		if(!$dts['idn']) $dts['idn'] = $dts['id'];
		$qry = 'SELECT date, idn, content, title, title_enc, content_enc, encode, visible FROM '.KML_PREFIX.'_news WHERE service='.MAIN_ID.' AND idn='.(int)$dts['idn'];
		if($_SESSION['dl_grants'][MAIN_ID]<2) $qry .= ' and iduser='.(int)$_SESSION['dl_login'];
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		if(mysql_num_rows($rsl)!=1) die($alang['sure']);
		$row = mysql_fetch_assoc($rsl);
		foreach($row as $ky=>$vl){
			if($ky!='content' && $ky!='content_enc') $row[$ky] = intoBrowser($vl);
		}
		$inf = $alang['news'].': '.$row['title'];
	}
	option_head($inf);
	echo formHead('admin.php?'.KML_LINK_SL.'op=news',10,2,'','','formula');
	if($dts['idn'] || $dts['id']) println('<input type="hidden" name="idn" value='.$dts['idn'].'>');
	print('<tr><td valign="top">');
	switch($dts['opt']){
		case $alang['yes']:{
			$qry = 'DELETE FROM '.KML_PREFIX.'_news WHERE idn='.(int)$dts['idn'];
			if($_SESSION['dl_grants'][MAIN_ID]<2) $qry .= ' and iduser='.(int)$_SESSION['dl_login'];
			if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) action_info('r',strtolower($alang['news']));
			$qry = 'DELETE FROM '.KML_PREFIX.'_comments WHERE iditem='.(int)$dts['idn'].' AND type='.MAIN_ID;
			if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) action_info('r',$alang['comments']);
			break;
		}
		case $alang['delete']: action_info('x',strtolower($alang['news']).' "'.$row['title'].'"'); break;
		case $alang['save']:{
			if(empty($dts['title']) || empty($dts['descr'])) die($alang['news_err']);
			if(DEFAULT_LANG!=$_SESSION['dl_config']['lang']){
				$tile = 'title_enc';
				$content = 'content_enc';
				$encode_ins = SQL(', encode=%s', $dts['encode']);
			}else{
				$tile = 'title';
				$content = 'content';
			}
			$qry = SQL('UPDATE '.KML_PREFIX.'_news SET '.$tile.'=%s, visible=%s, '.$content.'=%m'.$encode_ins.' WHERE idn=%d', $dts['title'], $dts['visible'], $dts['descr'], $dts['idn']);
			if($_SESSION['dl_grants'][MAIN_ID]<2) $qry .= ' and iduser='.(int)$_SESSION['dl_login'];
			if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) action_info('s',strtolower($alang['news']));
			break;
		}
		case $alang['add']:{
			if ($dts['title'] && $dts['descr']){
				if(DEFAULT_LANG!=$_SESSION['dl_config']['lang']){
					$tile = 'title_enc';
					$content = 'content_enc';
					$encode_list = ', encode';
					$encode_ins = SQL(', %s', $dts['encode']);
				}else{
					$tile = 'title';
					$content = 'content';
				}
				$qry = SQL('INSERT INTO '.KML_PREFIX.'_news(iduser, date, '.$tile.', '.$content.', visible, service'.$encode_list.') VALUES(%d, "'.time().'", %s, %m, %s, "'.MAIN_ID.'"'.$encode_ins.')', $_SESSION['dl_login'], $dts['title'], $dts['descr'], $dts['visible']);
				if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) action_info('a',strtolower($alang['news']));
			}else println($alang['news_err']);
			break;
		}
		case $alang['edit']:{
			$buttons = array(array('reset', '', $alang['reset']), array('submit', 'opt', $alang['delete']), array('submit', 'opt', $alang['save']));
			echo '<table>'.
			formSelectAssoc(1,$alang['visible'],'visible',$visible,$row['visible']);
			if(DEFAULT_LANG!=$_SESSION['dl_config']['lang']){
				echo formSelectAssoc(1,$alang['encode'],'encode',$encode,$row['encode']);
				$tile = 'title_enc';
				$content = 'content_enc';
			}else{
				$tile = 'title';
				$content = 'content';
			}
			echo formText(1,$alang['subject'],'title',$row[$tile],30,40).
			formTextarea(1,ucfirst($alang['content']),'descr',ubcode_rem($row[$content]),65000,13,50).
			formButtons($buttons).
			'</table>';
			break;
		}
		default:{
			$buttons = array(array('submit', 'opt', $alang['add']));
			echo '<table>'.
			formSelectAssoc(1,$alang['visible'],'visible',$visible,'Y');
			if(DEFAULT_LANG!=$_SESSION['dl_config']['lang']){
				echo formSelectAssoc(1,$alang['encode'],'encode',$encode,$_SESSION['dl_config']['lang']);
			}
			echo formText(1,$alang['subject'],'title','',30,40).
			formTextarea(1,ucfirst($alang['content']),'descr','',65000,13,50).
			formButtons($buttons).
			'</table>';
		}
	}
	println('</form>
	</td><td valign="top">
	<div class="head">'.$alang['news_list'].'</div><br/>');
	$qry = 'SELECT n.date, n.title, u.login, n.visible, n.idn FROM '.KML_PREFIX.'_news AS n, '.KML_PREFIX.'_users AS u WHERE n.service='.MAIN_ID.' AND n.iduser=u.iduser';
	if($_SESSION['dl_grants'][MAIN_ID]<2) $qry .= ' and n.iduser='.(int)$_SESSION['dl_login'];
	$qry .= ' ORDER BY n.idn DESC LIMIT 0,40';
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	println('<form method="post" action="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=news">
	<select name="idn" size="10">');
	while($row = mysql_fetch_assoc($rsl)){
		foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
		if ($row['visible']==1) $visible = $alang['no']; else $visible = $alang['yes'];
		print('<option value="'.$row['idn'].'"');
		if($row['idn']==$dts['idn']) print(' selected');
		println('>'.show_date($row['date'],2).' | '.$row['title'].' | '.substr(strtoupper($alang['visible']),0,3).': '.$visible);
	}
	println('</select><br/><span class="bold">ID:</span> <input type="text" name="id" size="5" value="'.$dts['idn'].'"> <input type=submit name="opt" value="'.$alang['delete'].'"> <input type=submit name="opt" value="'.$alang['edit'].'">
	</form>');
	ubbcodes_flags(3);
	print('</td></tr></table>');
}

# COMMENTS

Function comment($id,$option=''){
	global $alang;
	option_head($alang['comment'].' #'.$id);
	if($option=='del'){
		$qry = 'DELETE FROM '.KML_PREFIX.'_comments WHERE idcomment='.(int)$id;
		if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) action_info('r',strtolower($alang['comment']));
	}else{
		$qry = 'SELECT c.date, c.idcomment, c.comment, c.ip, u.iduser, u.login FROM '.KML_PREFIX.'_comments AS c, '.KML_PREFIX.'_users AS u WHERE c.idcomment='.(int)$id.' AND c.iduser=u.iduser';
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		$row = mysql_fetch_assoc($rsl);
		foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
		print('<div align="left">'.show_date($row['date'],2).' <span class="bold">'.$row['login'].'</span><span class="italic">IP: '.$row['ip']);
		$hip = gethostbyaddr($row['ip']);
		if ($row['ip'] != $hip) print(' ('.$hip.')');
		println('</span></div>
		<blockquote>'.$row['comment'].'</blockquote>
		<a href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=comment&id='.$row['idcomment'].'&option=del">'.$alang['delete'].'</a> | <a href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=ban&ip='.$row['ip'].'&opt='.$alang['add'].'">BAN</a> | <a href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=edit_user&id='.$row['iduser'].'">'.$alang['suser_edit'].'</a>');
	}
}

?>
