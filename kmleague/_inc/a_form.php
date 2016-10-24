<?php

#admin form functions

Function formHead($action='admin.php',$cls='2',$clp='2',$files='N',$class='',$name='',$method='post',$target='_self'){
	$return .= '<table cellspacing="'.$cls.'" cellpadding="'.$clp.'"';
	if($class) $return .= ' class="'.$class.'"';
	$return .= ">\n<form action=\"$action\" method=\"$method\"";
	if($name) $return .= ' name="'.$name.'"';
	if($files=='Y') $return .= ' enctype="multipart/form-data"';
	if($target!='_self') $return .= ' target="'.$target.'"';
	$return .= ">\n";
	return $return;
}

Function formFoot(){
	$return = "</form>\n</table>\n";
	return $return;
}

Function formText($req,$head,$name,$value,$maxlength='0',$size='0'){
	$return = "\t".'<tr><td class="hd'.$req.'">'.$head.':</td><td class="ct"><input type="text" name="'.$name.'" value="'.$value.'"';
	if($size>0) $return .= ' size="'.$size.'"';
	if($maxlength>0) $return .= ' maxlength="'.$maxlength.'"';
	$return .= "></td></tr>\n";
	return $return;
}

Function formTextarea($req,$head,$name,$value,$maxlength='0',$rows='0',$cols='0'){
	$return = "\t".'<tr><td class="hd'.$req.'" valign="top">'.$head.':</td><td class="ct"><textarea name="'.$name.'" maxlength="'.$maxlength.'" rows="'.$rows.'" cols="'.$cols.'">'.$value.'</textarea></td></tr>'."\n";
	return $return;
}

Function formSelectNormal($req,$head,$name,$array,$value,$default='',$size='0',$multiple='N'){
	if(is_array($value)){ $typ = 1; $multiple='T';}else $typ = 2;
	$return = "\t<tr><td class=\"hd".$req."\" valign=\"top\">$head:</td><td class=\"ct\"><select name=\"$name\"";
	if($size>0) $return .= ' size="'.$size.'"';
	if($multiple=='T') $return .= ' multiple';
	$return .= ">\n";
	if($default[0]=='Y') $return .= "\t\t".'<option>'.$default[1].'</option>'."\n";
	foreach($array as $ky){
		$return .= "\t\t<option";
		if($typ==1){
			if(in_array($ky, $value)) $return .= ' selected';
		}else{
			if($ky==$value) $return .= ' selected';
		}
		$return .= ">$ky</option>\n";
	}
	$return .= "\t</select></td></tr>\n";
	return $return;
}

Function formSelectAssoc($req,$head,$name,$array,$value,$default='',$size='0',$multiple='N'){
	if(is_array($value)){ $typ = 1; $multiple='Y';}else $typ = 2;
	$return = "\t<tr><td class=\"hd".$req."\" valign=\"top\">$head:</td><td class=\"ct\"><select name=\"$name\"";
	if($size>0) $return .= ' size="'.$size.'"';
	if($multiple=='Y') $return .= ' multiple';
	$return .= ">\n";
	if($default[0]=='Y') $return .= "\t\t".'<option value="'.$default[2].'">'.$default[1].'</option>'."\n";
	foreach($array as $ky=>$vl){
		$return .= "\t\t<option value=\"$ky\"";
		if($typ==1){
			if(in_array($ky, $value)) $return .= ' selected';
		}else{
			if($ky==$value) $return .= ' selected';
		}
		$return .= ">$vl</option>\n";
	}
	$return .= "\t</select></td></tr>\n";
	return $return;
}

Function formRadio($req,$head,$name,$array,$value,$spacer='<br/>'){
	$return = "\t<tr><td class=\"hd".$req."\" valign=\"top\">$head:</td><td class=\"ct\">\n";
	foreach($array as $ky=>$vl){
		$return .= "\t\t<input type=\"radio\" name=\"$name\" value=\"$ky\"";
		if($ky==$value) $return .= ' checked';
		$return .= "> $vl$spacer\n";
	}
	$return .= "\t</td></tr>\n";
	return $return;
}

Function formCheckbox($req,$head,$name,$array,$value,$spacer='<br/>'){
	if(is_array($value)) $typ = 1; else $typ = 2;
	$return = "\t<tr><td class=\"hd".$req."\" valign=\"top\">$head:</td><td class=\"ct\">\n";
	foreach($array as $ky=>$vl){
		$return .= "\t\t<input type=\"checkbox\" name=\"$name\" value=\"$ky\"";
		if($typ==1){
			if(in_array($ky, $value)) $return .= ' checked';
		}else{
			if($ky==$value) $return .= ' checked';
		}
		$return .= "> $vl$spacer\n";
	}
	$return .= "\t</td></tr>\n";
	return $return;
}

Function formButtons($buttons){
	$return .= "\t".'<tr><td colspan="2" class="sb">';
	foreach($buttons as $ky=>$button){
		$return .= '<input type="'.$button[0].'" name="'.$button[1].'" value="'.$button[2].'"> ';
	}
	$return .= "\t</td></tr>\n";
	return $return;
}

?>
