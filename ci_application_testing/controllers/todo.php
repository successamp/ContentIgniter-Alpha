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

class Todo extends CI_Controller {
/* Quick and dirty admin todo list */

	function __construct() {
		parent :: __construct();
		$flag = $this->input->post('flag');
		if ($flag == 'ADD') {
			$this->add_item();
		}
		elseif ($flag = 'EDIT') {
			$this->edit_item();
		}
	}

	function add_item() {
		$data = array('title' => $this->input->post('title'), 'status' => $this->input->post('status'), 'priority' => $this->input->post('priority'), 'notes' => $this->input->post('notes'));
		$this->db->insert('CI_MODULE_Todo', $data);
	}

	function edit_item() {
		$data = array('id' => $this->input->post('id'), 'title' => $this->input->post('title'), 'status' => $this->input->post('status'), 'priority' => $this->input->post('priority'), 'notes' => $this->input->post('notes'));
		$this->db->where('id', $this->input->post('id'));
		$this->db->update('CI_MODULE_Todo', $data);
	}

	function edit($id) {
		$data['item'] = $this->db->select('*')->from('MODULE_Todo')->where('id', $id)->get()->row_array();
        $data['priority'] = $this->db->select('*')->from('MODULE_Todo_PRIORITY')->get()->result_array();
        $data['status'] = $this->db->select('*')->from('MODULE_Todo_STATUS')->get()->result_array();
        $data['template'] = 'todo/todo_edit';
		$this->load->view('core_view', $data);
	}

	function index() {
        $data['active_list'] = $this->db->select('*')->from('MODULE_Todo')->where('status', 1)->order_by("priority", "desc")->order_by("id", "asc")->get()->result_array();
        $data['waiting_list'] = $this->db->select('*')->from('MODULE_Todo')->where('status', 2)->order_by("priority", "desc")->order_by("id", "asc")->get()->result_array();
        $data['priority'] = $this->db->select('*')->from('MODULE_Todo_PRIORITY')->get()->result_array();
        $data['status'] = $this->db->select('*')->from('MODULE_Todo_STATUS')->get()->result_array();
        $data['template'] = 'todo/todo';
		$this->load->view('core_view', $data);
	}

}
/* End of file todo.php */
