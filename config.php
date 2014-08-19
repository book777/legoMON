<?php

#			$file['template'] = '../templates/название_вашего_шаблона/book777.tpl';// DLE
#			$file['template'] = '../style/default/book777.html';// webMCR
//\\	Уберите # у нужной вам CMS 					\\// Нижнюю строчку можете удалить
if(!$file['template']) die('Вы не выбрали путь к шаблону в config.php');


$server[]= array('game.icrafts.su:18000', 'Сервер 1', 'переменная2', 'переменная3');
$server[]= array('94.23.234.87:25566',	 'Сервер 2', 'переменная2', 'переменная3');
$server[]= array('off.ensemplix.ru:25565',	'Сервер 3',	'переменная1',	'переменная2');

$time['cache'] = 5;// Кеширование (сек)
$time['out'] = 4;// Максимальное время отклика сервера
$time['record_day'] = 86400;// Частота обновления временного рекорда (86400 сек = 24 ч)


date_default_timezone_set("Europe/Moscow");// http://php.net/manual/ru/timezones.php

?>