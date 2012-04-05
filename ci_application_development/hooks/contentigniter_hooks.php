<?php
/**
 * ContentIgniter
 *
 * An open source CMS for CodeIgniter 2.0.3 or newer
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the Open Software License version 3.0
 *
 * This source file is subject to the Open Software License (OSL 3.0) that is
 * bundled with this package in the files license.txt / license.rst.  It is
 * also available through the world wide web at this URL:
 * http://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to obtain it
 * through the world wide web, please send an email to
 * licensing@ellislab.com so we can send you a copy immediately.
 *
 * @package		ContentIgniter
 * @author		Paul Dillinger
 * @copyright	Copyright (c) 2008 - 2012, Paul R. Dillinger. (http://prd.me/)
 * @license		http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @link		http://contentigniter.com
 * @since		Version 1.0
 * @filesource
 */
if (!defined('BASEPATH'))
	exit ('No direct script access allowed');


function contentigniter_cache_override($params) {
    ob_start();
    require_once(APPPATH .'libraries/FirePHP.class.php');
    $firephp = FirePHP::getInstance(true);
    $firephp->registerErrorHandler(FALSE);
   	$firephp->registerExceptionHandler();
   	$firephp->registerAssertionHandler();

    if (ENVIRONMENT === 'production') {
   		// Hide error reporting and turn off FirePHP output
   		$firephp->setEnabled(FALSE);
        if(!ENVIRONMENT_DEBUG){
       	    error_reporting(0); // Normal production, no errors, no FirePHP
        }
        else{
       		error_reporting(-1); // Admin debugging (no FirePHP) on 'production'
        }
   	}
   	else {
   		// Enable error reporting and FirePHP output
        error_reporting(-1);
        if(!ENVIRONMENT_DEBUG){
            $firephp->setEnabled(TRUE);
        }else{
            $firephp->setEnabled(FALSE);
        }
   		$firephp->log('Welcome to ContentIgniter: '.ENVIRONMENT);
   	}

    $OUT =& load_class('Output', 'core');
    $CFG =& load_class('Config', 'core');
    $URI =& load_class('URI', 'core');

    $user_tracking = empty($_COOKIE['CI_user_tracking']) ? '' : $_COOKIE['CI_user_tracking'];

    if(empty($user_tracking)){
        // Save entry infomration for tracking user later
        $userdata['refer'] = empty($_SERVER['HTTP_REFERER']) ? '(none)' : $_SERVER['HTTP_REFERER'];
        $userdata['entry'] = $_SERVER['REQUEST_URI'];
        $userdata['domain'] = $_SERVER['SERVER_NAME'];
        setcookie(
            'CI_user_tracking',
            json_encode($userdata),
        	time()+$CFG->item('sess_expiration'),
            $CFG->item('cookie_path'),
            $CFG->item('cookie_domain')
        );
    }

    if (ENVIRONMENT === 'production') {
        if ($OUT->_display_cache($CFG, $URI) == TRUE)
   		{
   			exit;
   		}
    }
}
