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
 * @package		ContentIgniter
 * @author		Paul Dillinger
 * @copyright	Copyright (c) 2008 - 2011, Paul R. Dillinger. (http://prd.me/)
 * @license		http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @link		http://contentigniter.com
 * @since		Version 1.0
 * @filesource
 */
if (!defined('BASEPATH'))
    exit ('No direct script access allowed');

class Feed extends CI_Controller {

	function __construct() {
		parent :: __construct();
        $this->load->helper('xml');
    }

    function index() {
        show_404();
    }

    function rss($extra=NULL) {
        if(!empty($extra)){
            show_404();
        }
        if ($this->cache->configured) {
            $latest_feed = $this->cache->get('feed.rss');
        }
        if(empty($latest_feed)){
            $this->load->model('Pagedata');
            $subdomain = $this->Pagedata->get_subdomain();
            $config = $this->Pagedata->get_page_config();
            $feed_limit = empty($config['feed_limit']) ? 20 : $config['feed_limit']; // Feed defaults to 20 items, but can be set to any number between 5 and 100.
            if($feed_limit < 5 || $feed_limit > 100){
                $feed_limit = 20;
            }
            $this->db->select('*')->from('CORE_News')->where('active', 1)->where('subdomain', $subdomain)->or_where('subdomain', '')->where('active', 1)->order_by('published', 'DESC')->limit($feed_limit);
            $rssdata = $this->db->get()->result_array();
            if (empty ($rssdata)) {
                show_404();
                exit;
            }
            else {
                $data['rssdata'] = $rssdata;
                $data['rootdomain'] = $this->Pagedata->get_data('rootdomain');
                $data['subdomain'] = $this->Pagedata->get_data('subdomain');
                if(empty($data['subdomain'])){
                    $data['rssdomain'] = $data['rootdomain'];
                }else{
                    $data['rssdomain'] = $data['subdomain']['id'] . '.' . $data['rootdomain'];
                }
                $data['template'] = array('_feed_rss_header', '_blank_nav', 'feed_rss', '_blank_aside', '_feed_rss_footer');
                $data['feed_name'] = empty($config['feed_name']) ? 'http://' . $data['rssdomain'] . '/' : $config['feed_name'];
                $data['feed_url'] = empty($config['feed_url']) ? 'http://' . $data['rssdomain'] . '/' : $config['feed_url'];
                $data['site_description'] = empty($config['site_description']) ? '' : $config['site_description'];
                $latest_feed = $this->load->view('core_view', $data, TRUE);
                if ($this->cache->configured) {
                    $feed_rss_cache_time = empty($config['feed_cache_time']) ? 3600 : $config['feed_cache_time']; // Cache for one hour or configured time of 60 seconds to 7 days.
                    if($feed_rss_cache_time < 60 || $feed_rss_cache_time > 604800){
                        $feed_rss_cache_time = 3600;
                    }
                    $this->cache->set('feed.rss', $latest_feed, null, $feed_rss_cache_time);
                }
            }
        }
		$this->output->enable_profiler(FALSE); // Profiler breaks the feed.
		header('Content-Type: text/xml');
        echo $latest_feed;
    }

}
/* End of file feed.php */
