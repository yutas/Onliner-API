#!/usr/bin/php -q
<?php

include dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'classes/B2B_Api.php';
$config = include dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'foundation.php';
$client = new B2B_Api($config['user'],$config['password']);

$active_categories_list = $client->get_active_categories();
	fputs(fopen('php://stdout','w'),"********************* все категории *******\n");
foreach($active_categories_list as $id => $name)
{
	
//	fputs(fopen('php://stdout','w'),$id." -> ".$name."\n");
}

$enabled_categories_list = $client->get_enabled_categories();
	fputs(fopen('php://stdout','w'),"********************* подключенные разделы ********\n");
foreach($enabled_categories_list as $id => $name)
{
//	fputs(fopen('php://stdout','w'),$id." -> ".$name."\n");
}


$get_disabled_categories_list = $client->get_disabled_categories();
	fputs(fopen('php://stdout','w'),"********************* НЕ подключенные разделы ********\n");
foreach($get_disabled_categories_list as $id => $name)
{
//	fputs(fopen('php://stdout','w'),$id." -> ".$name."\n");
}

	fputs(fopen('php://stdout','w'),"*********** Информация о разделе каталога mobile ********\n");
$mobile_info = $client->get_info('mobile');
//var_dump($mobile_info);


	fputs(fopen('php://stdout','w'),"*********** Список всех производителей для раздела каталога mobile ********\n");
$mobile_info = $client->get_vendors('mobile');
//var_dump($mobile_info);



	fputs(fopen('php://stdout','w'),"*********** Информация о разделе каталога mobile и моделе 5230  ********\n");
$mobile_info = $client->get_info('mobile','5230');
//var_dump($mobile_info);

