<?
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

class Cache {
	var $CI;
	var $configured;
    var $force_parse_template; // Allows forcing Pagedata to lookup, parse templates and not to cache them, wi
	var $path;
	var $uri;
	var $file;
	var $user_file = '';
	var $apc_available;
	var $file_available;
	var $per_user_cache;
    var $apc_prefix;

	function __construct() {
		log_message('debug', "Cache Class Initialized");
		$this->CI = & get_instance();
		$this->CI->benchmark->mark('Cache_Initialize_start');
		$this->path = $this->CI->config->item('cache_path');
		if (!is_writable($this->path)) {
			show_error("Error writing to your cache directory.  File caching is required.");
		}
		$this->apc_available = function_exists('apc_store');
		$uri_id = 'CORE_CACHE_URI_' . $this->CI->uri->uri_string();
		$this->per_user_cache = FALSE;
		if ($this->apc_available) {
//                              $this->apc_prefix = str_ireplace('www.', '', $_SERVER['SERVER_NAME']) . '.';
                              $this->apc_prefix = $_SERVER['SERVER_NAME'];
			$this->uri = apc_fetch($this->apc_prefix . $uri_id);
		}
		if (empty ($this->uri)) {
			$segs[0] = ($this->CI->uri->segment(1) == FALSE) ? 'default' : $this->CI->uri->segment(1);
			$segs[1] = ($this->CI->uri->segment(2) == FALSE) ? 'index' : $this->CI->uri->segment(2);
			if ($this->CI->uri->segment(3)) {
				$x = 3;
				while ($x < $this->CI->uri->total_rsegments() || $x == $this->CI->uri->total_rsegments()) {
					$y = $x - 1;
					$segs[$y] = $this->CI->uri->segment($x);
					$x++;
				}
			}
			$cache_uri = "";
			// Check to see if the application cache path exists
			foreach ($segs as $dir) {
				if (is_writable($this->path . $cache_uri . $dir . '/')) {
					$cache_uri .= $dir . '/';
				}
				else {
					if (mkdir($this->path . $cache_uri . $dir . '/', 0777)) {
						$cache_uri .= $dir . '/';
					}
					else {
						show_error("Error writing to your cache directory");
					}
				}
			}
			if (is_writable($this->path . $cache_uri)) {
				$this->uri = $cache_uri;
			}
			if ($this->apc_available) {
				$this->uri = $this->apc_prefix . $cache_uri;
				apc_store($this->apc_prefix . $uri_id, $cache_uri);
			}
		}
		if (!$this->CI->session->userdata('logged_in')) {
			$this->file = "visitor.inc";
		}
		else {
			$this->file = "visitor.inc";
			$this->user_file = $this->CI->session->userdata('user_name') . ".inc";
		}
		if (!empty ($this->path) && !empty ($this->uri) && !empty ($this->file)) {
			$this->file_available = TRUE;
		}
		if ($this->apc_available || $this->file_available) {
			$this->configured = ENVIRONMENT !== 'development' ? TRUE : FALSE; // Disable all cache calls under development (but allowed for test)
		}
		$this->CI->benchmark->mark('Cache_Initialize_end');
	}

	function per_user_cache_active() {
		if ($this->CI->session->userdata('logged_in')) {
			$this->per_user_cache = TRUE;
			$this->file = $this->user_file;
		}
	}

	function set($id, $data, $tags = NULL, $lifetime = NULL) {
		if ($this->apc_available) {
			$this->apc_set($id, $data, $tags, $lifetime);
		}
		elseif ($this->file_available) {
			$this->file_set($id, $data, $tags, $lifetime);
		}
		else {
			show_error('No cache configured');
		}
	}

	function get($id) {
		if ($this->apc_available) {
			return $this->apc_get($id);
		}
		elseif ($this->file_available) {
			return $this->file_get($id);
		}
		else {
			show_error('No cache configured');
		}
	}

	function find($tag) {
		//
	}

	function delete($id) {
		if ($this->apc_available) {
			$this->apc_delete($id);
		}
		elseif ($this->file_available) {
			$this->file_delete($id);
		}
		else {
			show_error('No cache configured');
		}
	}

	function apc_get($id) {
		if ($this->apc_available) {
            $cache = apc_fetch($this->apc_prefix . $id);
			return $cache;
		}
		else {
			show_error('APC not configured');
		}
	}

	function apc_set($id, $data, $tags = NULL, $lifetime = NULL) {
		if ($this->apc_available) {
            $lifetime = empty($lifetime) ? 0 : $lifetime * 60; // We store lifetime in minutes, apc needs it in seconds
			$success = apc_store($this->apc_prefix . $id, $data, $lifetime);
			if ($success && !empty ($tags)) {
				////////////////////////////////////////////////////////////////////////////////
				// Need to store TAG functionality
				////////////////////////////////////////////////////////////////////////////////
			}
			return $success;
		}
		else {
			show_error('APC not configured');
		}
	}

	function apc_delete($id) {
		if ($this->apc_available) {
			return apc_delete($id);
		}
		else {
			show_error('APC not configured');
		}
	}

	function file_get($id) {
		$data = NULL;
		if (is_file($this->path . $id)) {
			$file = fopen($this->path . $id, "r") or die("Your cache file can not be written to.");
			$file_size = filesize($this->template_cache_path);
			$cache_data = fread($file, $file_size);
			fclose($file);
			eval ('$data = ' . $cache_data . ';');
		}
		return $data;
	}

	function file_set($id, $data, $tags = NULL, $lifetime = NULL) {
		$cache_file = fopen($this->path . $id, "w") or die("Your cache file can not be written to.");
		$status = fwrite($cache_file, var_export($data, TRUE));
		fclose($cache_file);
		return $status;
	}

	function file_delete($id) {
	}

	function tag_delete($tag) {
		$items = $this->find($tag);
		if (!empty ($items)) {
			foreach ($items as $key) {
				if ($this->apc_available) {
					apc_delete($key);
				}
				else {
					file_delete($key);
				}
			}
		}
	}

	function clear_all() {
		$clean = FALSE;
		if ($this->file_available) {
			$this->clear_file();
			$clean = TRUE;
		}
		if ($this->apc_available) {
			$this->clear_apc();
			$clean = TRUE;
		}
		return $clean;
	}

	function clear_apc() {
		if ($this->apc_available) {
			apc_clear_cache('user');
			apc_clear_cache();
		}
	}

	function clear_file() {
		if ($this->file_available) {
			$this->CI->load->helper('file');
			delete_files($this->path, TRUE);
		}
	}

}