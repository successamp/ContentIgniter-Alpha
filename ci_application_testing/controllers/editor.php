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

class Editor extends CI_Controller {

	function __construct() {
		parent :: __construct();
        $this->load->helper('cookie');
        if (!$this->session->auth('editor')) {
            show_404();
        }
        if ($this->cache->configured) {
			$this->page_config = $this->cache->get('CORE_Pages_CONFIG');
		}
		if (empty ($this->page_config)) {
			$query = $this->db->select('*')->from('CORE_Pages_CONFIG')->get();
			foreach ($query->result_array() as $item) {
				$this->page_config[$item['key']] = $item['value'];
			}
			if ($this->cache->configured) {
				$this->cache->set('CORE_Pages_CONFIG', $this->page_config);
			}
		}

    }

    function index() {
        if (!$this->session->auth('editor')) {
            show_404();
        }else{
            redirect('/editor/home/');
        }
    }

    function home() {
        $data['template'] = 'editor_home';
        $this->load->view('core_view', $data);
    }

    function pages() {
        $data['page_admin_data'] = $this->db->select('*')->from('CORE_Pages')->order_by('id','desc')->get()->result_array();
        $data['template'] = 'editor_pages';
        $this->load->view('core_view', $data);
    }

    function pages_edit($page) {
        $page_content = '';
        if (!empty ($page)) {
            $query = $this->db->select('*')->from('CORE_Pages')->where('id', $page)->get()->row_array();
            if (!empty ($query)) {
                $HTML = $this->mu;
                $page_content .= $HTML->form('/editor/pages_edit_update/' . $page . '/')->id('ci_admin_edit_form')->addClass('simpleForm')->attr('style', 'width:600px;')->autoform('CORE_Pages', $query)->label('submit', ' ')->input('submit', 'submit', 'Submit')->close()->get();
            }
        }
        $data['page_content'] = $page_content;
        fb_log('page_content',$data['page_content']);
        $data['template'] = 'editor_pages_edit';

        if(empty($this->page_config['rootdomain'])){
          $domainurl = explode('.', $_SERVER['SERVER_NAME']);
          $count = count($domainurl);
          switch($count){
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
        }else{
          if(empty($this->page_config['no_subdomain'])){
            $data['display_rootdomain'] = '.' . $this->page_config['rootdomain'] . '/';
          }else{
            $data['display_rootdomain'] = $this->page_config['rootdomain'] . '/';
          }
        }
        $this->load->view('core_view', $data);
    }

    function pages_content($page) {
        $page_content = '';
        if (!empty ($page)) {
            $page_data = $this->db->select('*')->from('CORE_Pages_DATA_XREF')->where('page_id', $page)->get()->result_array();
            $HTML = $this->mu;
            if (!empty ($page_data)) {
                foreach ($page_data as $data_row) {
                    $query = $this->db->select('*')->from($data_row['data_table_name'])->where('id', $data_row['data_id'])->get()->row_array();
                    $page_content .= empty ($Content) ? '' : '<hr/>';
                    $page_content .= $HTML->form('/editor/pages_content_update/' . $page . '/' . $data_row['data_table_name'] . '/' . $data_row['data_id'] . '/')->addClass('simpleForm')->attr('style', 'width:600px;')->autoform($data_row['data_table_name'], $query)->label('submit', ' ')->input('submit', 'submit', 'Submit')->close()->get();
                }
            }
            else {
                $page_content .= $HTML->form('/editor/pages_add_content_to_page/' . $page . '/')->addClass('simpleForm')->attr('style', 'width:600px;')->label('submit', ' ')->input('submit', 'submit', 'Create Content Record')->close()->get();
            }
        }else{
        }
        $data['page_content'] = $page_content;
        fb_log('page_content',$data['page_content']);
        //$data['template'] = array('_header','_blank_nav','pages_content','_blank_aside','_footer');
        $data['template'] = 'editor_pages_content';
        $this->load->view('core_view', $data);
    }

    function pages_edit_update($page) {
        $data = $_POST;
        unset ($data['submit']);
		$data['url'] = $this->_format_url($data['url']);
        $data['title'] = $this->_clean_special($data['title']);
        $data['description'] = $this->_clean_special($data['description']);
        if ($data['id'] = $page) {
            $this->_backup_before_update('CORE_Pages', $page);
            $this->db->update('CORE_Pages', $data, "id = '$page'");
        }
        $this->clear_cache(TRUE);
        redirect('/editor/pages/');
    }

    function pages_content_update($page, $dtn, $dr) {
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

    function pages_add_page() {
        $time = time();
        $page_data = array('url' => 'NEW-'.$time);
        $this->db->insert('CORE_Pages', $page_data);
        redirect('/editor/pages/');
    }

    function pages_add_content_to_page($page) {
        $test = $this->db->select('*')->from('CORE_Pages')->where('id', $page)->get()->row_array();
        if (!empty ($page) && !empty ($test['id']) && $page == $test['id']) {
            $content_data = array('header' => '', 'body' => '');
            $this->db->insert('DATA_CORE_Pages_CONTENT', $content_data);
            $row_id = $this->db->insert_id();
            if (!empty ($row_id)) {
                $this->db->set('page_id', $page)->set('data_table_name', 'DATA_CORE_Pages_CONTENT')->set('data_id', $row_id)->set('attribute_name', 'Content')->insert('CORE_Pages_DATA_XREF');
            }
        }
        redirect('/editor/pages_content/' . $page . '/');
    }

    function pages_delete($page) {
        $data = $this->db->select('*')->from('CORE_Pages_DATA_XREF')->where('page_id', $page)->get()->result_array();
        if(!empty($data)){
            foreach($data as $delete_me){
                $test = $this->db->select('page_id')->from('CORE_Pages_DATA_XREF')->where('data_table_name', $delete_me['data_table_name'])->where('data_id', $delete_me['data_id'])->count_all_results();
                if ($test == 1){
                    //echo "Deleting " . $delete_me['data_table_name'] . " - " . $delete_me['data_id'] . "<br/>";
                    $this->_backup_before_update($delete_me['data_table_name'], $delete_me['data_id']); // Backup before delete
                    $this->db->delete($delete_me['data_table_name'], array('id' => $delete_me['data_id']));
                }
                $this->db->delete('CORE_Pages_DATA_XREF', array('page_id' => $page, 'data_id' => $delete_me['data_id'], 'data_table_name' => $delete_me['data_table_name']));
            }
        }
        $this->_backup_before_update('CORE_Pages', $page); // Backup before delete
        $this->db->delete('CORE_Pages', array('id' => $page));
        $this->clear_cache(TRUE);
        redirect('/editor/pages/');
    }

    function news() {
        $data['news_admin_data'] = $this->db->select('*')->from('CORE_News')->order_by('id','desc')->get()->result_array();
        $data['template'] = 'editor_news';
        $this->load->view('core_view', $data);
    }

    function news_edit($page) {
        $page_content = '';
        if (!empty ($page)) {
            $query = $this->db->select('*')->from('CORE_News')->where('id', $page)->get()->row_array();
            if (!empty ($query)) {
                $HTML = $this->mu;
                $page_content .= $HTML->form('/editor/news_edit_update/' . $page . '/')->id('ci_admin_edit_form')->addClass('simpleForm')->attr('style', 'width:600px;')->autoform('CORE_News', $query)->label('submit', ' ')->input('submit', 'submit', 'Submit')->close()->get();
            }
        }
        $data['page_content'] = $page_content;
        $data['template'] = 'editor_news_edit';
        if(empty($this->page_config['rootdomain'])){
          $domainurl = explode('.', $_SERVER['SERVER_NAME']);
          $count = count($domainurl);
          switch($count){
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
        }else{
          if(empty($this->page_config['no_subdomain'])){
            $data['display_rootdomain'] = '.' . $this->page_config['rootdomain'] . '/';
          }else{
            $data['display_rootdomain'] = $this->page_config['rootdomain'] . '/';
          }
        }
        $this->load->view('core_view', $data);
    }

    function news_edit_update($page) {
        $data = $_POST;
        unset ($data['submit']);
		$data['url'] = $this->_format_url($data['url']);
        $data['title'] = $this->_clean_special($data['title']);
        $data['description'] = $this->_clean_special($data['description']);
        $data['header'] = $this->_clean_special($data['header']);
        if ($data['id'] = $page) {
            $this->_backup_before_update('CORE_News', $page);
            $this->db->update('CORE_News', $data, "id = '$page'");
        }
        $this->clear_cache(TRUE);
        redirect('/editor/news/');
    }

    function news_add() {
        $time = time();
        $page_data = array('url' => 'NEW-'.$time);
        $this->db->insert('CORE_News', $page_data);
        redirect('/editor/news/');
    }

    function news_delete($page) {
        $this->_backup_before_update('CORE_News', $page); // Backup before delete
        $this->db->delete('CORE_News', array('id' => $page));
        $this->clear_cache(TRUE);
        redirect('/editor/news/');
    }

    function subdomains() {
        $data['page_admin_data'] = $this->db->select('*')->from('DATA_subdomain_PROFILE')->get()->result_array();
        $data['template'] = 'editor_subdomains';
        $this->load->view('core_view', $data);
    }

    function subdomain_edit($id) {
        $data['page_content'] = '';
        if (!empty ($id)) {
            $query = $this->db->select('*')->from('DATA_subdomain_PROFILE')->where('id', $id)->get()->row_array();
            if (!empty ($query)) {
                $HTML = $this->mu;
                $data['page_content'] = $HTML->form('/editor/subdomain_edit_update/')
                        ->id('ci_admin_edit_form')
                        ->addClass('simpleForm')
                        ->attr('style', 'width:600px;')
                        ->autoform('DATA_subdomain_PROFILE', $query)
                        ->input('old_id', 'hidden', $query['id'])
                        ->label('submit', ' ')->input('submit', 'submit', 'Submit')
                        ->close()
                        ->get();
            }
        }
        $data['template'] = 'editor_subdomains_edit';
        $this->load->view('core_view', $data);
    }

    function subdomain_edit_update() {
        $data = $_POST;
        $data_old_id = $data['old_id'];
        unset ($data['submit']);
        unset ($data['old_id']);
        //Allow html but encode special characters
        $data['name'] = $this->_clean_special($data['name']);
        $data['chapter_name'] = $this->_clean_special($data['chapter_name']);
        $data['legal_name'] = $this->_clean_special($data['legal_name']);
        $data['profile'] = $this->_clean_special($data['profile']);
        $data['short_profile'] = $this->_clean_special($data['short_profile']);

        if (!empty($data['id']) && !empty($data_old_id)) {
            $this->_backup_before_update('DATA_subdomain_PROFILE', $data_old_id);
            $this->db->update('DATA_subdomain_PROFILE', $data, "id = '$data_old_id'");
        }
        $this->clear_cache(TRUE);
        redirect('/editor/subdomains/');
    }

    function subdomain_add_subdomain() {
        $time = time();
        $page_data = array('id' => $time);
        $this->db->insert('DATA_subdomain_PROFILE', $page_data);
        redirect('/editor/subdomains/');
    }

    function subdomain_delete($id) {
        $this->_backup_before_update('DATA_subdomain_PROFILE', $id); // Backup before delete
        $this->db->delete('DATA_subdomain_PROFILE', array('id' => $id));
        $this->clear_cache(TRUE);
        redirect('/editor/subdomains/');
    }

    function clear_cache($internal_request=FALSE) {
        if ($this->cache->clear_all()) {
            if($internal_request !== TRUE){
                echo 'Cache Cleared! You may need to re-load (CTRL+F5) any previously viewed pages to see changes.  <a href="/account/">Account Home</a>';
                exit;
            }
        }
        else {
            show_error('Cache not properly configured');
        }
    }

    protected function _backup_before_update($table, $id) {
		if ($this->cache->configured) {
			$page_config = $this->cache->get('CORE_Pages_CONFIG');
		}
		if (empty ($page_config)) {
			$query = $this->db->select('*')->from('CORE_Pages_CONFIG')->get();
			foreach ($query->result_array() as $item) {
				$page_config[$item['key']] = $item['value'];
			}
			if ($this->cache->configured) {
				$this->cache->set('CORE_Pages_CONFIG', $page_config);
			}
		}
        $limit = empty($page_config['db_backup_items']) ? 3 : $page_config['db_backup_items']; // Default to keeping up to three backups for any row unless otherwise specified
        $backup_table = 'BACKUP_'.$table;
        if(!$this->db->table_exists($backup_table)){
            // Create a backup table
            if($this->db->table_exists($table)){
                $sql_cmd = 'CREATE TABLE ' . $this->db->dbprefix . $backup_table . ' SELECT * FROM ' . $this->db->dbprefix . $table . ' WHERE 0';
                if($this->db->simple_query($sql_cmd)){
                    $sql_cmd = 'ALTER TABLE ' . $this->db->dbprefix . $backup_table . ' ADD `backup_id` INT( 11 ) PRIMARY KEY AUTO_INCREMENT NOT NULL FIRST';
                    if(!$this->db->simple_query($sql_cmd)){
                        $msg = 'Error creating backup table (4) <<<>>> ' . $sql_cmd . ' <<<>>> ' . $this->db->_error_message();
                        $sql_cmd = 'DROP TABLE ' . $this->db->dbprefix . $backup_table;
                        $this->db->simple_query($sql_cmd);
                        exit($msg);
                    }

                }else{
                    exit('Error creating backup table (1) <<<>>> ' . $this->db->_error_message());
                }
            }else{
                exit('Error creating backup table, table does not exist <<<>>> ' . $this->db->_error_message());
            }
        }
        $backup_data = $this->db->select('*')->from($table)->where('id', $id)->get()->row_array();
        if(!empty($backup_data)){
            $this->db->insert($backup_table, $backup_data);
            fb_log('editor_258', $this->db->last_query());
            if($this->db->affected_rows() < 1){
                exit('ERROR BACKING UP DATA BEFORE UPDATE');
            }
            $count = $this->db->select('backup_id')->from($backup_table)->where('id', $id)->order_by('backup_id','desc')->get()->result_array();
            if(count($count) > $limit){
                $x = 0;
                foreach($count as $delete_id){
                    $x++;
                    if ($x > $limit){
                        $this->db->delete($backup_table, array('backup_id'=>$delete_id['backup_id']));
                    }
                }
            }
        }
    }

    protected function _clean_special($text, $allow_html=TRUE) {
        $returnval = htmlentities($text, ENT_NOQUOTES, null, FALSE);
        if($allow_html===TRUE){
            $returnval = str_replace('&lt;','<',$returnval);
            $returnval = str_replace('&gt;','>',$returnval);
        }
        return $returnval;
    }

	function _format_url($url) {
		$url = strtolower($url);
		$symbols = array('"',"'",' ','!','@','#','$','%','^','&','*','(',')','_','+','=','~','`','{','}','[',']','\\','|',':',';','<','>',',','?','/');
		$url = str_replace($symbols, '-', $url);
		return $url;
	}

    protected function _file_list($folder=null) {
        if(strpos($folder,'..') !== FALSE){
          die('Access denied');
        }
        if(empty($folder)){
          $folder = FCPATH;
        }else{
          $folder = FCPATH . $folder;
        }
        $ignore = array('.','..','.hg','.git','.htaccess','.htpasswd','cgi-bin');
        $images = array('jpg','jpeg','png','gif','tif');
        $ls = scandir($folder);
        $returnVal = array();
        if(!empty($ls)){
            $x = 0;
            foreach($ls as $value) {
                $path = $folder . $value;
                if(!in_array($value, $ignore)) {
                    $exploded_path = explode("/",$path);
                    $exploded_file = explode(".", end($exploded_path));
                    if(is_file($path) && is_readable($path)) {
                        $returnVal[$x]['is_file'] = TRUE;
                        $returnVal[$x]['filename'] = end($exploded_path);
                        $returnVal[$x]['path'] = $path;
                        $returnVal[$x]['ext'] = end($exploded_file);
                        if(in_array($returnVal[$x]['ext'], $images) !== FALSE){
                            $dim = getimagesize($path);
                            if($dim !== FALSE){ // If there was no error reading the image
                                $dim['width'] = $dim[0];
                                $dim['height'] = $dim[1];
                                $returnVal[$x]['imagesize'] = $dim;
                            }
                        }
                        $returnVal[$x]['date'] = date('Y-m-d',filectime($path));
                        $returnVal[$x]['size'] = number_format(filesize($path));
                        $x++;
                    }elseif(is_dir($path) && is_readable($path)) {
                        $returnVal[$x]['is_file'] = FALSE;
                        $returnVal[$x]['name'] = end($exploded_path);
                        $returnVal[$x]['path'] = $path;
                    }
                    $x++;
                }
            }
        }
        return $returnVal;
    }
}
/* End of file editor.php */
