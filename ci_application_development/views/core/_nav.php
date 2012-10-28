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
?><?php
if (file_exists(APPPATH . 'views/core/_nav.xml')) {
    $xml = simplexml_load_file(APPPATH . 'views/core/_nav.xml');
}
?>

<!-- Menu -->
<nav class="ci_col-1of1 clearfix ci_bottom ci_top">
    <div id="Menu">

        <ul>
            <li><a href="#submenu-1">User:</a></li>
            <?php
            if ($this->session->auth('editor')) {
                ?>
                <li><a href="#submenu-2">Editor</a></li>
                <?php
            }
            if ($this->session->auth('moderator')) {
                ?>
                <li><a href="#submenu-3">Moderator</a></li>
                <?php
            }
            if ($this->session->auth('moderator')) {
                ?>
                <li><a href="#submenu-4">Admin</a></li>
                <?php
            }
            if ($this->session->auth('moderator')) {
                ?>
                <li><a href="#submenu-5">Developer</a></li>
                <?php
            }
            ?>
        </ul>
        <div id="submenu-1">
            <?php
            if ($this->session->userdata("logged_in")) {
                ?>
                <button href="/account/">Account Home</button>
                <button href="/login/logout/">Logout</button>
                <?php
                if (!empty($xml->logged_in)) {
                    foreach ($xml->logged_in->link as $item) {
                        ?>
                        <button href="<?=$item->url;?>"><?=$item->name;?></button>
                        <?php
                    }
                }
            }
            if ($this->session->auth('member')) {
                if (!empty($xml->member)) {
                    foreach ($xml->member->link as $item) {
                        ?>
                        <button href="<?=$item->url;?>"><?=$item->name;?></button>
                        <?php
                    }
                }
            }
            ?>
        </div>
        <div id="submenu-2">
            <?php
            if ($this->session->auth('editor')) {
                ?>
                <button href="/editor/pages/">Edit Pages</button>
                <button href="/editor/news/">Edit News (Blog)</button>
                <button href="/editor/subdomains/">Edit Subdomains</button>
                <button href="/editor/clear_cache/">Clear All Cache</button>
                <?php
                if (!empty($xml->editor)) {
                    foreach ($xml->editor->link as $item) {
                        ?>
                        <button href="<?=$item->url;?>"><?=$item->name;?></button>
                        <?php
                    }
                }
            }
            ?>
        </div>
        <div id="submenu-3">
            <?php
            if ($this->session->auth('moderator')) {
                if (ENVIRONMENT === 'production') {
                    echo "<span style=\"color:green;\">(Production is ON)</span> &nbsp; ";
                } else {
                    echo "<button href=\"/moderator/set_production/\" class=\"menu_item\"><span style=\"color:green;\">Turn On</span> <span style=\"color:red;\">Production</span></button> &nbsp; ";
                }
                if (ENVIRONMENT === 'testing') {
                    echo "<span style=\"color:green;\">(Testing is ON)</span>  &nbsp; ";
                } else {
                    echo "<button href=\"/moderator/set_testing/\" class=\"menu_item\"><span style=\"color:green;\">Turn On</span> <span style=\"color:red;\">Testing</span></button> &nbsp; ";
                }
                if (!empty($xml->moderator)) {
                    foreach ($xml->moderator->link as $item) {
                        ?>
                        <button href="<?=$item->url;?>"><?=$item->name;?></button>
                        <?php
                    }
                }
            }
            ?>
        </div>
        <div id="submenu-4">
            <?php
            if ($this->session->auth('admin')) {
                ?>
                <button href="/admin/home/">Admin Home</button>
                <button href="/admin/change_password/">Change A Password</button>
                <button href="/todo/">Todo List</button>
                <?php
                if (!empty($xml->moderator)) {
                    foreach ($xml->moderator->link as $item) {
                        ?>
                        <button href="<?=$item->url;?>"><?=$item->name;?></button>
                        <?php
                    }
                }
            }
            ?>

        </div>
        <div id="submenu-5">
            <?php
            if ($this->session->auth('developer')) {
                if (ENVIRONMENT === 'production') {
                    echo "<span style=\"color:green;\">(Production is ON)</span> &nbsp; ";
                } else {
                    echo "<button href=\"/admin/set_production/\" class=\"menu_item\"><span style=\"color:green;\">Turn On</span> <span style=\"color:red;\">Production</span></button> &nbsp; ";
                }
                if (ENVIRONMENT === 'testing') {
                    echo "<span style=\"color:green;\">(Testing is ON)</span>  &nbsp; ";
                } else {
                    echo "<button href=\"/admin/set_testing/\" class=\"menu_item\"><span style=\"color:green;\">Turn On</span> <span style=\"color:red;\">Testing</span></button> &nbsp; ";
                }
                if (ENVIRONMENT === 'development') {
                    echo "<span style=\"color:green;\">(Development is ON)</span>  &nbsp; ";
                } else {
                    echo "<button href=\"/admin/set_development/\" class=\"menu_item\"><span style=\"color:green;\">Turn On</span> <span style=\"color:red;\">Development</span></button> &nbsp; ";
                }
                if (!ENVIRONMENT_DEBUG) {
                    echo "<button href=\"/admin/firephp_off/\"><span style=\"color:red;\">Turn Off FirePHP*</span></button>  &nbsp; <sup>*Errors will be reported to your screen</sup>";
                } else {
                    echo "<button href=\"/admin/firephp_on/\"><span style=\"color:green;\">Turn On FirePHP*</span></button> &nbsp; <sup>*Errors no longer reported to your screen</sup>";
                }

                if (!empty($xml->moderator)) {
                    foreach ($xml->moderator->link as $item) {
                        ?>
                        <button href="<?=$item->url;?>"><?=$item->name;?></button>
                        <?php
                    }
                }
            }
            ?>
        </div>

    </div>
    <!-- End Menu -->
</nav>
