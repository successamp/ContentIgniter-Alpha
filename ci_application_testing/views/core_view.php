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
if (!defined('BASEPATH')) exit('No direct script access allowed');
    if(is_array($template)){
        // You can specify a single template or an array of all templates
        foreach($template as $type_of_page_template){
            $this->load->view('core/'.$type_of_page_template, $_ci_data['_ci_vars']);
        }
    }else{
        $this->load->view('core/_header', $_ci_data['_ci_vars']);
        $this->load->view('core/_nav', $_ci_data['_ci_vars']);
        $this->load->view('core/'.$template, $_ci_data['_ci_vars']);
        $this->load->view('core/_aside', $_ci_data['_ci_vars']);
        $this->load->view('core/_footer', $_ci_data['_ci_vars']);
    }
