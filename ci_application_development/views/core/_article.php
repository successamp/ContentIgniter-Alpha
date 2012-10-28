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
<div id="Content" class="ci_col-1of1 clearfix ci_bottom">
    <div class="ui-widget ui-widget-content ui-corner-all clearfix">
        <h1 class="ui-widget-header ci-admin-header" id="PageTitle">
            Account Home
            <?php
            if (!empty($Content)) {
                foreach ($Content as $item) {
                    echo '<h1>' . $this->parse_content($item['header']) . ' - Grand Rapids, MI</h1>';
                }
            }
            ?>
        </h1>

        <!-- Start Content
                          <div class="ci-admin-content">
                              Content
                          </div>
                          -->

        <?php
        if (!empty($Content)) {
            foreach ($Content as $item) {
                echo $this->parse_content($item['body']);
            }
        }

        print_r($_ci_data['_ci_vars']);
        ?>

    </div>
</div><!-- End Content -->
