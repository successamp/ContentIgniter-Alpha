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
?>    <div class="ci_col-1of1">
        <div class=" admin_box">
        <h1 class="admin_box_title">Login</h1>

<?php if(!empty($error_message)){echo "<div class=\"error\">{$error_message}</div>";}?>

<form  action="<?=$_SERVER['REQUEST_URI'];?>" method="post">
<input type="hidden" name="redirect" value="<?=$redirect;?>"/>
    User Name: <input type="text" name="user_name" value="<?=$user;?>"/><br />
    Password: <input type="password" name="password" value="<?=$pass;?>"/><br />
    <input type="submit" name="submit" id="login_submit" value="Login" />
</form>

        </div>
    </div>