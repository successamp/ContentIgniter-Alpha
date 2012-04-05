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

class Editor extends CI_Model {

	public function __construct() {
		parent :: __construct();
		$this->benchmark->mark('Editor_model_Initialize_start');

		$this->benchmark->mark('Editor_model_Initialize_end');
	}

    function clear_cache() {
        if (!$this->cache->clear_all()) {
            show_error('Cache not properly configured');
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

    function clean_special($text, $allow_html=TRUE) {
        $returnval = htmlentities($text, ENT_NOQUOTES, null, FALSE);
        if($allow_html===TRUE){
            $returnval = str_replace('&lt;','<',$returnval);
            $returnval = str_replace('&gt;','>',$returnval);
        }
        return $returnval;
    }
    /*
    function view_assets($path1=null,$path2=null,$path3=null,$path4=null,$path5=null,$path6=null,$path7=null,$path8=null,$path9=null,$path10=null,$path11=null,$path12=null){
        $x = 0;
        $path = null;
        while($x < 12){
          $x++;
          $test_path = 'path'.$x;
          if(!empty($$test_path)){
           $path .= $$test_path . '/';
          }else{
            break;
          }
        }

        $dirs = $this->_file_list($path);
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() - 3600));
        header('Content-type: application/json');
        echo(json_encode($dirs));
        exit;
    }
    */

    function file_list($folder=null) {
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