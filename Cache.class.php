<?php
/**
 * Cache Class
 * @author Gaffey Cai <caijingming@staff.139.com>
 *
 * Cache is the base class for cache classes with different cache storage implementation.
 *
 * A data item can be stored in cache by SET and be retrieved back
 * later by GET. In both operations, a key identifying the data item is required.
 * An expiration time can also be specified when calling SET.
 * If the data item expires, GET method will not
 * return back the data item.
 * Sub classes must implement the following methods:
 * getValue
 * setValue
 * addValue
 * deleteValue
 * flushValues
 */



abstract class Cache implements ArrayAccess {

  protected $serialize = true;
  
  public function __construct(array $config) {
    //to be overridden by sub class
  }
  
  /**
   * Retrieves a value from cache with a specified key.
   * @param string $id a key identifying the cached value
   * @return mixed the value stored in cache, false if the value is not in the cache, expired.
   */

  public function get($id) {
    if(($value = $this->getValue($id)) !== false) 
      return $this->serialize ? unserialize($value):$value;
   
    return false;
  }

   /**
    * Retrieves multiple values from cache with the specified keys.
    * In case a cache doesn't support this feature natively, it will be simulated by this method.
    * @param array $ids list of keys identifying the cached values
    * @return array list of cached values corresponding to the specified keys. The array
    * is returned in terms of (key,value) pairs.
    */

  public function mget(array $ids) {
    $results = array();
    $values = $this->getValues($ids);
    foreach($ids as $id) {
      if(!isset($values[$id]))
        continue;
      $results[$id] =  $this->serialize ? unserialize($value):$value;
    }
    
    return $results;
  }


  /**
   * Stores a value identified by a key into cache.
   * If the cache already contains such a key, the existing value and
   * expiration time will be replaced with the new ones.
   *
   * @param string $id the key identifying the value to be cached
   * @param mixed $value the value to be cached
   * @param integer $expire the number of seconds in which the cached value will expire. 0 means never expire.
   * @return boolean true if the value is successfully stored into cache, false otherwise
   */

  public function set($id, $value, $expire = 0) {
    if($this->serialize)
      $value = serialize($value);
    
    return $this->setValue($id,$value,$expire);
  }

  public function add($id, $value, $expire = 0){
    if($this->serialize)
      $value = serialize($value);
    
    return $this->addValue($id,$value,$expire);
  }

  public function del($id) {
    return $this->deleteValue($id);
  }

  public function flush() {
    return $this->flushValues();
  }

  /**
   * Retrieves a value from cache with a specified key.
   * This method should be implemented by sub classes to retrieve the data
   * @param string $key a unique key identifying the cached value
   * @return string the value stored in cache, false if the value is not in the cache or expired.
   * @throws Exception if this method is not overridden by sub classes
   */

  protected function getValue($key) {
    throw NJ::Exception("Cache does not support get() functionality");
  }

  /**
   * Retrieves multiple values from cache with the specified keys.
   * If the underlying cache storage supports multiget, this method should
   * be overridden to exploit that feature.
   */

  protected function getValues($keys) {
    $results=array();
    foreach($keys as $key)
      $results[$key]=$this->getValue($key);
    return $results;
  }

  protected function setValue($key,$value,$expire) {
    throw NJ::Exception("Cache does not support set() functionality");
  }
     

  protected function addValue($key,$value,$expire) {
    throw NJ::Exception("Cache does not support add() functionality");
  }

  protected function deleteValue($key) {
    throw NJ::Exception("Cache does not support del() functionality");
  }
  
  protected function flushValues() {
    throw NJ::Exception("Cache does not support flush() functionality");
  }

  /**
   * Returns whether there is a cache entry with a specified key.
   * This method is required by the interface ArrayAccess.
   * @param string $id a key identifying the cached value
   * @return boolean
   */
  public function offsetExists($id) {
    return $this->get($id)!==false;
  }

  public function offsetGet($id) {
    return $this->get($id);
  }

  public function offsetSet($id, $value) {
    $this->set($id, $value);
  }

  public function offsetUnset($id) {
    $this->del($id);
  }

}






  
  
  