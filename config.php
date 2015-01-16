<?php

$server[]= array('176.9.120.79:25566', 'GalaxyTech', 'http://lemoncraft.ru/MagicTech.html');
$server[]= array('176.9.120.79:25567', 'Technology (PVE-PVP)', 'http://lemoncraft.ru/MagicTech.html');

$file['template'] = '../templates/Default/book777.tpl';// для DLE
#$file['template'] = '../style/default/book777.html';// для webMCR



$time['cache'] = 55;// Кеширование (сек)
$time['out'] = 2;// Максимальное время отклика сервера
$time['record_day'] = 86400;// Частота обновления временного рекорда (86400 сек = 24 ч)


date_default_timezone_set("Europe/Moscow");// http://php.net/manual/ru/timezones.php

?>
 