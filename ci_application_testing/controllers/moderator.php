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
 * paul.dillinger@gmail.com so we can send you a copy immediately.
 *
 * @package        ContentIgniter
 * @author         Paul Dillinger
 * @copyright      Copyright (c) 2008 - 2012, Paul R. Dillinger. (http://prd.me/)
 * @license        http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @link           http://contentigniter.com
 * @since          Version 1.0
 * @filesource
 */
if (!defined('BASEPATH')) {
    exit ('No direct script access allowed');
}

class Moderator extends CI_Controller
{

    function __construct()
    {
        parent :: __construct();
        $this->load->helper('cookie');
        if ($this->uri->segment(2) != 'clear_cache' && !$this->session->auth('moderator')) {
            show_404();
        }
    }

    function set_production()
    {
        if ($this->session->auth('moderator')) {
            $domain = str_replace('www.', '.', $_SERVER['SERVER_NAME']);
            if (!ENVIRONMENT_DEBUG) {
                setcookie('ENVIRONMENT', '', time() - 3600, '/', $domain); // Reset the cookie
                setcookie('ENVIRONMENT_KEY', '', time() - 3600, '/', $domain); // Reset the cookie
            } else {
                setcookie('ENVIRONMENT', 'production', time() + 86400, '/', $domain); // Set the cookie
            }
            redirect('/account/');
        } else {
            show_404();
        }
    }

    function set_testing()
    {
        if ($this->session->auth('moderator')) {
            $domain = str_replace('www.', '.', $_SERVER['SERVER_NAME']);
            $salt   = date('Fd');
            $secret = hash('md4', $salt . TEST_SECRET);
            setcookie('ENVIRONMENT', 'testing', time() + 86400, '/', $domain); // Set the cookie
            if (!ENVIRONMENT_DEBUG) {
                setcookie('ENVIRONMENT_KEY', $secret, time() + 86400, '/', $domain); // Set the cookie
            }
            redirect('/account/');
        } else {
            show_404();
        }
    }
}
/* End of file admin.php */
