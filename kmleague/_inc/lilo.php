<?php

$ipAddr = $_SERVER['REMOTE_ADDR'];
$ipAddrS = substr(0,strrpos($ipAddr,'.'),$ipAddr);

Function destroySession($typ=1){
	setcookie('hc', '', time()-30*24*3600, '/', KML_COOKIES);
	setcookie('hd', '', time()-30*24*3600, '/', KML_COOKIES);
	setcookie('hb', '', time()-30*24*3600, '/', KML_COOKIES);
	setcookie('hi', '', time()-30*24*3600, '/', KML_COOKIES);
	session_destroy();
	if($typ==1){
	header('Location: index.php?'.str_replace('&amp;', '&', KML_LINK_SL2));
	exit;
	}
}

Function hash_cookie($inf){
	return $inf;
	$hash = md5($inf.time());
	$hash2 = md5(strrev($inf).time());
	return strrev(substr($hash,0,3).$inf.substr($hash2,0,3));
}

Function unhash_cookie($inf){
	return $inf;
	return substr(substr(strrev($inf),5),0,strlen($inf)-7);
}

if($_GET['op'] == 'logout') destroySession();
elseif($_POST['remember']=='on' || ($_COOKIE['hc'] && $_COOKIE['hd'])){
	if($_POST['remember']){
		$hp = hash_cookie($_POST['passw']);
		$hl = hash_cookie($_POST['login']);
#		$hb = $_SERVER['HTTP_USER_AGENT'];
		$hi = $ipAddrS;
#		echo $hp.'|'.$hl.'|'.$hb.'|'.$hi.'<br/>';
	}else{
		$hp = $_COOKIE['hd'];
		$hl = $_COOKIE['hc'];
#		$hb = $_COOKIE['hb'];
		$hi = $_COOKIE['hi'];
#		echo $hp.'|'.$hl.'|'.$hb.'|'.$hi.'<br/>';
	}
	setcookie('hc', $hl, time()+30*24*3600, '/', KML_COOKIES);
	setcookie('hd', $hp, time()+30*24*3600, '/', KML_COOKIES);
#	setcookie('hb', $hb, time()+30*24*3600, '/', KML_COOKIES);
	setcookie('hi', $hi, time()+30*24*3600, '/', KML_COOKIES);
}

if($_GET['op']!='logout'){
	if($_POST['login'] && $_POST['passw']){
		$login = $_POST['login'];
		$passw = $_POST['passw'];
		$ltype = 'post';
	}elseif($_COOKIE['hc'] && $_COOKIE['hd']){
		$login = unhash_cookie($_COOKIE['hc']);
		$passw = unhash_cookie($_COOKIE['hd']);
		$ltype = 'cookies';
	}
}

if(!isset($_SESSION['dl_login']) || !isset($_SESSION['dl_grants'])){
	if(!empty($login) || !empty($passw)){
		if(banned($ipAddr)) destroySession();
		$qry = SQL('SELECT iduser, grants, login, timezone FROM '.KML_PREFIX.'_users WHERE login=%s AND pass=%s', $login, md5($passw));
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		$all = mysql_num_rows($rsl);
		if($all != 1){
			$status = $lang['log_inc'];
			$status_error = 1;
			destroySession(0);
		}else{
			$row = mysql_fetch_assoc($rsl);
			foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
			$_SESSION['dl_login'] = $row['iduser'];
			$_SESSION['dl_name'] = $row['login'];
			$_SESSION['dl_timezone'] = $row['timezone'];
			$_SESSION['dl_grants']['main'] = $row['grants'];
			if($ltype=='cookies') $_SESSION['dl_secure']['ip'] = $_COOKIE['hi'];
			else $_SESSION['dl_secure']['ip'] = $ipAddrShort;
			if($ltype=='cookies') $_SESSION['dl_secure']['agent'] = $_COOKIE['hb'];
			else $_SESSION['dl_secure']['agent'] = $_SERVER['HTTP_USER_AGENT'];
			$gqry = 'SELECT service, wgrant FROM '.KML_PREFIX.'_grants WHERE iduser='.$row['iduser'];
			$grsl = query(__FILE__,__FUNCTION__,__LINE__,$gqry,0);
			while($grow = mysql_fetch_assoc($grsl)) $_SESSION['dl_grants'][$grow['service']] = $grow['wgrant'];
			$cqry = 'SELECT p.idc, p.function, c.tag FROM '.KML_PREFIX.'_player AS p, '.KML_PREFIX.'_clan AS c WHERE (p.function="C" OR p.function="W") AND p.approve_user="Y" AND c.idc=p.idc AND p.iduser='.$row['iduser'];
			$crsl = query(__FILE__,__FUNCTION__,__LINE__,$cqry,0);
			if(mysql_num_rows($crsl)>0){
				while($crow=mysql_fetch_assoc($crsl)){
					$_SESSION['dl_clan'][$crow['idc']] = array($crow['function'],$crow['tag']);
				}
			}
			$cqry = 'SELECT tag, idc, "C" AS function FROM '.KML_PREFIX.'_clan WHERE iduser='.$row['iduser'];
			$crsl = query(__FILE__,__FUNCTION__,__LINE__,$cqry,0);
			if(mysql_num_rows($crsl)>0){
				while($crow=mysql_fetch_assoc($crsl)){
					$_SESSION['dl_clan'][$crow['idc']] = array($crow['function'],$crow['tag']);
				}
			}
			if(is_array($_SESSION['dl_clan'])){
				$kclans = array_keys($_SESSION['dl_clan']);
				$_SESSION['dl_config']['idc'] = $kclans[0];
			}
			$qry = 'UPDATE '.KML_PREFIX.'_users SET lastlog="'.time().'" WHERE iduser="'.$row['iduser'].'"';
			query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
			if(ereg('add_user', $_SERVER['HTTP_REFERER']) || ereg('auth', $_SERVER['HTTP_REFERER'])) $location = 'index.php?'.str_replace('&amp;', '&', KML_LINK_SL2);
			elseif($ltype=='post') $location = $_SERVER['HTTP_REFERER'];
			else $location = $_SERVER['REQUEST_URI'];
			header('Location: '.$location);
			exit;
		}
	}else $status_error = 1;
}#elseif(isset($_SESSION['dl_login'])){
#	if($_SESSION['dl_secure']['ip'] != $ipAddrShort || $_SESSION['dl_secure']['agent'] != $_SERVER['HTTP_USER_AGENT'] || banned($ipAddr)) destroySession();
#}

?>
