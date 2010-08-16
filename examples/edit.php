#!/usr/bin/php -q
<?php


include dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'classes/B2B_Api.php';
$config = include dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'foundation.php';
$client = new B2B_Api($config['user'],$config['password']);

$mobile = $client->get_info('mobile');

var_dump($mobile);