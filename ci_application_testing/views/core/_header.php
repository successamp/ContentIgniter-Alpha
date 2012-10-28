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
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<?php
header('Cache-Control: no-cache, must-revalidate');
header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time()));
?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
    <title>Powered By ContentIgniter</title>
    <meta name="robots" content="noindex, nofollow"/>
    <link rel="stylesheet" href="/uploads/admin/js/colorbox.1.3.15/colorbox.css" type="text/css"/>
    <link type="text/css" href="/uploads/admin/css/jqui-theme/jquery-ui-1.8.19.custom.css" rel="stylesheet"/>
    <link type="text/css" href="/uploads/admin/css/admin.css" rel="stylesheet"/>

    <?php
    if (!empty($custom_css)) {
        ?>
        <style type="text/css">
                <?=$custom_css;?>
        </style>
        <?php
    }

    ?>
    <script type="text/javascript" src="/uploads/admin/js/jquery-1.7.2.min.js"></script>
    <script type="text/javascript" src="/uploads/admin/js/jquery-ui-1.8.19.custom.min.js"></script>
    <script type="text/javascript" src="/uploads/admin/js/colorbox.1.3.15/jquery.colorbox-min.js"></script>
    <script type="text/javascript" src="/uploads/admin/js/tiny_mce.3.5.6/tiny_mce.js"></script>
    <script type="text/javascript" src="/uploads/admin/js/admin.js"></script>

    <?php
    if (!empty($custom_js)) {
        ?>
        <script type="text/javascript">
                <?=$custom_js;?>
        </script>
        <?php
    }
    ?>

    <script type="text/javascript">
        $(function () {

            // Accordion
            $("#accordion").accordion({ header:"h3" });

            // Tabs
            var $MenuTabs = $('#Menu').tabs();
            $('#Menu button').each(function () {
                var myHREF = $(this).attr('href');
                var myURL = document.URL.replace("http://", "").replace("https://", "").replace(document.domain, "");
                if (myHREF.length > 0 && myHREF == myURL) {
                    myParent = $(this).parent('div').attr('id');
                    if (myParent.length > 0) {
                        $MenuTabs.tabs('select', '#' + myParent);
                    }
                }
            });

            $(".buttons a").button();

            // Dialog
            $('#dialog').dialog({
                autoOpen:false,
                width:600,
                buttons:{
                    "Ok":function () {
                        $(this).dialog("close");
                    },
                    "Cancel":function () {
                        $(this).dialog("close");
                    }
                }
            });

            // Dialog Link
            $('#dialog_link').click(function () {
                $('#dialog').dialog('open');
                return false;
            });

            // Datepicker
            $('#datepicker').datepicker({
                inline:true
            });

            // Slider
            $('#slider').slider({
                range:true,
                values:[17, 67]
            });

            // Progressbar
            $("#progressbar").progressbar({
                value:20
            });

            //hover states on the static widgets
            $('#dialog_link, ul#icons li').hover(
                    function () {
                        $(this).addClass('ui-state-hover');
                    },
                    function () {
                        $(this).removeClass('ui-state-hover');
                    }
            );

            $("#Menu button, #PageTitle button").click(function () {
                if ($(this).attr("href") && $(this).attr("href").length != 0) {
                    var myTarget = $(this).attr("target");
                    if (myTarget == "_blank") {
                        window.open($(this).attr("href"));
                    } else {
                        document.location.href = $(this).attr("href");
                    }
                }
            });

        });
    </script>

    <style type="text/css">
            /*demo page css*/
        .demoHeaders {
            margin-top: 2em;
        }

        #dialog_link {
            padding: .4em 1em .4em 20px;
            text-decoration: none;
            position: relative;
        }

        #dialog_link span.ui-icon {
            margin: 0 5px 0 0;
            position: absolute;
            left: .2em;
            top: 50%;
            margin-top: -8px;
        }

        ul#icons {
            margin: 0;
            padding: 0;
        }

        ul#icons li {
            margin: 2px;
            position: relative;
            padding: 4px 0;
            cursor: pointer;
            float: left;
            list-style: none;
        }

        ul#icons span.ui-icon {
            float: left;
            margin: 0 4px;
        }
    </style>

</head>
<body>
<div class="ci_container_960 clearfix">
    <header class="ci_col-1of1 clearfix">
        <h6 class="ci-admin-title"><a href="http://contentigniter.com/" target="_blank">ContentIgniter</a> : A CMS For
            <a href="http://codeigniter.com/" target="_blank">CodeIgniter</a></h6>
    </header>
