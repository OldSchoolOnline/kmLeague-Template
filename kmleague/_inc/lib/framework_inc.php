<?

#protection for sql injection by anakin - php5.pl
$arrArguments = array();
$intArgumentIndex = 0;
function parseArgument($arrMatches) {
	global $arrArguments, $intArgumentIndex;
	$strMatch = $arrMatches[0];
	$strArgument = @$arrArguments[$intArgumentIndex++];
	if($strMatch=='%s' || $strMatch=='%m' || $strMatch=='%x'){
		if (get_magic_quotes_gpc()){
			$strArgument = stripslashes($strArgument);
		}
	}
	switch ($strMatch){
		case '%d': return (int)$strArgument;
		case '%f': return (float)$strArgument;
		case '%s': return '"'.mysql_real_escape_string($strArgument).'"';
		case '%m': return '"'.ubcode_add(mysql_real_escape_string($strArgument)).'"';
		case '%x': return '"'.comm_emo(mysql_real_escape_string($strArgument)).'"';
		case '%b': return (int)((bool)$strArgument);
	}
}

function SQL($strSql) {
	global $arrArguments, $intArgumentIndex;
	$arrArgs = func_get_args();
	array_shift($arrArgs);
	$arrArguments = $arrArgs;
	$intArgumentIndex = 0;
	return preg_replace_callback('/(%[fdsbmx])/', 'parseArgument', $strSql);    
}

Function query($file,$function,$line,$qry,$typ=1){
	$rsl = @mysql_query($qry);
	$errno = mysql_errno();
	if($errno==0){
		return $rsl;
	}else{
		//turn off errors mailing when there is no mail or there are errors: 1016 {table doesnt exists} 2013 {lost connection with DB} 1053 {restart DB} 1045 {cannot connect with database wrong lg/ps} 1226 {User 'xxx' has exceeded the 'max_questions' resource}
#		if(strlen(ERROR_MAIL)>0 && $errno!=1016 && $errno!=2013 && $errno!=1053 && $errno!=1045 && $errno!=1226){
			$message = "FILE: ".$file."\nFUNCTION: ".$function."\nLINE: ".$line."\n\nQUERY: ".$qry."\nERROR:".mysql_error()."\nERR NO.:".$errno."\n";
			if(is_array($_POST)){
				if(count($_POST)>0){
					$message .= "\n".'$_POST'."\n";
					foreach($_POST as $ky=>$vl) $message .= "\n".$ky.'=>'.$vl;
				}
			}
			if(is_array($_GET)){
				if(count($_GET)>0){
					$message .= "\n".'$_GET'."\n";
					foreach($_GET as $ky=>$vl) $message .= "\n".$ky.'=>'.$vl;
				}
			}
			$message .= "\nIP:".$_SERVER['REMOTE_ADDR']."\nREQUEST:".$_SERVER['REQUEST_URI']."\nREFERER:".$_SERVER['HTTP_REFERER'];
			echo nl2br($message);
#			if(@mail(ERROR_MAIL, 'error', $message, "From: error@{$_SERVER['SERVER_NAME']}\r\n")){
#				if($typ==1) echo 'Error sent to author.';
#			}else{
#				echo 'Server can\'t send mail, please send message bellow manually on address <a href="mailto:'.ERROR_MAIL.'">'.ERROR_MAIL.'</a><br/><div class="italic">'.nl2br($message).'</div>';
#			}
#		}
		if($typ==1) exit;
		else return 'ER';
	}
}

Function file_upl($plik_nazwa,$file_big,$big_ext,$dim_big='',$rozszerzenia='',$komunikat=0,$miniatura=0,$miniatura_nazwa='',$file_sml='',$sml_ext='',$dim_sml='',$proporcje=1){
	//change extenstion string case
	$big_ext = strtolower($big_ext);
	$sml_ext = strtolower($sml_ext);
	//check if file size is bigger then 0
	if($file_big['size']>0){
		//check if file extension is accaptable
		if(is_array($rozszerzenia)){
			if(!in_array($big_ext,$rozszerzenia)){
				if($komunikat) print('Rodzaj przes³anego pliku jest niedozwolony.<br />');
				return 0;
			}
		}
		#przeniesienie pliku z katalogu tymczasowego do wskazanego miejsca
		move_uploaded_file($file_big['tmp_name'],$plik_nazwa);
		#zmienienie uprawnieñ dostêpu do pliku
		chmod($plik_nazwa, 0755);
		if(file_exists($plik_nazwa)) $rtr = 1;
		if($komunikat){
			if(file_exists($plik_nazwa)) print('Plik dodany.<br />'); else print('Problem z dodaniem pliku.<br />');
		}
	}
	#sprawdzenie czy zosta³a wys³ana miniatura i dodanie jej do katalogu z miniaturami
	if($miniatura==1 || $miniatura==2){
		if($file_sml['size']>0){
			move_uploaded_file($file_sml['tmp_name'], $miniatura_nazwa);
			chmod($miniatura_nazwa, 0755);
			if(file_exists($miniatura_nazwa)) $rtr = 1;
			if($komunikat){
				if(file_exists($miniatura_nazwa)) print('Miniatura dodana.<br />'); else print('Problem z dodaniem miniatury.<br />');
			}
		}elseif($file_big['size']>0){
			if($miniatura==1){
				$sml_ext = $big_ext;
				$sml_ext = $big_ext;
				//przypisanie nazwy, rozszerzenia i katalogow z bigfile
			}
			$exp = explode(':',$dim_sml);
			$max_width = $exp[0];
			$max_height = $exp[1];
			#pobranie wymiarów du¿ego pliku
			if($proporcje==1){
				$size = GetImageSize($plik_nazwa);
				$width = $size[0];
				$height = $size[1];
				#ustalenie warto¶ci o które trzeba bêdzie pomniejszyæ zdjêcie
				$x_ratio = $max_width/$width;
				$y_ratio = $max_height/$height;
				#sprawdzenie które parametry trzeba zmieniæ
				if(($width<=$max_width) && ($height<=$max_height)){
					$new_width = $width;
					$new_height = $height;
				}elseif(($x_ratio*$height)<$max_height){
					$new_width = $max_width;
					$new_height = ceil($x_ratio*$height);
				}else{
					$new_width = ceil($y_ratio*$width);
					$new_height = $max_height;
				}
			}else{
				$new_width = $max_width;
				$new_height = $max_height;
			}
			#pobranie nazwy pliku i katalogu dla powiekszenia i miniatury
			$poz = strrpos($plik_nazwa, '/')+1;
			$plik = substr($plik_nazwa,$poz);
			$katalog = substr($plik_nazwa,0,$poz);
			$poz2 = strrpos($miniatura_nazwa, '/')+1;
			$plik2 = substr($miniatura_nazwa,$poz2);
			$katalog2 = substr($miniatura_nazwa,0,$poz2);
			#tworzenie miniatury
			$imageEditor = new ImageEditor($plik, $katalog);
			$imageEditor->resize($new_width, $new_height);
			$imageEditor->outputFile($plik2, $katalog2);
			if(file_exists($miniatura_nazwa)) $rtr = 1;
			if($komunikat==1){
				if(file_exists($miniatura_nazwa)) print('Miniatura utworzona.<br /><img src="'.$miniatura_nazwa.'"><br />'); else print('Problem z utworzeniem miniatury.<br />');
			}
		}
	}
	return $rtr;
}

Function array_norm($array,$value){
	foreach($array as $vl){
		$return .= '<option';
		if($vl==$value) $return .= ' selected';
		$return .= '>'.$vl.'</option>';
	}
	return $return;
}

Function array_assoc($array,$value){
	foreach($array as $ky=>$vl){
		$return .= '<option value="'.$ky.'"';
		if($ky==$value) $return .= ' selected';
		$return .= '>'.$vl.'</option>';
	}
	return $return;
}

Function massoc_opt($array,$value){
	if(is_array($value)) $typ = 1; else $typ = 2;
	foreach($array as $ky=>$vl){
		$return .= '<option value="'.$ky.'"';
		if($typ==1){
			if(in_array($ky, $value)) $return .= ' selected';
		}else{
			if($ky==$value) $return .= ' selected';
		}
		$return .= '>'.$vl.'</option>';
	}
	return $return;
}

Function radio_list($name,$array,$value,$spacer){
	foreach($array as $ky=>$vl){
		$str .= '<input type="radio" name="'.$name.'" value="'.$ky.'"';
		if($ky==$value) $str .= ' checked';
		$str .= ' /> '.$vl.$spacer;
	}
	return $str;
}

Function checkbox_assoc($name,$array,$value,$spacer){
	foreach($array as $ky=>$vl){
		$str .= '<input type="checkbox" name="'.$name.'" value="'.$ky.'"';
		if($ky==$value) $str .= ' checked';
		$str .= '> '.$vl.$spacer;
	}
	return $str;
}

Function intoDB($vl,$typ=''){

	switch($typ){
#		case '%d': return (int)$vl;
#		case 'I': return '"'.mysql_real_escape_string($vl).'"';
#		case '%m': return '"'.ubcode_add(mysql_real_escape_string(nl2br($vl))).'"';
#		case '%x': return '"'.comm_emo(mysql_real_escape_string(nl2br($vl))).'"';
		case 'N': return SQL('%d', $vl);
		case 'F': return SQL('%f', $vl);
		default: return SQL('%s', $vl);
	}
}

Function intoForm($vl,$type=1){
	if(get_magic_quotes_gpc()){
		$vl = stripslashes($vl);
	}
	if($type==1) $vl = htmlspecialchars($vl);
	return $vl;
}

Function intoBrowser($vl,$type=1){
	if(get_magic_quotes_gpc()){
		$vl = stripslashes($vl);
	}
	if($type==1) $vl = htmlspecialchars($vl);
	return $vl;
}

Function sqlGetKey($rsl){
	$row = mysql_fetch_assoc($rsl);
	if(mysql_num_rows($rsl)>0){
		foreach($row as $k=>$v){
			$ky = $k; break;
		}
		mysql_data_seek($rsl,0);
	}
	return $ky;
}

Function getArray($inf,$gType=''){
	if(!$inf) return;
	$aType = 'X';
	$iType = gettype($inf);
	if(is_array($inf)) return $inf;
	elseif($iType=='resource' || eregi('SELECT(.*)FROM', $inf)){
		if($iType!='resource'){
			$rsl = query(__FILE__,__FUNCTION__,__LINE__,$inf,0);
			$cols  = substr_count($inf, ',')+1;
			if($gType=='A') $ky = sqlGetKey($rsl);
		}else{
			$rsl = $inf;
			$ky = sqlGetKey($rsl);
		}
		$inf = array();
		if($gType=='A'){
			while($row=mysql_fetch_assoc($rsl)){
				$vl = $row[$ky];
				$aType = 'A';
				array_shift($row);
				$inf[$vl] = $row;
			}
		}else{
			while($row=mysql_fetch_row($rsl)){
				if(!$type){
					if($cols==2){
						$type = 2;
						$aType = 'A';
					}else $aType = 'N';
				}
				if($type==2) $inf[$row[0]] = $row[1];
				else $inf[] = $row[0];
			}
		}
	}else{
		$inf = explode("\n", $inf);
		$aType = 'N';
	}
	$return = array($inf,$aType);
	return $return;
}

Function qry_into_array($qry,$rsl='',$typ='N'){
	if($typ=='A'){
		$qry = str_ireplace('SELECT ', '', $qry);
		$ky = trim(substr($qry, 0, strpos($qry, ',')));
		while($row=mysql_fetch_assoc($rsl)){
			$vl = $row[$ky];
			array_shift($row);
			$array[$vl] = $row;
		}
	}
	return $array;
}

?>
