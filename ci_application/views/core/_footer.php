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
?>

<footer id="Footer" class="ci_col-1of1 clearfix">
    <div class="ui-widget ui-widget-content ui-corner-all clearfix">
        <div style="padding:3px 10px;">
            <?php
            if ($this->uri->segment(1) != 'login') {
                ?>
                <p>Current Environment :
                    <strong><span style="color:green;"><?=strtoupper(ENVIRONMENT);?></span></strong> : Alpha v1.0.0 :
                    Page rendered in {elapsed_time} seconds</p>
                <?php
            }
            ?>
        </div>
    </div>
</footer>
<br><br>

</body>
</html>