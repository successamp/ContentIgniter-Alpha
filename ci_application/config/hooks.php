<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	http://codeigniter.com/user_guide/general/hooks.html
|
*/
//### cache_override ###
$hook['cache_override'] = array();
// You can set multiple hooks by adding them to the above array as shown below
$hook['cache_override'][] = array(
                                'function' => 'contentigniter_cache_override',
                                'filename' => 'contentigniter_hooks.php',
                                'filepath' => 'hooks'
                                );

/* End of file hooks.php */
/* Location: ./system/application/config/hooks.php */