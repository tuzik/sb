<?php

class NJ {
  
  public static function import($class_name) {
	$prefix = defined('NJR') ? NJR:'/usr/share/NJR/';
	$class = str_replace('_','/',$class_name);
	$file_name = $class.".class.php";
	$file_path = $prefix.$file_name;
	
	if(file_exists($file_path))
	  return require($file_path);
  }

  public static function registerAutoloader($callback,$enabled = true) {
	if($enabled) {
	  spl_autoload_unregister(array('NJ','import'));
	  spl_autoload_register($callback);
	  spl_autoload_register(array('NJ','import'));
	}
	else
	  spl_autoload_unregister($callback);
  }
  
  public static function Exception($e) {
	error_log(date('Y-m-d h:i:s')." EXCEPTION\t".$e."\n",3,'kao.log');
  }

  public static function debug($info,array $more) {
	$str = date('Y-m-d h:i:s')." DEBUG\t".$info."\n";
	foreach($more as $k => $v) {
	  $str .= "\t\t".$k."\t".$v."\n";
	}
	error_log($str,3,'kao.log');
  }

  public static function profile($statue,$class,$more) {
	error_log(date('Y-m-d h:i:s')." ".microtime(true)." PROFILE\t(".$statue.") AT ".$class.".class.php INFO ".$more."\n",3,'kao.log');
  }

}

NJ::registerAutoloader(array('NJ','import'));