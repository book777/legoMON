<?php

$server[]= array('site.ru', 'MagicTech', 'http://site.ru/MagicTech.html');
$server[]= array('site.ru:25567', 'Technology (PVE-PVP)', 'http://site.ru/Technology.html');
$server[]= array('localhost', 'SERVER (PVE-PVP)');
$server[]= array('127.0.0.1', 'VEGAS LITE');


// Необходимо выбрать 1 из параметров, убрав у нужной строчки #
#$file['template'] = '../templates/Default/book777.tpl';// для DLE
#$file['template'] = '../style/default/book777.html';// для webMCR



$time['cache'] = 55;// Кеширование (сек)
$time['out'] = 2;// Максимальное время отклика сервера
$time['record_day'] = 86400;// Частота обновления временного рекорда (86400 сек = 24 ч)


date_default_timezone_set("Europe/Moscow");// http://php.net/manual/ru/timezones.php

?>
 