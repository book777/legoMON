<?php

require_once 'config.php';

if (file_exists($file['template']) & time() - $time['cache'] < filectime($file['template']))// Кеширование
	die(readfile($file['template']));

ob_start();// Включаем буфер

if($style) echo '<link href="http://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF']).'/template/style.css" rel="stylesheet" type="text/css" media="screen" />';

$temp = new MinecraftServer();// Инициализируем мониторинг
foreach($server as $e) {// Обрабатываем каждый сервер
	$get = $temp->getq($e[0], $time['out']);// Получаем информацию
	if($get['error'])// Если не удалось получить данные, выводим ошибку
		include 'template/offline.php';
	else {// Если всё ок
		$all['po'] += $get['player_online'];
		$all['pm'] += $get['player_max'];
		include 'template/online.php';
	}
}

// Абсолютный рекорд
$file['record'] =  file_get_contents('cache/record.log');// Число абсолютного
if($all['po'] >= $file['record']) {// Если теперь онлайн больше
	file_put_contents('cache/record.log', $all['po']);// Записать новый рекорд
	$record['all'] = $all['po'];// Присвоить переменной онлайн
} else// если нет
	$record['all'] = $file['record'];// присвоить старый рекорд

// Рекорд за день
$file['record_day'] = file_get_contents('cache/record_day.log');// Число временного
if($all['po'] > $file['record_day']) {// Если онлайн больше
	file_put_contents('cache/record_day.log', $all['po']);// Записать новый рекорд
	$record['day'] = $all['po'];// Присвоить переменной онлайн
} else// если нет
	$record['day'] = $file['record_day'];// присвоить старый рекорд
if(time() - $time['record_day'] > filemtime('cache/timefile.log')){// Если временной файл создан больше дня назад
	file_put_contents('cache/timefile.log', '');// перезаписываем временный файл
	file_put_contents('cache/record_day.log', $all['po']);// и перезаписываем теперешний онлайн
	$record['day'] = $all['po'];// Присвоить переменной онлайн
}

$all['percent'] = @floor(($all['po']/$all['pm'])*100);// % общего онлайна
$all['date'] = date_in_text(filemtime('cache/record.log'));// вывод красивой даты изменения файла
require_once 'template/all.php';// Выводим общий онлайн

echo base64_decode(date_in_text(false, true));// Делаем подпись
file_put_contents($file['template'], ob_get_contents());// Сохраняем вывод в файл




class MinecraftServer {// Class written by xPaw & modded by book777
	const STATISTIC = 0x00;
	const HANDSHAKE = 0x09;
	private $socket;
	function getp($address, $timeout = 3) {// Ping
		$thetime = microtime(true);
		if(!$in = @fsockopen($address, 25565, $errno, $errstr, $timeout))
			return [
				'address' => $address,
				'error' => 'Выключен'
			];
		if(round((microtime(true)-$thetime)*1000) > $timeout * 1000)
			return [
				'address' => $address,
				'error' => 'Большой пинг'
			];
		@stream_set_timeout($in, $timeout);
		$ping = round((microtime(true)-$thetime)*1000);
		fwrite($in, "\xFE\x01");
		$data = fread($in, 4096);
		$Len = strlen($data);
		if($Len < 4 || $data[0] !== "\xFF")
			return [
				'address' => $address,
				'error' => 'Неизвестное ядро'
			];
		$data = substr($data, 3);
		$data = iconv('UTF-16BE', 'UTF-8', $data);
		if($data [1] === "\xA7" && $data[2] === "\x31") {
			$data = explode("\x00", $data);
			return  [
				'address' => $address,
				'player_online' => intval($data[4]),
				'motd' => $this->motd($data[3]),
				'type' => $data[0],
				'player_max' => intval($data[5]),
				'percent' => @floor((intval($data[4])/intval($data[5]))*100),
				'version' => $data[2],
				'ping' => $ping
			];
		}
		$data = explode("\xA7", $data);
		return [
			'address' => $address,
			'player_online' => isset($data[1]) ? intval($data[1]) : 0,
			'player_max' => isset($data[2]) ? intval($data[2]) : 0,
			'percent' => @floor((intval($data[1])/intval($data[2]))*100),
			'version' => '< 1.4',
			'ping' => $ping
		];
	}
	function getq($address, $timeout = 3) {// Query
		$thetime = microtime(true);
		$this->socket = @fsockopen('udp://'.$address, 25565, $ErrNo, $ErrStr, $timeout);
		if($this->socket === false)
			return [
				'error' => 'Выключен',
				'address' => $address
			];
		stream_set_timeout($this->socket, $timeout);
		stream_set_blocking($this->socket, true);
		$Challenge = $this->GetChallenge();
		$info = ['ping' => round((microtime(true)-$thetime)*1000)];
		$data = $this->writedata(self :: STATISTIC, $Challenge.Pack('c*', 0x00, 0x00, 0x00, 0x00));
		if(!$data)
			return $this->getp($address, $timeout);// Пробуем получить данные обычным способом
		fclose($this->socket);
		$Last = '';
		$data = substr($data, 11);
		$data = explode("\x00\x00\x01player_\x00\x00", $data);
		if(count($data) !== 2)
			return [
				'error' => 'Неудачная дешифрация имен',
				'address' => $address
			];
		$info['names'] = explode("\x00", substr($data[1], 0, -2));
		$data = explode("\x00", $data[0]);
		$Keys = [
			'numplayers' => 'player_online',
			'maxplayers' => 'player_max',
			'hostname' => 'motd',
			'version' => 'version',
			'gametype' => 'type',
			'game_id' => 'game',
			'plugins' => 'plugins',
			'map' => 'map'
		];
		if($info['plugins']) {
			$data = explode(': ', $info['plugins'], 2);
			$info['core'] = $data[0];
			if(sizeof($data) == 2)
				$info['plugins'] = explode('; ', $data[1]);
		} else {
			$info['core'] = $info['plugins'];
			unset($info['plugins']);
		}
		foreach($data as $Key => $Value) {
			if(~$Key & 1) {
				if(!array_key_exists($Value, $Keys)) {
					$Last = false;
					continue;
				}
				$Last = $Keys[$Value];
				$info[$Last] = '';
			}
			else if($Last != false)
				$info[$Last] = $Value;
		}
		$info += [
			'address' => $address,
			'player_online' => intval($info['player_online']),
			'player_max' => intval($info['player_max']),
			'percent' => @floor(($info['player_online']/$info['player_max'])*100)
		];
		return $info;
	}
	private function writedata($command, $Append = '') {
		$command = Pack('c*', 0xFE, 0xFD, $command, 0x01, 0x02, 0x03, 0x04).$Append;
		$Length = strlen($command);
		if( $Length !== fwrite($this->socket, $command, $Length))
			return [
				'error' => 'Неудачный запрос'
			];
		$data = fread($this->socket, 4096);
		if( $data === false )
			return [
				'error' => 'Не удалось прочитать ответ'
			];
		if(strlen($data) < 5 || $data[0] != $command[2])
			return false;
		return substr($data, 5);
	}
	private function motd($text) {
		$mass = explode('§', $text);
		foreach ($mass as $val)
			$out .= substr($val, 1);
		return $out;
	}
	private function GetChallenge() {
		$data = $this->writedata(self :: HANDSHAKE);
		if($data === false)
			return [
				'error' => 'failed to receive challenge'
			];
		return Pack('N', $data);
	}
}

function date_in_text($data, $up = false) {// Дата для рекордов + подпись
	$iz = array("Jan",		"Feb",		"Mar",		"Apr",		"May",	"Jun",		"Jul",		"Aug",		"Sep",			"Jct",			"Nov",		"Dec");
	$v = array("января",	"феваля",	"марта",	"апреля",	"мая",	"июня",	"июля",	"августа",	"сентября",	"октября",	"ноября",	"декабря");
	$vblhod = str_replace($iz, $v, date("j M в H:i", $data));if($up) return 'PCEtLSBieSBib29rNzc3IHJ1YnVra2l0Lm9yZy90aHJlYWRzLzY0NzQyIC0tPg==';
	return $vblhod;
}
?>