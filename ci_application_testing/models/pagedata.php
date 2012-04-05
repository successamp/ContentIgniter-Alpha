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

class Pagedata extends CI_Model {
	protected $data = array();
	// Template data
	protected $page_config;
	// Page config settings
	protected $template_types = array();
	// Types of templates being called
	protected $template_ids = array();
	// Id's for each template on teh page
	protected $cache_time = 0;
	// Default cache time
	protected $rebuild_cache = FALSE;
	protected $subdomain;
	//########################################################################
	//# Construct function
	//########################################################################

	public function __construct() {
		parent :: __construct();
		$this->benchmark->mark('Pagedata_Initialize_start');
		$this->data['page'] = array();
		$this->data['HTML'] = $this->mu;
		// Load muQuery mark up language library
		$this->data['menu'] = array();
		$this->data['is_cached'] = array();
		$this->data['custom_js'] = '';
		$this->data['custom_css'] = '';
		$this->data['external_css'] = array();
		$this->data['external_js'] = array();
		$this->data['security'] = array();
		if ($this->cache->configured) {
			$this->page_config = $this->cache->get('CORE_Pages_CONFIG');
		}
		if (empty ($this->page_config)) {
			$query = $this->db->select('*')->from('CORE_Pages_CONFIG')->get();
			foreach ($query->result_array() as $item) {
				$this->page_config[$item['key']] = $item['value'];
			}
            /* Fetch needed items from the contentigniter config file */
            $this->page_config['rootdomain'] = $this->config->item('rootdomain');
            $this->page_config['no_subdomain'] = $this->config->item('no_subdomain');
            $this->page_config['page_template_types'] = $this->config->item('page_template_types');
            $this->page_config['static_url'] = $this->config->item('static_url');
            $this->page_config['secure_static_url'] = $this->config->item('secure_static_url');
            $this->page_config['ssl_installed'] = $this->config->item('ssl_installed');

			if ($this->cache->configured) {
				$this->cache->set('CORE_Pages_CONFIG', $this->page_config);
			}
		}
		// These are the template types called for each page by defualt.  If you modify this you may need to update other areas.
		// Changing _header requires updating get_js_css()
		// Changing _nav requires updating get_menu()
		$this->data['page_template_types'] = explode(',', $this->page_config['page_template_types']);
		$this->template_types = $this->data['page_template_types'];
		foreach ($this->template_types as $type) {
			$this->data['is_cached'][$type] = FALSE;
			$this->data[$type] = array();
			// Make a property for each template type
		}
		$this->benchmark->mark('Pagedata_Initialize_end');
	}
	//########################################################################
	//# Take a page name, look it up, and build it from cache or database
	//########################################################################

	public function build_page($page) {
		$this->get_subdomain();
		$this->benchmark->mark('Pagedata_Build_Page_start');
		$this->get_page($page);
		if (empty ($this->data['page'])) {
			// If no page data exit
			return NULL;
		}
		if ($this->cache->configured) {
			if ($this->set_permissions() == FALSE) {
				// Redirect if the user doesn't have sufficient permission
				redirect('/login' . $this->uri->uri_string());
			}
			$this->set_cache_vars();
			$this->get_templates(TRUE);
			if (!empty ($this->template_types)) {
				$this->get_menu();
				$this->get_js_css();
				$this->build_data();
				$this->get_elements();
				if ($this->rebuild_cache) {
					$this->rebuild();
				}
			}
		}
		else {
			if (!empty ($this->data['page'])) {
				if ($this->set_permissions() == FALSE) {
					redirect('/login' . $this->uri->uri_string());
					// Redirect if the user doesn't have sufficient permission
				}
				$this->get_templates();
				$this->get_menu();
				$this->get_js_css();
				$this->build_data();
				$this->get_elements();
			}
		}
		$this->benchmark->mark('Pagedata_Build_Page_end');
	}
	//########################################################################
	//# Give the template additional data
	//########################################################################

	public function set($attribute, $value) {
		if (!isset ($this->data[$attribute])) {
			$this->data[$attribute] = $value;
		}
		else {
			show_error("Unable to set attribute '{$attribute}' because it is already set. Use 'force_set_attribute()' to reset it.");
			exit;
		}
	}
	//########################################################################
	//# Overwrite template data if exists
	//########################################################################

	public function force_set_attribute($attribute, $value) {
		$this->data[$attribute] = $value;
	}

	//########################################################################
	//# Checks the cross reference table for any data that the page needs
	//########################################################################

    protected function get_data_xref() {
        if ($this->cache->configured) {
			$data = $this->cache->get('CORE_Page_DATA_XREF_Cache_' . $this->data['page']['id']);
            if(!empty($data)){
                return $data;
            }
		}
        if (!empty ($this->template_ids)) {
            $this->db->select('*')->from('CORE_Templates_DATA_XREF');
            foreach ($this->template_ids as $id) {
                $this->db->or_where('template_id', $id);
            }
            $result_array = $this->db->get()->result_array();
		}else{
            $result_array = array();
        }
		$this->db->select('*')->from('CORE_Pages_DATA_XREF')->where('page_id', $this->data['page']['id']);
		$query = $this->db->get();
        $xref_data =  array_merge($result_array, $query->result_array());
        if ($this->cache->configured) {
            $this->cache->set('CORE_Page_DATA_XREF_Cache_' . $this->data['page']['id'], $xref_data, $this->data['page']['id'], $this->data['page']['cache_page']);
        }
		return $xref_data;
    }

	//########################################################################
	//# Builds the data variables called in a page
	//########################################################################

    protected function build_data() {
        $result_array = $this->get_data_xref();
        if ($this->cache->configured) {
            foreach ($result_array as $key => $item) {
    			$data = $this->cache->get('CORE_Page_DATA_Cache_' . $item['data_table_name'] . '__' . $item['data_id']);
                if(!empty($data)){
                    $this->set($item['attribute_name'], $data);
                    unset($result_array[$key]);
                }
		    }
        }
		foreach ($result_array as $item) {
            $name = $item['attribute_name'];
            $this->benchmark->mark('DATA_' . $name . '_start');
			if ($item['data_id'] > 0) {
				$this->db->select('*')->from($item['data_table_name'])->where('id', $item['data_id']);
			}
			elseif ($item['data_id'] == 0) {
				$this->db->select('*')->from($item['data_table_name']);
			}
			if (!empty($item['order_by'])) {
                $this->db->order_by($item['order_by']);
			}
            $row_query = $this->db->get();
			if (isset ($this->$name)) {
				show_error("Unable to set attribute '{$name}' because it is already set.");
				exit;
			}
			$x = 0;
			$up_array = array();
			foreach ($row_query->result_array() as $row) {
				$up_array[$x] = $row;
				$x++;
			}
			$this->set($name, $up_array);
            if ($this->cache->configured) {
                $this->cache->set('CORE_Page_DATA_Cache_' . $item['data_table_name'] . '__' . $item['data_id'], $up_array, $this->data['page']['id'], $item['cache_time']);
            }
			$this->benchmark->mark('DATA_' . $name . '_end');
		}
	}
	//########################################################################
	//# Pulls the page data from cache or database
	//########################################################################

	protected function get_page($page) {
	    //$this->load->helper('url'); //Autoloaded
		$canonical = '';
		if ($this->cache->configured) {
			$this->data['page'] = $this->cache->get('CORE_Pages_' . $page);
		}
		if (empty ($this->data['page'])) {
			$this->db->select('*')->from('CORE_Pages')->where('url', $page);
                              if(ENVIRONMENT === 'production'){ // Only show non-active pages in the development environment
                                        $query = $this->db->where('active',1)->get();
                              }else{ // Developers can navigate to any page, even inactive ones.
                                        $query = $this->db->get();
                              }
			$this->data['page'] = $query->row_array();
			if ($this->is_cache_configured()) { // Check to see if cache is configured and disable if the page isn't cacheable
				$this->cache->set('CORE_Pages_' . $page, $this->data['page']);
			}
		}
        if (empty ($this->data['page']['subdomain']) && empty($this->page_config['no_subdomain'])) {
            $canonical = site_url($page);
        }
        elseif ($this->data['page']['subdomain'] != $this->subdomain) {
            $this->data['page'] = array();
            // We remove any data effectively returning a 404
        }
        else{
            $canonical = empty($this->data['page']['ssl_required']) ? 'http://' : 'https://';
            $canonical .= empty($this->data['page']['subdomain']) ? $this->data['rootdomain'] : $this->data['page']['subdomain'] . '.' . $this->data['rootdomain'];
            if(!empty($this->page_config['no_subdomain'])){
                $canonical .= $this->page_config['default_page'] == $page ? '/' : '/' . $page . '/';
            }else{
                $canonical .= $this->data['page']['subdomain'] == $page ? '/' : '/' . $page . '/';
            }
        }
        $this->data['split_test'] = $this->analytics->start_split($this->data['page']);
//######################################################################################################################
//# DEBUG CODE, REMOVE AFTER DEVELOPMENT
//######################################################################################################################
        fb_log('split_test', $this->data['split_test']);
//######################################################################################################################

		$this->data['canonical'] = $canonical;
        $this->build_category_keywords();
	}

	//########################################################################
	//# Pulls subdomain data
	//########################################################################
	public function get_subdomain($force_subdomain = NULL) {
		if (empty ($this->subdomain) && $this->subdomain !== '') {
			$domainurl = explode('.', $_SERVER['SERVER_NAME']);
    	    if(!empty($this->page_config['no_subdomain'])){
                $this->subdomain = '';
                if(empty($this->page_config['rootdomain'])){
                    $this->data['rootdomain'] = $domainurl[0] . '.' . $domainurl[1];
                    if(!empty($domainurl[2])){
                        $this->data['rootdomain'] .= '.' . $domainurl[2];
                    }
                }else{
                    $this->data['rootdomain'] = $this->page_config['rootdomain'];
                }
	            $this->data['subdomain'] = array();
	            $this->data['fulldomain'] = $this->data['rootdomain'];
                $this->data['no_subdomain'] = TRUE;
	        }else{
    			$this->subdomain = empty($force_subdomain) ? strtolower($domainurl[0]) : $force_subdomain;
                if(empty($this->page_config['rootdomain'])){
                    $this->data['rootdomain'] = $domainurl[1] . '.' . $domainurl[2];
                    if(!empty($domainurl[3])){
                        $this->data['rootdomain'] .= '.' . $domainurl[3];
                    }
                }else{
                    $this->data['rootdomain'] = $this->page_config['rootdomain'];
                }
    		    $query = $this->db->select('*')->from('DATA_subdomain_PROFILE')->where('id', $this->subdomain)->get();
	            $this->data['subdomain'] = $query->row_array();
                if(empty($this->data['subdomain'])){
                    show_404();
                }
	            $this->data['fulldomain'] = $this->data['subdomain']['id'] . '.' . $this->data['rootdomain'];
                $this->data['no_subdomain'] = FALSE;
	        }
		}
		return $this->subdomain;
	}
	//########################################################################
	//# Pulls all template data from cache or database
	//########################################################################

	protected function get_templates($cache_on = FALSE, $template_prefix = 'default') {
		$cache_check = FALSE;
		$x = 0;
		$this->db->select('*')->from('CORE_Templates');
		$count = count($this->template_types);
		foreach ($this->template_types as $item) {
			if (empty ($this->data['page'][$item])) {
				$template_id = $this->page_config[$template_prefix . $item];
			}
			else {
				$template_id = $this->data['page'][$item];
			}
			if ($this->cache->configured) {
				$fetch_template = $this->cache->get('CORE_Templates' . $item . '_' . $template_id);
				if (!empty ($fetch_template)) {
					$this->data[$item] = $fetch_template;
					$this->template_ids[$x] = $fetch_template['id'];
					$x++;
					if ($fetch_template['cache_time'] > 0) {
						$cache_check = TRUE;
					}
					$count--;
				}
				else {
					$this->db->or_where('id', $template_id);
				}
			}
			else {
				$this->db->or_where('id', $template_id);
			}
		}
		if ($count > 0) {
			// If rows need to be gathered
			$query = $this->db->get();
			if ($query->num_rows()) {
				foreach ($query->result_array() as $item) {
					$this->data[$item['type']] = $item;
					$this->template_ids[$x] = $item['id'];
					$x++;
					if ($this->cache->configured) {
						$this->cache->set('CORE_Templates' . $item['type'] . '_' . $item['id'], $item, NULL, $item['cache_time']);// Added
					}
					if ($item['cache_time'] > 0) {
						$cache_check = TRUE;
					}
				}
			}
		}
		$this->db->count_all_results();// Was calling _reset_select() but it was made protected
		if ($cache_check && $cache_on) {
			$this->cache_expiration_check();
		}
	}
	//########################################################################
	//# Check for pages to be included in the menu
	//# menu_item can be greater than 1 for multiple menus
	//########################################################################

	protected function get_menu() {
		if (!$this->data['is_cached']['_nav']) {
			$custom_where = "`menu_item` > 0 AND `subdomain` = '{$this->subdomain}' OR `menu_item` = 1 AND `subdomain` = ''";
			$query = $this->db->select('menu_name,url,security,menu_item,ssl_required,menu_class,title')->from('CORE_Pages')->where($custom_where)->order_by('menu_item ASC, order ASC')->get();
			foreach ($query->result_array() as $item) {
				if (empty ($item['security']) || $this->session->auth($item['security']) == TRUE) {
					if ($item['url'] == $this->page_config['default_page'] || $item['url'] == $this->subdomain) {
						$item['url'] = "/";
					}
					else {
						$item['url'] = "/{$item['url']}/";
					}
					$this->data['menu'][] = $item;
				}
			}
		}
	}
	//########################################################################
	//# Checks each template for additional JS or CSS
	//########################################################################

	protected function get_js_css() {
		$this->data['static_url'] = empty($this->data['page']['ssl_required']) ? $this->page_config['static_url'] : $this->page_config['secure_static_url'];
		if (!$this->data['is_cached']['_header']) {
			if (!empty ($this->template_ids)) {

                $this->data['external_js'] = $this->db->select('*')
                        ->from('CORE_JS_XREF')
                        ->where_in('template_id', $this->template_ids)
                        ->join('CORE_JS', 'CORE_JS_XREF.js_id = CORE_JS.id')
                        ->order_by('priority','desc')
                        ->get()
                        ->result_array();

                $js_ids = array();
                foreach($this->data['external_js'] as $js){
                    $js_ids[] = $js['id'];
                }

                $query = $this->db->distinct('css_id')
                        ->from('CORE_JS_CSS_XREF')
                        ->where_in('js_id', $js_ids)
                        ->get();

                $css_js_ids = array();
                if(!empty($query)){
                  foreach($query->result_array() as $row){
                      $css_js_ids[] = $row['css_id'];
                  }
                }

				$query = $this->db->select('css_id')
                        ->from('CORE_CSS_XREF')
                        ->where_in('template_id', $this->template_ids)
                        ->get();

                $css_template_ids = array();
                if(!empty($query)){
                  foreach($query->result_array() as $row){
                      $css_template_ids[] = $row['css_id'];
                  }
                }

                $css_ids = array_merge($css_js_ids, $css_template_ids);

                $query = $this->db->distinct('*')
                        ->from('CORE_CSS')
                        ->where_in('id', $css_ids)
                        ->get();
                $this->data['external_css'] = empty($query) ? array() : $query->result_array();
			}
            $this->build_packages('css');
            $this->build_packages('js');
			if (!empty ($this->data['external_css'])){
                foreach($this->data['external_css'] as $css){
                    $external_css_url[] = $this->build_css_link($css);
                }
                $this->data['external_css_url'] = $external_css_url;
			}
			if (!empty ($this->data['external_js'])){
                foreach($this->data['external_js'] as $css){
                    $external_js_url[] = $this->build_js_link($css);
                }
                $this->data['external_js_url'] = $external_js_url;
			}
			if (!empty ($this->data['page_template_types'])) {
				foreach ($this->data['page_template_types'] as $type) {
					$template = isset ($this->data[$type]) ? $this->data[$type] : NULL;
					if (!empty ($template['custom_js'])) {
						$this->data['custom_js'] .= $template['custom_js'];
					}
					if (!empty ($template['custom_css'])) {
						$this->data['custom_css'] .= $template['custom_css'];
					}
				}
			}
		}
	}

    protected function build_packages($type){
        $packages = array();
        $library_names = array();
        if($type == 'css'){
            $this->load->helper('compressor');
        }

        foreach($this->data['external_'.$type] as $item){
            if(!empty($item['package'])){
                $packages[] = $item;
            }
        }
        if(!empty($packages)){
            foreach($packages as $item){
                $local_file = FCPATH . 'static/' . $type . '/' . $item['library_name'] . '/' . $item['version_major'] . '/' . $item['version_minor'] . '/' . $item['version_build'] . '/' . $item['file_name'];
                $local_dir = FCPATH . 'static/' . $type . '/' . $item['library_name'] . '/' . $item['version_major'] . '/' . $item['version_minor'] . '/' . $item['version_build'] . '/';
                if(!is_readable($local_file)){ // Build the file
                    fb_info('File not found, building ' . $type . ' for : ' . $item['library_name']);
                    $type_ids = array();
                    if($type == 'css'){
                        $js_id = $this->db->select('id, package')->from('CORE_JS')->where('library_name',$item['library_name'])->get()->result_array();
                        if(!empty($js_id[0]['id'])){
							if(!empty($js_id[0]['package'])){
								$js_id_array = array();
								foreach($this->db->select('item_js_id')->from('CORE_JS_Package_XREF')->where('package_js_id',$js_id[0]['id'])->get()->result_array() as $row){
									$js_id_array[] = $row['item_js_id'];
								}
								$js_id_array[] = $js_id[0]['id'];
							}else{
								$js_id_array = array($js_id[0]['id']);
							}
                            $js_result = $this->db->select('css_id')->from('CORE_JS_CSS_XREF')->where_in('js_id',$js_id_array)->get()->result_array();
                            foreach($js_result as $row){
                                $type_ids[] = $row['css_id'];
                            }
                        }
                        $this->db->select('item_css_id')->from('CORE_CSS_Package_XREF')->where('package_css_id',$item['id'])->order_by('order','asc');
                        foreach($this->db->get()->result_array() as $row){
                            $type_ids[] = $row['item_css_id'];
                            foreach($this->data['external_css'] as $key => $old_css){
                                if($old_css['id'] == $row['item_css_id']){
                                    unset($this->data['external_css'][$key]); // Remove the CSS if it was already included
                                }
                            }
                        }
                    }else{
                        $this->db->select('item_js_id')->from('CORE_JS_Package_XREF')->where('package_js_id',$item['id'])->order_by('order','asc');
                        foreach($this->db->get()->result_array() as $row){
                            $type_ids[] = $row['item_js_id'];
                            foreach($this->data['external_js'] as $key => $old_js){
                                if($old_js['id'] == $row['item_js_id']){
                                    unset($this->data['external_js'][$key]); // Remove the JS if it was already included
                                }
                            }
                        }
                    }
                    $type_ids = array_unique($type_ids);
                    $item_text = '';
                    foreach($type_ids as $id){
                        if($type == 'css'){
                            $item_table = 'CORE_CSS';
                            $type_dir = 'css';
                        }else{
                            $item_table = 'CORE_JS';
                            $type_dir = 'js';
                        }
                        $item_file = $this->db->select('*')->from($item_table)->where('id',$id)->get()->result_array();
                        if($type == 'css' && !empty($item_file[0]['js_folder'])){
                                $type_dir = 'js'; // Css files normally stay in the Css folder, but some stay with their js package
                        }
                        $read_file = FCPATH . 'static/' . $type_dir . '/' . $item_file[0]['library_name'] . '/' . $item_file[0]['version_major'] . '/' . $item_file[0]['version_minor'] . '/' . $item_file[0]['version_build'] . '/' . $item_file[0]['file_name'];
                        //$read_file_path = FCPATH . 'static/' . $type_dir . '/' . $item_file[0]['library_name'] . '/' . $item_file[0]['version_major'] . '/' . $item_file[0]['version_minor'] . '/' . $item_file[0]['version_build'] . '/';
                        if(is_readable($read_file)){
                            $handle = fopen($read_file, 'r');
			                $text = fread($handle, filesize($read_file));
			                fclose($handle);
                            if($type == 'css'){
                                $item_text .= "\n/*" . $read_file . "*/\n";
                                if(!preg_match('@[-\\.](?:min|close)\\.(?:js|css)$@i', $read_file)){
                                    fb_info('CSS Compiling '.$read_file);
                                    $item_text .= Minify_CSS_Compressor::process($text);
                                }else{
                                    fb_info($read_file.' already appears to be compiled based on the file name');
                                    $item_text .= $text;
                                }
                            }else{
                                $item_text .= "\n//" . $read_file . "\n";
                                if(!preg_match('@[-\\.](?:min|close)\\.(?:js|css)$@i', $read_file)){
                                    fb_info('JS Compiling '.$read_file);
                                    $compile_file_name = str_replace('.js', '.min.js', $read_file);
                                    if(is_readable($compile_file_name)){
                                        $read_file = $compile_file_name;
                                        fb_info('Previous compile found, using that: '.$compile_file_name);
                                    }else{
                                        $compilerCommand = sprintf('/usr/bin/java -jar %slibraries/compiler.jar --js %s --js_output_file %s',
					                        APPPATH, $read_file, $compile_file_name);
                                        exec($compilerCommand, $return, $code);
                                        if ($code != 0) {
                                            fb_error("Local closure compile failed, make sure you have java installed: ($code)");
                                            fb_log('$compilerCommand',$compilerCommand);
                                            fb_log('error return',$return);
                                        }else{
                                            fb_info('Created using local Closure Compiler: '.$compile_file_name);
                                        }
                                    }
                                    if(is_readable($compile_file_name)){
                                        $handle = fopen($compile_file_name, 'r');
			                            $item_text .= fread($handle, filesize($compile_file_name));
			                            fclose($handle);
                                        fb_info('Local closure file used: '.$compile_file_name);
                                    }else{
                                      // Offsite is never my favorite, but it works.  Using Google closure compiler.
                                      $ch = curl_init('http://closure-compiler.appspot.com/compile');
                                      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                                      curl_setopt($ch, CURLOPT_POST, 1);
                                      curl_setopt($ch, CURLOPT_POSTFIELDS, 'output_info=compiled_code&output_format=text&compilation_level=SIMPLE_OPTIMIZATIONS&js_code=' . urlencode($text));
                                      $item_text .= curl_exec($ch);
                                      curl_close($ch);
                                      fb_info('Remote closure compile used for '.$read_file);
                                    }
                                }else{
                                    fb_info($read_file.' already appears to be compiled based on the file name');
                                    $item_text .= $text;
                                }
                            }
                        }else{
                            fb_error($read_file.' is not readable');
                        }
                        foreach($this->data['external_'.$type] as $key => $old){
                            if($old['id'] == $id){
                                unset($this->data['external_'.$type][$key]); // Remove the CSS or JS if it was already packaged
                            }
                        }
                    }

                    if(!is_writable($local_dir)){
                        if (!mkdir($local_dir, 0777, true)) {
						    fb_error('Error writing to '.$local_dir);
					    }
                    }
                    $handle = fopen($local_file, 'x');
                    $status = fwrite($handle, $item_text);
                    fclose($handle);
                    if($status === false){
                        fb_error('Unable to write to '.$local_file);
                    }
                }else{
                    $BIG_type = strtoupper($type);
                    $js_id_array = array($item['id']);
                    $result = $this->db->select('item_'.$type.'_id')->from('CORE_'.$BIG_type.'_Package_XREF')->where('package_'.$type.'_id',$item['id'])->get()->result_array();
                    if(!empty($result)){
                        foreach($result as $row){
                            $js_id_array[] = $row['item_'.$type.'_id']; // Ignored if css, so it doesn't matter
                            foreach($this->data['external_'.$type] as $key => $old){
                                if($old['id'] == $row['item_'.$type.'_id']){
                                    unset($this->data['external_'.$type][$key]); // Remove the JS if it was already included
                                }
                            }
                        }
                    }
                    if($type == 'js'){
                        $result = $this->db->select('css_id')->from('CORE_JS_CSS_XREF')->where_in('js_id',$js_id_array)->get()->result_array();
                        if(!empty($result)){
                            foreach($result as $row){
                                foreach($this->data['external_css'] as $key => $old){
                                    if($old['id'] == $row['css_id']){
                                        unset($this->data['external_css'][$key]); // Remove the JS if it was already included
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    protected function build_css_link($css){
        if(empty($css['static'])){
            return $css['file_name'];
        }
        $url_base = $this->data['static_url'];
        $url_type_folder = $css['js_folder'] == 1 ? 'js' : 'css';
        $url_library = $css['library_name'];
        $url_major = $css['version_major'];
        $url_minor = $css['version_minor'];
        $url_build = $css['version_build'];
        $url_file = $css['file_name'];
        return $url_base . '/' . $url_type_folder . '/' . $url_library . '/' . $url_major . '/' . $url_minor . '/' . $url_build . '/' . $url_file;
    }

    protected function build_js_link($js){
        if(empty($js['static'])){
            return $js['file_name'];
        }
        $url_base = $this->data['static_url'];
        $url_library = $js['library_name'];
        $url_major = $js['version_major'];
        $url_minor = $js['version_minor'];
        $url_build = $js['version_build'];
        $url_file = $js['file_name'];
        return $url_base . '/js/' . $url_library . '/' . $url_major . '/' . $url_minor . '/' . $url_build . '/' . $url_file;
    }

	//########################################################################
	//# Checks for elements (micro-templates)
	//########################################################################

	protected function get_elements() {
        // Must Add Cache
		if (!$this->data['is_cached']['_header']) {
			if (!empty ($this->template_ids)) {
				$this->db->select('*')->from('CORE_Elements_XREF');
				foreach ($this->template_ids as $id) {
					$this->db->or_where('template_id', $id);
				}
				$this->db->join('CORE_Elements', 'CORE_Elements_XREF.element_id = CORE_Elements.id');
				$elements = $this->db->get()->result_array();
//				$this->data['elements'] = $elements->result_array();
                if(!empty($elements)){
                    foreach($elements as $item){
                        $this->data['elements'][$item['name']] = $item['code'];
                    }
                }
                else{
                    $this->data['elements'] = array();
                }
			}
		}
	}
	//########################################################################
	//# Checks to see if the user has permission to view the page
	//########################################################################

	protected function set_permissions() {
        // Make sure SSL is in the proper state
        $this->ssl_check();
        if (empty($this->data['page']['active'])) {
            return ENVIRONMENT !== 'production' ? TRUE : FALSE;
        }
		$query = $this->db->select('name')->from('CORE_Auth_GROUPS')->get();
		$this->data['security']['logged_in'] = $this->session->auth('logged_in');
		foreach ($query->result_array() as $item) {
			$this->data['security'][$item['name']] = $this->session->auth($item['name']);
		}
		if (empty ($this->data['page']['security'])) {
			return TRUE;
		}
		else {
			return $this->session->auth($this->data['page']['security']);
		}
	}

	//########################################################################
	//# Checks to see if the user has permission to view the page
	//########################################################################

	protected function ssl_check() {
/*
		if (!empty($this->data['page']['ssl_required']) && empty($_SERVER['HTTPS'])) {
            $url = 'https://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
            header('Location: '.$url);
            exit;
		}elseif (empty($this->data['page']['ssl_required']) && !empty($_SERVER['HTTPS'])) {
            $url = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
            header('Location: '.$url);
            exit;
		}
*/
		$ssl_required = empty($this->data['page']['ssl_required']) ? FALSE : TRUE;
        if(!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on'){
            $https = TRUE;
        }else{
            $https = FALSE;
        }
        if($ssl_required === FALSE && $https === TRUE){
            // SSL is on, but page doesn't require it.
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']);
            exit();
        }elseif($ssl_required === TRUE && $https === FALSE){
            // SSL required, but not on
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: https://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']);
            exit();
        }
	}

	//########################################################################
	//# Sets how to cache the page and how long
	//########################################################################

	protected function cache_expiration_check() {
		$data = $this->cache->get($this->cache->uri . $this->cache->file);
		if (!empty ($data)) {
			$output_header_cache = 1440;
			foreach ($this->data['page_template_types'] as $type) {
				if (empty ($data[$type]) && $this->data[$type]['cache_time'] > 0) {
					$this->rebuild_cache = TRUE;
					break;
				}
				$template = $this->data[$type];
				if ($output_header_cache > $template['cache_time']) {
					$output_header_cache = $template['cache_time'];
				}
				if ($template['cache_time'] > 0 && ($data['cachetime'] + ($template['cache_time'] * 60)) > time()) {
					$this->data[$type] = $data[$type];
					$this->data['is_cached'][$type] = TRUE;
					$key = array_search($type, $this->template_types);
					unset ($this->template_types[$key]);
				}
				elseif ($template['cache_time'] > 0) {
					$this->rebuild_cache = TRUE;
				}
			}
			if ($this->rebuild_cache !== TRUE) {
				$this->output->cache($output_header_cache);
				$this->output->set_header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + (60 * $output_header_cache)));
			}
		}
		else {
			$this->rebuild_cache = TRUE;
		}
	}
	//########################################################################
	//# Sets default cache time and per user caching
	//########################################################################

	protected function set_cache_vars() {
		if (!empty ($this->data['page']['cache_page'])) {
			$this->cache_time = $this->data['page']['cache_page'];
		}
		else {
			$this->cache_time = $this->page_config['cache_time'];
		}
		if (!empty ($this->data['page']['per_user_cache'])) {
			$this->cache->per_user_cache_active();
		}
	}
	//########################################################################
	//# Checks for developer mode, cache disabled, and cache settings
    //# and will reset $this->cache->configured flag if needed.
    //# Should call a function to get and set.
	//########################################################################

	protected function is_cache_configured() {
		if (ENVIRONMENT === 'production') {
    		if (!empty($this->data['page']['no_cache'])) {
    			$this->cache_time = 0;
    		    $this->cache->configured = FALSE;
		    }
		}
		elseif (ENVIRONMENT === 'development') {
			$this->output->enable_profiler(TRUE);
			$this->cache_time = 0;
  		    $this->cache->configured = FALSE;
		}
		elseif (ENVIRONMENT === 'testing') {
			$this->cache_time = 0;
   		    $this->cache->configured = FALSE;
		}
   		return $this->cache->configured;
	}
	//########################################################################
	//# Build or re-build page cache if expired or non-existent
	//########################################################################

	protected function rebuild() {
		if (!empty ($this->template_types)) {
			$CI = & get_instance();
			$cache_data = array();
			foreach ($this->template_types as $type) {
				$template = $this->data[$type];
				if ($template['cache_time'] > 0) {
					$cache_data[$type] = $CI->load->db_view($type . '_' . $template['name'], $template['code'], $this->data, true);
					$this->data[$type] = $cache_data[$type];
					$this->data['is_cached'][$type] = TRUE;
				}
				else {
					$this->data[$type] = $CI->load->db_view($type . '_' . $template['name'], $template['code'], $this->data, true);
					$this->data['is_cached'][$type] = TRUE;
				}
			}
			if (!empty ($cache_data)) {
				foreach ($this->data['page_template_types'] as $type) {
					if (!isset ($cache_data[$type])) {
						$cache_data[$type] = $this->data[$type];
					}
				}
				$cache_data['cachetime'] = time();
				$this->cache->set($this->cache->uri . $this->cache->file, $cache_data);
			}
		}
	}

	public function get_data($specific_data = NULL) {
	    if(empty($specific_data)){
    		return $this->data;
	    }else{
    		return $this->data[$specific_data];
	    }
	}

	public function get_page_config() {
		return $this->page_config;
	}

	public function build_category_keywords() {
		$page_id = $this->data['page']['id'];
        if ($this->cache->configured) {
			$this->data['page_category'] = $this->cache->get('CORE_Pages_CATEGORY_page_id_' . $page_id);
		}
		if (empty ($this->data['page_category'])) {
			$page_category = $this->db->select('category_id')->from('CORE_Pages_CATEGORY_XREF')->where('page_id', $page_id)->get()->result_array();
            if(!empty($page_category)){
                $category_ids = array();
                foreach($page_category as $cat){
                    $category_ids[] = $cat['category_id'];
                }
                $this->data['page_category'] = $this->db->select('*')->from('CORE_Pages_CATEGORY')->where_in('id', $category_ids)->order_by('keyword','asc')->get()->result_array();
    			if ($this->cache->configured) {
    				$this->cache->set('CORE_Pages_CATEGORY_page_id_' . $page_id, $this->data['page_category']);
    			}
            }
		}
	}
}