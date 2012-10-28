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

class Editor extends CI_Controller
{

    function __construct()
    {
        parent :: __construct();
        $this->load->helper('cookie');
        if (!$this->session->auth('editor')) {
            show_404();
        }
        if ($this->cache->configured) {
            $this->page_config = $this->cache->get('CORE_Pages_CONFIG');
        }
        if (empty ($this->page_config)) {
            $query = $this->db
                ->select('*')
                ->from('CORE_Pages_CONFIG')
                ->get();
            foreach ($query->result_array() as $item) {
                $this->page_config[$item['key']] = $item['value'];
            }
            if ($this->cache->configured) {
                $this->cache->set('CORE_Pages_CONFIG', $this->page_config);
            }
        }
        if(empty($_COOKIE['CI_KCFINDER'])){
            $CI_KCFINDER = array();
            $CI_KCFINDER['disabled'] = false;
            $CI_KCFINDER['uploadURL'] = '/static/uploads';
            $CI_KCFINDER['uploadDir'] = FCPATH.'static/uploads';
            $CI_KCFINDER['maxImageWidth'] = 400;
            $CI_KCFINDER['maxImageHeight'] = 400;
            $CI_KCFINDER['jpegQuality'] = 80;
            $CI_KCFINDER['secret'] = 'PUT SECRET HERE';
            setcookie('CI_KCFINDER', serialize($CI_KCFINDER), (time() + $this->config->item('sess_expiration')), $this->config->item('cookie_path'), $this->config->item('cookie_domain'));
        }
    }

    function index()
    {
        if (!$this->session->auth('editor')) {
            show_404();
        } else {
            redirect('/editor/home/');
        }
    }

    function home()
    {
        $data['template'] = 'editor_home';
        $this->load->view('core_view', $data);
    }

    function pages()
    {
        $data['page_admin_data'] = $this->db
            ->select('*')
            ->from('CORE_Pages')
            ->order_by('id', 'desc')
            ->get()
            ->result_array();
        $data['template']        = 'editor_pages';
        $this->load->view('core_view', $data);
    }

    function pages_edit($page)
    {
        $page_content = '';
        if (!empty ($page)) {
            $query = $this->db
                ->select('*')
                ->from('CORE_Pages')
                ->where('id', $page)
                ->get()
                ->row_array();
            if (!empty ($query)) {
                $HTML = $this->mu;
                $page_content .= $HTML
                    ->form('/editor/pages_edit_update/' . $page . '/')
                    ->id('ci_admin_edit_form')
                    ->addClass('simpleForm')
                    ->autoform('CORE_Pages', $query)
                    ->label('submit', ' ')
                    ->input('submit', 'submit', 'Submit')
                    ->close()
                    ->get();
            }
        }
        $data['page_content'] = $page_content;
        fb_log('page_content', $data['page_content']);
        $data['template'] = 'editor_pages_edit';

        if (empty($this->page_config['rootdomain'])) {
            $domainurl = explode('.', $_SERVER['SERVER_NAME']);
            $count     = count($domainurl);
            switch ($count) {
                case 2:
                    $data['display_rootdomain'] = $_SERVER['SERVER_NAME'] . '/';
                    break;
                case 3:
                    $data['display_rootdomain'] = '.' . $domainurl[1] . '.' . $domainurl[2] . '/';
                    break;
                default:
                    $data['display_rootdomain'] = '.' . $domainurl[1] . '.' . $domainurl[2] . '.' . $domainurl[3] . '/';
                    break;
            }
        } else {
            if (empty($this->page_config['no_subdomain'])) {
                $data['display_rootdomain'] = '.' . $this->page_config['rootdomain'] . '/';
            } else {
                $data['display_rootdomain'] = $this->page_config['rootdomain'] . '/';
            }
        }
        $this->load->view('core_view', $data);
    }

    function pages_content($page)
    {
        $page_content = '';
        if (!empty ($page)) {
            $page_data = $this->db
                ->select('*')
                ->from('CORE_Pages_DATA_XREF')
                ->where('page_id', $page)
                ->get()
                ->result_array();
            $HTML      = $this->mu;
            if (!empty ($page_data)) {
                foreach ($page_data as $data_row) {
                    $query = $this->db
                        ->select('*')
                        ->from($data_row['data_table_name'])
                        ->where('id', $data_row['data_id'])
                        ->get()
                        ->row_array();
                    $page_content .= empty ($Content) ? '' : '<hr/>';
                    $page_content .= $HTML
                        ->form('/editor/pages_content_update/' . $page . '/' . $data_row['data_table_name'] . '/' . $data_row['data_id'] . '/')
                        ->addClass('simpleForm')
                        ->autoform($data_row['data_table_name'], $query)
                        ->label('submit', ' ')
                        ->input('submit', 'submit', 'Submit')
                        ->close()
                        ->get();
                }
            } else {
                $page_content .= $HTML
                    ->form('/editor/pages_add_content_to_page/' . $page . '/')
                    ->addClass('simpleForm')
                    ->label('submit', ' ')
                    ->input('submit', 'submit', 'Create Content Record')
                    ->close()
                    ->get();
            }
        } else {
        }
        $data['page_content'] = $page_content;
        fb_log('page_content', $data['page_content']);
        //$data['template'] = array('_header','_blank_nav','pages_content','_blank_aside','_footer');
        $data['template'] = 'editor_pages_content';
        $this->load->view('core_view', $data);
    }

    function pages_edit_update($page)
    {
        $data = $_POST;
        unset ($data['submit']);
        $data['url']         = $this->_format_url($data['url']);
        $data['title']       = $this->_clean_special($data['title']);
        $data['description'] = $this->_clean_special($data['description']);
        if ($data['id'] = $page) {
            $this->_backup_before_update('CORE_Pages', $page);
            $this->db->update('CORE_Pages', $data, "id = '$page'");
        }
        $this->clear_cache(TRUE);
        redirect('/editor/pages/');
    }

    function pages_content_update($page, $dtn, $dr)
    {
        $data = $_POST;
        unset ($data['submit']);
        $data['header'] = $this->_clean_special($data['header']);
        if ($data['id'] = $dr) {
            $this->_backup_before_update($dtn, $dr);
            $this->db->update($dtn, $data, "id = '$dr'");
        }
        $this->clear_cache(TRUE);
        redirect('/editor/pages/');
    }

    function pages_add_page()
    {
        $time      = time();
        $page_data = array('url' => 'NEW-' . $time);
        $this->db->insert('CORE_Pages', $page_data);
        redirect('/editor/pages/');
    }

    function pages_add_content_to_page($page)
    {
        $test = $this->db
            ->select('*')
            ->from('CORE_Pages')
            ->where('id', $page)
            ->get()
            ->row_array();
        if (!empty ($page) && !empty ($test['id']) && $page == $test['id']) {
            $content_data = array(
                'header' => '',
                'body'   => ''
            );
            $this->db->insert('DATA_CORE_Pages_CONTENT', $content_data);
            $row_id = $this->db->insert_id();
            if (!empty ($row_id)) {
                $this->db
                    ->set('page_id', $page)
                    ->set('data_table_name', 'DATA_CORE_Pages_CONTENT')
                    ->set('data_id', $row_id)
                    ->set('attribute_name', 'Content')
                    ->insert('CORE_Pages_DATA_XREF');
            }
        }
        redirect('/editor/pages_content/' . $page . '/');
    }

    function pages_delete($page)
    {
        $data = $this->db
            ->select('*')
            ->from('CORE_Pages_DATA_XREF')
            ->where('page_id', $page)
            ->get()
            ->result_array();
        if (!empty($data)) {
            foreach ($data as $delete_me) {
                $test = $this->db
                    ->select('page_id')
                    ->from('CORE_Pages_DATA_XREF')
                    ->where('data_table_name', $delete_me['data_table_name'])
                    ->where('data_id', $delete_me['data_id'])
                    ->count_all_results();
                if ($test == 1) {
                    //echo "Deleting " . $delete_me['data_table_name'] . " - " . $delete_me['data_id'] . "<br/>";
                    $this->_backup_before_update($delete_me['data_table_name'], $delete_me['data_id']); // Backup before delete
                    $this->db->delete($delete_me['data_table_name'], array('id' => $delete_me['data_id']));
                }
                $this->db->delete('CORE_Pages_DATA_XREF', array(
                                                               'page_id'         => $page,
                                                               'data_id'         => $delete_me['data_id'],
                                                               'data_table_name' => $delete_me['data_table_name']
                                                          ));
            }
        }
        $this->_backup_before_update('CORE_Pages', $page); // Backup before delete
        $this->db->delete('CORE_Pages', array('id' => $page));
        $this->clear_cache(TRUE);
        redirect('/editor/pages/');
    }

    function q_a()
    {
        $data['page_admin_data'] = $this->db
            ->select('*')
            ->from('DATA_FAQ_CONTENT')
            ->order_by('order', 'asc')
            ->get()
            ->result_array();
        $data['template']        = 'editor_q_a';
        $this->load->view('core_view', $data);
    }

    function q_a_add()
    {
        $time = time();
        $data = array('question' => 'NEW-' . $time);
        $this->db->insert('DATA_FAQ_CONTENT', $data);
        redirect('/editor/q_a/');
    }

    function q_a_edit($page)
    {
        $page_content = '';
        if (!empty ($page)) {
            $query = $this->db
                ->select('*')
                ->from('DATA_FAQ_CONTENT')
                ->where('id', $page)
                ->get()
                ->row_array();
            if (!empty ($query)) {
                $HTML = $this->mu;
                $page_content .= $HTML
                    ->form('/editor/q_a_update/' . $page . '/')
                    ->id('ci_admin_edit_form')
                    ->addClass('simpleForm')
                    ->autoform('DATA_FAQ_CONTENT', $query)
                    ->label('submit', ' ')
                    ->input('submit', 'submit', 'Submit')
                    ->close()
                    ->get();
            }
        }
        $data['page_content'] = $page_content;
        fb_log('page_content', $data['page_content']);
        $data['template'] = 'editor_q_a_edit';

        $this->load->view('core_view', $data);
    }

    function q_a_update($item)
    {
        $data = $_POST;
        unset ($data['submit']);
        $data['question'] = $this->_clean_special($data['question']);
        $data['order']    = is_numeric($data['order']) ? $data['order'] : 999;
        if ($data['id'] = $item) {
            $this->_backup_before_update('DATA_FAQ_CONTENT', $item);
            $this->db->update('DATA_FAQ_CONTENT', $data, "id = '$item'");
        }
        $this->clear_cache(TRUE);
        redirect('/editor/q_a/');
    }

    function q_a_delete($item)
    {
        $this->_backup_before_update('DATA_FAQ_CONTENT', $item); // Backup before delete
        $this->db->delete('DATA_FAQ_CONTENT', array('id' => $item));
        $this->clear_cache(TRUE);
        redirect('/editor/q_a/');
    }

    function news()
    {
        $data['news_admin_data'] = $this->db
            ->select('*')
            ->from('CORE_News')
            ->order_by('id', 'desc')
            ->get()
            ->result_array();
        $data['template']        = 'editor_news';
        $this->load->view('core_view', $data);
    }

    function news_edit($page)
    {
        $page_content = '';
        if (!empty ($page)) {
            $query = $this->db
                ->select('*')
                ->from('CORE_News')
                ->where('id', $page)
                ->get()
                ->row_array();
            if (!empty ($query)) {
                $HTML = $this->mu;
                $form_settings = array(
                    'action' => '/editor/news_edit_update/' . $page . '/',
                    'method' => 'post',
                    'open' => TRUE,
                    'enctype' => 'multipart/form-data'
                );
                $page_content .= $HTML
                    ->form($form_settings)
                    ->id('ci_admin_edit_form')
                    ->addClass('simpleForm')
                    ->autoform('CORE_News', $query)
                    ->label('post_img_upload', 'Upload Image')
                    ->input('post_img_upload','file')
                    ->label('submit', ' ')
                    ->input('submit', 'submit', 'Submit')
                    ->close()
                    ->get();
            }
        }
        $data['page_content'] = $page_content;
        $data['template']     = 'editor_news_edit';
        if (empty($this->page_config['rootdomain'])) {
            $domainurl = explode('.', $_SERVER['SERVER_NAME']);
            $count     = count($domainurl);
            switch ($count) {
                case 2:
                    $data['display_rootdomain'] = $_SERVER['SERVER_NAME'] . '/';
                    break;
                case 3:
                    $data['display_rootdomain'] = '.' . $domainurl[1] . '.' . $domainurl[2] . '/';
                    break;
                default:
                    $data['display_rootdomain'] = '.' . $domainurl[1] . '.' . $domainurl[2] . '.' . $domainurl[3] . '/';
                    break;
            }
        } else {
            if (empty($this->page_config['no_subdomain'])) {
                $data['display_rootdomain'] = '.' . $this->page_config['rootdomain'] . '/';
            } else {
                $data['display_rootdomain'] = $this->page_config['rootdomain'] . '/';
            }
        }
        $this->load->view('core_view', $data);
    }

    function news_edit_update($page)
    {
        $data = $_POST;
        unset ($data['submit']);
        if(!empty($_FILES['post_img_upload']['name'])){
            $data['post_img'] = $this->news_add_image($data);
        }

        $data['url']         = $this->_format_url($data['url']);
        $data['title']       = $this->_clean_special($data['title']);
        $data['description'] = $this->_clean_special($data['description']);
        $data['header']      = $this->_clean_special($data['header']);
        if ($data['id'] = $page) {
            $this->_backup_before_update('CORE_News', $page);
            $this->db->update('CORE_News', $data, "id = '$page'");
        }
        $this->clear_cache(TRUE);
        redirect('/editor/news/');
    }

    function news_add_image($data)
    {
        $blog_img_dir = $this->config->item('blog_img_dir');
        $blog_id = $data['id'];
        $config['upload_path'] = FCPATH . $blog_img_dir . $blog_id .'/';
        $config['allowed_types'] = 'gif|jpg|png';
        $config['max_size']	= $this->config->item('blog_upload_max_size');
        $config['max_width']  = $this->config->item('blog_upload_max_width');
        $config['max_height']  = $this->config->item('blog_upload_max_height');

        if (!is_writable($config['upload_path'])) {
            if (!mkdir($config['upload_path'], 0777, TRUE)) {
                fb_error('Error writing to ' . $config['upload_path']);
            }
        }

        $this->load->library('upload', $config);

        if ( ! $this->upload->do_upload('post_img_upload'))
        {
            fb_error($this->upload->display_errors());
            return NULL;
        }
        else
        {
            $upload_data = $this->upload->data();
            $img = new Imagick($upload_data['full_path']);
            $height = $img->getImageHeight();
            $width = $img->getImageWidth();
            if($height > $this->config->item('blog_image_max_height') || $width > $this->config->item('blog_image_max_width')){
                $img->scaleImage($this->config->item('blog_image_max_width'), $this->config->item('blog_image_max_height'), TRUE);
            }
            $img->setImageCompression(Imagick::COMPRESSION_JPEG);
            $img->setImageCompressionQuality($this->config->item('blog_image_quality'));
            $img->stripImage();
            $filename = $this->_format_url($this->_clean_special($data['title'])) . '.jpg';
            if(is_readable($config['upload_path'] . $filename)){
                $x = 1;
                while(is_readable($config['upload_path'] . $x . '-' . $filename)){
                    $x++;
                    if($x > 999){
                        break; // Just in case
                    }
                }
                $filename = $x . '-' . $filename;
            }
            $img->writeImage($config['upload_path'].$filename);
        }

        $upload_file_name = $blog_img_dir . $blog_id .'/' .$filename;
        if(is_readable(FCPATH . $upload_file_name)){
            return $upload_file_name;
        }else{
            fb_error('Error saving ' . $upload_file_name);
            return NULL;
        }
    }

    function custom_image_upload()
    {
        $dir = glob(FCPATH . 'static/uploads/image/*',GLOB_ONLYDIR);
        $dir_list = array();
        $dir_list['name'] = 'dir_list';
        $dir_list['class'] = 'mceNoEditor';
        $dir_list['value'] = '';
        if(!empty($dir)){
            foreach($dir as &$item){
                $item = str_replace(FCPATH . 'static/uploads/image/','',$item);
                $dir_list['value'] .= $item."\n";
            }
        }

        fb_log('$dir',$dir);
        $HTML = $this->mu;
        $form_settings = array(
            'action' => '/editor/add_custom_image/',
            'method' => 'post',
            'open' => TRUE,
            'enctype' => 'multipart/form-data'
        );
        $page_content = $HTML
            ->form($form_settings)
            ->id('ci_admin_edit_form')
            ->addClass('simpleForm')
            ->label('keywords', 'Image Keywords')
            ->input('keywords')
            ->label('max_height', 'Maximum Height')
            ->input('max_height', null, 400)
            ->label('max_width', 'Maximum Width')
            ->input('max_width', null, 400)
            ->label('quality', 'Quality')
            ->input('quality', null, 80)
            ->label('dir_list', 'Sub-Directory List : For Reference')
            ->textarea($dir_list)
            ->label('dir', 'Sub-Directory (leave blank for default)')
            ->input('dir')
            ->label('post_img_upload', 'Upload Image')
            ->input('post_img_upload','file')
            ->label('submit', ' ')
            ->input('submit', 'submit', 'Submit')
            ->close()
            ->get();

        $data['page_content'] = $page_content;
        $data['template'] = 'editor_image_upload';
        $this->load->view('core_view', $data);
    }

    function add_custom_image()
    {

        $config['upload_path'] = FCPATH . 'static/uploads/';
        $config['allowed_types'] = 'gif|jpg|png';
        $img_path = empty($_POST['dir']) ? $config['upload_path'] . 'image/' : $config['upload_path'] . 'image/' . $_POST['dir'] . '/';
        $root_img_path = empty($_POST['dir']) ? '/static/uploads/image/' : '/static/uploads/image/' . $_POST['dir'] . '/';

        if (!is_writable($config['upload_path'])) {
            if (!mkdir($config['upload_path'], 0777, TRUE)) {
                fb_error('Error writing to ' . $config['upload_path']);
                show_error('Error writing to the upload path');
            }
        }
        if (!is_writable($img_path)) {
            if (!mkdir($img_path, 0777, TRUE)) {
                fb_error('Error writing to ' . $img_path);
                show_error('Error writing to the image path');
            }
        }

        $this->load->library('upload', $config);

        if ( ! $this->upload->do_upload('post_img_upload'))
        {
            show_error($this->upload->display_errors());
        }
        else
        {
            $upload_data = $this->upload->data();
            $img = new Imagick($upload_data['full_path']);
            $height = $img->getImageHeight();
            $width = $img->getImageWidth();
            if($height > $_POST['max_height'] || $width > $_POST['max_width']){
                $img->scaleImage($_POST['max_width'], $_POST['max_height'], TRUE);
            }
            $img->setImageCompression(Imagick::COMPRESSION_JPEG);
            $img->setImageCompressionQuality($_POST['quality']);
            $img->stripImage();
            $filename = $this->_format_url($this->_clean_special($_POST['keywords'])) . '.jpg';
            if(is_readable($img_path . $filename)){
                $x = 1;
                while(is_readable($img_path . $x . '-' . $filename)){
                    $x++;
                    if($x > 999){
                        break; // Just in case
                    }
                }
                $filename = $x . '-' . $filename;
            }
            $img->writeImage($img_path.$filename);
        }

        if(is_readable($img_path . $filename)){
            unlink($upload_data['full_path']);
            $this->session->set_flashdata('success', $root_img_path.$filename);
            redirect('/editor/custom_image_upload/');
        }else{
            fb_error('Error saving ' . $img_path . $filename);
            show_error('Error saving ' . $filename);
        }
    }

    function news_add()
    {
        $time      = time();
        $page_data = array('url' => 'NEW-' . $time);
        $this->db->insert('CORE_News', $page_data);
        redirect('/editor/news/');
    }

    function news_delete($page)
    {
        $this->_backup_before_update('CORE_News', $page); // Backup before delete
        $this->db->delete('CORE_News', array('id' => $page));
        $this->clear_cache(TRUE);
        redirect('/editor/news/');
    }

    function subdomains()
    {
        $data['page_admin_data'] = $this->db
            ->select('*')
            ->from('DATA_subdomain_PROFILE')
            ->get()
            ->result_array();
        $data['template']        = 'editor_subdomains';
        $this->load->view('core_view', $data);
    }

    function subdomain_edit($id)
    {
        $data['page_content'] = '';
        if (!empty ($id)) {
            $query = $this->db
                ->select('*')
                ->from('DATA_subdomain_PROFILE')
                ->where('id', $id)
                ->get()
                ->row_array();
            if (!empty ($query)) {
                $HTML                 = $this->mu;
                $data['page_content'] = $HTML
                    ->form('/editor/subdomain_edit_update/')
                    ->id('ci_admin_edit_form')
                    ->addClass('simpleForm')
                    ->autoform('DATA_subdomain_PROFILE', $query)
                    ->input('old_id', 'hidden', $query['id'])
                    ->label('submit', ' ')
                    ->input('submit', 'submit', 'Submit')
                    ->close()
                    ->get();
            }
        }
        $data['template'] = 'editor_subdomains_edit';
        $this->load->view('core_view', $data);
    }

    function subdomain_edit_update()
    {
        $data        = $_POST;
        $data_old_id = $data['old_id'];
        unset ($data['submit']);
        unset ($data['old_id']);
        //Allow html but encode special characters
        $data['name']          = $this->_clean_special($data['name']);

        if (!empty($data['id']) && !empty($data_old_id)) {
            $this->_backup_before_update('DATA_subdomain_PROFILE', $data_old_id);
            $this->db->update('DATA_subdomain_PROFILE', $data, "id = '$data_old_id'");
        }
        $this->clear_cache(TRUE);
        redirect('/editor/subdomains/');
    }

    function subdomain_add_subdomain()
    {
        $time      = time();
        $page_data = array('id' => $time);
        $this->db->insert('DATA_subdomain_PROFILE', $page_data);
        redirect('/editor/subdomains/');
    }

    function subdomain_delete($id)
    {
        $this->_backup_before_update('DATA_subdomain_PROFILE', $id); // Backup before delete
        $this->db->delete('DATA_subdomain_PROFILE', array('id' => $id));
        $this->clear_cache(TRUE);
        redirect('/editor/subdomains/');
    }

    function view_assets($path1 = NULL, $path2 = NULL, $path3 = NULL, $path4 = NULL, $path5 = NULL, $path6 = NULL, $path7 = NULL, $path8 = NULL, $path9 = NULL, $path10 = NULL, $path11 = NULL, $path12 = NULL)
    {
        $x    = 0;
        $path = NULL;
        while ($x < 12) {
            $x++;
            $test_path = 'path' . $x;
            if (!empty($$test_path)) {
                $path .= $$test_path . '/';
            } else {
                break;
            }
        }

        $dirs = $this->_file_list($path);
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() - 3600));
        header('Content-type: application/json');
        echo(json_encode($dirs));
        exit;
    }

    function clear_cache($internal_request = FALSE)
    {
        if ($this->cache->clear_all()) {
            if ($internal_request !== TRUE) {
                echo 'Cache Cleared! You may need to re-load (CTRL+F5) any previously viewed pages to see changes.  <a href="/account/">Account Home</a>';
                exit;
            }
        } else {
            show_error('Cache not properly configured');
        }
    }

    protected function _backup_before_update($table, $id)
    {
        if ($this->cache->configured) {
            $page_config = $this->cache->get('CORE_Pages_CONFIG');
        }
        if (empty ($page_config)) {
            $query = $this->db
                ->select('*')
                ->from('CORE_Pages_CONFIG')
                ->get();
            foreach ($query->result_array() as $item) {
                $page_config[$item['key']] = $item['value'];
            }
            if ($this->cache->configured) {
                $this->cache->set('CORE_Pages_CONFIG', $page_config);
            }
        }
        $limit        = empty($page_config['db_backup_items']) ? 3 : $page_config['db_backup_items']; // Default to keeping up to three backups for any row unless otherwise specified
        $backup_table = 'BACKUP_' . $table;
        if (!$this->db->table_exists($backup_table)) {
            // Create a backup table
            if ($this->db->table_exists($table)) {
                $sql_cmd = 'CREATE TABLE ' . $this->db->dbprefix . $backup_table . ' SELECT * FROM ' . $this->db->dbprefix . $table . ' WHERE 0';
                if ($this->db->simple_query($sql_cmd)) {
                    $sql_cmd = 'ALTER TABLE ' . $this->db->dbprefix . $backup_table . ' ADD `backup_id` INT( 11 ) PRIMARY KEY AUTO_INCREMENT NOT NULL FIRST';
                    if (!$this->db->simple_query($sql_cmd)) {
                        $msg     = 'Error creating backup table (4) <<<>>> ' . $sql_cmd . ' <<<>>> ' . $this->db->_error_message();
                        $sql_cmd = 'DROP TABLE ' . $this->db->dbprefix . $backup_table;
                        $this->db->simple_query($sql_cmd);
                        exit($msg);
                    }

                } else {
                    exit('Error creating backup table (1) <<<>>> ' . $this->db->_error_message());
                }
            } else {
                exit('Error creating backup table, table does not exist <<<>>> ' . $this->db->_error_message());
            }
        }
        $backup_data = $this->db
            ->select('*')
            ->from($table)
            ->where('id', $id)
            ->get()
            ->row_array();
        if (!empty($backup_data)) {
            $this->db->insert($backup_table, $backup_data);
            fb_log('editor_258', $this->db->last_query());
            if ($this->db->affected_rows() < 1) {
                exit('ERROR BACKING UP DATA BEFORE UPDATE');
            }
            $count = $this->db
                ->select('backup_id')
                ->from($backup_table)
                ->where('id', $id)
                ->order_by('backup_id', 'desc')
                ->get()
                ->result_array();
            if (count($count) > $limit) {
                $x = 0;
                foreach ($count as $delete_id) {
                    $x++;
                    if ($x > $limit) {
                        $this->db->delete($backup_table, array('backup_id' => $delete_id['backup_id']));
                    }
                }
            }
        }
    }

    protected function _clean_special($text, $allow_html = TRUE)
    {
        $returnval = htmlentities($text, ENT_NOQUOTES, NULL, FALSE);
        if ($allow_html === TRUE) {
            $returnval = str_replace('&lt;', '<', $returnval);
            $returnval = str_replace('&gt;', '>', $returnval);
        }
        return $returnval;
    }

    function _format_url($url)
    {
        $url     = strtolower($url);
        $symbols = array(
            '"',
            "'",
            ' ',
            '!',
            '@',
            '#',
            '$',
            '%',
            '^',
            '&',
            '*',
            '(',
            ')',
            '_',
            '+',
            '=',
            '~',
            '`',
            '{',
            '}',
            '[',
            ']',
            '\\',
            '|',
            ':',
            ';',
            '<',
            '>',
            ',',
            '?',
            '/'
        );
        $url     = str_replace($symbols, '-', $url);
        return $url;
    }

    protected function _file_list($folder = NULL)
    {
        if (strpos($folder, '..') !== FALSE) {
            die('Access denied');
        }
        if (empty($folder)) {
            $folder = FCPATH;
        } else {
            $folder = FCPATH . $folder;
        }
        $ignore    = array(
            '.',
            '..',
            '.hg',
            '.git',
            '.htaccess',
            '.htpasswd',
            'cgi-bin'
        );
        $images    = array(
            'jpg',
            'jpeg',
            'png',
            'gif',
            'tif'
        );
        $ls        = scandir($folder);
        $returnVal = array();
        if (!empty($ls)) {
            $x = 0;
            foreach ($ls as $value) {
                $path = $folder . $value;
                if (!in_array($value, $ignore)) {
                    $exploded_path = explode("/", $path);
                    $exploded_file = explode(".", end($exploded_path));
                    if (is_file($path) && is_readable($path)) {
                        $returnVal[$x]['is_file']  = TRUE;
                        $returnVal[$x]['filename'] = end($exploded_path);
                        $returnVal[$x]['path']     = $path;
                        $returnVal[$x]['ext']      = end($exploded_file);
                        if (in_array($returnVal[$x]['ext'], $images) !== FALSE) {
                            $dim = getimagesize($path);
                            if ($dim !== FALSE) { // If there was no error reading the image
                                $dim['width']               = $dim[0];
                                $dim['height']              = $dim[1];
                                $returnVal[$x]['imagesize'] = $dim;
                            }
                        }
                        $returnVal[$x]['date'] = date('Y-m-d', filectime($path));
                        $returnVal[$x]['size'] = number_format(filesize($path));
                        $x++;
                    } elseif (is_dir($path) && is_readable($path)) {
                        $returnVal[$x]['is_file'] = FALSE;
                        $returnVal[$x]['name']    = end($exploded_path);
                        $returnVal[$x]['path']    = $path;
                    }
                    $x++;
                }
            }
        }
        return $returnVal;
    }
}
/* End of file editor.php */
