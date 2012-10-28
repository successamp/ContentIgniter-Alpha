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
        <h1 class="ui-widget-header ci-admin-header" id="PageTitle">Add / Update / Edit Subdomain
            <small>New Subdomain: <a href="/editor/subdomain_add_subdomain/"><img
                    src="/static/img/icons/Add.png" height="36" width="36" alt="Create A New Subdomain"
                    style="vertical-align: middle;"/></a></small>
        </h1>
        <table style="width:100%;" class="admin_table">
            <tr>
                <th style="width:5%;">Edit</th>
                <th style="width:20%;">Subdomain</th>
                <th style="width:5%;">Active</th>
                <th style="width:65%;">Legal Name</th>
                <th style="width:5%;">Delete</th>
            </tr>
            <?php
            foreach ($page_admin_data as $item) {
                $active       = empty($item['active']) ? 'Off' : 'On';
                $active_class = empty($item['active']) ? ' class="error"' : '';

                ?>
                <tr<?=$active_class;?>>
                    <td><a href="/editor/subdomain_edit/<?=$item['id'];?>/" class="ci_admin_content_box"><img
                            src="/static/img/icons/Modify.png" height="24" width="24" alt="Edit Subdomain"/></a></td>
                    <td><?=$item['id'];?></td>
                    <td><?=$active;?></td>
                    <td><?=$item['legal_name'];?></td>
                    <td><a href="/editor/subdomain_delete/<?=$item['id'];?>/" class="ci_admin_delete"><img
                            src="/static/img/icons/Delete.png" height="24" width="24" alt="Delete This Subdomain"
                            title="delete <?=$item['id'];?>"/></a></td>
                </tr>
                <?php
            }
            ?>

        </table>
    </div>
</div>
