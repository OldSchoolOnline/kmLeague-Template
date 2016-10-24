<?

//framework created by KMprojekt
//usage in different purpose then KMleague or redistributing without KMprojekt's permission is forbidden

#FORMS LIBRARY
require_once('framework_settings.php');
require_once('lib/framework_inc.php');
if(FCK==1) require_once('editor/fckeditor.php');
if(FPDF==1) require_once('lib/fpdf/fpdf.php');
if(IMGD==1) require_once('lib/ImageEditor.php');
if(HOLIDAYS==1) require_once('lib/holidays.php');

Function fwSearchForm($post,$get,$fields,$basics){
	//order $fields by position and write results into $sfield with default settings
	if(!$basics['search_form_style']) $basics['search_form_style'] = 2;
	foreach($fields as $ky=>$vl){
		if($vl['search']==1){
			if($vl['search_head']) $vl['head'] = $vl['search_head'];
			if($vl['search_type']) $vl['type'] = $vl['search_type'];
			if($vl['search_values']) $vl['values'] = $vl['search_values'];
			if($vl['search_param1']) $vl['param1'] = $vl['search_param1'];
			elseif($vl['search_type']=='S') $vl['param1'] = '';
			if($vl['search_param2']) $vl['param2'] = $vl['search_param2'];
			elseif($vl['search_type']=='S') $vl['param2'] = '';
			if($post['search_item'][$ky]) $vl['default'] = $post['search_item'][$ky];
			if(!$vl['search_pos_col']) $vl['search_pos_col'] = 0;
			if(!$vl['search_pos_row']) $vl['search_pos_row'] = 0;
			$vl['show'] = $basics['search_form_style'];
			$sfields[$ky] = $vl;
			$sort_array_col[$ky] = $vl['search_pos_col'];
			$sort_array_row[$ky] = $vl['search_pos_row'];
		}
	}
	#sorting array
	array_multisort($sort_array_col,SORT_ASC,$sort_array_row,SORT_ASC,$sfields);
	$r .= '<form action="'.$_SERVER['PHP_SELF'].'?op='.$basics['op'].'" method="post">';
	if($basics['search_form_style']==2) $r .= '<table cellspacing="1" cellpadding="4">';
	foreach($sfields as $ky=>$inf){
		if($basics['search_form_style']==4){
			$sHead = '';
			$sFields = '';
		}
		$f = fwFormItem('search_item['.$ky.']',$inf);
		$sHead .= $f['head'];
		$sFields .= $f['field'];
		if($post['search_item'][$ky]){
			if($inf['type']=='I') $where .= ' AND '.$ky.' LIKE "%'.$post['search_item'][$ky].'%"'; else $where .= ' AND '.$ky.'="'.$post['search_item'][$ky].'"';
		}
		if($basics['search_form_style']==4) $r .= $sHead.': '.$sFields.' ';
	}
	$search_button = '<input class="addItem" type="submit" name="search" value="'.INF_SEARCH.'" />';
	if($basics['search_form_style']==2) $r .= '<tr>'.$sHead.'<td class="formListHead">&nbsp;</td></tr>
	<tr class="formItem">'.$sFields.'<td class="formButtons">'.$search_button.'</td></tr>
	</table>';
	elseif($basics['search_form_style']==4) $r .= $search_button;
	$r .= '</form>';
	$return = array($r,$where);
	return $return;
}

Function fwFormatItemGet($item){
	preg_match_all('!\{(.*)\}!Usi', $item, $matches1);
	preg_match_all('!\^(.*)\^!Usi', $item, $matches2);
	preg_match_all('!\[(.*)\]!Usi', $item, $matches3);
	$matches['A'] = $matches1[1];
	$matches['B'] = $matches2[1];
	$matches['C'] = $matches3[1];
	return $matches;
}

#$var($var)
Function fwFormatItemChange($item,$matches,$row,$tabinfos){
	if($matches['A']) foreach($matches['A'] as $ky=>$vl) $item = str_replace('{'.$vl.'}', $row[$vl], $item);
	if($matches['B']) foreach($matches['B'] as $ky=>$vl){
		if(substr_count($vl, ';')>0){
			$inf = explode(';', $vl);
		}else{
			$inf[0] = $vl;
			$inf[1] = 'Y-m-d';
		}
		$item = str_replace('^'.$vl.'^', date($inf[1], $row[$inf[0]]), $item);
	}
	if($matches['C']){
		foreach($matches['C'] as $ky=>$vl){
			$inf2 = explode(';', $vl);
			$vk = $row[$inf2[1]];
			if($vk && $inf2[0]){
				$str = "\$str = \$tabinfos[".$inf2[0].']['.$vk.'];';
				eval ($str);
				$item = str_replace('['.$vl.']', $str, $item);
			}else $item = str_replace('['.$vl.']', '', $item);
		}
	}
	return $item;
}

Function fwUpdateItem($basics,$fields,$post){
	$qry = 'UPDATE `'.$basics['table'].'` SET';
	foreach($fields as $ky=>$vl){
		#pominiecie naglowkow
		if($vl['type']!='H'){
			if($vl['type']!='F' && $vl['type']!='P' && $vl['param2']['multi']!='X'){
				if(++$lq!=1) $qry .= ', ';
			}
			if($vl['type']=='T' || $vl['type']=='I' || $vl['type']=='P'){
				if($vl['param2']>0) $post[$ky] = substr($post[$ky],0,$vl['param2']);
			}
			if($vl['type']=='C' || $vl['type']=='M'){
				if($vl['param2']['multi']!='X' && is_array($post[$ky])){
					$tmp = '';
					foreach($post[$ky] as $xvl) $tmp .= $xvl;
					$post[$ky] = $tmp;
				}
			}
			if($vl['type']=='D'){
				if(!$post[$ky.'_month']) $post[$ky.'_month'] = 1;
				if(!$post[$ky.'_day']) $post[$ky.'_day'] = 1;
				if($post[$ky.'_year']){
					$post[$ky] = mktime($post[$ky.'_hour'], $post[$ky.'_minute'], 0, $post[$ky.'_month'], $post[$ky.'_day'], $post[$ky.'_year']);
				}else $post[$ky] = 0;
			}
			#dodanie pliku
			if($vl['type']=='F'){
				$par = $vl['param2'];
				$big_ext = substr($_FILES[$ky.'_b']['name'],strrpos($_FILES[$ky.'_b']['name'],'.')+1);
				if($par['name']=='I') $name = $basics['id'].'_'.$ky;
				else $name = md5($_FILES[$ky.'_b']['tmp_name'].time());
				$big_link = $par['bigl'].$name.'.'.$big_ext;
				#usuniecie miniatury
				if($par['mini']==1){
					$sml_ext = substr($_FILES[$ky.'_s']['name'],strrpos($_FILES[$ky.'_s']['name'],'.')+1);
					$sml_link = $par['bigl'].$name.'.'.$sml_ext;
				}elseif($par['mini']==2){
					#usuniecie powiekszenia
					$sml_ext = $big_ext;
					$sml_link = $par['smll'].$name.'.'.$sml_ext;
				}
				if(file_upl($big_link,$_FILES[$ky.'_b'],$big_ext,$par['bigd'],$vl['values'],$par['info'],$par['mini'],$sml_link,$_FILES[$ky.'_s'],$sml_ext,$par['smld'],$par['ratio'])==1){
					$post[$ky] = $name.'.'.$big_ext;
					if(++$lq!=1) $qry .= ', ';
					$qry .= '`'.$ky.'`='.intoDB($post[$ky], 'I');
				}
				if($par['descr']==1) $qry .= ', `'.$ky.'_descr`='.intoDB($post[$ky.'_descr'], 'I');
			}elseif($vl['type']=='P'){
				if(strlen($post[$ky])>0){
					if(++$lq!=1) $qry .= ', ';
					$qry .= '`'.$ky.'`='.intoDB(md5($post[$ky]), 'I');
				}
			}elseif($vl['param2']['multi']=='X'){
				$xqry = $vl['param2']['qry_del'].$basics['id'];
				query(__FILE__,__FUNCTION__,__LINE__,$xqry,0);
				if(is_array($post[$ky])){
					foreach($post[$ky] as $xvl){
						$xqry = $vl['param2']['qry_ins'];
						$xqry = str_replace('{id}', $basics['id'], $xqry);
						$xqry = str_replace('{value}', intoDB($xvl, 'I'), $xqry);
						query(__FILE__,__FUNCTION__,__LINE__,$xqry,0);
					}
				}
			}else{
				if($vl['type']=='N'){
					$post[$ky] = str_replace(',', '.',$post[$ky]);
					$pos = strpos($vl['param1'], '.');
					if($pos>0){
						$vl['type'] = 'F';
						$expl = explode('.', $vl['param1']);
					}else $expl[1] = 0;
					$post[$ky] = round($post[$ky], $expl[1]);
				}
				$qry .= '`'.$ky.'`='.intoDB($post[$ky], $vl['type']);
			}
		}
	}
	$qry .= ' WHERE `'.$basics['key'].'`='.$basics['id'];
	//jesli zapytanie musi spelniac dodatkowy warunek to dodajemy ten warunek
	if($basics['query_req']) $qry .= ' AND '.$basics['query_req'];
	return $qry;
}

Function fvCheckData($fields,$post){
	$ok = 1;
	foreach($fields as $ky=>$vl){
		if($vl['req']){
			if($vl['type']=='D'){
				$ok = 1;
			}elseif($vl['type']=='F'){
				$ok = 1;
			}else{
				if(empty($post[$ky])){
					$ok = 0;
					$errs[] = $ky;
				}
			}
		}
	}
	return array($ok,$errs);
}

Function fwVarCleanup($fields,$basics){
	foreach($fields as $ky=>$vl){
		//if alias is set save its values into full name
		if($vl['T']) $vl['type'] = $vl['T'];
		if($vl['H']) $vl['head'] = $vl['H'];
		if($vl['R']) $vl['req'] = $vl['R'];
		if($vl['D']) $vl['default'] = $vl['D'];
		if($vl['V']) $vl['values'] = $vl['V'];
		if($vl['P1']) $vl['param1'] = $vl['P1'];
		if($vl['P2']) $vl['param2'] = $vl['P2'];
		if($vl['RO']) $vl['readonly'] = $vl['RO'];
		if($vl['S']) $vl['search'] = $vl['S'];
		if($vl['ST']) $vl['search_type'] = $vl['ST'];
		if($vl['SH']) $vl['search_head'] = $vl['SH'];
		if($vl['SP1']) $vl['search_param1'] = $vl['SP1'];
		if($vl['SV']) $vl['search_values'] = $vl['SV'];
		if($vl['SPC']) $vl['search_pos_col'] = $vl['SPC'];
		if($vl['SPR']) $vl['search_pos_row'] = $vl['SPR'];
		if($vl['A']) $vl['addon'] = $vl['A'];
		if($vl['AF']) $vl['addon_field'] = $vl['AF'];
		if($vl['I']) $vl['descr'] = $vl['I'];
		if($vl['P']) $vl['show'] = $vl['P'];
		//default settings
		if(!$vl['type']) $vl['type'] = 'I';
		if($vl['type']=='N'){
			if(strpos($vl['param1'], '.')){
				$expl = explode('.', $vl['param1']);
				$vl['param2'] = $expl[0]+$expl[1]+1;
			}else $vl['param2'] = $vl['param1'];
		}
		if($vl['type']=='F') $basics['files'] = 1;
		//save changes in files table
		$fields[$ky] = $vl;
	}
	return array($fields,$basics);
}

Function fwGetValues($fields,$basics){
	//okreslenie listy pol jakie beda pobierane z bazy danych
	foreach($fields as $ky=>$vl){
		if($vl['param2']['multi']=='X'){
			//pobranie wartosci dla tablicy
			$xqry = $vl['param2']['qry_sel'].$basics['id'];
			$xrsl = query(__FILE__,__FUNCTION__,__LINE__,$xqry,0);
			while($xrow = mysql_fetch_row($xrsl)) $fields[$ky]['default'][] = $xrow[0];
		}elseif($vl['type']!='H'){
#					if(++$lt!=1) $items .= '';
			$items .= ', `'.$ky.'`';
		}
		if($vl['type']=='F' && $vl['param2']['descr']==1) $items .= ', `'.$ky.'_descr`';
	}
	$qry = 'SELECT `'.$basics['key'].'`'.$items.' FROM `'.$basics['table'].'` WHERE `'.$basics['key'].'`='.$basics['id'];
	//jesli zapytanie musi spelniac dodatkowy warunek to dodajemy ten warunek
	if($basics['query_req']) $qry .= ' AND '.$basics['query_req'];
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	if(mysql_num_rows($rsl)!=1) die(INF_SURE);
	$row = mysql_fetch_assoc($rsl);
	//zamieniamy wartosci domyslne dla pol na te pobrane z bazy danych
	foreach($fields as $ky=>$vl){
		//zamiana wartosci C|M na tablice
		if($vl['type']!='H' && $vl['type']!='P'){
			if($vl['type']=='C' || $vl['type']=='M'){
				$all = strlen($row[$ky]);
				if($all>0){
					settype($vl['default'], "array");
					for($i=0;$i<$all;$i++) $vl['default'][] = $row[$ky]{$i};
				}
			}elseif($vl['type']=='F'){
				$vl['default']['file'] = intoForm($row[$ky]);
				if($vl['param2']['descr']==1) $vl['default']['descr'] = intoForm($row[$ky.'_descr']);
			}else{
				if($vl['type']=='T' && $vl['param2']=='W') $vl['default'] = intoForm($row[$ky],2);
				else $vl['default'] = intoForm($row[$ky]);
			}
			$fields[$ky] = $vl;
		}
	}
	return $fields;
}

Function fwRemoveItem($basics){
	if(SAFE_DEL==1) $qry = 'UPDATE `'.$basics['table'].'` SET `removed`="'.time().'", `removed_by`="'.$_SESSION[SESSION_L].'" WHERE `'.$basics['key'].'`="'.$basics['id'].'"'; else $qry = 'DELETE FROM `'.$basics['table'].'` WHERE `'.$basics['key'].'`="'.$basics['id'].'"';
	if($basics['query_req']) $qry .= ' AND '.$basics['query_req'];
	if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) return 1; else return 0;
}

Function fwAddItem($basics){
	$qry = 'INSERT INTO `'.$basics['table'].'`(`'.$basics['key'].'`'.$basics['query_add'].') VALUES(""'.$basics['query_add_value'].')';
	query(__FILE__,__FUNCTION__,__LINE__,$qry,0);
	$id = mysql_insert_id();
	return $id;
}

Function fwNewItem($basics,$fields,$post){
	$basics['id'] = fwAddItem($basics);
	$qry = fwUpdateItem($basics,$fields,$post);
	if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) return array(1,$basics['id']);
	else return array(0,$basics['id']);
}

Function fwCreateForm($basics,$fields,$post){
	if($basics['editonly']!=1) $del_opt = '<input class="addItem" name="opt3" type="submit" value="'.INF_DEL.'" /> ';
	if($basics['id']) $options = $del_opt.'<input class="addItem" name="opt2" type="submit" value="'.INF_SAVE.'" />'; else $options = '<input class="submit" type="submit" name="opt1" value="'.INF_ADD.'" />';
	$r .= "\n".'<input type="hidden" name="post_form" value="1"/><table cellspacing="1" cellpadding="4">'."\n";
	foreach($fields as $key=>$inf){
		if($post['post_form']==1){
			if($inf['type']=='D'){
				if($post[$key.'_year']){
					if(!$post[$key.'_month']) $post[$key.'_month'] = 1;
					if(!$post[$key.'_day']) $post[$key.'_day'] = 1;
					$post[$key] = mktime($post[$key.'_hour'], $post[$key.'_minute'], 0, $post[$key.'_month'], $post[$key.'_day'], $post[$key.'_year']);
				}else $post[$key] = 0;
				$inf['default'] = $post[$key];
			}else $inf['default'] = $post[$key];
		}
		$f = fwFormItem($key,$inf);
		$r .= $f['head'].$f['field'].$f['foot'];
	}
	$r .= '<tr><td colspan="2" class="formButtons">'.$options.'</td></tr>'."\n".'</table>'."\n";
	return $r;
}


Function fwItemsList($query,$basics){
	$rsl = query(__FILE__,__FUNCTION__,__LINE__,$query,0);
	$r = '<table cellspacing="1" cellpadding="4"'.$basics['list_size_width'].'>'."\n";
	$r .= '<tr><td class="formListHead">#ID</td>';
	foreach($basics['list_items'] as $vl) $r .= '<td class="formListHead">'.$vl.'</td>';
	if(!$basics['search_form_buttons']) $basics['search_form_buttons'] = 1;
	if($basics['search_form_buttons']==1) $r .= '<td class="formListHead">'.INF_OPTIONS.'</td>';
	$r .= '</tr>'."\n";
	$colspan = count($basics['list_items'])+2;
	if($basics['list_divider']) $matches = fwFormatItemGet($basics['list_divider_head']);
	array_unshift($basics['list_values'], '{'.$basics['key'].'}');
	while($row = mysql_fetch_assoc($rsl)){
		if($basics['list_divider']){
			if($divide!=$row[$basics['list_divider']]){
				$r .= '<tr class="formListItem2"><td colspan="'.$colspan.'">'.fwFormatItemChange($basics['list_divider_head'],$matches,$row,$basics['tabinfos']).'</td></tr>';
			}
		}
		$link = $_SERVER['PHP_SELF'].'?op='.$basics['op'].'&amp;id='.$row[$basics['key']].'&amp;opt=';
		++$i;
		if($row[$basics['key']]==$basics['id']) $class = 'formListItemA'; else $class = 'formListItem'.($i%2);
		$r .= '<tr class="'.$class.'">';
		$kl = 0;
		foreach($basics['list_values'] as $vl){
			$add = '';
			if(++$kl==1) $add = '<a name="id'.$row[$basics['key']].'"></a>';
			$matchesX = fwFormatItemGet($vl);
			$vl = fwFormatItemChange($vl,$matchesX,$row,$basics['tabinfos']);
			$r .= '<td>'.$add.$vl.'</td>';
		}
		if($basics['search_form_buttons']==1){
			if($basics['editonly']!=1) $del_opt = ' <a onClick="usun(\''.$link.'4\',\''.$basics['inf_del_query'].'\'); return false;" href="'.$link.'3#id'.$row[$basics['key']].'"><img style="vertical-align: bottom;" src="im/delete.gif" alt="'.INF_DEL.'" title="'.INF_DEL.'" /></a>';
			$r .= '<td class="center"><a href="'.$link.'5#id'.$row[$basics['key']].'"><img style="vertical-align: bottom; bottom;" src="im/edit.gif" alt="'.INF_EDIT.'" title="'.INF_EDIT.'" /></a>'.$del_opt.'</td></tr>'."\n";
		}
		if($basics['list_divider']) $divide = $row[$basics['list_divider']];
	}
	$r .= '</table>'.$basics['descrR'];
	return $r;
}

Function data_form($post,$get,$fields,$basics,$typ=1){
	//set default settings & aliases
	if(!$basics['search_form_style']) $basics['search_form_style'] = 2;
	if($basics['single_item']>0) $basics['id'] = $basics['single_item'];
	$basics['id'] = (int)$basics['id'];
	$cleanVar = fwVarCleanup($fields,$basics);
	$fields = $cleanVar[0];
	$basics = $cleanVar[1];
	//recognize what information should be displayed
	if($typ==0 || $basics['single_item']>0){
		$form = 1;
		$list = 0;
	}elseif($typ==1){
		$form = 1;
		$list = 1;
	}elseif($typ==2){
		$form = 0;
		$list = 1;
	}
	if($typ==2) $r .= fwContentHead($basics['header_list']);
	if($form == 1){
		if($basics['id']) $r .= fwContentHead($basics['header_edit']); else $r .= fwContentHead($basics['header']);
		//okreslenie opcji na podstawie wyslanej przez get wartosci zmiennej opt
		if($basics['opt']) $post['opt'.$basics['opt']] = 1;
		//security checking
		if($basics['editonly']==1 && ($post['opt3']||$post['opt4'])){
			unset($post['opt3'],$post['opt4']);
		}
		//get values for choosen ID
		if($basics['id']) $fields = fwGetValues($fields,$basics);
		if($basics['files']==1) $enctype = ' enctype="multipart/form-data"';
		$r .= "\n".'<form method="post"'.$enctype.' action="'.$_SERVER['PHP_SELF'].'?op='.$basics['op'].'">'.$basics['form_addon'];
		if($basics['id']) $r .= '<input type="hidden" name="id" value="'.$basics['id'].'" />';
		if($post['opt2'] || $post['opt1']){
			$status = fvCheckData($fields,$post);
			if($status[0]==0){
				unset($post['opt2']);
				unset($post['opt1']);
				$r .= '<div class="infoAction">'.INF_REQUIRED.' (';
				foreach($status[1] as $k=>$v){
					if(++$rs!=1) $r .= ', ';
					$r .= $fields[$v]['head'];
				}
				$r .= ')</div>';
			}
		}
		#############################################
		#aktualizacja wpisu###############################
		if($post['opt2']){
			$qry = fwUpdateItem($basics,$fields,$post);
			if(query(__FILE__,__FUNCTION__,__LINE__,$qry,0)) $r .= '<div class="infoAction">'.$basics['inf_upd'].'</div>';
		#############################################
		#zapytanie przed usunieciem wpisu#####################
		}
		if($post['opt3']){
			$r .= $basics['inf_del_query'].' ? <input class="submit" type="submit" name="opt4" value="'.INF_YES.'">';
		#############################################
		#usuniecie wpisu#################################
		}
		if($post['opt4']){
			if(fwRemoveItem($basics)) $r .= '<div class="infoAction">'.$basics['inf_del'].'</div>';
		#############################################
		#dodanie wpisu##################################
		}
		if($post['opt1']){
			$addResult = fwNewItem($basics,$fields,$post);
			if($addResult[0]==1) $r .= '<div class="infoAction">'.$basics['inf_add'].'</div>';
			$basics['id'] = $addResult[1];
		#############################################
		#wyswietlenie formularza###########################
		}
		if($post['opt5'] || $post['opt10'] || $basics['single_item']>0 || (!$post['opt1'] && !$post['opt2'] && !$post['opt3'] && !$post['opt4'])){
			$r .= fwCreateForm($basics,$fields,$post);
		}
		$r .= '</form><br />'.$basics['descrL'];
	}
	if($list==1){
		$r .= '</td><td valign="top"><div class="head">';
		$r .= '<a href="'.$_SERVER['PHP_SELF'].'?op='.$basics['op'].'&amp;opt=10"><img alt="new" title="'.INF_NEW.'" src="im/new.gif" /></a> ';
		if($typ==1) $r .= $basics['header_list'];
		$r .= '</div><br/>';
		#############################################
		#lista z wyborem#############################
		$query_list = $basics['list_query'];
		if($basics['search_form']==1){
			$search_inf = fwSearchForm($post,$get,$fields,$basics);
			$r .= $search_inf[0];
			if($search_inf[1]){
				if(strpos($query_list,'WHERE')<1){
					$query_list .= ' WHERE '.substr($search_inf[1],5);
				}else $query_list .= $search_inf[1];
			}
		}
		$query_list .= $basics['list_query_add'];
		if($basics['list_size']){
			$lStyle = explode(':', $basics['list_size']);
			$class = ' style="width: '.$lStyle[0].'px; height: '.$lStyle[1].'px; overflow: auto;"';
			$basics['list_size_width'] = ' style="width: '.($lStyle[0]-20).'px;"';
		}
		$r .= "\n".'<div'.$class.'>'."\n";
		#display list with items
		$r .= fwItemsList($query_list,$basics);
	}
	$r .= fwContentFoot($basics['descrM']);
	if($basics['output']=='F'){
		$return['content'] = $r;
		$return['id'] = $basics['id'];
	}else $return = $r;
	return $return;
}

Function form_select($key,$inf){
	if(!is_array($inf['values'])) return;
	if($inf['readonly']==1) $readonly = ' readonly'; // change into return VALUE <input type="hidden">
	if($inf['param1']){
		$param1 = explode(':',$inf['param1']);
		$style = ' style="';
		if($param1[1]) $style .= 'width: '.$param1[1].'px;';
		if($param1[0]) $size = ' size="'.$param1[0].'"';
		$style .= '"';
	}
	$r = '<select'.$style.$size.$readonly.$inf['addon_field'].' name="'.$key.'">';
	if($inf['param2']=='N'){
		foreach($inf['values'] as $vl){
			$r .= '<option';
			if($vl==$inf['default']) $r .= ' selected';
			$r .= '>'.$vl.'</option>';
		}
	}else{
		foreach($inf['values'] as $ky=>$vl){
			$r .= '<option value="'.$ky.'"';
			if($ky==$inf['default']) $r .= ' selected';
			$r .= '>'.$vl.'</option>';
		}
	}
	$r .= '</select>';
	return $r;
}

Function form_multiselect($key,$inf){
	if(!is_array($inf['values'])) return;
	if($inf['readonly']==1) $readonly = ' readonly'; // change into return <input type="hidden">
	if($inf['param1']){
		$param1 = explode(':',$inf['param1']);
		$style = ' style="';
		if($param1[1]) $style .= 'width: '.$param1[1].'px;';
		if($param1[0]) $size = ' size="'.$param1[0].'"';
		$style .= '"';
	}
	$r = '<select multiple'.$style.$size.$readonly.$inf['addon_field'].' name="'.$key.'[]">';
	foreach($inf['values'] as $ky=>$vl){
		$r .= '<option value="'.$ky.'"';
		//
		if(in_array($ky,$inf['default'])) $r .= ' selected';
		$r .= '>'.$vl.'</option>';
	}
	$r .= '</select>';
	return $r;
}

Function form_checkbox($key,$inf){
	if(!is_array($inf['values'])) return;
	if($inf['readonly']==1) $readonly = ' readonly';
	if(is_array($inf['default'])) $array = 1;
	foreach($inf['values'] as $ky=>$vl){
		$r .= '<input'.$readonly.$inf['addon_field'].' class="brd0" type="checkbox" name="'.$key.'[]" value="'.$ky.'"';
		if($array == 1){
			if(in_array($ky,$inf['default'])) $r .= ' checked';
		}
		$r .= ' /> '.$vl.$inf['param1'];
	}
	return $r;
}

Function form_input($key,$inf){
	if($inf['readonly']==1) $readonly = ' readonly';
	if($inf['param1']){
		$param1 = explode(':',$inf['param1']);
		$style = ' style=\'';
		if($param1[0]) $style .= 'width: '.$param1[0].'px;';
		if($param1[1]) $style .= 'height: '.$param1[1].'px;';
		$style .= '\'';
	}
	if($inf['multi']) $key2 = $key.'[]'; else $key2 = $key;
	if($inf['param2']>0) $maxlen = ' maxlength=\''.$inf['param2'].'\'';
	$field = '<input type=\'text\' name=\''.$key2.'\''.$maxlen.$style.$readonly.$inf['addon_field'].' value=\'\' />';
	if($inf['multi']){
		$add = ' <img src="im/new.gif" onClick="addNewElement(\'field_'.$key.'\', \''.addslashes($field).'<br/>\')"/>';
		if(is_array($inf['default'])){
			foreach($inf['default'] as $dvl){
				$r .= str_replace('value=\'\'', 'value=\''.$dvl.'\'', $field);
				if(++$lk==1) $r .= $add;
				$r .= '<br/>';
			}
		}else $r = $field.$add.'<br/>';
	}else $r = str_replace('value=\'\'', 'value=\''.$inf['default'].'\'', $field);
	return $r;
}

Function form_multi_input($key,$inf){
	if($inf['readonly']==1) $readonly = ' readonly';
	if($inf['param1']){
		$param1 = explode(':',$inf['param1']);
		$style = ' style="';
		if($param1[0]) $style .= 'width: '.$param1[0].'px;';
		if($param1[1]) $style .= 'height: '.$param1[1].'px;';
		$style .= '"';
	}
	if($inf['param2']>0) $maxlen = ' maxlength="'.$inf['param2'].'"';
	$r .= '<input type="text" name="'.$key2.'"'.$maxlen.$style.$readonly.$inf['addon_field'].' value="'.$inf['default'].'" />';
	return $r;
}

Function form_number($key,$inf){
	if($inf['readonly']==1) $readonly = ' readonly';
	if($inf['param2']>0) $maxlen = ' maxlength="'.$inf['param2'].'"';
	$r .= '<input type="text" name="'.$key.'" style="width: '.($inf['param2']*8).'px;"'.$maxlen.$style.$readonly.$inf['addon_field'].' value="'.$inf['default'].'" />';
	return $r;
}

Function form_passw($key,$inf){
	if($inf['readonly']==1) $readonly = ' readonly';
	if($inf['param1']){
		$param1 = explode(':',$inf['param1']);
		$style = ' style="';
		if($param1[0]) $style .= 'width: '.$param1[0].'px;';
		if($param1[1]) $style .= 'height: '.$param1[1].'px;';
		$style .= '"';
	}
	if($inf['param2']>0) $maxlen = ' maxlength="'.$inf['param2'].'"';
	$r = '<input type="text" name="'.$key.'"'.$maxlen.$style.$readonly.$inf['addon_field'].' value="'.$inf['default'].'" />';
	return $r;
}

Function form_radio($key,$inf){
	if(!is_array($inf['values'])) return;
	if($inf['readonly']==1) $readonly = ' readonly';
	foreach($inf['values'] as $ky=>$vl){
		$r .= '<input'.$readonly.$inf['addon_field'].' class="brd0" type="radio" name="'.$key.'" value="'.$ky.'"';
		if($ky==$inf['default']) $r .= ' checked';
		$r .= ' /> '.$vl.$inf['param1'];
	}
	return $r;
}

Function form_textarea($key,$inf){
	if($inf['readonly']==1) $readonly = ' readonly';
	if($inf['param2']=='W'){
		$param1 = explode(':',$inf['param1']);
		$oFCKeditor = new FCKeditor($key);
		$oFCKeditor->Value = $inf['default'];
		$oFCKeditor->Width = $param1[0];
		$oFCKeditor->Height = $param1[1];
		$r = $oFCKeditor->Create();
	}else{
		$param1 = explode(':',$inf['param1']);
		$style = ' style="';
		if($param1[0]) $style .= 'width: '.$param1[0].'px;';
		if($param1[1]) $style .= 'height: '.$param1[1].'px;';
		$style .= '"';
		$r = '<textarea name="'.$key.'"'.$style.$readonly.$inf['addon_field'].'>'.$inf['default'].'</textarea>';
	}
	return $r;
}

Function form_date($key,$inf){
	#YMDHI
	switch($inf['param1']){
		case 'I':{
			if($inf['param2']['I']) $switch = $inf['param2']['I']; else $switch = 1;
			$minutes[] = '';
			for($i=0;$i<60;$i+=$switch){
				if(strlen($i)==1) $j = '0'.$i; else $j = $i;
				$minutes[] = $j;
			}
			if($inf['default']) $minute = date('i', $inf['default']); else $minute = '';
		}
		case 'H':{
			$hours[] = '';
			if($inf['param2']['H']) $mm = explode(':', $inf['param2']['H']);
			else $mm = array(0,23);
			for($i=$mm[0];$i<$mm[1]+1;$i++){
				if(strlen($i)==1) $j = '0'.$i; else $j = $i;
				$hours[] = $j;
			}
			if($inf['default']) $hour = date('H', $inf['default']); else $hour = '';
		}
		case 'D':{
			$days[] = '';
			for($i=1;$i<32;$i++){
				if(strlen($i)==1) $j = '0'.$i; else $j = $i;
				$days[] = $j;
			}
			if($inf['default']) $day = date('d', $inf['default']); else $day = '';
		}
		case 'M':{
			$months[] = '';
			for($i=1;$i<13;$i++){
				if(strlen($i)==1) $j = '0'.$i; else $j = $i;
				$months[] = $j;
			}
			if($inf['default']) $month = date('m', $inf['default']); else $month = '';
		}
		case 'Y':{
			$years[] = '';
			if($inf['param2']['Y']){
				$mm = explode(':', $inf['param2']['Y']);
				for($i=$mm[0];$i<$mm[1]+1;$i++) $years[] = $i;
			}else $years[] = date('Y');
			if($inf['default']) $year = date('Y', $inf['default']); else $year = '';
		}
	}
	if($inf['readonly']==1){
		if($years) $r = '<input '.$inf['addon_field'].' readonly type="text" style="width: 30px" name="'.$key.'_year" value="'.$year.'" />';
		if($months) $r .= '-<input '.$inf['addon_field'].' readonly type="text" style="width: 20px" name="'.$key.'_month" value="'.$month.'" />';
		if($days) $r .= '-<input '.$inf['addon_field'].' readonly type="text" style="width: 20px" name="'.$key.'_day" value="'.$day.'" />';
		if($hours) $r .= ' <input '.$inf['addon_field'].' readonly type="text" style="width: 20px" name="'.$key.'_hour" value="'.$hour.'" />';
		if($minutes) $r .= ':<input '.$inf['addon_field'].' readonly type="text" style="width: 20px" name="'.$key.'_minute" value="'.$minute.'" />';
	# readonly;
	}else{
		if($years) $r = '<select '.$inf['addon_field'].' name="'.$key.'_year">'.array_norm($years,$year).'</select>';
		if($months) $r .= '-<select '.$inf['addon_field'].' name="'.$key.'_month">'.array_norm($months,$month).'</select>';
		if($days) $r .= '-<select '.$inf['addon_field'].' name="'.$key.'_day">'.array_norm($days,$day).'</select>';
		if($hours) $r .= ' <select '.$inf['addon_field'].' name="'.$key.'_hour">'.array_norm($hours,$hour).'</select>';
		if($minutes) $r .= ':<select '.$inf['addon_field'].' name="'.$key.'_minute">'.array_norm($minutes,$minute).'</select>';
	}
	return $r;
}

Function form_file($key,$inf){
	if($inf['readonly']==1) $readonly = ' readonly';
	if($inf['param1']){
		$style = ' size="'.$inf['param1'].'"';
		$txtstyle = ' style="width: '.($inf['param1']*8).'px;"';
	}
	if($inf['param2']['mini']==1 || $inf['param2']['descr']==1) $r = INF_FILE_BIG;
	$r .= ' <input type="file"'.$style.$inf['addon_field'].' name="'.$key.'_b" />';
	if($inf['param2']['mini']==1) $r .= '<br/>'.INF_FILE_SMALL.' <input type="file"'.$style.$inf['addon_field'].' name="'.$key.'_s" />';
	if($inf['param2']['descr']==1) $r .= '<br/>'.INF_FILE_DESCR.' <input type="text"'.$txtstyle.' name="'.$key.'_descr" value="'.$inf['default']['descr'].'" maxlength="200"/>';
	return $r;
}

Function fwFormItem($key,$inf){
	//get array values and type
	if($inf['values']){
		if(!is_array($inf['values'])){
#	echo 'as';
			$getInfo = getArray($inf['values']);
			if($getInfo[1]!='X' && !empty($getInfo[1])) $inf['param2'] = $getInfo[1];
			//if array type is not set then its ASSOC by default
			if(!$inf['param2']) $inf['param2'] = 'A';
			$inf['values'] = $getInfo[0];
			print_r($inf['values']);
		}
	}
	//dodac funkcjonalnosc do show
	if(!$inf['show']) $inf['show'] = 1;
	$class = 'formHead';
	if($inf['req']) $class .= 'R';
	if($inf['type']=='F' && $inf['default']['file']){
		$link = $inf['param2']['bigl'].$inf['default']['file'];
		if($inf['param2']['mini']>0){
			$link2 = $inf['param2']['smll'].$inf['default']['file'];
			if(file_exists($link2)) $file = ' <a target="_blank" href="'.$link2.'"><img src="im/file2.gif" /></a>';
		}
		if(file_exists($link)) $file .= ' <a target="_blank" href="'.$link.'"><img src="im/file.gif" /></a>';
	}
	if($inf['descr']){
		$titleadd = '<span class="silver">?</span>';
		$title = ' title="'.$inf['descr'].'"';
	}
	if($inf['multi']) $field_id = ' id="field_'.$key.'"';
	if($inf['type']=='H'){
		$r['head'] = '<tr class="formListItem2"><td colspan="2"'.$title.'>'.$titleadd.$inf['head'].'</td></tr>';
	}else{
		if($inf['show']=='2'){
			$r['head'] = '<td class="formListHead"'.$title.'>'.$titleadd.$inf['head'].'</td>';
			$r['field'] = '<td'.$field_id.'>';
		}elseif($inf['show']=='1'){
			$r['head'] = '<tr class="formItem"><td class="'.$class.'"'.$title.'>'.$titleadd.$inf['head'].$file.':</td><td'.$field_id.'>';
		}elseif($inf['show']=='3'){
			$r['head'] = '<tr class="formItem"><td colspan="2" class="'.$class.'"'.$title.$field_id.'>'.$titleadd.$inf['head'].$file.':<br />';
		}elseif($inf['show']=='4'){
			$r['head'] = '<span class="bold"'.$title.$field_id.'>'.$inf['head'].'</span>';
		}
		switch($inf['type']){
			case 'S': $r['field'] .= form_select($key,$inf); break;
			case 'I': $r['field'] .= form_input($key,$inf); break;
			case 'R': $r['field'] .= form_radio($key,$inf); break;
			case 'D': $r['field'] .= form_date($key,$inf); break;
			case 'C': $r['field'] .= form_checkbox($key,$inf); break;
			case 'T': $r['field'] .= form_textarea($key,$inf); break;
			case 'F': $r['field'] .= form_file($key,$inf); break;
			case 'M': $r['field'] .= form_multiselect($key,$inf); break;
			case 'P': $r['field'] .= form_passw($key,$inf); break;
			case 'N': $r['field'] .= form_number($key,$inf); break;
			case 'MI': $r['field'] .= form_multi_input($key,$inf); break;
		}
		$r['field'] .= $inf['addon'];
		if($inf['show']==2) $r['field'] .= '</td>'."\n";
		if($inf['show']==1 || $inf['show']==3) $r['foot'] = '</td></tr>'."\n";
	}
	return $r;
}

?>
