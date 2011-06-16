#!/usr/bin/php -q
<?php

include dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'classes/B2B_Api.php';
$config = include dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'foundation.php';
$client = new B2B_Api($config['user'],$config['password'],TRUE);

if( $client->get_error_msg())
{
    exit($client->get_error_msg()."\n");
}




$positions = array(
                array(
                        'cat_id' => 'mobile',
                        'dev_id' => '5800xm',
                        'price' => 360,
                        'beznal' => 1,
                        'on_stock' => 1,
                        'comment' => 'Клевый телефон',
                        'delete' => 0
                        ),
                 array(
                        'cat_id' => 'mobile',
                        'dev_id' => '5800xm',
                        'price' => 300,
                        'beznal' => 2,
                        'on_stock' => 2,
                        'comment' => 'Клевый телефон 2',
                        'delete' => 0
                        ),
                    );


foreach($positions as $params)
{
    $resp = $client->insert_position($params['cat_id'],$params['dev_id'],$params) ;

    echo "pos_id: ".$resp."\n";
    echo $params['cat_id']."\t".$params['dev_id']."\t".$client->get_error_msg()."\n";

}

$response = $client->get_pricelist_report($client->get_access_key());
var_dump($response);

$client->commit();
