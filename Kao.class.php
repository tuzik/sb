<?php

class Kao {
  

  
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