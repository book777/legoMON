<?php

require_once 'config.php';

if (file_exists($file['template']) & time() - $time['cache'] < filectime($file['template']))// Кеширование
	die(readfile($file['template']));

ob_start();// Включаем буфер

foreach($server as $e) {// Обрабатываем каждый сервер
	$get = server($e[0], $time['out']);// Получаем информацию
	if($get['error'])// Если не удалось получить данные, выводим ошибку
		include 'template/offline.php';
	else {// Если всё ок
		$all['po'] += $get['player_online'];
		$all['pm'] += $get['player_max'];
		include 'template/online.php';
	}
}

// Абсолютный рекорд
$file['record'] =  readfile('cache/record.log');// Число абсолютного
if($all['po'] >= $file['record']) {// Если теперь онлайн больше
	file_put_contents('cache/record.log', $all['po']);// Записать новый рекорд
	$record['all'] = $all['po'];// Присвоить переменной онлайн
} else// если нет
	$record['all'] = $file['record'];// присвоить старый рекорд

// Рекорд за день
$file['record_day'] = readfile('cache/record_day.log');// Число временного
if($all['po'] > $file['record_day']) {// Если онлайн больше
	filemtime('cache/timefile.log')
	file_put_contents('cache/record_day.log', $all['po']);// Записать новый рекорд
	$record['day'] = $all['po'];// Присвоить переменной онлайн
}

$all['percent'] = @floor(($all['po']/$all['pm'])*100);
$all['date'] = date_in_text(filemtime('cache/record.log'));
require_once 'template/all.php';


echo base64_decode(date_in_text(false, true));// Делаем подпись
file_put_contents($file['template'], ob_get_contents());// Сохраняем вывод в файл

function server($address, $timeout) {
	$thetime = microtime(true);
	if(!$in = @fsockopen($address, 25565, $errno, $errstr, $timeout)) {
		if(round((microtime(true)-$thetime)*1000) >= $timeout * 1000)
																												return array('error' => 'Большой пинг');
		else
																												return array('error' => 'Выключен');
	}
	@stream_set_timeout($in, $timeout);
	fwrite($in, "\xFE\x01");
	$data = fread($in, 512);
	$Len = strlen($data);
	if($Len < 4 || $data[0] !== "\xFF")
																												return array('error' => 'Неизвестное ядро');
	$data = substr($data, 3);
	$data = iconv('UTF-16BE', 'UTF-8', $data);
	if($data [1] === "\xA7" && $data[2] === "\x31") {
		$data = explode("\x00", $data);
		return  array(
			'motd' => motd($data[3]),
			'motd_html' => motd_html($data[3]),
			'player_online' => intval($data[4]),
			'player_max' => intval($data[5]),
			'percent' => @floor((intval($data[4])/intval($data[5]))*100),
			'version' => $data[2],
			'ping' => round((microtime(true)-$thetime)*1000)
		);
	}
	$data = explode("\xA7", $data);
	return array(
		'motd' => motd(substr($data[0], 0, -1)),
		'motd_html' => motd_html(substr($data[0], 0, -1)),
		'player_online' => isset($data[1]) ? intval($data[1]) : 0,
		'player_max' => isset($data[2]) ? intval($data[2]) : 0,
		'percent' => @floor((intval($data[1])/intval($data[2]))*100),
		'version' => '< 1.4',
		'ping' => round((microtime(true)-$thetime)*1000)
	);
}
function motd($text) {
	$mass = explode('§', $text);
	foreach ($mass as $val)
		$out .= substr($val, 1);
	return $out;
}
function motd_html($minetext) {// Вывод названия сервера с цветами
	preg_match_all('/[§&][0-9a-z][^§&]*/', $minetext, $brokenupstrings);
	foreach ($brokenupstrings as $results) {
		$ending = '';
			foreach ($results as $individual) {
				$code = preg_split('/[&§][0-9a-z]/', $individual);
				preg_match('/[&§][0-9a-z]/', $individual, $prefix);
				if (isset($prefix[0])) {
					$actualcode = substr($prefix[0], 1);
					switch ($actualcode) {
						case '1': $returnstring .= '<font color="0000AA">'; $ending .= '</font>'; break;
						case '2': $returnstring .= '<font color="00AA00">'; $ending .= '</font>'; break;
						case '3': $returnstring .= '<font color="00AAAA">'; $ending .= '</font>'; break;
						case '4': $returnstring .= '<font color="AA0000">'; $ending .= '</font>'; break;
						case '5': $returnstring .= '<font color="AA00AA">'; $ending .= '</font>'; break;
						case '6': $returnstring .= '<font color="FFAA00">'; $ending .= '</font>'; break;
						case '7': $returnstring .= '<font color="AAAAAA">'; $ending .= '</font>'; break;
						case '8': $returnstring .= '<font color="555555">'; $ending .= '</font>'; break;
						case '9': $returnstring .= '<font color="5555FF">'; $ending .= '</font>'; break;
						case 'a': $returnstring .= '<font color="55FF55">'; $ending .= '</font>'; break;
						case 'b': $returnstring .= '<font color="55FFFF">'; $ending .= '</font>'; break;
						case 'c': $returnstring .= '<font color="FF5555">'; $ending .= '</font>'; break;
						case 'd': $returnstring .= '<font color="FF55FF">'; $ending .= '</font>'; break;
						case 'e': $returnstring .= '<font color="FFFF55">'; $ending .= '</font>'; break;
						case 'f': $returnstring .= '<font color="FFFFFF">'; $ending .= '</font>'; break;
						case 'r': $returnstring .= $ending; $ending = ''; break;
						case 'l': if (strlen($individual) > 2) { $returnstring .= '<b>'; $ending = '</b>'.$ending; break; }
						case 'm': if (strlen($individual) > 2) { $returnstring .= '<strike>'; $ending = '</strike>'.$ending; break; }
						case 'n': if (strlen($individual) > 2) { $returnstring .= '<span style="text-decoration:underline">'; $ending = '</span>'.$ending; break; }
						case 'o': if (strlen($individual)>2) { $returnstring .= '<i>'; $ending ='</i>'.$ending; break; }
					}
					if (isset($code[1])) {
						$returnstring .= $code[1];
						if (isset($ending) && strlen($individual) > 2) {
							$returnstring .= $ending;
							$ending = '';
						}
					}
				} else {
			$returnstring .= $individual;
			}
		}
	}
	return $returnstring;
}
function date_in_text($data, $up = false) {// Дата для рекордов + подпись
	$iz = array("Jan",		"Feb",		"Mar",		"Apr",		"May",	"Jun",		"Jul",		"Aug",		"Sep",			"Jct",			"Nov",		"Dec");
	$v = array("января",	"феваля",	"марта",	"апреля",	"мая",	"июня",	"июля",	"августа",	"сентября",	"октября",	"ноября",	"декабря");
	$vblhod = str_replace($iz, $v, date("j M в H:i", $data));if($up) return 'PCEtLSBieSBib29rNzc3IHJ1YnVra2l0Lm9yZy90aHJlYWRzLzY0NzQyIC0tPg==';
	return $vblhod;
}
?>