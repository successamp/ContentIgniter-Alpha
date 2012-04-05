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

class Dbimport extends CI_Model {

	//Example:
	// The file must be located in the Application/db_import/ directory
	//$this->load->model('Dbimport');
	//$this->Dbimport->get_db_from_file('cms_contentIgniter.sql');
	function __construct() {
		parent :: __construct();
	}

	function get_db_from_file($dbfile, $dbname = NULL, $drop = FALSE) {
		$file = APPPATH . 'db_import/' . $dbfile;
		if (file_exists($file)) {
			include (APPPATH . 'config/database' . EXT);
			if (empty ($dbname)) {
				$dbname = $active_group;
			}
			if ($drop) {
				$MyDB = $this->load->database($dbname, TRUE);
				$tables = $MyDB->list_tables();
				if (!empty ($tables)) {
					$myDB->load->dbforge();
					foreach ($tables as $item) {
						$myDB->dbforge->drop_table($item);
					}
				}
			}
			$syscmd = "mysql -u{$db[$dbname]['username']} -p{$db[$dbname]['password']} {$db[$dbname]['database']} < $file";
			system($syscmd, $err);
		}
		else {
			show_error('The database import file does not exist.  Please make sure you have the correct path.');
		}
	}

}