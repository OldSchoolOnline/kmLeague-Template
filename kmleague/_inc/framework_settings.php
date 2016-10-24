<?
#FORMS TEXTS
define('INF_OPTIONS', $lang['options']);
define('INF_SEARCH', $lang['search']);
define('INF_NEW', $lang['new_item']);
define('INF_DEL', $lang['delete']);
define('INF_EDIT', $lang['edit']);
define('INF_SAVE', $lang['save']);
define('INF_ADD', $lang['add']);
define('INF_SURE', $lang['sure']);
define('INF_YES', $lang['yes']);
define('INF_NO', $lang['no']);
define('INF_REQUIRED', $lang['form_error1']);
define(INF_FILE_BIG, 'b:');#new
define(INF_FILE_SMALL, 's:');#new
define(INF_FILE_DESCR, 'd:');#new
define('CSS', 'framework.css');
define(FCK, 0);
define(IMGD, 1);
define(FPDF, 0);
define(WATERMARK, 'selcuktente.png');#new
define(HOLIDAYS, 0);
define(KML_ERROR_MAIL, 'cl@epf.pl');
//nazwa sesji dla loginu
define('SESSION_L', 'tgkL');
//nazwa sesji dla loginu
define('SESSION_N', 'tgkN');
//nazwa sesji dla uprawnien
define('SESSION_G', 'tgkP');
//minimalna wartosc uprawnien pozwalajaca na korzystanie z dodatkowych rzeczy jak FCK
define('SESSION_MG', 2);
//set rows as deleted/dont remove phisicly
define('SAFE_DEL', 0);

Function fwOptionHead($title){
	echo '<div class="menu_head2">&nbsp;'.$title.'</div>';
}

Function fwContentHead($title){
	fwOptionHead($title);
	echo '<table cellspacing="10"><tr><td valign="top"><br/><br/>';
}

Function fwContentFoot($descr){
	$r = '</div>
	</td></tr></table>'.$descr;
	return $r;
}

?>
