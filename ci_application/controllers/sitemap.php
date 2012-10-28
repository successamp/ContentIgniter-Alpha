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

class Sitemap extends CI_Controller
{

    function __construct()
    {
        parent :: __construct();
        $this->load->helper('xml');
    }

    function index()
    {
        show_404();
    }

    function xml()
    {
        if ($this->cache->configured) {
            $latest_sitemap = $this->cache->get('sitemap.xml');
        }
        if (empty($latest_sitemap)) {
            $this->load->model('Pagedata');
            $this->load->model('Newsdata');
            $config_no_subdomain = $this->config->item('no_subdomain');
            $subdomain         = $this->Pagedata->get_subdomain();
            $subdomain_details = $this->Pagedata->get_data('subdomain');
            if (empty($config_no_subdomain) && empty($subdomain_details['active'])) { // Disable sitemaps for non-active subdomains
                show_404();
                exit;
            }

            $config = $this->Pagedata->get_page_config();
            //### Pages
            $this->db
                ->select('*')
                ->from('CORE_Pages')
                ->where('active', 1)
                ->where('sitemap_item', 1)
                ->where('subdomain', $subdomain)
                ->or_where('subdomain', '')
                ->where('sitemap_item', 1)
                ->where('active', 1)
                ->order_by('priority', 'DESC')
                ->order_by('id');
            $sitemapdata_pages = $this->db
                ->get()
                ->result_array();
            //### News (blog)
            $this->db
                ->select('*')
                ->from('CORE_News')
                ->where('active', 1)
                ->where('sitemap_item', 1)
                ->where('subdomain', $subdomain)
                ->or_where('subdomain', '')
                ->where('sitemap_item', 1)
                ->where('active', 1)
                ->order_by('priority', 'DESC')
                ->order_by('published', 'DESC');
            $sitemapdata_news = $this->db
                ->get()
                ->result_array();

            $sitemapdata_news_pagination = $this->Newsdata->sitemap_pagination();

            foreach ($sitemapdata_news as &$item) {
                $item['url'] = 'news/' . $item['url'];
            }

            //### Merge Pages, News, and News Pagination
            $sitemapdata = array_merge($sitemapdata_pages, $sitemapdata_news_pagination);
            $sitemapdata = array_merge($sitemapdata, $sitemapdata_news);

            $data['sitemapdata'] = $sitemapdata;
            $data['rootdomain']  = $this->Pagedata->get_data('rootdomain');
            $data['subdomain']   = $this->Pagedata->get_data('subdomain');
            $data['template']    = array(
                '_sitemap_header',
                '_blank_nav',
                'sitemap_xml',
                '_blank_aside',
                '_sitemap_footer'
            );
            if (empty ($sitemapdata)) {
                show_404();
                exit;
            } else {
                $latest_sitemap = $this->load->view('core_view', $data, TRUE);
                if ($this->cache->configured) {
                    $sitemap_xml_cache_time = empty($config['sitemap_xml_cache_time']) ? 3600 : $config['sitemap_xml_cache_time']; // Cache for configured time or one hour
                    $this->cache->set('sitemap.xml', $latest_sitemap, NULL, $sitemap_xml_cache_time);
                }
            }
        }
        $this->output->enable_profiler(FALSE); // Profiler breaks the feed.
        header("Content-Type: text/xml");
        echo $latest_sitemap;
    }

}
/* End of file sitemap.php */
