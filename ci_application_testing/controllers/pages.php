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

class Pages extends CI_Controller
{

    function __construct()
    {
        parent :: __construct();
    }

    function index()
    {
        $page  = $this->uri->segment(1);
        $extra = $this->uri->segment(2);
        $this->load->model('Pagedata');
        $page_config = $this->Pagedata->get_page_config();
        $subdomain   = $this->Pagedata->get_subdomain();
        if ($page == 'pages' || ($page == $subdomain && !empty($page)) || $page == $page_config['default_page'] || !empty($extra)) {
            show_404(); // Avoid duplicate content
        } else {
            if (empty ($page)) {
                if (empty($page_config['no_subdomain']) && $subdomain != 'www') {
                    $page = $subdomain;
                } else {
                    $page = $page_config['default_page'];
                }
            }
            // If certain pages need to load models or fetch data that the normal data system can't handle, put them in the following file:
            $contentigniter_hook = APPPATH . 'hooks/contentigniter_hook_pages_pre_get_data.php';
            if (is_readable($contentigniter_hook)) {
                include($contentigniter_hook);
            }
            $this->Pagedata->build_page($page);
            $data = $this->Pagedata->get_data();
            if (empty ($data['page'])) {
                show_404();
            } else {
                $this->load->view('master_view', $data);
            }
        }
    }

}
/* End of file pages.php */