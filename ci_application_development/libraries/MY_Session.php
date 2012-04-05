<?php
/**
 * SEAuth
 *
 * A simple auth module for CodeIgniter 2.0.3 or higher
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
 * @package		SEAuth
 * @author		Paul Dillinger
 * @copyright	Copyright (c) 2008 - 2012, Paul R. Dillinger. (http://prd.me/)
 * @license		http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @link		http://codeigniter.com/seauth
 * @since		Version 1.0
 * @filesource
 */

/*
| -------------------------------------------------------------------
| SEAuth - Session Extension for Authorization
| -------------------------------------------------------------------
| This is intended as a simple way to integrate session authorization
| in to any CodeIgniter application.
| $this->session->login($username,$password);
| $this->session->logout();
| $this->session->auth($privilege_type_needed);
| -------------------------------------------------------------------
*/
class MY_Session extends CI_Session {
	var $privilege = array();

	function MY_Session() {
		parent :: __construct();
	}

	///////////////////////////////////////////////////////////////////////////////
	// Validates username and password info then begins the session              //
	///////////////////////////////////////////////////////////////////////////////
	function login($username, $password) {
		if ($this->CI->config->item('sess_encrypt_cookie') == TRUE AND $this->CI->config->item('sess_use_database') == TRUE AND $this->CI->config->item('sess_table_name') != '') {
			// Veryfiy username and password
			$username_max_length = 64;
			$password_max_length = 32;
			if (strlen($username) > $username_max_length || strlen($password) > $password_max_length) {
				// Call security
                $firephp = FirePHP::getInstance(true);
				$firephp->log('Username or password was rejected due to excessive length');
				return FALSE;
			}

			//Check to see if user exists and get the nonce
            $this->CI->load->database();
            $query = $this->CI->db->select('create_date')->from('CORE_Auth_USERS')->where('username', $username)->get()->result_array();
            if(count($query) == 1){
                if(!empty($query[0]['create_date'])){
                    $nonce = strtotime($query[0]['create_date']);
                }else{
                  show_error('UNABLE TO FETCH ALL USER DATA');
                }
            }else{
                return false; // The username was not found or was found multiple times
            }

            //User exists, now let's check the password
            $salt = $this->CI->config->item('salt');
			$secure_password = hash_hmac('sha512', $password . $nonce, $salt);
			$query = $this->CI->db->select('*')->from('CORE_Auth_USERS')->where('username', $username)->where('password', $secure_password)->get();
			if ($query->num_rows() == 1) {
				foreach ($query->result_array() as $row) {
					$userdata = array();
					$userdata['user_name'] = $username;
					$userdata['user_id'] = $row['id'];
					$userdata['logged_in'] = TRUE;
					$permissions = $this->CI->db->select('name')->from('CORE_Auth_GROUPS')->join('CORE_Auth_XREF', 'CORE_Auth_GROUPS.id = CORE_Auth_XREF.group_id')->where('CORE_Auth_XREF.user_id', $row['id'])->get();
					foreach ($permissions->result_array() as $permission) {
						$userdata[$permission['name']] = 1;
					}
					$this->set_userdata($userdata);
					return true;  // Success - User logged in
				}
			}
			else {
				return false; // Failure, invalid username or password
			}
		}
		else {
			show_error('ENCRYPTION AND DATABASE MUST BE ENABLED - PLEASE READ /APPLICATION/CONFIG/AUTH.PHP');
			return false;
		}
	}

	///////////////////////////////////////////////////////////////////////////////
	// Removes the session authorization and user name from the client           //
	///////////////////////////////////////////////////////////////////////////////
	function logout() {
		if ($this->userdata("logged_in")) {
			$this->sess_destroy();
		}
	}

	///////////////////////////////////////////////////////////////////////////////
	// Checks to see if the user is logged in and if they have access to the area//
	///////////////////////////////////////////////////////////////////////////////
	function auth($access) {
		if ($this->CI->config->item('sess_encrypt_cookie') == TRUE AND $this->CI->config->item('sess_use_database') == TRUE AND $this->CI->config->item('sess_table_name') != '') {
			if ($this->userdata("logged_in")) {
				if ($this->userdata($access) == TRUE) {
					return TRUE;
				}
				else {
					return FALSE;
				}
			}
		}
		else {
			show_error('ENCRYPTION AND DATABASE MUST BE ENABLED');
			return FALSE;
		}
	}

	///////////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////////
	// Checks to see if the user is logged in and if they have access to the area//
	///////////////////////////////////////////////////////////////////////////////
	function get_user_data() {
		if ($this->CI->config->item('sess_encrypt_cookie') == TRUE AND $this->CI->config->item('sess_use_database') == TRUE AND $this->CI->config->item('sess_table_name') != '') {
			if ($this->userdata("logged_in")) {
				$user_tables = $this->CI->db->select('table_name, handle, row')->from('CORE_Auth_DATA_XREF')->where('user_id', $this->userdata('user_id'))->get();
				foreach ($user_tables->result_array() as $item) {
					if (!isset ($table_list[$item['table_name']])) {
						$table_list[$item['table_name']] = array();
						$handle_list[$item['table_name']] = $item['handle'];
					}
					array_push($table_list[$item['table_name']], $item['row']);
				}
				foreach ($table_list as $table => $row) {
					$this->CI->db->select('*')->from($table);
					foreach ($row as $key => $value) {
						$this->CI->db->or_where('id', $value);
					}
					$query = $this->CI->db->get();
					$returnVal[$handle_list[$table]] = $query->result_array();
				}
			}
			return $returnVal;
		}
		else {
			show_error('ENCRYPTION AND DATABASE MUST BE ENABLED');
			return FALSE;
		}
	}
	///////////////////////////////////////////////////////////////////////////////

}