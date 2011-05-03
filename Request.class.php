<?php

class NJ_Request {
  
  static function send($url, $params='', $timeout=null) {

	if (!function_exists('curl_init'))
	  throw new Exception('Request requires the CURL PHP extension to be loaded');

	$ch = curl_init();
    
    $opts = array(CURLOPT_CONNECTTIMEOUT => empty($timeout)?10:$timeout,
			      CURLOPT_TIMEOUT        => empty($timeout)?5:$timeout,
			      CURLOPT_RETURNTRANSFER => true,
			      CURLOPT_USERAGENT       => "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)",
			      CURLOPT_POST           => 1,
				  );
	
    if (is_array($params) || is_object($params)) {
      $opts[CURLOPT_POSTFIELDS] = http_build_query($params, null, '&'); 
    } else {
      $opts[CURLOPT_POSTFIELDS] = $params;
    }
	
    $opts[CURLOPT_URL] = $url;

    // disable the 'Expect: 100-continue' behaviour. This causes CURL to wait
    // for 2 seconds if the server does not support this header.
    if (isset($opts[CURLOPT_HTTPHEADER])) {
      $existing_headers = $opts[CURLOPT_HTTPHEADER];
      $existing_headers[] = 'Expect:';
      $opts[CURLOPT_HTTPHEADER] = $existing_headers;
    } else {
      $opts[CURLOPT_HTTPHEADER] = array('Expect:');
    }

    curl_setopt_array($ch, $opts);
    $result = curl_exec($ch);
	
    if ($result === false) {
	  $err = curl_errno($ch);
	  $info = curl_getinfo($ch,CURLINFO_HTTP_CODE);
	  curl_close($ch);
	  throw new Exception("curl error message $err,response code $info");
    }
    curl_close($ch);
    return $result;
  }

  /**
   * Non-blocking send method
   * 
   * @param Array $stack handle requests
   * @example $stack = array(array('url' => 'www.example0.com','params' => '','timeout' => 3),
   *                         array('url' => 'www.example1.com','params' => '','timeout' => 3),
   *                        );
   */
  
  static function asend(array $stack) {
	if (!function_exists('curl_init'))
	  throw new Exception('Request requires the CURL PHP extension to be loaded');
	
	if(!count($stack)) return false;

	$call = $ret = array();

	$mh = curl_multi_init();
	
	foreach($stack as $k => $v) {
	  $timeout = empty($v['timeout'])?3:$v['timeout'];

	  $ch = curl_init();	  
	  curl_setopt($ch, CURLOPT_URL, $v['url']);  
	  curl_setopt($ch, CURLOPT_HEADER, 0);  
	  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  
	  curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);  
	  curl_setopt($ch, CURLOPT_POST, 1);  

	  if(is_array($v['params']) || is_object($v['params']))
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($v['params'], null, '&'));  
	  else
		curl_setopt($ch, CURLOPT_POSTFIELDS, $v['params']);
  
	  curl_multi_add_handle($mh, $ch);  
	  
	  $call[$k] = $ch;
	}
	  
	$flag = null;  	
	do {  
	  curl_multi_exec($mh, $flag);  
	} while ($flag > 0);
	
	foreach ($stack as $k => $v) {  
	  if ($call[$k]) {  
		$ret[$k] = curl_multi_getcontent($call[$k]);  
		curl_multi_remove_handle($mh, $call[$k]);  
	  } else {  
		$ret[$k] = false;     
	  }  
	}
	
	curl_multi_close($mh);
  
	return $ret;  
  }

}