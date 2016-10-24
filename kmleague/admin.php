<?php

session_start();
###loading ... base include cfg
$cfg = '_inc/config.php';
if(file_exists($cfg)) $mainDir = ''; else $mainDir = '../';
define(DIR, $mainDir);

###loading ... basic incs
require_once(DIR.$cfg);
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


if(!$_SESSION['dl_config']['idl']) $_SESSION['dl_config']['idl'] = KML_DEFAULT_LEAGUE;
if($_GET['idl']) $_SESSION['dl_config']['idl'] = $_GET['idl'];
define('LEAGUE', $_SESSION['dl_config']['idl']);
###loading ... basic incs
require_once(DIR.'_inc/basic_func.php');
###loading ... scripts configuration
get_league_cfg();

#WEBSITE ADMINISTRATOR MODE
if($_SESSION['dl_grants']['main'] == 4) $_SESSION['dl_grants'][MAIN_ID] = 3;
if($_SESSION['dl_grants'][MAIN_ID]<1){
	header('Location: index.php');
	exit;
}

require_once(DIR.'_lang/'.$_SESSION['dl_config']['lang'].'.php');

require_once(DIR.'_inc/framework.php');
require_once(DIR.'_inc/oth_func.php');
require_once(DIR.'_inc/a_form.php');
require_once(DIR.'_inc_team/adm_func.php');
require_once(DIR.'_inc_team/adm_grpsns.php');
require_once(DIR.'_inc_team/adm_match.php');
require_once(DIR.'_inc_team/adm_screen.php');
require_once(DIR.'_inc_team/adm_schedule.php');
require_once(DIR.'_inc_team/adm_clans.php');
require_once(DIR.'_inc_team/adm_penalty.php');
require_once(DIR.'_inc/a_news.php');
require_once(DIR.'_inc/a_poll.php');
require_once(DIR.'_inc/a_other.php');
require_once(DIR.'_inc/reg_forum.php');
require_once(DIR.'_inc_team/adm_demo.php');

require_once(DIR.'_inc/lilo.php');

index_header($_SESSION['dl_config']['lang']);

println($status);
if(!empty($status)) adm_log_form();
$time_start = getmicrotime();

#ACTUAL SEASON QUERY
$sqry = 'SELECT ids FROM '.KML_PREFIX.'_season WHERE league='.LEAGUE.' ORDER BY ids DESC LIMIT 0,1';
$ids = @mysql_result(query(__FILE__,__FUNCTION__,__LINE__,$sqry,0), 0, 'ids');
if(!$ids) $ids = 0;
define('IDS', $ids);

println('<div id="website">
<div id="menu"><div class="logo"><a target="_blank" href="http://kmleague.net"><img src="http://kmleague.net/kml.php?type=2&amp;ver=1.4.0&amp;web='.ADDRESS.'" alt="KMleague"/></a></div>');
###MENU SECTION
print('<div id="menu1" class="ddmx">
<div class="menu_head"><img src="adm/options.gif">'.$alang['options'].'</div>
<div class="menu_content">
<a class="menu_link" href="admin.php?'.KML_LINK_SL.'op=news">'.strtoupper($alang['news']).'</a>
<a class="menu_link" href="admin.php?'.KML_LINK_SL.'op=interv">'.strtoupper($alang['interview']).'</a>');
if($_SESSION['dl_grants'][MAIN_ID]>2) print('<a class="menu_link" href="admin.php?'.KML_LINK_SL.'op=search_user">'.strtoupper($alang['users']).'</a>');
if($_SESSION['dl_grants'][MAIN_ID]>1) print('<a class="menu_link" href="admin.php?'.KML_LINK_SL.'op=poll">'.strtoupper($alang['polls']).'</a>');
if($_SESSION['dl_grants'][MAIN_ID]>2){
	print('<a class="menu_link" href="admin.php?'.KML_LINK_SL.'op=rules">'.strtoupper($alang['rules']).'</a>
	<a class="menu_link" href="admin.php?'.KML_LINK_SL.'op=faq">'.$alang['admin_faq'].'</a>
	<a class="menu_link" href="admin.php?'.KML_LINK_SL.'op=ptable">'.strtoupper($alang['poff_table']).'</a>');
	print('<a class="item1" href="admin.php?'.KML_LINK_SL.'op=group">'.strtoupper($alang['groups']).'</a>');
	$sqry = 'SELECT gphase FROM '.KML_PREFIX.'_group WHERE league='.LEAGUE.' AND ids='.IDS.' GROUP BY gphase';
	$srslt = query(__FILE__,__FUNCTION__,__LINE__,$sqry,0);
	if(mysql_num_rows($srslt)>0){
		print('<div class="section">');
		while($srow = mysql_fetch_array($srslt)){
			print('<a class="item2" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=groups&amp;id='.$srow['gphase'].'">'.strtoupper($alang['round']).' '.$srow['gphase'].'</a>');
		}
		print('</div>');
	}
	print('<a class="menu_link" href="admin.php?'.KML_LINK_SL.'op=mailer">'.strtoupper($alang['mailer']).'</a>
	<a class="menu_link" href="admin.php?'.KML_LINK_SL.'op=award">'.strtoupper($alang['awards']).'</a>
	<a class="menu_link" href="admin.php?'.KML_LINK_SL.'op=map_list">'.$alang['map_list'].'</a>
	<a class="menu_link" href="admin.php?'.KML_LINK_SL.'op=servers">'.$alang['server_list'].'</a>
	<a class="menu_link" href="admin.php?'.KML_LINK_SL.'op=pensta">'.strtoupper($alang['pensta']).'</a>
	<a class="menu_link" href="admin.php?'.KML_LINK_SL.'op=point_system">'.strtoupper($alang['points_standards']).'</a>
	<a class="menu_link" href="admin.php?'.KML_LINK_SL.'op=menu_builder">'.$alang['menu_builder'].'</a>
	<a class="menu_link" href="admin.php?'.KML_LINK_SL.'op=ban">'.$alang['bans'].'</a>
	<a class="menu_link" href="admin.php?'.KML_LINK_SL.'op=cfg">'.strtoupper($alang['config']).'</a>
	<a class="menu_link" href="admin.php?'.KML_LINK_SL.'op=tabfix">'.strtoupper($alang['table_fix']).'</a>
	<a class="menu_link" href="admin.php?'.KML_LINK_SL.'op=season">'.strtoupper($alang['seasons']).'</a>');
}
if($_SESSION['dl_grants']['main']>2) print('<a class="menu_link" href="admin.php?'.KML_LINK_SL.'op=leagues">'.strtoupper($alang['leagues']).'</a>');
println('</div>
<div class="menu_head"><img src="adm/matches.gif">'.$alang['matches'].'</div>
<div class="menu_content">');
println('<a class="item1" href="javascript:void(0)">'.strtoupper($alang['match_new']).'</a>
<div class="section">

<a class="item2" href="admin.php?'.KML_LINK_SL.'op=match&amp;idg=N">'.$alang['other_match'].'</a>
<a class="item2" href="admin.php?'.KML_LINK_SL.'op=match&amp;idg=P">'.$alang['playoff'].'</a>');
$gphase = '';
$qry = 'SELECT g.gname, t.idg, g.gphase FROM '.KML_PREFIX.'_table AS t, '.KML_PREFIX.'_group AS g WHERE t.league='.LEAGUE.' AND t.idg=g.idg AND g.ids='.IDS.' GROUP BY g.idg ORDER BY g.gphase DESC, g.gname ASC';
$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
if(mysql_num_rows($rsl)>0){
	while($row = mysql_fetch_array($rsl)){
		foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
		if($row['gphase']!=$gphase){
			if($gphase) println('</div>');
			println('<a class="item2 arrow" href="javascript:void(0)">'.strtoupper($alang['gphase']).' '.$row['gphase'].'<img src="adm/arrow1.gif" alt="" height="12" width="10"></a>
			<div class="section">');
		}
		println('<a class="item2" href="admin.php?'.KML_LINK_SL.'op=match&amp;idg='.$row['idg'].'">'.strtoupper($row['gname']).'</a>');
		$gphase = $row['gphase'];
	}
	print('</div>');
}
println('</div>');
println('<a class="menu_link" href="admin.php?'.KML_LINK_SL.'op=ematch">'.strtoupper($alang['match_edit']).'</a>
<a class="menu_link" href="admin.php?'.KML_LINK_SL.'op=screen">'.strtoupper($alang['screens']).'</a>
<a class="menu_link" href="admin.php?'.KML_LINK_SL.'op=demos">'.strtoupper($alang['demos']).'</a>
</div>');
if($_SESSION['dl_grants'][MAIN_ID]>2){
	print('<div class="menu_head"><img src="adm/schedule.gif">'.strtoupper($alang['schedule']).'</div>
	<div class="menu_content">
	<a class="item1" href="javascript:void(0)">'.strtoupper($alang['manual']).'</a>
	<div class="section">
	<a class="item2" href="admin.php?'.KML_LINK_SL.'op=schedule&amp;idg=N">'.$alang['other_match'].'</a>
	<a class="item2" href="admin.php?'.KML_LINK_SL.'op=schedule&amp;idg=P">'.$alang['playoff'].'</a>');
	$gphase = '';
	$qry = 'SELECT g.gname, t.idg, g.gphase FROM '.KML_PREFIX.'_table AS t, '.KML_PREFIX.'_group AS g WHERE t.league='.LEAGUE.' AND t.idg=g.idg AND g.ids='.IDS.' GROUP BY g.idg ORDER BY g.gphase DESC, g.gname ASC';
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	if(mysql_num_rows($rsl)>0){
		while($row = mysql_fetch_array($rsl)){
			foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
			if($row['gphase']!=$gphase){
				if($gphase) print('</div>');
				print('<a class="item2 arrow" href="javascript:void(0)">'.strtoupper($alang['gphase']).' '.$row['gphase'].'<img src="adm/arrow1.gif" alt="" height="12" width="10"></a>
				<div class="section">');
				$crts = -1;
			}
			print('<a class="item2" href="admin.php?'.KML_LINK_SL.'op=schedule&amp;idg='.$row['idg'].'">'.strtoupper($row['gname']).'</a>');
			$gphase = $row['gphase'];
		}
		print('</div>');
	}
	print('</div>
	<a class="menu_link" href="admin.php?'.KML_LINK_SL.'op=aschedule">'.strtoupper($alang['auto']).'</a>
	<a class="menu_link" href="admin.php?'.KML_LINK_SL.'op=dround">'.strtoupper($alang['rdesc']).'</a>
	</div>');
}
print('<div class="menu_head"><img src="adm/clans.gif">'.strtoupper($alang['clans']).'</div>');
print('<div class="menu_content">');
if($_SESSION['dl_grants'][MAIN_ID]>2 && LEAGUE_TYPE=='T') print('<a class="menu_link" href="admin.php?'.KML_LINK_SL.'op=clan">'.$alang['manage'].'</a>
<a class="menu_link" href="admin.php?'.KML_LINK_SL.'op=inleague">'.$alang['league_in'].'</a>');
if($_SESSION['dl_grants'][MAIN_ID]>2) print('<a class="menu_link" href="admin.php?'.KML_LINK_SL.'op=signup">'.strtoupper($alang['signup']).'</a>');
if($_SESSION['dl_grants'][MAIN_ID]>1) print('<a class="menu_link" href="admin.php?'.KML_LINK_SL.'op=caward">'.strtoupper($alang['awards']).'</a>
<a class="menu_link" href="admin.php?'.KML_LINK_SL.'op=penalty">'.strtoupper($alang['penalties']).'</a>
<a class="menu_link" href="admin.php?'.KML_LINK_SL.'op=special_table">'.strtoupper($alang['special_table']).'</a>');
if(LEAGUE_TYPE=='T') print('<a class="menu_link" href="admin.php?'.KML_LINK_SL.'op=ips">'.strtoupper($alang['player_search']).'</a>');
print('</div>
    <script type="text/javascript">
    var ddmx = new DropDownMenuX(\'menu1\');
    ddmx.type = "vertical";
    ddmx.delay.show = 0;
    ddmx.delay.hide = 400;
    ddmx.position.levelX.left = 2;
    ddmx.init();
    </script>
<div class="foot"><a class="foot_link" target="_blank" href="http://kmleague.net">KMleague ver 1.4.0</a><br/>2002-'.date('Y').' &copy; <a class="foot_link" target="_blank" href="http://kmprojekt.pl">KMprojekt</a></div>
<a target="_blank" href="http://www.mozilla.com/firefox/"><img border="0" src="adm/ff.gif"/></a>
</div><br/>');

print('</div>');
###END OF MENU

print('<div id="content">
<div class="top_right"><a class="atop_right" href="index.php?'.KML_LINK_SL2.'">'.strtoupper($alang['website']).'</a> | <a class="atop_right" href="admin.php?'.KML_LINK_SL2.'">'.strtoupper($alang['admin']).'</a> | <a class="atop_right" href="admin.php?'.KML_LINK_SL.'op=logout">'.strtoupper($alang['prof_logout']).'</a></div>');

switch($_REQUEST['op']){
	case 'news': news($_POST,$_GET['idn']); break;
	case 'interv': interviews($_POST,$_SESSION['dl_login'],$_SESSION['dl_grants'][MAIN_ID]); break;
	case 'demos': demos($_POST); break;
	case 'screen': screen($_POST); break;
	case 'pstat': ply_stats($_POST); break;
	case 'match': match($_REQUEST['idg'],$_POST,$_SESSION['dl_login'],$_GET); break;
	case 'ematch': matches_list($_GET); break;
	case 'nstat': new_ply_stats($_POST); break;
	case 'ips': ip_search($_GET); break;
}

if($_SESSION['dl_grants'][MAIN_ID]>1){
	switch($_REQUEST['op']){
		case 'comment': comment($_GET['id'],$_GET['option']); break;
		case 'poll': polls($_POST); break;
		case 'penalty': penalties($_POST); break;
		case 'special_table': special_table($_POST,$_REQUEST['id'],$_REQUEST['opt']); break;
		case 'caward': clan_award($_POST,$_REQUEST['id'],$_GET['opt']); break;
	}
}

if($_SESSION['dl_grants'][MAIN_ID]>2){
	switch($_REQUEST['op']){
		case 'point_system': point_system($_POST,$_REQUEST['id'],$_REQUEST['opt']); break;
		case 'menu_builder': menu_builder($_POST,$_REQUEST['id'],$_REQUEST['opt']); break;
		case 'cfg': league_config($_POST,$_REQUEST['id'],$_REQUEST['opt']); break;
		case 'season': season($_POST,$_REQUEST['id'],$_REQUEST['opt']); break;
		case 'signup': signups($_POST); break;
		case 'servers': servers_list($_POST,$_REQUEST['id'],$_REQUEST['opt']); break;
		case 'map_list': map_list($_POST,$_REQUEST['id'],$_REQUEST['opt']); break;
		case 'inleague': league_in($_POST); break;
		case 'award': awards($_POST,$_REQUEST['id'],$_REQUEST['opt']); break;
		case 'aschedule': auto_scheduler($_POST); break;
		case 'mailer': mailer($_POST); break;
		case 'schedule': schedule($_POST,$_REQUEST['idg'],$_SESSION['dl_login']); break;
		case 'about': incl_file($_POST,'ABOUT','about'); break;
		case 'rules': incl_file($_POST,'RULES','rules'); break;
		case 'faq': incl_file($_POST,'FAQ','faq'); break;
		case 'ban': ban($_REQUEST['ip'],$_REQUEST['opt']); break;
		case 'search_user': search_user($_POST['search']); break;
		case 'edit_user': user($_REQUEST['id']); league_user($_REQUEST['id']); break;
		case 'save_prof': save_prof($_POST); break;
		case 'save_leader': save_leader($_POST); break;
		case 'groups': clans_groups($_POST,$_REQUEST['id']); break;
		case 'clan': clan($_POST,$_SESSION['dl_login'],$_REQUEST['idc'],$_REQUEST['opt']); break;
		case 'group': groups($_POST,$_REQUEST['id'],$_REQUEST['opt']); break;
		case 'tabfix': table_fixer($_POST['idg']); break;
		case 'ptable': playoff_tables($_POST,$_REQUEST['id'],$_REQUEST['opt']); break;
		case 'dround': rounds_description($_POST); break;
		case 'pensta': penalty_standards($_POST); break;
	}
}

if($_SESSION['dl_grants']['main']>2){
	switch($_REQUEST['op']){
		case 'leagues': leagues($_POST,$_REQUEST['id'],$_REQUEST['opt']); break;
	}
}
if(!$_REQUEST['op']){
	option_head($alang['adm_guide']);
	println('<div align="left">'); 
	if(file_exists('data/faq_'.LEAGUE.'.htm')) readfile('data/faq_'.LEAGUE.'.htm');
	println('</div>');
}
$time_end = getmicrotime();

println('</div><!--'.round(($time_end-$time_start),3).'-->
</div>
</div>');


bottom();

?>
