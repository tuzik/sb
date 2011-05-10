<?php
require('../Cache.class.php');
require('../Shcache.class.php');

$sh = new NJ_Shcache();
//$sh['a'] = 1;
//$sh[9] = 2;
//$sh['c'] = 1;
$n  = 10000000;
$sh->flush();
//}
