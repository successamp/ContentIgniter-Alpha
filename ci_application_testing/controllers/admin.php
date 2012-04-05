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

class Admin extends CI_Controller {

    function __construct() {
        parent :: __construct();
        $this->load->helper('cookie');
        if ($this->uri->segment(2) != 'clear_cache' && !$this->session->auth('admin')) {
            show_404();
        }
    }

    function index() {
        if (!$this->session->auth('admin')) {
            show_404();
        }else{
            redirect('/admin/home/');
        }
    }

    function set_production(){
        if ($this->session->auth('developer')) {
            $domain = str_replace('www.', '.', $_SERVER['SERVER_NAME']);
            if(!ENVIRONMENT_DEBUG){
                setcookie ('ENVIRONMENT', '', time() - 3600, '/', $domain); // Reset the cookie
                setcookie ('ENVIRONMENT_KEY', '', time() - 3600, '/', $domain); // Reset the cookie
            }else{
                setcookie ('ENVIRONMENT', 'production', time() + 86400, '/', $domain); // Set the cookie
            }
            redirect('/account/');
        }
        else {
            show_404();
        }
    }

    function set_development(){
        if ($this->session->auth('developer')) {
            $domain = str_replace('www.', '.', $_SERVER['SERVER_NAME']);
            $salt  = date('Fd');
            $secret  = hash('md4', $salt . DEVEL_SECRET);
            setcookie ('ENVIRONMENT', 'development', time() + 86400, '/', $domain); // Set the cookie
            if(!ENVIRONMENT_DEBUG){
                setcookie ('ENVIRONMENT_KEY', $secret, time() + 86400, '/', $domain); // Set the cookie
            }
            redirect('/account/');
        }
        else {
            show_404();
        }
    }

    function set_testing(){
        if ($this->session->auth('developer')) {
            $domain = str_replace('www.', '.', $_SERVER['SERVER_NAME']);
            $salt  = date('Fd');
            $secret  = hash('md4', $salt . TEST_SECRET);
            setcookie ('ENVIRONMENT', 'testing', time() + 86400, '/', $domain); // Set the cookie
            if(!ENVIRONMENT_DEBUG){
                setcookie ('ENVIRONMENT_KEY', $secret, time() + 86400, '/', $domain); // Set the cookie
            }
            redirect('/account/');
        }
        else {
            show_404();
        }
    }

    function firephp_off(){
        if ($this->session->auth('developer')) {
            $domain = str_replace('www.', '.', $_SERVER['SERVER_NAME']);
            $salt  = date('Fd');
            $secret  = hash('md4', $salt . DEBUG_SECRET);
            if(ENVIRONMENT === 'production'){
                setcookie ('ENVIRONMENT', 'production', time() + 86400, '/', $domain); // Set the cookie
            }
            setcookie ('ENVIRONMENT_KEY', $secret, time() + 86400, '/', $domain); // Set the cookie
            redirect('/account/');
        }
        else {
            show_404();
        }
    }

    function firephp_on(){
        if ($this->session->auth('developer')) {
            $domain = str_replace('www.', '.', $_SERVER['SERVER_NAME']);
            $salt  = date('Fd');
            $secret  = hash('md4', $salt . DEBUG_SECRET);
            if(ENVIRONMENT === 'production'){
                setcookie ('ENVIRONMENT', '', time() + 86400, '/', $domain); // Set the cookie
                $secret  = '';
            }
            elseif(ENVIRONMENT === 'testing'){
                setcookie ('ENVIRONMENT', 'testing', time() + 86400, '/', $domain); // Set the cookie
                $secret  = hash('md4', $salt . TEST_SECRET);
            }
            elseif(ENVIRONMENT === 'development'){
                setcookie ('ENVIRONMENT', 'development', time() + 86400, '/', $domain); // Set the cookie
                $secret  = hash('md4', $salt . DEVEL_SECRET);
            }
            setcookie ('ENVIRONMENT_KEY', $secret, time() + 86400, '/', $domain); // Set the cookie
            redirect('/account/');
        }
        else {
            show_404();
        }
    }

    function home() {
        $data['template'] = 'admin_home';
        $this->load->view('core_view', $data);
    }

    function info(){
        phpinfo();
    }

	function cms_update()
	{
		$this->load->library('migration');
		if ( ! $this->migration->current())
		{
			show_error($this->migration->error_string());
		}
		echo '<p>CMS is up to date</p>';
		exit();
	}

    function pages() {
        $data['page_admin_data'] = $this->db->select('*')->from('CORE_Pages')->get()->result_array();
        $data['template'] = 'admin_pages';
        $this->load->view('core_view', $data);
    }

    function pages_edit($page) {
        if (!empty ($page)) {
            $query = $this->db->select('*')->from('CORE_Pages')->where('id', $page)->get()->row_array();
            $HTML = $this->mu;
            echo $HTML->form('/admin/pages_edit_update/' . $page . '/')->id('ci_admin_edit_form')->addClass('simpleForm')->attr('style', 'width:600px;')->autoform('CORE_Pages', $query)->label('submit', ' ')->input('submit', 'submit', 'Submit')->close()->get();
        }
        exit;
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
                    $page_content .= $HTML->form('/admin/pages_content_update/' . $page . '/' . $data_row['data_table_name'] . '/' . $data_row['data_id'] . '/')->addClass('simpleForm')->attr('style', 'width:600px;')->autoform($data_row['data_table_name'], $query)->label('submit', ' ')->input('submit', 'submit', 'Submit')->close()->get();
                }
            }
            else {
                $page_content .= $HTML->form('/admin/pages_add_content_to_page/' . $page . '/')->addClass('simpleForm')->attr('style', 'width:600px;')->label('submit', ' ')->input('submit', 'submit', 'Create Content Record')->close()->get();
            }
        }else{
        }
        $data['page_content'] = $page_content;
        fb_log('page_content',$data['page_content']);
        //$data['template'] = array('_header','_blank_nav','pages_content','_blank_aside','_footer');
        $data['template'] = 'admin_pages_content';
        $this->load->view('core_view', $data);
    }

    function pages_edit_update($page) {
        $data = $_POST;
        unset ($data['submit']);
        if ($data['id'] = $page) {
            $this->backup_before_update('CORE_Pages', $page);
            $this->db->update('CORE_Pages', $data, "id = '$page'");
        }
        $this->clear_cache(TRUE);
        redirect('/admin/pages/');
    }

    function pages_content_update($page, $dtn, $dr) {
        $data = $_POST;
        unset ($data['submit']);
        if ($data['id'] = $dr) {
            $this->backup_before_update($dtn, $dr);
            $this->db->update($dtn, $data, "id = '$dr'");
        }
        $this->clear_cache(TRUE);
        redirect('/admin/pages/');
    }

    function pages_add_page() {
        $time = time();
        $page_data = array('url' => 'NEW-'.$time);
        $this->db->insert('CORE_Pages', $page_data);
        redirect('/admin/pages/');
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
        redirect('/admin/pages_content/' . $page . '/');
    }

    function pages_delete($page) {
        $data = $this->db->select('*')->from('CORE_Pages_DATA_XREF')->where('page_id', $page)->get()->result_array();
        if(!empty($data)){
            foreach($data as $delete_me){
                $test = $this->db->select('page_id')->from('CORE_Pages_DATA_XREF')->where('data_table_name', $delete_me['data_table_name'])->where('data_id', $delete_me['data_id'])->count_all_results();
                if ($test == 1){
                    //echo "Deleting " . $delete_me['data_table_name'] . " - " . $delete_me['data_id'] . "<br/>";
                    $this->backup_before_update($delete_me['data_table_name'], $delete_me['data_id']); // Backup before delete
                    $this->db->delete($delete_me['data_table_name'], array('id' => $delete_me['data_id']));
                }
                $this->db->delete('CORE_Pages_DATA_XREF', array('page_id' => $page, 'data_id' => $delete_me['data_id'], 'data_table_name' => $delete_me['data_table_name']));
            }
        }
        $this->backup_before_update('CORE_Pages', $page); // Backup before delete
        $this->db->delete('CORE_Pages', array('id' => $page));
        $this->clear_cache(TRUE);
        redirect('/admin/pages/');
    }

    function templates() {
        $this->load->model('Pagedata');
        $this->Pagedata->build_page('admin/templates');
        $data = $this->Pagedata->get_data();
        $this->load->view('master_view', $data);
    }

    function modules() {
        $this->load->model('Pagedata');
        $this->Pagedata->build_page('admin/modules');
        $data = $this->Pagedata->get_data();
        $this->load->view('master_view', $data);
    }

    function clear_cache($internal_request=FALSE) {
        if ($this->cache->clear_all()) {
            if($internal_request !== TRUE){
                echo "Cache Cleared!";
                exit;
            }
        }
        else {
            show_error('Cache not properly configured');
        }
    }

    function users_add_user($username=null, $password=null) {
        if(empty($username)){
            $username = $this->input->post('username', TRUE);
            $password = $this->input->post('password', TRUE);
        }
        if(!empty($username) && !empty($password)){
            //$this->load->library('encrypt');
            $salt = $this->config->item('salt');
            $nonce = time();
            $create_date = date('Y-m-d H:i:s',$nonce);
        	$password = hash_hmac('sha512', $password . $nonce, $salt);
            $user_data = array('username'=>$username, 'password'=>$password, 'create_date'=>$create_date);
            $success = $this->db->insert('CORE_Auth_USERS', $user_data);
            if($success == 1){
                $this->session->set_flashdata('success_msg', 'Account added');
            }else{
                $this->session->set_flashdata('error_msg', 'Account was not successfully added');
            }
        }
        redirect('/admin/home/');
    }

    function change_password($username=null, $password=null) {
        if(empty($username)){
            fb_info('username is empty checking POST');
            $username = $this->input->post('username');
            $password = $this->input->post('password');
        }
        if(!empty($username)){
            fb_info('username found, attempting to change password');
            $data['msg'] = $this->users_change_password($username, $password);
        }
        $data['page']['title'] = 'Cahnge Password';
        $data['template'] = 'admin_change_password';
        $data['users'] =  $this->db->select('username')->from('CORE_Auth_USERS')->get()->result_array();
   		$this->load->view('core_view', $data);
    }

    protected function users_change_password($username, $password) {
        if(!empty($username)){
          if(strlen($password) < 9){
              return array('class'=>'error','text'=>'Password less than nine characters long');
          }
            $query = $this->db->select('*')->from('CORE_Auth_USERS')->where('username', $username)->get()->result_array();
            if(count($query) == 1){
                if(!empty($query[0]['create_date'])){
                    $nonce = strtotime($query[0]['create_date']);
                    $user_id = $query[0]['id'];
                }else{
                    return array('class'=>'error','text'=>'Unable to fetch user');
                }
            }else{
                return array('class'=>'error','text'=>'Username was not found or was found multiple times');
            }
            $salt = $this->config->item('salt');
        	$password = hash_hmac('sha512', $password . $nonce, $salt);
            $user_data = array('password'=>$password);
            $success = $this->db->where('id', $user_id)->update('CORE_Auth_USERS', $user_data);
            if($success == 1){
                return array('class'=>'success','text'=>'Account updated');
            }else{
                return array('class'=>'error','text'=>'Account was not successfully updated - Verify username and password');
            }
        }else{
            return array('class'=>'error','text'=>'Username and password are required');
        }
    }

    function backup_before_update($table, $id) {
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
        $limit = empty($page_config['db_backup_items']) ? 3 : $page_config['db_backup_items']; // Default to keepign up to three backups for any row unless otherwise specified
        $backup_table = 'BACKUP_'.$table;
        if($this->db->table_exists($backup_table)){
            $backup_data = $this->db->select('*')->from($table)->where('id', $id)->get()->row_array();
            if(!empty($backup_data)){
                $this->db->insert($backup_table, $backup_data);
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
        }else{
            fb_error('can not backup table ('.$backup_table.') as a backup table does not exist');
        }
    }

    function dump_live_db_to_development() {
        $prod = $this->load->database('defualt', TRUE);
        $devel = $this->load->database('development', TRUE);
        // Need to figure out a good way to drop all tables from $devel and copy over all tables from $prod
    }
}
/* End of file admin.php */
