<?php
require('Kao.class.php');
require('DBConnection.class.php');

$config = array('host' => '120.132.144.174',
		  'db'   => 'pangu',
		  'port' => '3306',
		  'username' => 'aspire',
		  'password' => 'aspire.de');

$db = new DBConnection($config,$debug = true, $profile = true);

var_dump($db->query('select * from user_confirm'));

var_dump($db->_pdo);

$db2 = new DBConnection($config);
var_dump($db->_pdo);


