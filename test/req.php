<?php
require("../NJ.class.php");
require("../Cache.class.php");
require("../Request.class.php");

$stack = array(array('url' => 'http://www.baidu.com','timeout'=>10),
		 array('url' => 'http://www.google.com','timeout'=>10),
			   );

var_dump(Request::asend($stack));