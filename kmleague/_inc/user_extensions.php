<?php

#user function cannot print value, it must return value to work correctly with templates
Function user1(){
	global $lang;
//	$rtr .= left_menu_head($lang['services']);
//	$rtr .= services();
//	$rtr .= left_menu_foot();
//	$rtr .= left_menu_head($lang['links']);
//	$rtr .= links();
//	$rtr .= left_menu_foot();
	return $rtr;
}

Function user2(){
	$rtr = '';
	return $rtr;
}

Function user3(){
	$rtr = '';
	return $rtr;
}

Function user4(){
	$rtr = '';
	return $rtr;
}

$lang['ip'] = 'PB Guid';

?>
