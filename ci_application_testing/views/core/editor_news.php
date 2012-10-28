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
        <h1 class="ui-widget-header ci-admin-header" id="PageTitle">Add / Update / Edit News
            <small>New News Item: <a href="/editor/news_add/"><img src="/static/img/icons/Add.png" height="36"
                                                                   width="36" alt="Create A News Post"
                                                                   style="vertical-align: middle;"/></a></small>
        </h1>
        <?php
        $subdomain_list = array();
        foreach ($news_admin_data as $item) {
            if (empty($subdomain_list['sub' . $item['subdomain']])) {
                $subdomain_list['sub' . $item['subdomain']] = empty($item['subdomain']) ? '*' : $item['subdomain'];
            }
        }
        ksort($subdomain_list);
        ?>

        <table style="width:100%;" class="admin_table">
            <tr>
                <th style="width:5%;">Edit</th>
                <th style="width:10%;">
                    <select id="subdom_select" style="font-size:70%;">
                        <option selected="selected" value="">All Subdomains</option>
                        <?php
                        foreach ($subdomain_list as $key => $value) {
                            echo '<option value="' . $key . '">' . $value . '</option>';
                        }

                        ?>
                    </select>
                </th>
                <th style="width:5%;">Active</th>
                <th style="width:20%;">URL</th>
                <th style="width:25%;">Title</th>
                <th style="width:30%;">Description</th>
                <th style="width:5%;">Delete</th>
            </tr>
            <?php
            foreach ($news_admin_data as $item) {
                $url          = $item['url'] != 'home' ? $item['url'] : '';
                $subdomain    = empty($item['subdomain']) ? '*' : $item['subdomain'];
                $active       = empty($item['active']) ? 'Draft' : 'Live';
                $active_class = empty($item['active']) ? ' error' : '';
                ?>
                <tr class="subdom sub<?=$item['subdomain'] . $active_class;?>">
                    <td><a href="/editor/news_edit/<?=$item['id'];?>/" class="ci_admin_content_box"><img
                            src="/static/img/icons/Modify.png" height="24" width="24" alt="Content"/></a></td>
                    <td><?=$subdomain;?></td>
                    <td><?=$active;?></td>
                    <td><a target="_blank" href="/news/<?=$url;?>/">/news/<?=$url;?>/</a></td>
                    <td><?=$item['title'];?></td>
                    <td class="end"><em><?=$item['description'];?></em></td>
                    <td><a href="/editor/news_delete/<?=$item['id'];?>/" class="ci_admin_delete"><img
                            src="/static/img/icons/Delete.png" height="24" width="24" alt="Delete This News Item"
                            title="delete /news/<?=$url;?>/"/></a></td>
                </tr>
                <?php
            }
            ?>

        </table>
    </div>
</div>
