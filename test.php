<?php
require('Kao.class.php');
require('DBConnection.class.php');

$config = array('host' => '120.132.144.174',
		  'db'   => 'pangu',
		  'port' => '3306',
		  'username' => 'aspire',
		  'password' => 'aspire.de');

$db = new DBConnection($config,$debug = true, $profile = true);
$db->setAlive(true);

var_dump($db->exec(" insert into user_confirm (`user_id` , `type`,`code`,`status`,`time_create`)values (12112,'mobile','111','invalid','221111')"));
var_dump($db->exec(" insert into user_confirm (`user_id` , `type`,`code`,`status`,`time_create`)values (1112112,    'mobile','111','invalid','221111')"));
var_dump($db->exec(" insert into user_confirm (`user_id` , `type`,`code`,`status`,`time_create`)values (12112,    'mobile','111','invalid','22111199')"));


var_dump($db->query("select * from user_confirm"));
