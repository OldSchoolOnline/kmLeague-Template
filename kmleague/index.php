<?php

###if webspider visit website session wont be started
if(!preg_match('/(google|msn|yahoo|onet|wp|szukacz|interia|WebCrawler|WebSpider)/i', $_SERVER['HTTP_USER_AGENT'])) session_start();

###loading ... base include cfg
$cfg = '_inc/config.php';
if(file_exists($cfg)) $mainDir = ''; else $mainDir = '../';
define(DIR, $mainDir);

###loading ... basic incs
require_once(DIR.$cfg);

###if database variables are not set redirect user to install script
if(KML_DB_HOST=='KML_DB_HOST' || KML_DB_NAME=='KML_DB_NAME') header('Location: install.php');
if(file_exists('install.php')){
	echo 'Remove installation file from server first (install.php).';
	exit;
}

require_once(DIR.'_inc/dbcon.php');

###loading ... default options & language
if(!$_REQUEST['league']) $idleague = KML_DEFAULT_LEAGUE;
else $idleague = $_REQUEST['league'];
define(LEAGUE, $idleague);

#season currently playing
$qry = 'SELECT ids FROM '.KML_PREFIX.'_season WHERE league='.LEAGUE.' ORDER BY ids DESC LIMIT 0,1';
$idsa = @mysql_result(mysql_query($qry), 0, 'ids');
if(!$idsa) $idsa = 0;#limit number of errors when you have empty league
define(IDSA,$idsa);

#change/choose season
if((int)$_REQUEST['season']>0){
	$qry = 'SELECT sname, descr FROM '.KML_PREFIX.'_season WHERE ids='.(int)$_REQUEST['season'];
	$rsl = mysql_query($qry);
	if(mysql_num_rows($rsl)>0){
		$row = mysql_fetch_assoc($rsl);
		define(KML_SEASON_NAME, $row['sname']);
		define(KML_SEASON_DESCR, $row['descr']);
		$idseason = (int)$_REQUEST['season'];
	}
}

if($idseason<1){
	$qry = 'SELECT sname, descr FROM '.KML_PREFIX.'_season WHERE ids='.IDSA;
	$rsl = mysql_query($qry);
	$row = mysql_fetch_assoc($rsl);
	define(KML_SEASON_NAME, $row['sname']);
	define(KML_SEASON_DESCR, $row['descr']);
	$idseason = IDSA;
}
define(IDS,$idseason);

define(KML_LINK_SL, 'league='.LEAGUE.'&amp;season='.IDS.'&amp;');
define(KML_LINK_SL2, 'league='.LEAGUE.'&amp;season='.IDS);
define(KML_LINK_SLF, '<input type="hidden" name="league" value="'.LEAGUE.'"/><input type="hidden" name="season" value="'.IDS.'"/>');

###loading ... scripts configuration
require_once(DIR.'_inc/basic_func.php');

if(!$_REQUEST['op']) $_REQUEST['op'] = 'news';
if(!$_SESSION['dl_config']['lang']) $_SESSION['dl_config']['lang'] = DEFAULT_LANG;
if($_REQUEST['lang']) $_SESSION['dl_config']['lang'] = $_REQUEST['lang'];
define(LANG, $_SESSION['dl_config']['lang']);

###loading ... language
require_once(DIR.'_lang/'.$_SESSION['dl_config']['lang'].'.php');

#$time_start = getmicrotime();

###include file with user settings/changes
include('_inc/user_extensions.php');

###loading ... other includes
require_once(DIR.'_inc/oth_func.php');
require_once(DIR.'_inc/lib/framework_inc.php');
require_once(DIR.'_inc/structure.php');
require_once(DIR.'_inc/news.php');
require_once(DIR.'_inc/users.php');
require_once(DIR.'_inc/pm.php');
require_once(DIR.'_inc/reg.php');
require_once(DIR.'_inc/reg_forum.php');
require_once(DIR.'_inc/poll.php');
require_once(DIR.'_inc_team/html_show.php');
require_once(DIR.'_inc_team/clans.php');
require_once(DIR.'_inc_team/tables.php');
require_once(DIR.'_inc_team/matches.php');
require_once(DIR.'_inc_team/demos.php');
require_once(DIR.'_inc_team/statistics.php');
###layout functions
require_once(SKIN.'tmpl/layout_functions.php');

###loading ... logIn & logOut scripts
require_once(DIR.'_inc/lilo.php');

if($_POST['change_clan']){
	$_POST['idc'] = intval($_POST['idc']);
	$qry = 'SELECT iduser FROM '.KML_PREFIX.'_player WHERE (function="C" OR function="W") AND idc='.$_POST['idc'].' AND approve_user="Y" AND iduser='.$_SESSION['dl_login'];
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	$qry = 'SELECT iduser FROM '.KML_PREFIX.'_clan WHERE idc='.$_POST['idc'].' AND iduser='.$_SESSION['dl_login'];
	$rsl2 = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	if(mysql_num_rows($rsl)<1 && mysql_num_rows($rsl2)<1) die($lang['sure']);
	else $_SESSION['dl_config']['idc'] = $_POST['idc'];
}

###creat full layout array
$layout = array('clan_leaders_options'=>'', 'xml_lang'=>XML_LANG, 'website_encoding'=>ENCODING);

$layout['website_title'] = NAME;
$layout['skin'] = SKIN;
$layout['meta_keywords'] = META_KEYWORDS.', online tournament, online league';

watchers_log($_SESSION['dl_login']);

if($_SESSION['dl_login']){
	switch($_GET['op']){
		case 'add_com':{
			if($_POST['idc']>0){
				$layout['website_referer'] = str_replace('&', '&amp;', $_POST['link']);
				$layout['content_header'] = $lang['edit_comment'];
			}else{
				$layout['website_referer'] = str_replace('&', '&amp;', $_SERVER['HTTP_REFERER']);
				$layout['content_header'] = $lang['new_com'];
			}
			$layout['content'] = add_com($_POST,$_SESSION['dl_login']);
			break;
		}
		case 'vote':{
			$layout['website_referer'] = str_replace('&', '&amp;', $_SERVER['HTTP_REFERER']);
			$layout['content_header'] = $lang['poll'];
			$layout['content'] = add_voice($_POST,$_SESSION['dl_login']);
			break;
		}
		case 'spm': mark_message($_GET['id'],$_SESSION['dl_login']); break;
	}
}

###if content is set already it will show up
if($layout['content']){
	$buffer = '';
	$template = fopen(SKIN.'tmpl/comm_vote.html', 'r');
	while(!feof($template)) $buffer .= fgets($template,4096);
	fclose($template);
	foreach($layout as $ky=>$vl) $buffer = str_replace('{'.$ky.'}', $vl, $buffer);
	echo $buffer;
	exit;
}

if($_SESSION['dl_grants']['main'] == 4) $_SESSION['dl_grants'][MAIN_ID] = 3;

if(isset($_SESSION['dl_login']) && isset($_SESSION['dl_grants'])){
	if($_SESSION['dl_grants']['main']==0) $status = $lang['acc_bl'];
	else{
		$status .= profile_options();
	}
}

	if(KML_MULTI_DIRS=='N'){
		$layout['leagues_list_head'] = $lang['leagues'];
		$layout['leagues_list'] = leagues_list();
	}
	
	$layout['navigation_shortcut'] = show_place($_REQUEST['op']);

	$layout['main_menu_head'] = $lang['menu'];
	$layout['main_menu'] = main_menu();
	$layout['menu_tables_head'] = $lang['tabs'];
	$layout['menu_tables'] = show_tables();
	$layout['login_profile_head'] = $lang['profile'];
	$layout['login_profile_box'] = $status;
	if($status_error=='1') $layout['login_profile_box'] .= form_log();
	if(LEAGUE_TYPE=='T' || KML_MULTI_LEAGUES=='Y'){
		if($_SESSION['dl_login']>0 && $_SESSION['dl_grants']>0) $layout['clan_leaders_options'] = cl_options($_SESSION['dl_login']);
	}
	$layout['powered_by_head'] = $lang['powered_by'];
	$layout['powered_by'] = powered_by();
	$layout['latest_news_head'] = $lang['lnews'];
	$layout['latest_news'] = latest_news(SHORT_LATEST_NEWS);
	$layout['latest_matches_head'] = $lang['lmatch'];
	$layout['latest_matches'] = last_match(SHORT_LATEST_MATCHES);
	$layout['informations_box_head'] = $lang['site_info'];
	$layout['informations_box'] = informations();

###options reserved only for logged in users
if($_SESSION['dl_login']){
	switch($_REQUEST['op']){
		case 'team_roster': $layout['content'] = team_roster($_REQUEST['id'],$_POST,$_SESSION['dl_grants']['main'],$_SESSION['dl_grants'][MAIN_ID]); break;
		case 'team_managment': $layout['content'] = team_managment($_REQUEST['opx'],$_POST,$_GET,$_REQUEST); break;
		case 'player_teams': $layout['content'] = player_teams($_GET,$_POST); break;
		case 'pm': $layout['content'] = pm($_SESSION['dl_login'],$_REQUEST['id'],$_REQUEST); break;
		case 'pms': $layout['content'] = pms($_SESSION['dl_login'],$_POST); break;
		case 'spm': $layout['content'] = spm($_SESSION['dl_login'],$_REQUEST['id']); break;
		case 'dpm': $layout['content'] = dpm($_SESSION['dl_login'],$_REQUEST['id']); break;
		case 'avatar': $layout['content'] = avatar($_SESSION['dl_login'],$_POST['avatar']); break;
		case 'profile': $layout['content'] = profile($_POST,$_SESSION['dl_login']); break;
		case 'add_clan': $layout['content'] = new_clan($_POST); break;
		case 'ecomm': $layout['content'] = edit_comment($_GET['idc'],$_SESSION['dl_grants'][MAIN_ID],$_SESSION['dl_login']); break;
		case 'sign': $layout['content'] = sign_ups($_POST,$_SESSION['dl_grants']['main'],$_SESSION['dl_login']); break;
	}
}
switch($_REQUEST['op']){
	case 'polls': $layout['content'] = archive_poll($_GET['id'],$_SESSION['dl_login']); break;
	case 'awards': $layout['content'] = awards(); break;
	case 'demos': $layout['content'] = demos($_GET,$ids); break;
	case 'inter': $layout['content'] = interviews($_GET['id'],$_SESSION['dl_grants']['main'],$_SESSION['dl_grants'][MAIN_ID]); break;
	case 'pstats': $layout['content'] = players_stats($_GET['sort'],$_GET['type']); break;
	case 'lstats': $layout['content'] = league_stats(); break;
	case 'cstats': $layout['content'] = clans_stats(); break;
	case 'all_matches': $layout['content'] = all_matches(); break;
	case 'schedule': $layout['content'] = schedule(); break;
	case 'descr': $layout['content'] = descr($_GET['id'],$_SESSION['dl_grants']['main'],$_SESSION['dl_grants'][MAIN_ID]); break;
	case 'poff': $layout['content'] = play_offs($_GET['id']); break;
	case 'gr': $layout['content'] = gr($_GET['id']); break;
	case 'clans': $layout['content'] = clans($_GET['id'],$_GET['so'],$_SESSION['dl_grants'][MAIN_ID]); break;
	case 'rules': $layout['content'] = show_rules(); break;
	case 'fpass': $layout['content'] = forgot_pass($_POST['mail']); break;
	case 'npass': $layout['content'] = authorize_pass($_GET); break;
	case 'auth': $layout['content'] = authorize($_GET); break;
	case 'add_user': $layout['content'] = add_user($_POST); break;
	case 'reg': $layout['content'] = form_reg(); break;
	case 'crew': $layout['content'] = serv_squad($_SESSION['dl_grants']['main']); break;
	case 'users': $layout['content'] = users($_GET); break;
	case 'com': $layout['content'] = comments($_GET['id'],$_SESSION['dl_grants']['main'],$_SESSION['dl_grants'][MAIN_ID]); break;
	case 'anews': $layout['content'] = add_news($_POST,$_SESSION['dl_grants']['main'],$_SESSION['dl_login']); break;
	case 'news': $layout['content'] = news($_GET['p'],NEWS_LIMIT_SHOW); break;
	case 'special_table': $layout['content'] = special_table($_GET['idc']); break;
}

$layout['user_info1'] = user1();
$layout['user_info2'] = user2();
$layout['user_info3'] = user3();
$layout['user_info4'] = user4();

$layout['languages_head'] = $lang['languages'];
$layout['languages'] = languages();
$layout['seasons_head'] = $lang['seasons'];
$layout['seasons'] = season_list();
$layout['upcoming_matches_head'] = $lang['umatch'];
$layout['upcoming_matches'] = upc_matches();
$layout['mini_tables_head'] = $lang['ranks'];
$layout['mini_tables'] = mini_tables();
$layout['mini_special_tab_head'] = $lang['special_table'];
$layout['mini_special_tab'] = special_table_short();
$layout['polls_head'] = $lang['poll'];
$layout['polls'] = active_polls($_SESSION['dl_login']);
$layout['watchers_head'] = $lang['online'];
$layout['watchers'] = watchers();

#$time_end = getmicrotime();
#$layout['timer'] = round(($time_end-$time_start),3);

#templates
$template_cfg = fopen(SKIN.'tmpl/main_layouts.cfg', 'r');
while(!feof($template_cfg)){
	$buffer = fgets($template_cfg,4096);
	$buf = explode(':', trim($buffer));
	if($buf[0] && $buf[1]){
		if($buf[0]!='default') $templates[$buf[0]] = $buf[1];
		else $default = $buf[1];
	}
}
fclose($template_cfg);

$buffer = '';
if(array_key_exists($_REQUEST['op'], $templates)) $tmpl = $templates[$_REQUEST['op']]; else $tmpl = $default;

$template = fopen(SKIN.'tmpl/'.$tmpl, 'r');
while(!feof($template)){
	$buffer .= fgets($template,4096);
}
fclose($template);

foreach($layout as $ky=>$vl){
	$buffer = str_replace('{'.$ky.'}', $vl, $buffer);
}
echo $buffer;

?>
