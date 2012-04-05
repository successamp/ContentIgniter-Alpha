<?php
// Modified from: http://jamieonsoftware.com/journal/2012/1/7/syntax-sugar-5-a-quick-codeigniter-caching-helper-function.html
function cache($key, $data) {
	$CI =& get_instance();
	if($CI->cache->configured){
		$return_cache = $CI->cache->get($key);
	}
    if (empty($cache)) {
        // There's been a miss, so run our data function and store it
        $return_cache = $data($CI);
		if($CI->cache->configured){
	        $CI->cache->set($key, $return_cache);
		}
    }
    return $return_cache;
}
