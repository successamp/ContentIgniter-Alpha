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

if (file_exists(APPPATH.'views/core/_nav.xml')){
    $xml = simplexml_load_file(APPPATH.'views/core/_nav.xml');
}
?>
    <div class="ci_col-1of6 noprint">
        <div class="admin_box" id="Menu">
            <h1 class="admin_box_title">Menu</h1>
            <h2>User Menu:</h2>
<?php
if ($this->session->userdata("logged_in")){

    if(!empty($xml->logged_in)){
        foreach($xml->logged_in->link as $item){
?>
                <a href="<?=$item->url;?>" class="menu_item"><?=$item->name;?></a><br/>
<?php
        }
    }
}
if ($this->session->auth('member')){
    if(!empty($xml->member)){
        foreach($xml->member->link as $item){
?>
                <a href="<?=$item->url;?>" class="menu_item"><?=$item->name;?></a><br/>
<?php
        }
    }
}
if ($this->session->auth('editor')){
?>
            <h2>Editor Menu:</h2>

<?php
    if(!empty($xml->editor)){
        foreach($xml->editor->link as $item){
?>
                <a href="<?=$item->url;?>" class="menu_item"><?=$item->name;?></a><br/>
<?php
        }
    }
}
if ($this->session->auth('moderator')){
?>
            <h2>Moderator Menu:</h2>
<?php
    if(!empty($xml->moderator)){
        foreach($xml->moderator->link as $item){
?>
                <a href="<?=$item->url;?>" class="menu_item"><?=$item->name;?></a><br/>
<?php
        }
    }
}
if ($this->session->auth('admin')){
?>
            <h2>Admin Menu:</h2>

<?php
    if(!empty($xml->admin)){
        foreach($xml->admin->link as $item){
?>
                <a href="<?=$item->url;?>" class="menu_item"><?=$item->name;?></a><br/>
<?php
        }
    }
}
if ($this->session->auth('developer')){
?>
            <h2>Developer Menu:</h2>
<?php
    if(ENVIRONMENT === 'production'){
        echo "Production is ON<br/>";
    }else{
        echo "<a href=\"/admin/set_production/\" class=\"menu_item\">Production OFF (turn on)</a><br/>";
    }
    if(ENVIRONMENT === 'development'){
        echo "Development is ON<br/>";
    }else{
        echo "<a href=\"/admin/set_development/\" class=\"menu_item\">Development OFF (turn on)</a><br/>";
    }
    if(ENVIRONMENT === 'testing'){
        echo "Testing is ON<br/>";
    }else{
        echo "<a href=\"/admin/set_testing/\" class=\"menu_item\">Testing OFF (turn on)</a><br/>";
    }
    if(!ENVIRONMENT_DEBUG){
        echo 'FirePHP is ON (<a href="/admin/firephp_off/">Turn Off</a>) <br/>';
    }else{
        echo 'FirePHP is OFF* (<a href="/admin/firephp_on/">Turn On</a>)<br/> &nbsp; &nbsp; <sup>*Errors reported to your screen</sup><br/>';
    }
    if(!empty($xml->developer)){
        foreach($xml->developer->link as $item){
?>
                <a href="<?=$item->url;?>" class="menu_item"><?=$item->name;?></a><br/>
<?php
        }
    }
}
?>
        </div>
        <p>Page rendered in {elapsed_time} seconds</p>
    </div>
