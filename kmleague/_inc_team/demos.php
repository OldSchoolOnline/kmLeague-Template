<?php

Function demos($dts){
	global $lang;
	$rtr = content_line('T',$lang['demos']);
	$rtr .= '<td>';
	if(strlen(DEMOSQUAD)>10){
		$rtr .= '';
	}else{
		$qry = 'SELECT D.map FROM '.KML_PREFIX.'_demo AS d, '.KML_PREFIX.'_match AS m WHERE d.league='.LEAGUE.' AND d.idm=m.idm AND m.ids='.IDS.' GROUP BY d.map';
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		while($row=mysql_fetch_assoc($rsl)){
			foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
			$maps[] = $row['map'];
		}
		$qry = 'SELECT d.pov FROM '.KML_PREFIX.'_demo AS d, '.KML_PREFIX.'_match AS m WHERE d.league='.LEAGUE.' AND d.idm=m.idm AND m.ids='.IDS.' GROUP BY d.pov';
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		while($row=mysql_fetch_assoc($rsl)){
			foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
			$povs[] = $row['pov'];
		}
		if(LEAGUE_TYPE=='D') $qry = 'SELECT c1.login AS t1, m.idc1 AS c1, c2.login AS t2, m.idc2 AS c2 from '.KML_PREFIX.'_users AS c1, '.KML_PREFIX.'_users AS c2, '.KML_PREFIX.'_match AS m WHERE m.league='.LEAGUE.' AND m.idc1=c1.iduser AND m.idc2=c2.iduser AND m.ids='.IDS;
		else $qry = 'SELECT c1.tag AS t1, c1.idc AS c1, c2.tag AS t2, c2.idc AS c2 from '.KML_PREFIX.'_clan AS c1, '.KML_PREFIX.'_clan AS c2, '.KML_PREFIX.'_match AS m WHERE m.league='.LEAGUE.' AND m.idc1=c1.idc AND m.idc2=c2.idc AND m.ids='.IDS;
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		while($row=mysql_fetch_assoc($rsl)){
			foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
			$clans[$row['c1']] = $row['t1'];
			$clans[$row['c2']] = $row['t2'];
		}
		$rtr .= '<form action="'.$_SERVER['PHP_SELF'].'" method="get">'.KML_LINK_SLF.'<input type="hidden" name="op" value="demos">
		<table cellspacing="1" cellpadding="5" class="tab_brdr">
		<tr><td class="tab_head2">'.$lang['clan'].'</td><td class="tab_head2">'.$lang['map'].'</td><td class="tab_head2">'.$lang['pov'].'</td><td class="tab_head2"></td></tr>
		<tr class="content2"><td><select name="clan"><option value="0">---'.array_assoc($clans,$dts['clan']).'</select></td><td><select name="map"><option value="0">---'.array_norm($maps,$dts['map']).'</select></td><td><select name="pov"><option value="0">---'.array_norm($povs,$dts['pov']).'</select></td><td><input type="submit" value="'.$lang['search'].'"></td></tr>
		</table>
		</form><br/><br/>';
		if($dts['clan'] || $dts['map'] || $dts['pov']){
			if(LEAGUE_TYPE=='D')$qry = 'SELECT d.idd, d.pov, d.map, d.time, d.link, d.extension, c1.country AS ctry1, c1.login AS cnm1, c2.country AS ctry2, c2.login AS cnm2, m.idm, m.idc1, m.idc2 FROM '.KML_PREFIX.'_match AS m, '.KML_PREFIX.'_users AS c1, '.KML_PREFIX.'_users AS c2, '.KML_PREFIX.'_demo AS d WHERE d.league='.LEAGUE.' AND m.idc1=c1.iduser AND m.idc2=c2.iduser AND m.idm=d.idm AND m.ids='.IDS;
			else $qry = 'SELECT d.idd, d.pov, d.map, d.time, d.link, d.extension, c1.country AS ctry1, c1.tag AS cnm1, c2.country AS ctry2, c2.tag AS cnm2, m.idm, m.idc1, m.idc2 FROM '.KML_PREFIX.'_match AS m, '.KML_PREFIX.'_clan AS c1, '.KML_PREFIX.'_clan AS c2, '.KML_PREFIX.'_demo AS d WHERE d.league='.LEAGUE.' AND m.idc1=c1.idc AND m.idc2=c2.idc AND m.idm=d.idm AND m.ids='.IDS;
			if($dts['clan']) $qry .= ' AND (m.idc1="'.(int)$dts['clan'].'" OR m.idc2="'.(int)$dts['clan'].'")';
			if($dts['pov']) $qry .= SQL(' AND d.pov=%s', $dts['pov']);
			if($dts['map']) $qry .= SQL(' AND d.map=%s', $dts['map']);
			$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
			if(mysql_num_rows($rsl)>0){
				$rtr .= '<table cellspacing="1" cellpadding="5" class="tab_brdr">
				<tr><td class="tab_head2">'.$lang['clans'].'</td><td class="tab_head2">'.$lang['map'].'</td><td class="tab_head2">'.$lang['pov'].'</td><td class="tab_head2"></td></tr>';
				while($row=mysql_fetch_assoc($rsl)){
					foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
					if(!$row['link']) $link = 'demos/'.$row['time'].'_'.$row['cnm1'].'_vs_'.$row['cnm2'].'_'.$row['map'].'_'.$row['pov'].'.'.$row['extension']; else $link = $row['link'];
					$rtr .= '<tr class="content2"><td class="content1"><a class="link" href="'.$_SERVER['PHP_SELF'].'?'.KML_LINK_SL.'op=descr&id='.$row['idm'].'">'.show_flag($row['ctry1']).$row['cnm1'].' vs '.$row['cnm2'].show_flag($row['ctry2']).'</a></td><td>'.$row['map'].'</td><td>'.$row['pov'].'</td><td><a class="link" target="_blank" href="'.$link.'">'.$lang['dwld'].'</a></td></tr>';
				}
				$rtr .= '</table>';
			}else $rtr .= $lang['mysql_emp'].'<br/>';
		}
	}
	$rtr .= '<br/>'.content_line('B');
	return $rtr;
}

Function match_demos($idm){
	if(strlen(DEMOSQUAD)>10){
		$qry = 'SELECT ds.iddemo, ds.map, ds.pov FROM '.KML_PREFIX.'_demo AS d, ds_demos AS ds WHERE d.league='.LEAGUE.' AND d.idd_ds=ds.iddemo AND d.idm='.$idm;
		$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
		if(mysql_num_rows($rsl)>0){
			while($row = mysql_fetch_array($rsl)){
				foreach($row as $ky=>$vl) $row[$ky] = intoBrowser($vl);
				if(++$i!=1) $rtr .= ', ';
				$rtr .= '<a class="link" target="_blank" href="'.DEMOSQUAD.'index.php?op=df&amp;id='.$row['iddemo'].'">'.$row['map'].' &laquo; '.$row['pov'].'</a>';
			}
		}else $rtr = '&#8212;&#8212;&#8212;';
	}else{
		$dqry = 'SELECT c1.tag AS cnm1, c2.tag AS cnm2, d.link, d.map, d.pov, d.time, d.extension FROM '.KML_PREFIX.'_demo_sa AS d, '.KML_PREFIX.'_match AS m, '.KML_PREFIX.'_clan AS c1, '.KML_PREFIX.'_clan AS c2 WHERE d.league='.LEAGUE.' AND d.idm='.$idm.' AND d.idm=m.idm AND m.idc1=c1.idc AND m.idc2=c2.idc';
		$drsl = query(__FILE__,__FUNCTION__,__LINE__,$dqry,0);
		if(mysql_num_rows($drsl)>0){
			while($drow = mysql_fetch_assoc($drsl)){
				foreach($drow as $ky=>$vl) $drow[$ky] = intoBrowser($vl);
				if($drow['link']) $link = $drow['link']; else $link = 'demos/'.$drow['time'].'_'.$drow['cnm1'].'_vs_'.$drow['cnm2'].'_'.$drow['map'].'_'.$drow['pov'].'.'.$drow['extension'];
				if(++$i!=1) $rtr .= ', ';
				$rtr .= '<a class="link" href="'.$link.'">'.$drow['map'].' &laquo; '.$drow['pov'].'</a>';
			}
		}else $rtr .= '&#8212;&#8212;&#8212;';
	}
	return $rtr;
}

?>
