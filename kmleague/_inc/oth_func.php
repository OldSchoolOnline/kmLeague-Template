<?php

Function println($param){
 	if(isset($param)) print($param . "\n");
}

Function othercuts($param){
	$param = stripslashes($param);
	$param = htmlspecialchars($param);
	return $param;
}

Function banned($ip){
	$qry = SQL('select ip from '.KML_PREFIX.'_bans where ip=%s', $ip);
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	if(mysql_num_rows($rsl) > 0) return 1;
	return 0;
}

Function flag($idf,$typ){
	global $countries2;
	global $countries;
	if($typ==1) return $countries2[$idf];
	else return $countries[$idf];
}

Function show_date($time, $typ=1, $format=''){
	if($time>0){
		$timeshift = (TIMEFIX*60*60)+$_SESSION['dl_timezone']*60;
		if($typ==1) return date('Y-m-d', $time+$timeshift);
		if($typ==2) return date('Y-m-d H:i', $time+$timeshift);
		if($typ==3) return date($format, $time+$timeshift);
	}
}

Function ubcode_add($param){
	global $countries2;
	$param = str_replace('\n', '<br/>', $param);
	$param = stripslashes($param);
	$param = htmlspecialchars($param);
	$param = eregi_replace('\[SCREEN=&quot;([[:alnum:]]+)&quot;\]', '<img alt="screen" class="screen" src="screen/\\1.jpg" />', $param);
	$param = preg_replace('!\[MAIL=&quot;([[:alnum:]@\._]+)&quot;\](.*)\[/MAIL\]!Usi', '<a href="mailto:\\1">\\2</a>', $param);
	$param = eregi_replace('\[MAIL=&quot;([[:alnum:]@\._]+)&quot;\]', '<a href="mailto:\\1">\\1</a>', $param);
	$param = preg_replace('!\[FLAG=&quot;(.*)&quot;\]!Usie', "'<img alt=\"flag\" src=\"".DIR."_country/'.flag('\\1',1).'.gif\" />'", $param);
	$param = preg_replace('!\[LFLAG=&quot;(.*)&quot;\]!Usi', '<img alt="flag" src="'.DIR.'_country/\\1.gif" />', $param);
	$param = preg_replace('!\[USER=&quot;([[:digit:]]+)&quot;\](.*)\[/USER\]!Usi', '<a class="menu" href="index.php?op=users&amp;id=\\1">\\2</a>', $param);
	$param = preg_replace('!\[URL\]([[:alnum:]]+)://(.*)\[/URL\]!Usi', '<a class="menu" target="_blank" href="\\1://\\2">\\2</a>', $param);
	$param = preg_replace('!\[URL=&quot;([[:alnum:]]+)://(.*)&quot;\](.*)\[/URL\]!Usi', '<a class="menu" target="_blank" href="\\1://\\2">\\3</a>', $param);
	$param = preg_replace('!\[RIMG=&quot;([[:alnum:]]+)://(.*)&quot;\]!Usi', '<img alt="img" src="\\1://\\2" align="right" />', $param);
	$param = preg_replace('!\[LIMG=&quot;([[:alnum:]]+)://(.*)&quot;\]!Usi', '<img alt="img" src="\\1://\\2" align="left" />', $param);
	$param = preg_replace('!\[IMG=&quot;([[:alnum:]]+)://(.*)&quot;\]!Usi', '<img alt="img" src="\\1://\\2" />', $param);
	$param = preg_replace('!\[B\](.*)\[/B\]!Usi', '<span class="bold">\\1</span>', $param);
	$param = preg_replace('!\[I\](.*)\[/I\]!Usi', '<span class="italic">\\1</span>', $param);
	$param = preg_replace('!\[U\](.*)\[/U\]!Usi', '<span class="underline">\\1</span>', $param);
	$param = addslashes($param);
	$param = str_replace('r&lt;br/&gt;', '<br/>', $param);
	$param = str_replace('&lt;br /&gt;rn', '<br/>', $param);
	$param = str_replace('&lt;br /&gt;', '<br/>', $param);
	$param = str_replace('&lt;br/&gt;', '<br/>', $param);
	return $param;
}

Function ubcode_rem($param,$type=1){
	if (get_magic_quotes_gpc()){
		$param = stripslashes($param);
	}
	$param = ereg_replace('<img alt="screen" class="screen" src="screen/([[:alnum:]]+).jpg" />', '[SCREEN="\\1"]', $param);
	$param = preg_replace('!<a href="mailto:([[:alnum:]@\._]+)">(.*)</a>!Usi', '[MAIL="\\1"]\\2[/mail]', $param);
	$param = preg_replace('!<img alt="flag" src="'.DIR.'_country/([[:digit:]]+).gif" />!Usie', "'[FLAG=\"'.flag('\\1',2).'\"]'", $param);
	$param = str_replace('`','"',$param);
	$param = preg_replace('!<a class="menu" href="index.php\?op=users&amp;id=([[:digit:]]+)">(.*)</a>!Usi', '[USER="\\1"]\\2[/USER]', $param);
	$param = preg_replace('!<a class="menu" target="_blank" href="([[:alnum:]]+)://(.*)">(.*)</a>!Usi', '[URL="\\1://\\2"]\\3[/URL]', $param);
	$param = preg_replace('!<img alt="img" src="([[:alnum:]]+)://(.*)" />!Usi', '[IMG="\\1://\\2"]', $param);
	$param = preg_replace('!<img alt="img" src="([[:alnum:]]+)://(.*)" align="right" />!Usi', '[RIMG="\\1://\\2"]', $param);
	$param = preg_replace('!<img alt="img" src="([[:alnum:]]+)://(.*)" align="left" />!Usi', '[LIMG="\\1://\\2"]', $param);
	$param = preg_replace('!<span class="italic">(.*)</span>!Usi', '[I]\\1[/I]', $param);
	$param = preg_replace('!<span class="bold">(.*)</span>!Usi', '[B]\\1[/B]', $param);
	$param = preg_replace('!<span class="underline">(.*)</span>!Usi', '[U]\\1[/U]', $param);
	#$param = str_replace("\n\n", "\n", $param);
	if($type==1){
		$param = str_replace('<br/>', "\n", $param);
		$param = str_replace('<br />', "\n", $param);
	}else{
		$param = str_replace('<br/>', '', $param);
	}
	return $param;
}

Function comm_emo($param){
	$param = str_replace('\n', '<br/>', $param);
	$param = stripslashes($param);
	$param = htmlspecialchars($param);
	$param = str_replace(';)', '<img alt="emo" src="'.DIR.'_emo/wink.gif" />', $param);
	$param = str_replace(':P', '<img alt="emo" src="'.DIR.'_emo/tongue.gif" />', $param);
	$param = str_replace(':)', '<img alt="emo" src="'.DIR.'_emo/smile.gif" />', $param);
	$param = str_replace(':S', '<img alt="emo" src="'.DIR.'_emo/sleepy.gif" />', $param);
	$param = str_replace(':(', '<img alt="emo" src="'.DIR.'_emo/sad.gif" />', $param);
	$param = str_replace(':rotfl:', '<img alt="emo" src="'.DIR.'_emo/rotfl.gif" />', $param);
	$param = str_replace(':nono:', '<img alt="emo" src="'.DIR.'_emo/nono.gif" />', $param);
	$param = str_replace(':!!!', '<img alt="emo" src="'.DIR.'_emo/mad.gif" />', $param);
	$param = str_replace(':*', '<img alt="emo" src="'.DIR.'_emo/kiss.gif" />', $param);
	$param = str_replace(':help:', '<img alt="emo" src="'.DIR.'_emo/help.gif" />', $param);
	$param = str_replace(':???:', '<img alt="emo" src="'.DIR.'_emo/he.gif" />', $param);
	$param = str_replace(':o', '<img alt="emo" src="'.DIR.'_emo/eek.gif" />', $param);
	$param = str_replace(':dir:', '<img alt="emo" src="'.DIR.'_emo/director.gif" />', $param);
	$param = str_replace(':dis:', '<img alt="emo" src="'.DIR.'_emo/dis.gif" />', $param);
	$param = str_replace(':[', '<img alt="emo" src="'.DIR.'_emo/cry.gif" />', $param);
	$param = str_replace(':D', '<img alt="emo" src="'.DIR.'_emo/blaugh.gif" />', $param);
	$param = str_replace(':arg:', '<img alt="emo" src="'.DIR.'_emo/argue.gif" />', $param);
	$param = str_replace(':kali:', '<img alt="emo" src="'.DIR.'_emo/kali.gif" />', $param);
	$param = str_replace(':puke:', '<img alt="emo" src="'.DIR.'_emo/puke.gif" />', $param);
	$param = str_replace(':cool:', '<img alt="emo" src="'.DIR.'_emo/cool.gif" />', $param);
#	$param = preg_replace('!http://(www\.)?([a-zA-Z0-9])+(\.([a-zA-Z0-9\_])+/?){0,}/?!Usie', '<a target="_blank" href="\\1">\\1</a>', $param);
	$param = addslashes($param);
	$param = str_replace('&lt;br /&gt;rn', '<br/>', $param);
	$param = str_replace('&lt;br /&gt;', '<br/>', $param);
	$param = str_replace('r&lt;br/&gt;', '<br/>', $param);
	return $param;
}

Function post_edit($param){
	if (get_magic_quotes_gpc()){
		$param = stripslashes($param);
	}
	$param = str_replace('<img alt="emo" src="'.DIR.'_emo/wink.gif" />', ';)', $param);
	$param = str_replace('<img alt="emo" src="'.DIR.'_emo/tongue.gif" />', ':P', $param);
	$param = str_replace('<img alt="emo" src="'.DIR.'_emo/smile.gif" />', ':)', $param);
	$param = str_replace('<img alt="emo" src="'.DIR.'_emo/sleepy.gif" />', ':S:', $param);
	$param = str_replace('<img alt="emo" src="'.DIR.'_emo/sad.gif" />', ':(', $param);
	$param = str_replace('<img alt="emo" src="'.DIR.'_emo/rotfl.gif" />', ':rotfl:', $param);
	$param = str_replace('<img alt="emo" src="'.DIR.'_emo/nono.gif" />', ':nono:', $param);
	$param = str_replace('<img alt="emo" src="'.DIR.'_emo/mad.gif" />', ':!!!', $param);
	$param = str_replace('<img alt="emo" src="'.DIR.'_emo/kiss.gif" />', ':*', $param);
	$param = str_replace('<img alt="emo" src="'.DIR.'_emo/help.gif" />', ':help:', $param);
	$param = str_replace('<img alt="emo" src="'.DIR.'_emo/he.gif" />', '???', $param);
	$param = str_replace('<img alt="emo" src="'.DIR.'_emo/eek.gif" />', ':o', $param);
	$param = str_replace('<img alt="emo" src="'.DIR.'_emo/director.gif" />', ':dir:', $param);
	$param = str_replace('<img alt="emo" src="'.DIR.'_emo/dis.gif" />', ':dis:', $param);
	$param = str_replace('<img alt="emo" src="'.DIR.'_emo/cool.gif" />', ':ql:', $param);
	$param = str_replace('<img alt="emo" src="'.DIR.'_emo/cry.gif" />', ':[', $param);
	$param = str_replace('<img alt="emo" src="'.DIR.'_emo/blaugh.gif" />', ':D', $param);
	$param = str_replace('<img alt="emo" src="'.DIR.'_emo/argue.gif" />', ':arg:', $param);
	$param = str_replace('<img alt="emo" src="'.DIR.'_emo/kali.gif" />', ':kali:', $param);
	$param = str_replace('<img alt="emo" src="'.DIR.'_emo/puke.gif" />', ':puke:', $param);
	$param = str_replace('<br/>', "\n", $param);
	$param = str_replace('<br />', "\n", $param);
	return $param;
}

Function get_langs(){
	$langdir = DIR.'_lang/';
	$dir = opendir($langdir);
	while($file = readdir($dir)){
		if($file!='.' && $file!='..' && $file!='readme.txt' && $file!='index.html'){
			$inf = explode('.', $file);
			$langs[$inf[0]] = $inf[0];
		}
	}
	return $langs;
}

Function raport_error($file,$function,$line,$qry,$typ=1){
	$message = $file."\n<br/>\n".$function."\n<br/>\n".$line."\n<br/>\n".$qry;
	if(mail('cl@epf.pl', 'KMleague error', $message)){
		if($typ==1) print('Error sent to author.');
	}else{
		if($typ==1) print('Can\'t send mail, you can send message bellow manually on address <a href="mailto:cl@pf.pl">cl@epf.pl</a><br/><br/><span class="italic">'.$message.'</span>');
	}
	if($typ==1) exit;
}

Function links(){
	$rtr .= '&raquo; <a class="menu" target="_blank" href="http://kmprojekt.pl">Tworzenie WWW</a><br/>&raquo; <a class="menu" target="_blank" href="http://www.deathmatchzone.com">Deathmatch Zone</a><br/>';
	return $rtr;
}

Function ubbcodes_flags($type=1){
	global $lang;
	global $countries;
	if($type==2) print('<table><tr><td valign="top">');
	?>
	<div class="blockHead" onclick="flip('ubbcodes');"><?=$lang['codes']?></div>
	<div class="block" id="ubbcodes">
	<table>
	<?php
	if($type==1) print('<tr><td class="blockElement" onClick=dodaj(\'[SCREEN=""]\')>[SCREEN="screen_name"]<div class="italic">ie.: [SCREEN="Q2DM3ff41064438106"]</div></td></tr>');
	?>
	<tr><td class="blockElement" onClick=dodaj('[MAIL=""][/MAIL]')>[MAIL="mail_address"]text[/MAIL]<div class="italic">ie.: [MAIL="cl@pf.pl"]Claymore[/MAIL]</div></td></tr>
	<tr><td class="blockElement" onClick=dodaj('[FLAG=""]')>[FLAG="country"]<div class="italic">ie.: [FLAG="USA"]</div></td></tr>
	<tr><td class="blockElement" onClick=dodaj('[USER=""][/USER]')>[USER="user_id"]nick_name[/USER]<div class="italic">ie.: [USER="1"]Claymore[/USER]</div></td></tr>
	<tr><td class="blockElement" onClick=dodaj('[URL="http://"][/URL]')>[URL="protocol://address"]text[/URL]<div class="italic">ie.: [URL="http://ds.q2scene.net"]demosquad[/URL]</div></td></tr>
	<tr><td class="blockElement" onClick=dodaj('[URL][/URL]')>[URL]protocol://address[/URL]<div class="italic">ie.: [URL]http://ds.q2scene.net[/URL]</div></td></tr>
	<tr><td class="blockElement" onClick=dodaj('[I][/I]')>[I]text[/I]<div class="italic">ie.: [I]this text will be italic[/I]</div></td></tr>
	<tr><td class="blockElement" onClick=dodaj('[B][/B]')>[B]text[/B]<div class="italic">ie.: [B]this text will be bold[/B]</div></td></tr>
	<tr><td class="blockElement" onClick=dodaj('[U][/U]')>[U]text[/U]<div class="italic">ie.: [U]this text will be underline[/U]</div></td></tr>
	<tr><td class="blockElement" onClick=dodaj('[IMG="http://"]')>[IMG="protocol://address"]<div class="italic">ie.: [IMG="http://q2scene.net/euroq2l/img/main.gif"]</div></td></tr>
	<tr><td class="blockElement" onClick=dodaj('[RIMG="http://"]')>[RIMG="protocol://address"]<div class="italic">ie.: [RIMG="http://q2scene.net/euroq2l/img/main.gif"]</div></td></tr>
	<tr><td class="blockElement" onClick=dodaj('[LIMG="http://"]')>[LIMG="protocol://address"]<div class="italic">ie.: [LIMG="http://q2scene.net/euroq2l/img/main.gif"]</div></td></tr>
<?php
	if($type==3) print('<tr><td class="blockElement" onClick="dodaj(\'%%\')">'.$lang['tag_info'].'</td></tr>');
	print('</table>
	</div><br/>');
	if($type==2) print('</td><td valign="top">');
	print('<div class="blockHead" onclick="flip(\'flags\');">'.$lang['flags'].'</div>
	<div class="block" id="flags">
	<table cellspacing="5"><tr>');
	$i = -1;
	foreach($countries as $ky=>$vl){
		if(++$i%3==0) print('</tr><tr>');
		print('<td class="blockElement" onclick="dodaj_flage(\''.$ky.'\')"><img alt="'.$lang['flag'].'" src="'.DIR.'_country/'.$ky.'.gif"> '.$vl.'</td>');
	}
	print('</tr></table>
	</div>');
	if($type==2) print('</td></tr></table>');
}

#uzyte w news.php usunac jak panel admina bedzie na buforowaniu
Function ubbcodes_flags2($type=1){
	global $lang;
	global $countries;
	if($type==2) $rtr = '<table><tr><td valign="top">';
	$rtr .= '<div class="blockHead" onclick="flip(\'ubbcodes\');">'.$lang['codes'].'</div>
	<div class="block" id="ubbcodes">
	<table>';
	if($type==1) $rtr .= '<tr><td class="blockElement" onClick=dodaj(\'[SCREEN=""]\')>[SCREEN="screen_name"]<div class="italic">ie.: [SCREEN="Q2DM3ff41064438106"]</div></td></tr>';
	$rtr .= '
	<tr><td class="blockElement" onClick=dodaj(\'[MAIL=""][/MAIL]\')>[MAIL="mail_address"]text[/MAIL]<div class="italic">ie.: [MAIL="cl@pf.pl"]Claymore[/MAIL]</div></td></tr>
	<tr><td class="blockElement" onClick=dodaj(\'[FLAG=""]\')>[FLAG="country"]<div class="italic">ie.: [FLAG="USA"]</div></td></tr>
	<tr><td class="blockElement" onClick=dodaj(\'[USER=""][/USER]\')>[USER="user_id"]nick_name[/USER]<div class="italic">ie.: [USER="1"]Claymore[/USER]</div></td></tr>
	<tr><td class="blockElement" onClick=dodaj(\'[URL="http://"][/URL]\')>[URL="protocol://address"]text[/URL]<div class="italic">ie.: [URL="http://ds.q2scene.net"]demosquad[/URL]</div></td></tr>
	<tr><td class="blockElement" onClick=dodaj(\'[URL][/URL]\')>[URL]protocol://address[/URL]<div class="italic">ie.: [URL]http://ds.q2scene.net[/URL]</div></td></tr>
	<tr><td class="blockElement" onClick=dodaj(\'[I][/I]\')>[I]text[/I]<div class="italic">ie.: [I]this text will be italic[/I]</div></td></tr>
	<tr><td class="blockElement" onClick=dodaj(\'[B][/B]\')>[B]text[/B]<div class="italic">ie.: [B]this text will be bold[/B]</div></td></tr>
	<tr><td class="blockElement" onClick=dodaj(\'[U][/U]\')>[U]text[/U]<div class="italic">ie.: [U]this text will be underline[/U]</div></td></tr>
	<tr><td class="blockElement" onClick=dodaj(\'[IMG="http://"]\')>[IMG="protocol://address"]<div class="italic">ie.: [IMG="http://q2scene.net/euroq2l/img/main.gif"]</div></td></tr>
	<tr><td class="blockElement" onClick=dodaj(\'[RIMG="http://"]\')>[RIMG="protocol://address"]<div class="italic">ie.: [RIMG="http://q2scene.net/euroq2l/img/main.gif"]</div></td></tr>
	<tr><td class="blockElement" onClick=dodaj(\'[LIMG="http://"]\')>[LIMG="protocol://address"]<div class="italic">ie.: [LIMG="http://q2scene.net/euroq2l/img/main.gif"]</div></td></tr>';
	if($type==3) $rtr .= '<tr><td class="blockElement" onClick="dodaj(\'%%\')">'.$lang['tag_info'].'</td></tr>';
	$rtr .= '</table>
	</div><br/>';
	if($type==2) $rtr .= '</td><td valign="top">';
	$rtr .= '<div class="blockHead" onclick="flip(\'flags\');">'.$lang['flags'].'</div>
	<div class="block" id="flags">
	<table cellspacing="5"><tr>';
	$i = -1;
	foreach($countries as $ky=>$vl){
		if(++$i%3==0) $rtr .= '</tr><tr>';
		$rtr .= '<td class="blockElement" onclick="dodaj_flage(\''.$ky.'\')"><img alt="'.$lang['flag'].'" src="'.DIR.'_country/'.$ky.'.gif"> '.$vl.'</td>';
	}
	$rtr .= '</tr></table>
	</div>';
	if($type==2) $rtr .= '</td></tr></table>';
	return $rtr;
}

Function clear_string($str){
	$str = str_replace(' ', '_', $str);
	$str = str_replace('\'', '', $str);
	return $str;
}

Function send_pm($whom,$who,$title,$content,$mail=1){
	global $lang;
	if($mail==1){
		$qry = 'SELECT send_pm_mail, mail FROM '.KML_PREFIX.'_users WHERE iduser='.(int)$whom;
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		$row = mysql_fetch_assoc($rsl);
		$qry2 = 'SELECT login, name FROM '.KML_PREFIX.'_users WHERE iduser='.(int)$who;
		$rsl2 = query(__FILE__,__FUNCTION__,__LINE__,$qry2,0);
		$row2 = mysql_fetch_assoc($rsl2);
		foreach($row2 as $ky=>$vl) $row2[$ky] = intoBrowser($vl);
		if($row['send_pm_mail']==1){
			$msg = str_replace('{{nick_name}}', $row2['login'], $lang['pm_mail_header'])."\n\n".$content."\n\n".$lang['pm_mail_foot'];
			$header = 'From: '.KML_ADMIN_MAIL;
			mail($row['mail'], $lang['pm_info_mail'],$msg, $header);
		}
	}
	$qry = SQL('INSERT INTO '.KML_PREFIX.'_pm(idauthor, iduser, date, title, content, ip, showed) VALUES(%d, %d, "'.time().'", %s, %x, "'.$_SERVER['REMOTE_ADDR'].'", "N")', $who, $whom, $title, $content);
	if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) return 1;
}

Function get_all_cl($idc){
	$qry = 'SELECT iduser FROM '.KML_PREFIX.'_clan WHERE idc='.$idc;
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	$row = mysql_fetch_assoc($rsl);
	$cls[] = $row['iduser'];
	$qry = 'SELECT iduser FROM '.KML_PREFIX.'_player WHERE approve_user="Y" AND (function="C" OR function="W") AND idc='.$idc;
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	while($row=mysql_fetch_assoc($rsl)) $cls[] = $row['iduser'];
	return $cls;
}

Function create_dates($minute='',$year=''){
	if(!$year[0]) $year[0] = date('Y');
	if(!$year[1]) $year[1] = date('Y')+1;
	$year[1] += 1;
	for($i=$year[0];$i<$year[1];$i++){
		$years[] = $i;
	}
	for($i=1;$i<13;$i++){
		if(strlen($i)==1) $j = '0'.$i; else $j=$i;
		$months[] = $j;
	}
	for($i=1;$i<32;$i++){
		if(strlen($i)==1) $j = '0'.$i; else $j=$i;
		$days[] = $j;
	}
	for($i=0;$i<24;$i++){
		if(strlen($i)==1) $j = '0'.$i; else $j=$i;
		$hours[] = $j;
	}
	if(!$minute) $minute = 5;
	for($i=0;$i<60;$i+=$minute){
		if(strlen($i)==1) $j = '0'.$i; else $j=$i;
		$minutes[] = $j;
	}
	return array('years'=>$years, 'months'=>$months, 'days'=>$days, 'hours'=>$hours, 'minutes'=>$minutes);
}

Function pages($head,$all,$p,$link,$limit=10,$add_name='',$add_link_vl=''){
	if($all>1){
		$rtr .= '<div class="list">'.$head;
		if($add_name){
			if($p==$add_link_vl) $rtr .= '<span class="bold">'.$add_name.'</span> | '; else $rtr .= '<a class="list_link" href="index.php?'.$link.'&amp;p='.$add_link_vl.'">'.$add_name.'</a> | ';
		}
		if($p>=$limit) $rtr .= '<a class="list_link" href="index.php?'.$link.'&amp;p=0">1</a> | ';
		if($p>$limit) $rtr .= '... | ';
		for($i=0;$i<$all;$i++){
			if($i>$p-$limit && $i<$p+$limit){
				if($i!=$p) $rtr .= '<a class="list_link" href="index.php?'.$link.'&amp;p='.$i.'">'.($i+1).'</a>'; else $rtr .= '<span class="bold">'.($i+1).'</span>';
				if($i!=$all-1) $rtr .= ' | ';
			}
		}
		if($p<$all-($limit+1)) $rtr .= ' ... | ';
		if($p<$all-$limit) $rtr .= '<a class="list_link" href="index.php?'.$link.'&amp;p='.($all-1).'">'.$all.'</a>';
		$rtr .= '</div>';
	}
	return $rtr;
}

?>
