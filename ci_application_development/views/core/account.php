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
?>    <div class="ci_col-5of6">
        <div class=" admin_box">
            <h1 class="admin_box_title">Welcome...</h1>
            <h2>Hello <?=$profile['handle'];?></h2>
            <p>Name: <?php echo $profile['first_name'] . " " . $profile['last_name'];?>
            <br/>
            Email: <?=$profile['email'];?></p>
        </div>

<?php

$success_msg = $this->session->flashdata('success_msg');
$error_msg = $this->session->flashdata('error_msg');
if(!empty($success_msg)){
    echo '<div class="ci_msg success">'.$success_msg.'</div>';
}
if(!empty($error_msg)){
    echo '<div class="ci_msg error">'.$error_msg.'</div>';
}

?>

    </div>