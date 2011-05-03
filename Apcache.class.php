<?php

class NJ_Apcache extends NJ_Cache {
  
  public function __construct(array $config = array()) {
	if(!extension_loaded('apc'))
	  throw new Exception("Apcache requires PHP apc extension to be loaded");
	$this->serialize = false;
  }

  protected function getValue($key) {
	return apc_fetch($key);
  }

  protected function getValues($keys) {
	return apc_fetch($keys);
  }

  protected function setValue($key,$value,$expire) {
	return apc_store($key,$value,$expire);
  }

  protected function addValue($key,$value,$expire) {
	return apc_add($key,$value,$expire);
  }

  protected function deleteValue($key) {
	return apc_delete($key);
  }

  protected function flushValues() {
	return apc_clear_cache('user');
  }

}
  