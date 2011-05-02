<?php
require("../NJ.class.php");
require("../Cache.class.php");
require("../Memcache.class.php");


$config = array('servers' => array(0 => array('host' => '120.132.144.173',
                                              'port' => '11211',
                                              'weight'=> 1
                                              ),
                                   ),
                'compress' => false,
                'serilize' => false);
NJ::profile('begin','memcache','');

$mc = Memcache::Instance($config);
$mc->flush();
var_dump($mc->set('test1','test11',300));
var_dump($mc->set('test2','test22',300));
$mc->set('test3','test33',0);
$mc->set('test4','test44',0);
$mc->set('test5',1,0);
var_dump($mc->set('config',$config,300));

var_dump($mc->get('config'));

echo $mc['test1'];
echo $mc['test2'];
unset($mc['test2']);

var_dump($mc['test2']);

$mc->inc('test5',3);

echo $mc['test5'];

var_dump($mc->append('test4','sssssssssssssssss'));
echo $mc['test4'];

NJ::profile('end','memcache','');

