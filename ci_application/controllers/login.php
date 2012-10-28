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

class Login extends CI_Controller
{

    function __construct()
    {
        parent :: __construct();
    }

    function index()
    {
        $this->load->model('Pagedata');
        $page_config = $this->Pagedata->get_page_config();

        fb_log('ssl_installed', $page_config['ssl_installed']);
        $base_url = $this->config->item('base_url');
        fb_log('$base_url', $base_url);
        $url = str_replace('http://', 'https://', $base_url) . uri_string();
        fb_log('$url', $url);
        $current_url = current_url();
        fb_log('$current_url', $current_url);

        if (!empty($page_config['ssl_installed']) && empty($_SERVER['HTTPS'])) {
            $url = 'https://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
            header('Location: ' . $url);
            exit;
        }
        $redirect = "/";
        $check    = $this->uri->segment(2);
        if (empty($check)) {
            $redirect = "/account/";
        } else {
            if ($this->uri->segment(2) == 'logout') {
                $this->logout();
            } else {
                foreach ($this->uri->segment_array() as $item) {
                    if ($item != 'login') {
                        $redirect = $redirect . $item . "/";
                    }
                }
            }
        }
        $user = $this->input->post('user_name', TRUE);
        $pass = $this->input->post('password', TRUE);
        if (!empty ($user) && !empty ($pass)) {
            $logged_in = $this->session->login($user, $pass);
        }
        if (isset ($logged_in)) {
            if ($logged_in) {
                if ($this->input->post('redirect')) {
                    $redirect = $this->input->post('redirect');
                }
                redirect($redirect);
            } else {
                $error_message = "Bad user name or password. $logged_in ";
            }
        } else {
            if (!empty ($user)) {
                $error_message = "Password is required";
            } elseif (!empty ($pass)) {
                $error_message = "Username is required";
            } else {
                $error_message = FALSE;
                $user          = '';
                $pass          = '';
            }
        }
        $data['error_message'] = $error_message;
        $data['user']          = $user;
        $data['pass']          = $pass;
        $data['redirect']      = $redirect;
        $data['template']      = array(
            '_simple_header',
            '_blank_nav',
            'login',
            '_blank_aside',
            '_footer'
        );
        $this->load->view('core_view', $data);
    }

    function logout()
    {
        $this->session->logout();
        redirect('');
    }

}
/* End of file login.php */
