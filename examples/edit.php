#!/usr/bin/php -q
<?php


include dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'classes/B2B_Api.php';
$config = include dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'foundation.php';
$client = new B2B_Api($config['user'],$config['password']);

$memcards = $client->export_price();

$params_to_edit = array(
						'price' => 222,
						'comment' => 'tro-lo-lo',
						);

foreach ($memcards as $pos_id => $values) {
	
	$client->edit_position(
						$values['cat_id'],
						$values['dev_id'],
						$pos_id,
						$params_to_edit
					);

}

$client->commit();