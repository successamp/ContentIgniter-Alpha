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
 * @copyright	Copyright (c) 2008 - 2011, Paul R. Dillinger. (http://prd.me/)
 * @license		http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @link		http://contentigniter.com
 * @since		Version 1.0
 * @filesource
 */
if (!defined('BASEPATH'))
	exit ('No direct script access allowed');

class Analytics {
	var $CI;
    var $analytics_code;

	function __construct() {
        // Requires Session and Database autoloaded before it can run.
		log_message('debug', "Split Class Initialized");
		$this->CI = & get_instance();
		$this->CI->benchmark->mark('Split_Initialize_start');

        //

        // Begin tracking page user entered on and from where
        $user_tracking = $this->CI->session->userdata('user_tracking');
        if(empty($user_tracking)){
            // Session time should be a least a few hours for this this to be fairly accurate
            $userdata['user_tracking'] = TRUE;
            $userdata['user_tracking_refer'] = empty($_SERVER['HTTP_REFERER']) ? '' : $_SERVER['HTTP_REFERER'];
            $userdata['user_tracking_entry'] = $_SERVER['REQUEST_URI'];
            $userdata['user_tracking_domain'] = $_SERVER['SERVER_NAME'];
            $this->CI->session->set_userdata($userdata);
        }
		$this->CI->benchmark->mark('Split_Initialize_end');
	}

    function start_split($page){
        if(!empty($page['split_test'])){
            // split_id_1, split_id_2, split_id_3, split_id_4, or split_id_5 depending on which slot is being used by the page
            $split_test = $this->CI->session->userdata('split_id_' . $page['split_test']);
            if(empty($split_id)){
                // Get Split Test ID
                $split = $this->_generate_split($page);
                if(!empty($split)){
                    $split_test = array($split['slot'] => $split['value'], $split['name'] => $split['value']);
                    $this->CI->session->set_userdata(array('split_id_' . $split['slot'] => $split_test));
                    // From: http://code.google.com/apis/analytics/docs/tracking/gaTrackingCustomVariables.html
                    //  _gaq.push(['_setCustomVar',
                    //       1,                   // This custom var is set to slot #1.  Required parameter.
                    //       'Items Removed',     // The name acts as a kind of category for the user activity.  Required parameter.
                    //       'Yes',               // This value of the custom variable.  Required parameter.
                    //       2                    // Sets the scope to session-level.  Optional parameter.
                    //    ]);
                    //  _gaq.push(['_trackEvent',
                    //       'Shopping', // category of activity
                    //       'Item Removal', // Action
                    //    ]);
                    $this->analytics_code = "
                        _gaq.push(['_setCustomVar',
                          ".$split['slot'].",
                          '".$split['name']."',
                          '".$split['value']."',
                          ".$split['scope']."
                        ]);
                    ";
                }
            }
            return $split_test;
        }
        return array();
    }

    function _generate_split($page){
        $tests = $this->db->select('*')->from('CORE_Split_Tests')->where('page_id', $page['id'])->where('active',1)->order_by('visitors','asc')->get();
        $split = array();
        if(!empty($tests)){
            $split = array('slot' => $tests[0]['slot'], 'name' => $tests[0]['name'], 'value' => $tests[0]['value'], 'scope' => $tests[0]['scope']);
            $visitors = $tests[0]['visitors'] + 1;
            $this->db->update('CORE_Split_Tests', array('visitors' => $visitors), 'id = '.$tests[0]['id']);
        }
        return $split;
    }

    function fetch_head_js(){
        // Fetch Google Analytics code for before </head>
    }

    function fetch_foot_js(){
        // Fetch Google Analytics code for before </body>
    }


}