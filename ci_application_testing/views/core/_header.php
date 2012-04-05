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
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<?php
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time()));
?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
<title>Powered By ContentIgniter</title>
<meta name="robots" content="noindex, nofollow"/>
<link rel="stylesheet" href="/uploads/admin/admin.css" type="text/css"/>
<link rel="stylesheet" href="/uploads/admin/colorbox/colorbox.css" type="text/css"/>
<link rel="stylesheet" href="/uploads/admin/jquery-ui-1.8.16/css/smoothness/jquery-ui-1.8.16.custom.css" type="text/css"/>
<?php
          if(!empty($custom_css)){
?>
<style type="text/css">
<?=$custom_css;?>
</style>
<?php
          }

?>

<script type="text/javascript" src="/uploads/admin/jquery.min.js"></script>
<script type="text/javascript" src="/uploads/admin/jquery-ui-1.8.16/js/jquery-ui-1.8.16.custom.min.js"></script>
<script type="text/javascript" src="/uploads/admin/colorbox/jquery.colorbox-min.js"></script>
<script type="text/javascript" src="/uploads/admin/tinymce/tiny_mce.js"></script>
<script type="text/javascript" src="/uploads/admin/admin.js"></script>

<?php
          if(!empty($custom_js)){
?>
<script type="text/javascript">
<?=$custom_js;?>
</script>
<?php
          }
?>

</head>
<body>
<div class="ci_container_fluid" id="grand_rapids_content">