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

class News extends CI_Controller
{

    function __construct()
    {
        parent :: __construct();
    }

    function index($post = NULL)
    {
        $this->load->model('Newsdata');
        $page_config = $this->Newsdata->get_page_config();
        if (empty($post) || is_numeric($post)) {
            $items = empty($page_config['news_items']) ? 10 : $page_config['news_items'];
            $this->_display_latest($items, $post);
        } else {
            $this->_display_item($post);
        }
    }

    function _display_item($post)
    {
        if (strtolower($post) == 'index') {
            show_404();
        }
        $this->Newsdata->build_page($post);
        $data = $this->Newsdata->get_data();

        if (empty ($data['page'])) {
            show_404();
        } else {
            $this->load->view('master_view', $data);
        }
    }

    function _display_latest($items = 10, $post = 0)
    {
        if ($post == 1) {
            show_404();
        }
        $this->load->model('Pagedata');
        $this->Pagedata->build_page('news');

        $start_item = empty($post) ? 0 : (($post - 1) * $items);
        fb_info('Fetching ' . $items . ' items starting at ' . $start_item);
        $subs = array(
            '',
            $this->Pagedata->get_subdomain()
        );

        $pagination = $this->Newsdata->get_pagination($items, $post, $subs);
        $this->Pagedata->set('pagination', $pagination);
        fb_log('pagination', $pagination);

        $this->db
            ->select('*')
            ->from('CORE_News');
        if (ENVIRONMENT === 'production') {
            $this->db->where('active', 1);
            $this->db->where('published <= NOW()');
        }
        $this->db->where_in('subdomain', $subs);
        $news_posts = $this->db
            ->order_by('pinned', 'desc')
            ->order_by('published', 'desc')
            ->order_by('id', 'desc')
            ->limit($items, $start_item)
            ->get()
            ->result_array();
        if (empty($news_posts)) {
            show_404();
        }
        $this->Pagedata->set('news_posts', $news_posts);
        $data = $this->Pagedata->get_data();

        if (empty ($data['page'])) {
            show_404();
        } else {
            $this->load->view('master_view', $data);
        }
    }
}
/* End of file news.php */
