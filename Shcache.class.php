<?php

class NJ_Shcache extends NJ_Cache implements Countable {

  public $shc_index = '/tmp/shc/index';
  
  public $shc_size  = 2097152;

  protected $shc_index_id = false;

  protected $shmmni = 4090;

  public function __construct(array $config = array()) {
	if( !function_exists('ftok') )
	  throw new Exception("Schcache requires PHP module semaphore to be loaded");

	if(isset($config['key']))
	  $this->shc_index = '/tmp/shc/index/'.$config['key'];
	
	if(isset($config['buckets']))
	  $this->shmmin = $config['buckets'];

	$this->serialize = false;

	$shc_key = self::ftok($this->shc_index,'s');
	
	$this->shc_index_id = shm_attach($shc_key,$this->shc_size);
		
	if ($this->shc_index_id === false)
	  die ("Unable to create shcache block in index");
	
	if(!self::has_var($this->shc_index_id,0))
	  shm_put_var($this->shc_index_id,0,self::size()+self::size(array()));
	
	if(!self::has_var($this->shc_index_id,1))
	  shm_put_var($this->shc_index_id,1,array());
	
  }

  static function size($obj = null) {
	$block_init_size = PHP_INT_SIZE * 4 + 8;
	$int_size = (((strlen(serialize(PHP_INT_MAX))+ (4 * PHP_INT_SIZE)) /4 ) * 4 ) + 4; 
	
	if($obj === null)
	  return $block_init_size + $int_size;
	else
	  return (((strlen(serialize($obj))+ (4 * PHP_INT_SIZE)) /4 ) * 4 ) + 4; 
  }

  static function ftok($path,$char) {
	if(!is_dir(dirname($path))) {
	  @mkdir(dirname($path),0777,true);
	  touch($path);
	}
	else {
	  $st = @stat($path);
	  if(!$st)
		touch($path);
	}
	return ftok($path,$char);
  }

  static function has_var($id,$key) {
	if(!function_exists('shm_has_var'))
	  return shm_get_var($id,$key);
	else
	  return shm_has_var($id,$key);
  }
  
  static function gc($key) {
	$path = "/tmp/shc/store/".$key;
	$id = shm_attach(self::ftok($path,'s'));
	shm_remove($id);
	@unlink($path);
	return shm_detach($id);
  }

  public function dump() {
	$index_array = shm_get_var($this->shc_index_id,1);
	$size = shm_get_var($this->shc_index_id,0);
	echo "total index size is :$size\n=======================\n";
	var_dump($index_array);
	shm_detach($this->shc_index_id);
  }
	
  protected function getValue($key) {
	$index_array = shm_get_var($this->shc_index_id,1);
	
	if(false === array_search($key,$index_array))
	  return false;
	else {
	  	$path = "/tmp/shc/store/".$key;
		$id = shm_attach(self::ftok($path,'s'));
		$value = shm_get_var($id,0);
   
		shm_detach($id);
		return $value;
	}
  }

  protected function setValue($key,$value,$expire = 0) {
	
	if(self::size($value) >= $this->shc_size) 
	  throw new Exception("Shcache requires value's size should be smaller than 2MB");

	$index_array = shm_get_var($this->shc_index_id,1);

	if(false === array_search($key,$index_array)) {
	  array_push($index_array,$key);

	  $curr = self::size($index_array);	  
	  
	  while($curr >= $this->shc_size || count($index_array) >= $this->shmmni) {
		$item = array_shift($index_array);
		self::gc($item);
		$curr = self::size($index_array);
	  }
	  
	  shm_put_var($this->shc_index_id,0,$curr);
	  shm_put_var($this->shc_index_id,1,$index_array);
	}

	$path = "/tmp/shc/store/".$key;
	$id = shm_attach(self::ftok($path,'s'),self::size()+self::size($value));
	$res = shm_put_var($id,0,$value);
	shm_detach($id);
	
	return $res;
  }


  protected function addValue($key,$value,$expire = 0) {
	return $this->setValue($key,$value,$expire);
  }
	  
  protected function deleteValue($key) {
	$index_array = shm_get_var($this->shc_index_id,1);

	if(($index = array_search($key,$index_array)) === false)
	  return $index;
	else {
	  unset($index_array[$index]);
	  $size = self::size($index_array);
	  shm_put_var($this->shc_index_id,0,$size);
	  shm_put_var($this->shc_index_id,1,$index_array);
	
	  $path = "/tmp/shc/store/".$key;
	  $id = shm_attach(self::ftok($path,'s'));
	  shm_remove($id);
	  @unlink($path);
	  return shm_detach($id);
	}
  }

  protected function flushValues() {
	$index_array = shm_get_var($this->shc_index_id,1);
	foreach($index_array as $key) {
	  	$path = "/tmp/shc/store/".$key;
		$id = shm_attach(self::ftok($path,'s'));
		shm_remove($id);
		shm_detach($id);
		@unlink($path);
	}
	shm_remove($this->shc_index_id);
	shm_detach($this->shc_index_id);
  }

  public function close() {
	shm_detach($this->shc_index_id);
  }

  public function count() {
	$index_array = shm_get_var($this->shc_index_id,1);
	return count($index_array);
  }

}	
	

	
		
	  