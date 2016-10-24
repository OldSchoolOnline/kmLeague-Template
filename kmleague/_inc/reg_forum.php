<?php

$forum_types = array('', 'phpBB');

Function create_account_kml($passw,$login,$mail,$hash=1){
	if($hash==1) $passw = md5($passw);
	$qry = SQL('INSERT INTO '.KML_PREFIX.'_users(login, pass, mail, `grants`, added, avatar, show_mail) VALUES(%s, %s, %s, "1", "'.time().'", "1", "N")', $login, $passw, $mail);
	query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	return mysql_insert_id();
}

function reg_forum($id){
	if(FORUM=='phpBB') reg_forum_phpbb($id);
}

function pass_forum($id,$npass){
	if(FORUM=='phpBB') pass_forum_phpbb($id,$npass);
}

#tested with phpBB ver. 2.0.20-.21
function reg_forum_phpbb($id){
	$qry = 'SELECT iduser, login, pass, added, country, mail FROM '.KML_PREFIX.'_users WHERE iduser='.$id;
	if($row['country']==36) $lang = 'polish'; else $lang = 'english';
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	if(mysql_num_rows($rsl)>0){
		$row = mysql_fetch_assoc($rsl);
		foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
		$qry = 'INSERT INTO '.FORUM_PREFIX.'_users(user_id, user_active, username, user_password, user_session_time, user_session_page, user_lastvisit, user_regdate, user_level, user_posts, user_timezone, user_style, user_lang, user_dateformat, user_new_privmsg, user_unread_privmsg, user_last_privmsg, user_login_tries, user_last_login_try, user_emailtime, user_viewemail, user_attachsig, user_allowhtml, user_allowbbcode, user_allowsmile, user_allowavatar, user_allow_pm, user_allow_viewonline, user_notify, user_notify_pm, user_popup_pm, user_rank, user_avatar, user_avatar_type, user_email) VALUES("'.$row['iduser'].'", "1", "'.$row['login'].'", "'.$row['pass'].'", "0", "0", "0", "'.$row['added'].'", "0", "0", "0.00", "1", "'.$lang.'", "d M Y h:i", "0", "0", "0", "0", "0", NULL, "0", "0", "0", "1", "1", "1", "1", "1", "0", "1", "0", "0", "", "0", "'.$row['mail'].'")';
		query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		$user_id = $row['iduser'];
		$qry = 'INSERT INTO '.FORUM_PREFIX.'_groups(group_type, group_name, group_description, group_moderator, group_single_user) VALUES(1, "", "Personal User", "0", "1")';
		query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		$group_id = mysql_insert_id();
		$qry = 'INSERT INTO '.FORUM_PREFIX.'_user_group(group_id, user_id, user_pending) VALUES("'.$group_id.'", "'.$user_id.'", "0")';
		query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	}
}

function pass_forum_phpbb($id,$npass){
	$qry = SQL('UPDATE '.FORUM_PREFIX.'_users SET user_password=%s WHERE user_id=%d', $npass, $id);
	query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
}

Function show_last_topics($limit,$section){
	if(FORUM=='phpBB') last_topics_phpbb($limit,$section);
}

#FIX IT
Function last_topics_phpbb($limit,$section){
	global $lang;
	$qry = 'SELECT T.date2, T.idt, T.t_name FROM frm_topic T WHERE T.ids='.(int)$section.' ORDER BY T.date2 DESC LIMIT 0,'.(int)$limit;
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	if(mysql_num_rows($rsl)>0){
		print('<br/>');
		right_box_header($lang['ltopics']);
		while($row = mysql_fetch_assoc($rsl)){
			foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
			$name = $row['t_name'];
			if(strlen($name)>15){
				$name = substr($name, 0, 15);
				$name .= '...';
			}
			print('<a class="menu" href="'.DIR.'forum/index.php?op=t&amp;id='.$row['idt'].'&p=e">'.show_date($row['date2'], 1).' | '.$name.'</a><br/>');
		}
		box_foot();
	}
}

?>