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

class MY_Output extends CI_Output
{

    function __construct()
    {
        parent :: __construct();
    }

    // --------------------------------------------------------------------
    /**
     * Update/serve a cached file
     *
     * @access    public
     * @return    void
     */
    function _display_cache(&$CFG, &$URI)
    {
        if (ENVIRONMENT != 'production') {
            return FALSE; // only show cache in production
        }
        if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') {
            return FALSE; // SSL Pages aren't cached
        }
        $cache_path = ($CFG->item('cache_path') == '') ? BASEPATH . 'cache/' : $CFG->item('cache_path');
        if (!is_dir($cache_path) OR !is_really_writable($cache_path)) {
            return FALSE;
        }
        // Build the file path.  The file name is an MD5 hash of the full URI
//		$uri = $CFG->item('base_url') . $CFG->item('index_page') . $URI->uri_string;
// Base_RUL was not workign with sub domains
        $uri      = $_SERVER['SERVER_NAME'] . $CFG->item('index_page') . $URI->uri_string;
        $filepath = $cache_path . md5($uri);
        if (!@ file_exists($filepath)) {
            return FALSE;
        }
        if (!$fp = @ fopen($filepath, FOPEN_READ)) {
            return FALSE;
        }
        flock($fp, LOCK_SH);
        $cache = '';
        if (filesize($filepath) > 0) {
            $cache = fread($fp, filesize($filepath));
        }
        flock($fp, LOCK_UN);
        fclose($fp);
        // Strip out the embedded timestamp
        if (!preg_match("/(\d+TS--->)/", $cache, $match)) {
            return FALSE;
        }
        $expire_time = trim(str_replace('TS--->', '', $match['1']));
        // Has the file expired? If so we'll delete it.
        if (time() >= $expire_time) {
            @ unlink($filepath);
            log_message('debug', "Cache file has expired. File deleted");
            return FALSE;
        }
        //------------------------------------------------------------------------------------------------------------------
        // Create and/or modify this file to preserve upgradeability
        $contentigniter_hook = APPPATH . 'hooks/contentigniter_hook_display_cache.php';
        //------------------------------------------------------------------------------------------------------------------

        if (is_readable($contentigniter_hook)) {
            include($contentigniter_hook);
        }

        // Display the cache
        $tomorrow = time() + 86400;
        // Time 24 hours from now.
        $expire_time > $tomorrow ? $output_header_cache = $tomorrow : $output_header_cache = $expire_time;
        $this->set_header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', $output_header_cache));
        $this->_display(str_replace($match['0'], '', $cache));
        log_message('debug', "Cache file is current. Sending it to browser.");
        return TRUE;
    }

    /**
     * Write a Cache File
     *
     * @access    public
     * @return    void
     */
    function _write_cache($output)
    {
        $CI   =& get_instance();
        $path = $CI->config->item('cache_path');

        $cache_path = ($path == '') ? BASEPATH . 'cache/' : $path;

        if (!is_dir($cache_path) OR !is_really_writable($cache_path)) {
            return;
        }

//		$uri =	$CI->config->item('base_url').
// base_url was not working with subdomains
        $uri = $_SERVER['SERVER_NAME'] . $CI->config->item('index_page') . $CI->uri->uri_string();

        $cache_path .= md5($uri);

        if (!$fp = @fopen($cache_path, FOPEN_WRITE_CREATE_DESTRUCTIVE)) {
            log_message('error', "Unable to write cache file: " . $cache_path);
            return;
        }

        $expire = time() + ($this->cache_expiration * 60);

        if (flock($fp, LOCK_EX)) {
            /*
            $this->CI->load->helper('compressor');
            $compressed_output = Minify_HTML::minify($output);
			fwrite($fp, $expire.'TS--->'.$compressed_output);
            */
            fwrite($fp, $expire . 'TS--->' . $output);
            flock($fp, LOCK_UN);
        } else {
            log_message('error', "Unable to secure a file lock for file at: " . $cache_path);
            return;
        }
        fclose($fp);
        @chmod($cache_path, DIR_WRITE_MODE);

        log_message('debug', "Cache file written: " . $cache_path);
    }
}
// END MY_Output Class
/* End of file MY_Output.php */
