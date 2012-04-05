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
 * @copyright	Copyright (c) 2008 - 2012, Paul R. Dillinger. (http://prd.me/)
 * @license		http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @link		http://contentigniter.com
 * @since		Version 1.0
 * @filesource
 */
if (!defined('BASEPATH'))
	exit ('No direct script access allowed');

class Account extends CI_Controller {

	function __construct() {
		parent :: __construct();
		if (!$this->session->auth('logged_in')) {
			// Allows the account item to be used to login
			redirect('/login/account/');
		}
	}

	function index() {
		$profile = $this->session->get_user_data();
		$data['profile'] = $profile['profile'][0];
        $data['template'] = 'account';
		$this->load->view('core_view', $data);
	}

    function change_password($current_password=null, $new_password=null) {
        if(empty($current_password) && empty($new_password)){
            $current_password = $this->input->post('current_password');
            $new_password = $this->input->post('new_password');
        }
        if(!empty($current_password) && !empty($new_password)){
            if(strlen($new_password) < 9){
                show_error('Password less than nine characters long'); // This should be caught by the JS fild validation, but just in case
            }

            $salt = $this->config->item('salt');
            $username = $this->session->userdata('user_name');
            $error = FALSE;

            $query = $this->db->select('*')->from('CORE_Auth_USERS')->where('username', $username)->get()->result_array();

            if(count($query) == 1){
                if(!empty($query[0]['create_date'])){
                    $nonce = strtotime($query[0]['create_date']);
                    $user_id = $query[0]['id'];
                    $old_password = $query[0]['password'];
                }else{
                    show_error('UNABLE TO FETCH USER');
                }
            }else{
                return false; // The username was not found or was found multiple times
            }

            if($old_password == hash_hmac('sha512', $current_password . $nonce, $salt)){
                $password = hash_hmac('sha512', $new_password . $nonce, $salt);
                $user_data = array('password'=>$password);
                $success = $this->db->where('id', $user_id)->update('CORE_Auth_USERS', $user_data);
                if($success == 1){
                    $this->session->set_flashdata('success_msg', 'Account updated');
                }else{
                    $error = TRUE;
                }
            }else{
                $error = TRUE;
            }

            if($error){
                $this->session->set_flashdata('error_msg', 'Account was not successfully updated. Please verify that your current password is corrent.');
            }

        }
        redirect('/account/');
    }

}
/* End of file account.php */
