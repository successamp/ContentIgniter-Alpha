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

class MY_Loader extends CI_Loader
{

    function __construct()
    {
        parent :: __construct();
    }

    function element($element_name, $return = FALSE, $vars = array())
    {
        $view_name = 'CORE_Element_' . $element_name;
        $elements  = $this->_ci_cached_vars['elements'];
        if (!empty($elements[$element_name])) {
            $view_code = $elements[$element_name];
            return $this->_ci_db_load(array(
                                           '_ci_view'      => $view_name,
                                           '_ci_view_code' => $view_code,
                                           '_ci_vars'      => $this->_ci_object_to_array($vars),
                                           '_ci_return'    => $return
                                      ));
        }
    }

    function template($template_name, $return = FALSE, $vars = array())
    {
        $view_name = 'CORE_Template_' . $template_name;
        $view_code = $this->_ci_cached_vars[$template_name]['code'];
        return $this->_ci_db_load(array(
                                       '_ci_view'      => $view_name,
                                       '_ci_view_code' => $view_code,
                                       '_ci_vars'      => $this->_ci_object_to_array($vars),
                                       '_ci_return'    => $return
                                  ));
    }

    function db_view($view_name, $view_code, $vars = array(), $return = FALSE)
    {
        return $this->_ci_db_load(array(
                                       '_ci_view'      => $view_name,
                                       '_ci_view_code' => $view_code,
                                       '_ci_vars'      => $this->_ci_object_to_array($vars),
                                       '_ci_return'    => $return
                                  ));
    }

    function _ci_db_load($_ci_data)
    {
        // Set the default data variables
        foreach (array(
                     '_ci_view',
                     '_ci_vars',
                     '_ci_path',
                     '_ci_return'
                 ) as $_ci_val) {
            $$_ci_val = (!isset ($_ci_data[$_ci_val])) ? FALSE : $_ci_data[$_ci_val];
        }
        // This allows anything loaded using $this->load (views, files, etc.)
        // to become accessible from within the Controller and Model functions.
        // Only needed when running PHP 5
        $_ci_CI = & get_instance();
        foreach (get_object_vars($_ci_CI) as $_ci_key => $_ci_var) {
            if (!isset ($this->$_ci_key)) {
                $this->$_ci_key = & $_ci_CI->$_ci_key;
            }
        }


        /*
          * Extract and cache variables
          *
          * You can either set variables using the dedicated $this->load_vars()
          * function or via the second parameter of this function. We'll merge
          * the two types and cache them so that views that are embedded within
          * other views can have access to these variables.
          */
        if (is_array($_ci_vars)) {
            $this->_ci_cached_vars = array_merge($this->_ci_cached_vars, $_ci_vars);
        }
        extract($this->_ci_cached_vars);
        /*
          * Buffer the output
          *
          * We buffer the output for two reasons:
          * 1. Speed. You get a significant speed boost.
          * 2. So that the final rendered template can be
          * post-processed by the output class.  Why do we
          * need post processing?  For one thing, in order to
          * show the elapsed page load time.  Unless we
          * can intercept the content right before it's sent to
          * the browser and then stop the timer it won't be accurate.
          */
        ob_start();
        // If the PHP installation does not support short tags we'll
        // do a little string replacement, changing the short tags
        // to standard PHP echo statements.
        if ((bool)@ ini_get('short_open_tag') === FALSE AND config_item('rewrite_short_tags') == TRUE) {
            echo eval ('?>' . preg_replace("/;*\s*\?>/", "; ?>", str_replace('<?=', '<?php echo ', $_ci_data['_ci_view_code'])));
        } else {
            echo eval ('?>' . $_ci_data['_ci_view_code']);
            // include() vs include_once() allows for multiple views with the same name
        }
        log_message('debug', 'File loaded: ' . $_ci_path);
        // Return the file data if requested
        if ($_ci_return === TRUE) {
            $buffer = ob_get_contents();
            @ ob_end_clean();
            return $buffer;
        }
        /*
          * Flush the buffer... or buff the flusher?
          *
          * In order to permit views to be nested within
          * other views, we need to flush the content back out whenever
          * we are beyond the first level of output buffering so that
          * it can be seen and included properly by the first included
          * template and any subsequent ones. Oy!
          *
          */
        if (ob_get_level() > $this->_ci_ob_level + 1) {
            ob_end_flush();
        } else {
            // PHP 4 requires that we use a global
            global $OUT;
            $OUT->append_output(ob_get_contents());
            @ ob_end_clean();
        }
    }

    function parse_content($_content_igniter_incoming)
    {
        while (strrpos($_content_igniter_incoming, '{{') !== FALSE) {
            $_content_igniter_incoming = $this->replace_content($_content_igniter_incoming);
        }
        return $_content_igniter_incoming;
    }

    function replace_content($content)
    {
        $start = strpos($content, '{{') + 2;
        $end   = strpos($content, '}}');
        if ($end && $end >= $start) {
            $inject_value = substr($content, $start, ($end - $start));
            if (strpos($inject_value, '->') !== FALSE || strpos($inject_value, '-&gt;') !== FALSE) {
                // String is an array, we must break it down.
                if (strpos($inject_value, '->') !== FALSE) {
                    $var_exploded = explode('->', $inject_value);
                } else {
                    $var_exploded = explode('-&gt;', $inject_value); // TinyMCE encodes the text
                }
                $cached_vars = $this->_ci_cached_vars;
                foreach ($var_exploded as $current_var) {
                    if (isset($cached_vars[$current_var])) {
                        $cached_vars = $cached_vars[$current_var];
                    }
                }
                if (is_string($cached_vars)) {
                    $content = str_replace('{{' . $inject_value . '}}', $cached_vars, $content);
                } else {
                    $content = str_replace('{{' . $inject_value . '}}', '{' . $inject_value . '}', $content);
                }
            } elseif (isset($this->_ci_cached_vars[$inject_value])) {
                $content = str_replace('{{' . $inject_value . '}}', $this->_ci_cached_vars[$inject_value], $content);
            } else {
                $content = str_replace('{{' . $inject_value . '}}', '{' . $inject_value . '}', $content);
            }
        } else {
            $content = str_replace('{{', '{', $content);
        }
        return $content;
    }

}
/* End of file Loader.php */
/* Location: ./system/libraries/Loader.php */
