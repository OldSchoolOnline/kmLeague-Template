<?php

Function polls($dts){
	global $alang;
	$yesno = array('Y'=>strtolower($alang['yes']), 'N'=>strtolower($alang['no']), 'A'=>$alang['archive']);
	if($dts['idp']){
		$qry = 'SELECT question, up_time, active FROM '.KML_PREFIX.'_p_main WHERE idp='.(int)$dts['idp'].' AND service='.MAIN_ID;
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		if(mysql_num_rows($rsl)!=1) die($alang['sure']);
		$row = mysql_fetch_assoc($rsl);
		foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
		$inf = ': '.$row['question'];
		$sqry = 'SELECT answer FROM '.KML_PREFIX.'_p_answers WHERE idp='.(int)$dts['idp'].' ORDER BY ida';
		$srsl = query(__FILE__,__FUNCTION__,__LINE__,$sqry,0);
		$dts['old_answers'] = '';
		while($srow=mysql_fetch_assoc($srsl)){
			foreach($srow as $ky=>$vl) $srow[$ky] = intoBrowser($vl);
			if($dot==1) $dts['old_answers'] .= ';';
			$dts['old_answers'] .= $srow['answer'];
			$dot = 1;
		}
		if($dts['opt']==$alang['edit']){
			$dts['question'] = $row['question'];
			$dts['active'] = $row['active'];
			if($row['up_time']==0) $row['up_time'] = 0;
			else $dts['up_time'] = ceil(($row['up_time']-time())/86400);
			$dts['answers'] = $dts['old_answers'];
		}
	}
	if(!$inf) option_head($alang['new_poll']); else option_head($alang['edit_poll'].$inf);
	println('<table cellspacing="10"><tr><td valign="top">
	<form method="post" action="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=poll">');
	if($dts['idp']) println('<input type="hidden" name="idp" value="'.$dts['idp'].'">');
	switch($dts['opt']){
		case $alang['save']:
		case $alang['add']:{
			$answers = explode(';', $dts['answers']);
			$all = count($answers);
			if($all<2) print($alang['poll_err1']);
			println('<table>
			<tr><td colspan="2" class="hd">'.$dts['question'].'</td></tr>');
			for($i=0;$i<$all;$i++){
				if(strlen($answers[$i])>0) println('<tr><td>'.$answers[$i].'</td><td><input type="radio" name="sample"></td></tr>');
			}
			if($dts['up_time']>0){
				$dts['up_time'] = time() + $dts['up_time']*86400;
				println('<tr><td colspan="2" align="right">'.$alang['poll_fsh'].' '.show_date($dts['up_time'],2).'</td></tr>'); 
			}elseif($dts['up_time']==0) println('<tr><td colspan="2" align="right">'.$alang['poll_ntl'].'</td></tr>');
			elseif($dts['up_time']<0) println('<tr><td colspan="2" align="right">'.$alang['poll_fsd'].'</td></tr>');
			print('<tr><td colspan="2"><span class="hd">'.$alang['visible'].':</span> '.$yesno[$dts['active']].'</td></tr>
			<tr><td colspan="2" align="right"><br/><input type="submit" name="opt" value="'.$alang['back'].'"> ');
			if($all>1) print('<input type="submit" name="opt" value="'.$alang['approve'].'">');
			println('</td></tr>
			<input type="hidden" name="answers" value="'.$dts['answers'].'"><input type="hidden" name="old_answers" value="'.$dts['old_answers'].'"><input type="hidden" name="active" value="'.$dts['active'].'"><input type="hidden" name="question" value="'.$dts['question'].'"><input type="hidden" name="up_time" value="'.$dts['up_time'].'">
			</table>');
			break;
		}
		case $alang['delete']: action_info('x',strtolower($alang['poll']).' "'.$row['question'].'"'); break;
		case $alang['yes']:{
			$qry = 'SELECT ida FROM '.KML_PREFIX.'_p_answers WHERE idp='.(int)$dts['idp'];
			$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
			while($row=mysql_fetch_assoc($rsl)){
				$qry = 'DELETE FROM '.KML_PREFIX.'_p_choices WHERE ida='.$row['ida'];
				query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
			}
			$qry = 'DELETE FROM '.KML_PREFIX.'_p_answers WHERE idp='.(int)$dts['idp'];
			query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
			$qry = 'DELETE FROM '.KML_PREFIX.'_p_main WHERE idp='.(int)$dts['idp'];
			query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
			action_info('r',strtolower($alang['poll']));
			break;
		}
		case $alang['approve']:{
			if(!$dts['idp']){
				$qry = SQL('INSERT INTO '.KML_PREFIX.'_p_main(question, up_time, active, st_time, service) VALUES(%s, %d, %s, "'.time().'", "'.MAIN_ID.'")', $dts['question'], $dts['up_time'], $dts['active']);
				query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
				$id = mysql_insert_id();
			}else{
				$qry = SQL('UPDATE '.KML_PREFIX.'_p_main SET question=%s, up_time=%d, active=%s WHERE idp=%d', $dts['question'], $dts['up_time'], $dts['active'], $dts['idp']);
				query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
				$id = $dts['idp'];
			}
			if($dts['answers']!=$dts['old_answers']){
				if($dts['idp']){
					$qry = 'SELECT ida FROM '.KML_PREFIX.'_p_answers WHERE idp='.(int)$dts['idp'];
					$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
					while($row=mysql_fetch_assoc($rsl)){
						$qry = 'DELETE FROM '.KML_PREFIX.'_p_choices WHERE ida='.(int)$row['ida'];
						query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
					}
					$qry = 'DELETE FROM '.KML_PREFIX.'_p_answers WHERE idp='.(int)$dts['idp'];
					if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) print($alang['old_answers_rem']);
				}
				$answers = explode(';', $dts['answers']);
				$all = count($answers);
				for($i=0;$i<$all;$i++){
					if(strlen($answers[$i])>0){
						$qry = SQL('INSERT INTO '.KML_PREFIX.'_p_answers(idp, answer) VALUES(%d, %s)', $id, trim($answers[$i]));
						query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
					}
				}
			}
			if(!$dts['idp']) action_info('a',strtolower($alang['poll'])); else action_info('s',strtolower($alang['poll']));
			break;
		}
		default:{
			if(!$dts['up_time']) $dts['up_time'] = 0;
			println('<table>
			<tr><td class="hd">'.$alang['question'].'</td><td><input type="text" name="question" size="40" maxlength="100" value="'.$dts['question'].'"></td></tr>
			<tr><td class="hd">'.$alang['answers'].'</td><td><input type="text" name="answers" size="40" value="'.$dts['answers'].'"><input type="hidden" name="old_answers" value="'.$dts['old_answers'].'"></td></tr>
			<tr><td class="hd">'.$alang['pdur_time'].'</td><td><input type="text" name="up_time" size="40" value="'.$dts['up_time'].'"></td></tr>
			<tr><td class="hd">'.$alang['visible'].':</td><td><select name="active">'.array_assoc($yesno,$dts['active']).'</select></td></tr>
			<tr><td colspan="2" align="right">');
			if($dts['idp']) print('<input type="submit" name="opt" value="'.$alang['delete'].'"> <input type="submit" name="opt" value="'.$alang['save'].'">'); else print('<input type="submit" name="opt" value="'.$alang['add'].'">');
			print('</td></tr>
			</table>
			<br/><br/>'.$alang['poll_info']);
		}
	}
	print('
	</form></td><td valign="top">');
	$qry = 'SELECT question, idp, active, up_time FROM '.KML_PREFIX.'_p_main WHERE service='.MAIN_ID.' ORDER BY idp DESC LIMIT 0,50';
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	if(mysql_num_rows($rsl)>0){
		while($row=mysql_fetch_assoc($rsl)){
			foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
			$active = 'N';
			if($row['active']=='Y' && $row['up_time']>time()) $active = 'Y';
			$polls[$row['idp']] = $alang['visible'].':'.$yesno[$row['active']].' | '.$row['question'];
		}
		println('<form method="post" action="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=poll">
		<div class="head">'.$alang['poll_list'].'</div><br/>
		<select name="idp" size="10">'.array_assoc($polls,$dts['idp']).'</select><br/>
		<input type="submit" name="opt" value="'.$alang['delete'].'"> <input type="submit" name="opt" value="'.$alang['edit'].'">
		</form>');
	}
	println('</td></tr>
	</table>');
}

?>
