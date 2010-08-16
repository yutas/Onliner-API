#!/usr/bin/php -q
<?php

include dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'classes/B2B_Api.php';
$config = include dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'foundation.php';
$client = new B2B_Api($config['user'],$config['password']);

$x = $client->search_device('58','mobile','nokia');

$y = $client->search_category('ел');

var_dump($x);
echo "\n ********** \n";
var_dump($y);