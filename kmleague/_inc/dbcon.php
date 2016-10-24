<?php
	$db = @mysql_connect(KML_DB_HOST,KML_DB_USER,KML_DB_PASSW);
	if($db) $bd = @mysql_select_db(KML_DB_NAME); else die('db error: connection');
	if(!$bd) die('db error: base choice');
?>
