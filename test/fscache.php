<?php

require("../NJ.class.php");
require("../Cache.class.php");
require("../Fscache.class.php");


$config = array('fs_path'  => '/var/tmp',
                'fs_level' => 1,
                'gc_chance' => 10000,
                'serilize' => false);

$f = new Fscache($config);

$f->add('test1','asdasdsa');

$f->append('test1','sssssssssss');
