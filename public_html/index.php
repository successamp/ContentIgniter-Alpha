<?php
/**
 * ContentIgniter
 *
 * An open source CMS for CodeIgniter
 * Modified from CodeIgniter 2.0.3 index.php
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
 * paul.dillinger@gmail.com so we can send you a copy immediately.
 *
 * @package		ContentIgniter
 * @author		Paul Dillinger
 * @copyright	Copyright (c) 2008 - 2012, Paul R. Dillinger. (http://prd.me/)
 * @license		http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @link		http://contentigniter.com
 * @since		Version 1.0
 * @filesource
 */

/*
 *---------------------------------------------------------------
 * APPLICATION ENVIRONMENT
 *---------------------------------------------------------------
 *
 * You can load different configurations depending on your
 * current environment. Setting the environment also influences
 * things like logging and error reporting.
 *
 * This can be set to anything, but default usage is:
 *
 *     development
 *     testing
 *     production
 *
 * NOTE: If you change these, also change the error_reporting() code below
 *
 */

/*
 *---------------------------------------------------------------
 * CONTENTIGNITER : ENVIRONMENT
 *---------------------------------------------------------------
 *
 * ContentIgniter uses this system to switch between environments for those who
 * don't have dedicated environment servers.  By setting a secret in a cookie
 * we can quickly and easily determine the environment.
 *
 * Additionally we define a new constant. ENVIRONMENT_DEBUG is used in the case
 * that the 'production' environment needs to have error messages turned on for
 * a user.
 *
 * NOTE: Security can be a concern with this setup as these secrets don't
 * require a user to be logged in.  This is intentional as it allows testing
 * with any type of user account (even users not logged in).  Cookies last until
 * midnight server time.
 *
 */

// Un-comment and update to fix cross sub-domain cookie loss
//ini_set('session.cookie_domain', '.domain.com');


	// Cookie set to enable the 'development' environment
    // MAKE THESE UNIQUE
    define('DEVEL_SECRET', 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');

    // Cookie set to enable the 'test' environment
    // MAKE THESE UNIQUE
	define('TEST_SECRET', 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');

    // Cookie set to enable error reporting in the 'production' environment
    // This also disables FirePHP
    // MAKE THESE UNIQUE
	define('DEBUG_SECRET', 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');

    $environment_cookie = $_COOKIE['ENVIRONMENT'];

	if(empty($environment_cookie)){
    	define('ENVIRONMENT', 'production');
       	define('ENVIRONMENT_DEBUG', FALSE);
	}else{
        $salt  = date('Fd'); // Cookie is good until midnight
        $environment_key_cookie = $_COOKIE['ENVIRONMENT_KEY'];
	    if($environment_cookie == 'development'){
	        if($environment_key_cookie == hash('md4', $salt . DEVEL_SECRET)){
            	define('ENVIRONMENT', 'development');
            	define('ENVIRONMENT_DEBUG', FALSE);
	        }
	        elseif($environment_key_cookie == hash('md4', $salt . DEBUG_SECRET)){
            	define('ENVIRONMENT', 'development');
            	define('ENVIRONMENT_DEBUG', TRUE);
	        }
    	    else{
	            setcookie ('ENVIRONMENT', '', time() - 3600); // Reset the cookie
            	define('ENVIRONMENT', 'production');
        	    define('ENVIRONMENT_DEBUG', FALSE);
            }
	    }
	    elseif($environment_cookie == 'testing'){
	        if($environment_key_cookie == hash('md4', $salt . TEST_SECRET)){
            	define('ENVIRONMENT', 'testing');
            	define('ENVIRONMENT_DEBUG', FALSE);
	        }
	        elseif($environment_key_cookie == hash('md4', $salt . DEBUG_SECRET)){
            	define('ENVIRONMENT', 'testing');
            	define('ENVIRONMENT_DEBUG', TRUE);
	        }
    	    else{
	            setcookie ('ENVIRONMENT', '', time() - 3600); // Reset the cookie
            	define('ENVIRONMENT', 'production');
        	    define('ENVIRONMENT_DEBUG', FALSE);
            }
	    }
	    elseif($environment_cookie == 'production'){
	        if($environment_key_cookie == hash('md4', $salt . DEBUG_SECRET)){
            	define('ENVIRONMENT', 'production');
            	define('ENVIRONMENT_DEBUG', TRUE);
	        }
    	    else{
	            setcookie ('ENVIRONMENT', '', time() - 3600); // Reset the cookie
            	define('ENVIRONMENT', 'production');
        	    define('ENVIRONMENT_DEBUG', FALSE);
            }
	    }
	    else{
	        setcookie ('ENVIRONMENT', '', time() - 3600); // Reset the cookie
        	define('ENVIRONMENT', 'production');
        	define('ENVIRONMENT_DEBUG', FALSE);
	    }
	}

/*
 *---------------------------------------------------------------
 * ERROR REPORTING
 *---------------------------------------------------------------
 *
 * Different environments will require different levels of error reporting.
 * By default development will show errors but testing and live will hide them.
 */

if (defined('ENVIRONMENT'))
{
/*
 *---------------------------------------------------------------
 * PATHS
 *---------------------------------------------------------------
 *
 */
    define('DEVELOPMENT_SYSTEM_PATH', '../CodeIgniter_2.1.0/system');

    define('TESTING_SYSTEM_PATH', '../CodeIgniter_2.1.0/system');

    define('PRODUCTION_SYSTEM_PATH', '../CodeIgniter_2.1.0/system');

    define('DEVELOPMENT_APPLICATION_FOLDER', '../ci_application_development');

    define('TESTING_APPLICATION_FOLDER', '../ci_application_testing');

    define('PRODUCTION_APPLICATION_FOLDER', '../ci_application');
/*
 *---------------------------------------------------------------
 */

	switch (ENVIRONMENT)
	{
		case 'development':
			error_reporting(-1);
          	$system_path = DEVELOPMENT_SYSTEM_PATH;
        	$application_folder = DEVELOPMENT_APPLICATION_FOLDER;
		break;

		case 'testing':
			error_reporting(-1);
          	$system_path = TESTING_SYSTEM_PATH;
        	$application_folder = TESTING_APPLICATION_FOLDER;
		break;

		case 'production':
			error_reporting(0);
          	$system_path = PRODUCTION_SYSTEM_PATH;
        	$application_folder = PRODUCTION_APPLICATION_FOLDER;
		break;

		default:
			exit('The application environment is not set correctly.');
	}
}

/*
 * --------------------------------------------------------------------
 * DEFAULT CONTROLLER
 * --------------------------------------------------------------------
 *
 * Normally you will set your default controller in the routes.php file.
 * You can, however, force a custom routing by hard-coding a
 * specific controller class/function here.  For most applications, you
 * WILL NOT set your routing here, but it's an option for those
 * special instances where you might want to override the standard
 * routing in a specific front controller that shares a common CI installation.
 *
 * IMPORTANT:  If you set the routing here, NO OTHER controller will be
 * callable. In essence, this preference limits your application to ONE
 * specific controller.  Leave the function name blank if you need
 * to call functions dynamically via the URI.
 *
 * Un-comment the $routing array below to use this feature
 *
 */
	// The directory name, relative to the "controllers" folder.  Leave blank
	// if your controller is not in a sub-folder within the "controllers" folder
	// $routing['directory'] = '';

	// The controller class file name.  Example:  Mycontroller
	// $routing['controller'] = '';

	// The controller function you wish to be called.
	// $routing['function']	= '';


/*
 * -------------------------------------------------------------------
 *  CUSTOM CONFIG VALUES
 * -------------------------------------------------------------------
 *
 * The $assign_to_config array below will be passed dynamically to the
 * config class when initialized. This allows you to set custom config
 * items or override any default config values found in the config.php file.
 * This can be handy as it permits you to share one application between
 * multiple front controller files, with each file containing different
 * config values.
 *
 * Un-comment the $assign_to_config array below to use this feature
 *
 */
	// $assign_to_config['name_of_config_item'] = 'value of config item';



// --------------------------------------------------------------------
// END OF USER CONFIGURABLE SETTINGS.  DO NOT EDIT BELOW THIS LINE
// --------------------------------------------------------------------

/*
 * ---------------------------------------------------------------
 *  Resolve the system path for increased reliability
 * ---------------------------------------------------------------
 */

	// Set the current directory correctly for CLI requests
	if (defined('STDIN'))
	{
		chdir(dirname(__FILE__));
	}

	if (realpath($system_path) !== FALSE)
	{
		$system_path = realpath($system_path).'/';
	}

	// ensure there's a trailing slash
	$system_path = rtrim($system_path, '/').'/';

	// Is the system path correct?
	if ( ! is_dir($system_path))
	{
		exit("Your system folder path does not appear to be set correctly. Please open the following file and correct this: ".pathinfo(__FILE__, PATHINFO_BASENAME));
	}

/*
 * -------------------------------------------------------------------
 *  Now that we know the path, set the main path constants
 * -------------------------------------------------------------------
 */
	// The name of THIS file
	define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));

	// The PHP file extension
	// this global constant is deprecated.
	define('EXT', '.php');

	// Path to the system folder
	define('BASEPATH', str_replace("\\", "/", $system_path));

	// Path to the front controller (this file)
	define('FCPATH', str_replace(SELF, '', __FILE__));

	// Name of the "system folder"
	define('SYSDIR', trim(strrchr(trim(BASEPATH, '/'), '/'), '/'));


	// The path to the "application" folder
	if (is_dir($application_folder))
	{
		define('APPPATH', $application_folder.'/');
	}
	else
	{
		if ( ! is_dir(BASEPATH.$application_folder.'/'))
		{
			exit("Your application folder path does not appear to be set correctly. Please open the following file and correct this: ".SELF);
		}

		define('APPPATH', BASEPATH.$application_folder.'/');
	}

/*
 * --------------------------------------------------------------------
 * LOAD THE BOOTSTRAP FILE
 * --------------------------------------------------------------------
 *
 * And away we go...
 *
 */
require_once BASEPATH.'core/CodeIgniter.php';

/* End of file index.php */
/* Location: ./index.php */