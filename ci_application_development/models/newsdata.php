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

//########################################################################
//# Hack to load Pagedata to allow model to extend model
//########################################################################
include_once dirname(__FILE__) . '/pagedata.php';

class Newsdata extends Pagedata
{
    //########################################################################
    //# Construct function
    //########################################################################

    public function __construct()
    {
        parent :: __construct();
    }

    //########################################################################
    //# set get_templates to use 'News' as a prefix and benchmark IDs
    //########################################################################
    public function build_page($page)
    {
        $this->get_subdomain();
        $this->benchmark->mark('Newsdata_Build_Page_start');
        $this->get_page($page);
        if (empty ($this->data['page'])) {
            // If no page data exit
            return NULL;
        }
        if ($this->is_cache_configured()) {
            if ($this->set_permissions() == FALSE) {
                // Redirect if the user doesn't have sufficient permission
                redirect('/login' . $this->uri->uri_string());
            }
            $this->set_cache_vars();
            $this->get_templates(TRUE, 'news');
            if (!empty ($this->template_types)) {
                $this->get_menu();
                $this->get_js_css();
                $this->build_data();
                $this->get_elements();
                if ($this->rebuild_cache) {
                    $this->rebuild();
                }
            }
        } else {
            if (!empty ($this->data['page'])) {
                if ($this->set_permissions() == FALSE) {
                    redirect('/login' . $this->uri->uri_string());
                    // Redirect if the user doesn't have sufficient permission
                }
                $this->get_templates(FALSE, 'news');
                $this->get_menu();
                $this->get_js_css();
                $this->build_data();
                $this->get_elements();
            }
        }
        $this->benchmark->mark('Newsdata_Build_Page_end');
    }

    //########################################################################
    //# Changed CORE_Page to News and cache id, updated canonical
    //########################################################################
    protected function get_page($page)
    {
        $canonical = '';
        if ($this->cache->configured) {
            $this->data['page'] = $this->cache->get('CORE_News_' . $page);
        }
        if (empty ($this->data['page'])) {
            $this->db
                ->select('*')
                ->from('CORE_News')
                ->where('url', $page);
            if (ENVIRONMENT === 'production') { // Only show non-active pages in the development environment
                $query = $this->db
                    ->where('active', 1)
                    ->get();
            } else { // Developers can navigate to any page, even inactive ones.
                $query = $this->db->get();
            }
            $this->data['page'] = $query->row_array();
            if ($this->cache->configured) {
                $this->cache->set('CORE_News_' . $page, $this->data['page']);
            }
        }
        if (empty ($this->data['page']['subdomain']) && empty($this->page_config['no_subdomain'])) {
            $canonical_array = array(
                $this->uri->segment(1),
                $page
            ); //Get the news controller name (can be blog, news, etc.) and strap the page on the end
            $canonical       = site_url($canonical_array);
        } elseif ($this->data['page']['subdomain'] != $this->subdomain) {
            $this->data['page'] = array();
            // We remove any data effectively returning a 404
        } else {
            $canonical = empty($this->data['page']['ssl_required']) ? 'http://' : 'https://';
            $canonical .= empty($this->data['page']['subdomain']) ? $this->data['rootdomain'] : $this->data['page']['subdomain'] . '.' . $this->data['rootdomain'];
            $canonical .= '/' . $this->uri->segment(1) . '/' . $page . '/';
        }
        $this->data['split_test'] = $this->analytics->start_split($this->data['page']);
//######################################################################################################################
//# DEBUG CODE, REMOVE AFTER DEVELOPMENT
//######################################################################################################################
        fb_log('split_test', $this->data['split_test']);
//######################################################################################################################

        $this->data['canonical'] = $canonical;
        $this->build_category_keywords();
    }

    //########################################################################
    //# Changed CORE_Page_CATEGORY_XREF to News and cache id
    //########################################################################
    public function build_category_keywords()
    {
        $page_id = $this->data['page']['id'];
        if ($this->cache->configured) {
            $this->data['page_category'] = $this->cache->get('CORE_News_CATEGORY_page_id_' . $page_id);
        }
        if (empty ($this->data['page_category'])) {
            $page_category = $this->db
                ->select('category_id')
                ->from('CORE_News_CATEGORY_XREF')
                ->where('news_id', $page_id)
                ->get()
                ->result_array();
            if (!empty($page_category)) {
                $category_ids = array();
                foreach ($page_category as $cat) {
                    $category_ids[] = $cat['category_id'];
                }
                $this->data['page_category'] = $this->db
                    ->select('*')
                    ->from('CORE_Page_CATEGORY')
                    ->where_in('id', $category_ids)
                    ->order_by('keyword', 'asc')
                    ->get()
                    ->result_array();
                if ($this->cache->configured) {
                    $this->cache->set('CORE_News_CATEGORY_page_id_' . $page_id, $this->data['page_category']);
                }
            }
        }
    }

    //########################################################################
    //# Removed CORE_Pages_DATA_XREF lookup and changed cache id
    //########################################################################
    protected function get_data_xref()
    {
        if ($this->cache->configured) {
            $data = $this->cache->get('CORE_News_DATA_XREF_Cache_' . $this->data['page']['id']);
            if (!empty($data)) {
                return $data;
            }
        }
        if (!empty ($this->template_ids)) {
            $this->db
                ->select('*')
                ->from('CORE_Templates_DATA_XREF');
            foreach ($this->template_ids as $id) {
                $this->db->or_where('template_id', $id);
            }
            $xref_data = $this->db
                ->get()
                ->result_array();
        } else {
            $xref_data = array();
        }
        if ($this->cache->configured) {
            $this->cache->set('CORE_News_DATA_XREF_Cache_' . $this->data['page']['id'], $xref_data, $this->data['page']['id'], $this->data['page']['cache_page']);
        }
        return $xref_data;
    }

    protected function get_top_posts()
    {

    }

    public function get_pagination($items, $post, $subs)
    {
        $this->db
            ->select('*')
            ->from('CORE_News');
        if (ENVIRONMENT === 'production') {
            $this->db->where('active', 1);
        }
        $this->db->where_in('subdomain', $subs);
        $total_posts   = $this->db->count_all_results();
        $total_pages   = ceil($total_posts / $items);
        $this_page_num = empty($post) ? 1 : $post;

        fb_log('total posts', $total_posts);
        fb_log('total pages', $total_pages);
        fb_log('this page', $this_page_num);

        $x          = 0;
        $pagination = array();
        while ($x < $total_pages) {
            $x++;
            $item          = array();
            $item['page']  = $x;
            $item['class'] = ($x == $this_page_num) ? 'active' : '';
            if ($x == $this_page_num) {
                $item['class'] = 'active';
                $item['url']   = '#';
            } else {
                $item['class'] = '';
                $item['url']   = ($x == 1) ? '/' . $this->uri->segment(1) . '/' : '/' . $this->uri->segment(1) . '/' . $x . '/';
            }
            $pagination[] = $item;
        }
        return $pagination;
    }

    public function sitemap_pagination()
    {
        $subs        = array(
            '',
            parent :: get_subdomain()
        );
        $page_config = parent :: get_page_config();
        $items       = empty($page_config['news_items']) ? 10 : $page_config['news_items'];

        $this->db
            ->select('*')
            ->from('CORE_News');
        $this->db->where('active', 1);
        $this->db->where('sitemap_item', 1);
        $this->db->where_in('subdomain', $subs);
        $total_posts = $this->db->count_all_results();
        $total_pages = ceil($total_posts / $items);

        $x          = 0;
        $pagination = array();
        while ($x < $total_pages) {
            $x++;
            $item         = array();
            $item['page'] = $x;
            $item['url']  = $page_config['news_controller'] . '/' . $x;
            if ($x != 1) {
                $pagination[] = $item;
            }
        }
        return $pagination;
    }

}