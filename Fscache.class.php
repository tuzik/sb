<?php

class Fscache extends Cache {

  public $_fspath;
  
  public $_suffix='.cache';
  
  public $_fslevel=0;
  
  private $_gc_probability=100;
  private $_gced=false;


  public function __construct(array $config) {
    if(isset($config['fs_path'])) {
      $this->_fspath = $config['fs_path'];
      if(!is_dir($this->_fspath))
        mkdir($this->_fspath,0777,true);
    }
    
    if(isset($config['fs_level']))
      $this->_fslevel = $config['fs_level'];
    
    if(isset($config['gc_chance']))
      $this->setGCProbability($config['gc_chance']);
    
    if(isset($config['serilize']))
      $this->serialize = $config['serilize'];
    
  }
  
  
  public function getGCProbability() {
      return $this->_gc_probability;
  }
    
  public function setGCProbability($value) {
    $value=(int)$value;
    if($value<0)
      $value=0;
    if($value>1000000)
      $value=1000000;
    $this->_gc_probability=$value;
  }


  protected function flushValues() {
    $this->gc(false);
    return true;
  }


  protected function getValue($key) {
    $cacheFile=$this->getCacheFile($key);
    if(($time=@filemtime($cacheFile))>time())
      return file_get_contents($cacheFile);
    else if($time>0)
      @unlink($cacheFile);
    return false;
  }


  protected function setValue($key,$value,$expire) {
    if(!$this->_gced && mt_rand(0,1000000)<$this->_gc_probability) {
      $this->gc();
      $this->_gced=true;
    }

    if($expire<=0)
      $expire=31536000; // 1 year
    $expire+=time();

    $cacheFile=$this->getCacheFile($key);
    if($this->_fslevel>0)
      @mkdir(dirname($cacheFile),0777,true);
    if(@file_put_contents($cacheFile,$value,LOCK_EX)!==false) {
      @chmod($cacheFile,0777);
      return @touch($cacheFile,$expire);
    }
    else
      return false;
  }

  function append($key,$value) {
    if(!$this->_gced && mt_rand(0,1000000)<$this->_gc_probability) {
      $this->gc();
      $this->_gced=true;
    }


    $cacheFile=$this->getCacheFile($key);

    if(@file_put_contents($cacheFile,$value,FILE_APPEND)!==false) {
      @chmod($cacheFile,0777);
      return @touch($cacheFile,$expire);
    }
    else
      return false;
  }

  protected function addValue($key,$value,$expire) {
    $cacheFile=$this->getCacheFile($key);
    if(@filemtime($cacheFile)>time())
      return false;
    return $this->setValue($key,$value,$expire);
  }


  protected function deleteValue($key) {
    $cacheFile=$this->getCacheFile($key);
    return @unlink($cacheFile);
  }


  protected function getCacheFile($key) {
    if($this->_fslevel>0) {
      $base=$this->_fspath;
      for($i=0;$i<$this->_fslevel;++$i) {
        if(($prefix=substr($key,$i+$i,2))!==false)
          $base.=DIRECTORY_SEPARATOR.$prefix;
      }
      return $base.DIRECTORY_SEPARATOR.$key.$this->_suffix;
    }
    else
      return $this->_fspath.DIRECTORY_SEPARATOR.$key.$this->_suffix;
  }


  protected function gc($expiredOnly=true,$path=null) {
    if($path===null)
      $path=$this->_fspath;
    if(($handle=opendir($path))===false)
      return;
    while(($file=readdir($handle))!==false) {
      if($file[0]==='.')
        continue;
      $fullPath=$path.DIRECTORY_SEPARATOR.$file;
      if(is_dir($fullPath))
        $this->gc($expiredOnly,$fullPath);
      else if($expiredOnly && @filemtime($fullPath)<time() || !$expiredOnly)
        @unlink($fullPath);
    }
    closedir($handle);
  }
  }
