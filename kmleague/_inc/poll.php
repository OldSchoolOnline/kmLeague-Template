<?php

Function active_polls($login){
	global $lang;
	$qry = 'SELECT idp, question, up_time, active, st_time FROM '.KML_PREFIX.'_p_main WHERE service="'.MAIN_ID.'" AND active="Y" AND (up_time="0" OR up_time>"'.(time()-POLL_LIVE_TIME*86400).'") ORDER BY idp DESC';
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	$active_polls = mysql_num_rows($rsl);
	if($active_polls>0){
		while($row=mysql_fetch_assoc($rsl)){
			foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
			$rtr .= show_poll($login,$row);
		}
	}
	return $rtr;
}

Function scores_poll($id, $info){
	global $lang;
	$id = intval($id);
	$qry = 'SELECT COUNT(c.id_user) AS count, a.ida AS id, a.answer FROM '.KML_PREFIX.'_p_answers AS a LEFT JOIN '.KML_PREFIX.'_p_choices AS c USING(ida) WHERE a.idp='.$id.' GROUP BY a.ida ORDER BY count DESC';
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	while($row = mysql_fetch_assoc($rsl)){
		foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
		$scores[] = array($row['answer'], $row['count']);
		$all_voices += $row['count'];
	}
	$mrow = info_poll($id);
	foreach($mrow as $ky=>$vl) $mrow[$ky] = intoBrowser($vl);
	if($mrow!=0){
		$rtr .= '<table width="95%" cellpadding="4" cellspacing="0">
		<tr><td colspan="2" class="bold">'.$mrow['question'].'</td></tr>';
		if($all_voices==0){
			$rtr .= '<tr><td>'.$lang['poll_nv'].'</td></tr>';
		}else{
			for($i=0;$i<count($scores);$i++){
				$avg = round(($scores[$i][1]/$all_voices)*100);
				$rtr .= '<tr><td>'.$scores[$i][0].' <br/><img alt="bb" src="'.DIR.'_img/bb.gif" width="1" height="11" /><img alt="vote" src="'.SKIN.'img/vote.gif" style="height: 11px; width: '.$avg.'px" /><img alt="bb" src="'.DIR.'_img/bb.gif" style="height: 11px; width: '.(100-$avg).'px" /><img alt="bb" src="'.DIR.'_img/bb.gif" style="width: 1px; height: 11px" /><br/>'.$scores[$i][1].' ('.$avg.'%)</td></tr>';
			}
			$rtr .= '<tr><td>'.ucfirst($lang['voices']).': '.$all_voices.'</td></tr>';
		}
		$rtr .= '</table>';
		if($info==1){
			$rtr .= $lang['poll_st'].' '.show_date($mrow['st_time'], 2).' ';
			if($mrow['active']=='N') $rtr .= $lang['poll_if'];
			if($mrow['up_time']>0 && $mrow['active']=='Y') if(time()<$mrow['up_time']) $rtr .= ' ('.$lang['poll_ft'].': '.show_date($mrow['up_time'], 2).')';
			$rtr .= '.';
		}
	}
	return $rtr;
}

Function info_poll($id){
	$qry = 'SELECT idp, question, up_time, active, st_time FROM '.KML_PREFIX.'_p_main WHERE idp='.$id;
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	if(mysql_num_rows($rsl)>0) return mysql_fetch_assoc($rsl);
	else return 0;
}

Function archive_poll($id, $id_user){
	global $lang;
	$yesno = array('Y'=>ucfirst(strtolower($lang['yes'])), 'N'=>ucfirst(strtolower($lang['no'])), 'A'=>ucfirst(strtolower($lang['no'])));
	$rtr = content_line('T',$lang['polls']);
	$rtr .= '<td>';
	if($id){
		$rtr .= scores_poll($id, $info);
	}else{
		$qry = 'SELECT idp, question, up_time, active, st_time FROM '.KML_PREFIX.'_p_main WHERE active!="N" AND service="'.MAIN_ID.'" ORDER BY idp DESC LIMIT 0,50';
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		if(mysql_num_rows($rsl)>0){
			$rtr .= '<table class="tab_brdr" cellspacing="1" cellpadding="5">
			<tr><td class="tab_head3">'.$lang['date'].'</td><td class="tab_head3">'.$lang['question'].'</td><td class="tab_head3">'.ucfirst($lang['active']).'</td></tr>';
			while($row=mysql_fetch_assoc($rsl)){
				foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
				if(time()>$row['up_time'] && $row['up_time']>0) $row['active'] = 'N';
				$rtr .= '<tr class="content3"><td>'.show_date($row['st_time'], 1).'</td><td><a class="tab_link2" href="index.php?'.KML_LINK_SL.'op=polls&amp;id='.$row['idp'].'">'.$row['question'].'</a></td><td>'.$yesno[$row['active']].'</td></tr>';
			}
			$rtr .= '</table>
			<br/>';
		}else $rtr .= $lang['no_polls'];
	}
	$rtr .= content_line('B');
	return $rtr;
}

Function add_voice($dts, $login){
	global $lang;
	if(!$dts['id']){
		$rtr .= $lang['sure'];
		return $rtr;
	}
	$ip = $_SERVER['REMOTE_ADDR'];
	if(banned($ip)){
		$rtr .= $lang['ban'].'<br/>('.$lang['comm_wait'].')';
		return $rtr;
	}
	if($login < 1){
		$rtr .= $lang['acc_block'].'<br/>('.$lang['comm_wait'].')';
		return $rtr;
	}
	$qry = 'SELECT c.id_user from '.KML_PREFIX.'_p_choices AS c, '.KML_PREFIX.'_p_answers AS a where c.ida=a.ida and c.ida='.(int)$dts['id'].' and a.idp='.(int)$dts['pool'].' and c.id_user='.(int)$login;
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	if(mysql_num_rows($rsl)>0) $rtr .= $lang['voice_err'].'<br/>('.$lang['comm_wait'].')';
	else{
		$qry = 'INSERT INTO '.KML_PREFIX.'_p_choices(id_user, ida) VALUES("'.(int)$login.'", "'.(int)$dts['id'].'")';
		if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) $rtr .= $lang['voice_add'].'<br/>('.$lang['comm_wait'].')';
	}
	return $rtr;
}

?>
