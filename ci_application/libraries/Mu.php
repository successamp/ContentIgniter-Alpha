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
include_once('MUQuery.php');

class Mu extends MUQuery {
          // Extended version of the MUQuery library with special helper functions
          var $CI;

          public function __construct($lang=NULL) {
                    parent :: __construct($lang);
                    $this->CI = & get_instance();
          }

	public function autoform($data_table, $data_array) {
		$query = $this->CI->db->select('*')->from('CORE_Table_Structure')->where('table_name', $data_table)->order_by('order')->get()->result_array();
                    foreach($query as $field){
                              $field_data = $this->CI->db->select('field_PHP')->from('CORE_Input_Fields')->where('id', $field['input_id'])->get()->row_array();
                              $this->label($field['field_name'],$field['description']);
                              $this->build_field($field_data['field_PHP'], $data_table, $field['field_name'], $data_array[$field['field_name']]);
                              $this->hr();
                    }
                    return $this;
	}

	protected function build_field($field_PHP, $table, $field_name, $value=NULL) {
	          eval($field_PHP);
	}

	protected function build_dropdown($table, $field_name, $value=NULL){
                    $drop_down_fields = $this->CI->db->select('*')->from('CORE_Drop_Down_DATA_XREF')->where('table', $table)->where('field_name', $field_name)->get()->row_array();
                    $drop_down_default = $this->CI->db->select('value')->from('CORE_Pages_CONFIG')->where('key', 'default'.$field_name)->get()->row_array();
                    $drop_down_query = $this->CI->db->select($drop_down_fields['lookup_value_field'].' as VALUE_FIELD, '.$drop_down_fields['lookup_display_field'].' as DISPLAY_FIELD', FALSE)->from($drop_down_fields['lookup_table']);

                    if(!empty($drop_down_fields['where'])){
                              $drop_down_query->where($drop_down_fields['where']);
                    }
                    if(!empty($drop_down_fields['order_by'])){
                              $drop_down_query->order_by($drop_down_fields['order_by']);
                    }
                    $drop_down = $drop_down_query->get()->result_array();
                    $this->select($field_name);
                    $default_field = empty($drop_down_default['value']) ? '' : $drop_down_default['value'];
                    if(!empty($drop_down_fields['DEFUALT_DISPLAY_NAME'])){
                        $this->option($drop_down_fields['DEFUALT_DISPLAY_NAME'], $drop_down_fields['DEFUALT_VALUE'], TRUE);
                        if($value == $drop_down_fields['DEFUALT_VALUE']){
                                  $this->attr('selected',TRUE);
                        }
                    }
                    foreach($drop_down as $item){
                              $this->option($item['DISPLAY_FIELD'], $item['VALUE_FIELD'], TRUE);
                              if($item['VALUE_FIELD'] == $value){
                                        $this->attr('selected',TRUE);
                              }
                    }
                    $this->close();
	}

}
