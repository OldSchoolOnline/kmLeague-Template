<?php

Function left_menu_head($title,$align='left'){
	$return = '<table cellspacing="0" cellpadding="0" class="leftBox">
	<tr><td class="BoxTleft"></td><td class="box_head">'.$title.'</td><td class="BoxTright"></td></tr>
	<tr><td class="BoxMleft"></td><td class="BoxMcenter"></td><td class="BoxMright"></td></tr>
	<tr><td class="BoxCleft"></td><td class="main_menu" style="text-align: '.$align.'">';
	return $return;
}

Function left_menu_foot(){
	$return = '</td>
	<td class="BoxCright"></td></tr>
	<tr><td class="leftBoxBleft"></td><td class="leftBoxBcenter"></td><td class="leftBoxBright"></td></tr>
	</table>';
	return $return;
}

Function top_box_header($title){
	$return = '<table cellspacing="0" cellpadding="0" class="topBox">
	<tr><td class="BoxTleft"></td><td class="box_head">'.$title.'</td><td class="BoxTright"></td></tr>
	<tr><td class="BoxMleft"></td><td class="BoxMcenter"></td><td class="BoxMright"></td></tr>
	<tr><td class="BoxCleft"></td>
	<td class="top_box" valign="top">';
	return $return;
}

Function box_foot(){
	$return = '</td>
	<td class="BoxCright"></td></tr>
	<tr><td class="leftBoxBleft"></td><td class="leftBoxBcenter"></td><td class="leftBoxBright"></td></tr>
	</table>';
	return $return;
}

Function right_box_header($title){
	$return = '<table cellspacing="0" cellpadding="0" class="rightBox">
	<tr><td class="BoxTleft"></td><td class="box_head">'.$title.'</td><td class="BoxTright"></td></tr>
	<tr><td class="BoxMleft"></td><td class="BoxMcenter"></td><td class="BoxMright"></td></tr>
	<tr><td class="BoxCleft"></td>
	<td class="top_box" valign="top">';
	return $return;
}

Function content_line($type,$header=''){
	if($type=='T') $template = 'top';
	elseif($type=='M') $template = 'middle';
	elseif($type=='B') $template = 'bottom';
	$tmpl = file_get_contents(SKIN.'tmpl/item_content_'.$template.'.html');
	$tmpl = str_replace('{header}', $header, $tmpl);
	return $tmpl;
}

?>
