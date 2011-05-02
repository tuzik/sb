<?php

class Memcache extends Cache {
  
  private $_cache = null;
  
  private $_servers = array();

  protected $_options = array('servers' => array(array('host'     => '127.0.0.1',
                                                      'port'     => 11211,
                                                      'weight'   => 1,
                                                      ),
                                                ),
                              'compress' => true,
                              'serilize' => true,
                              );


  function __construct(array $config) {
    
    if(isset($config['servers'])) {
      $servers = $config['servers'];
      if(isset($servers['host']))
        $servers = array(0 => $servers);
      $this->setOption('servers',$servers);
    }
    

    $cache = $this->getCache();

    foreach($this->_options['servers'] as $v)
      $cache->addServer($v['host'], $v['port'], $v['weight']);
    $cache->setOption(Memcached::OPT_HASH, Memcached::HASH_CRC);
    $cache->setOption(Memcached::OPT_DISTRIBUTION,
                      Memcached::DISTRIBUTION_CONSISTENT);
    
    if(isset($config['compress'])) {
      $this->setOption('compress',$config['compress']);
      $cache->setOption(Memcached::OPT_COMPRESSION, (bool)$config['compress']);
    }

    if(isset($config['serilize'])) {
      $this->setOption('serilize',$config['serilize']);
      $this->serialize = $config['serilize'];
    }
  }

  static function instance(array $config) {
    static $instances = array();
    
    $index = serialize($config);
    if(isset($instances[$index]))
      return $instances[$index];
    
    $class  = __CLASS__;
    $obj    = new $class($config);
    $instances[$index] = $obj;
    
    return $obj;
  }

  function setOption($name,$value) {
    if(!is_string($name)) throw new Exception("invalid argument for memcache options : $name");
    
    $name = strtolower($name);
    if(array_key_exists($name,$this->_options))
      $this->_options[$name] = $value;
  }

  function getCache() {
    if($this->_cache !== null)
      return $this->_cache;
    else
      return $this->_cache = new Memcached();
  }

  protected function getValue($key) {
    return $this->_cache->get($key);
  }

  protected function getValues(array $keys) {
    return $this->_cache->getMulti($keys);
  }

  protected function setValue($key, $value, $expire) {
    if($expire>0)
      $expire+=time();
    else
      $expire=0;
    
    return $this->_cache->set($key,$value,$expire);
  }

  protected function addValue($key, $value, $expire) {
     if($expire>0)
      $expire+=time();
    else
      $expire=0;
    
    return $this->_cache->add($key,$value,$expire);
  }

  protected function deleteValue($key) {
    return $this->_cache->delete($key);
  }

  protected function flushValues() {
    return $this->_cache->flush();
  }
  
  /**
   * Set serilize false.when calling these 4 functions,
   * since item's value which was serilized in memcache storage 
   * is different with the origin passed to set or add function .
   * Retrieves the value from cache will be wroing in serilized context.
   */
  
  function inc($key, $offset = 1) {
    return $this->_cache->increment($key, $offset);
  }
  
  function dec($key, $offset = 1) {
    return $this->_cache->decrment($key, $offset);
  }

 function append($key, $value) {
   if($this->_options['compress']) return false;
   return $this->_cache->append($key, $value);
 }

 function prepend($key, $value) { 
   if($this->_options['compress']) return false;
   return $this->_cache->prepend($key, $value);
 }

}

  
  
  