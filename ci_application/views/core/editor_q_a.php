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
            Add / Update / Edit Q&amp;A
            <small style="float:right;">New Q&amp;A:
                <a href="/editor/q_a_add/"><img src="/static/img/icons/Add.png" height="36" width="36" alt="Create A New Q&amp;A" style="vertical-align: middle;"/></a>
            </small>
        </h1>
        <?php
        $subdomain_list = array();
        foreach ($page_admin_data as $item) {
            if (empty($subdomain_list['sub' . $item['subdomain']])) {
                $subdomain_list['sub' . $item['subdomain']] = empty($item['subdomain']) ? '*' : $item['subdomain'];
            }
        }
        ksort($subdomain_list);
        ?>
        <table style="width:100%;" class="admin_table">
            <thead>
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
                <th style="width:5%;">Order</th>
                <th style="width:75%;">Question</th>
                <th style="width:5%;">Delete</th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach ($page_admin_data as $item) {
                $subdomain    = empty($item['subdomain']) ? '*' : $item['subdomain'];
                $active_class = empty($item['active']) ? ' error' : '';
                ?>
            <tr class="subdom sub<?=$item['subdomain'] . $active_class;?>">
                <td class="buttons"><a href="/editor/q_a_edit/<?=$item['id'];?>/"><img
                        src="/static/img/icons/Modify.png" height="24" width="24" alt="Modify Q&amp;A"/></a></td>
                <td><?=$subdomain;?></td>
                <td class="order"><?=$item['order'];?></td>
                <td><?=$item['question'];?></td>
                <td class="buttons"><a href="/editor/q_a_delete/<?=$item['id'];?>/" class="ci_admin_delete"><img
                        src="/static/img/icons/Delete.png" height="24" width="24" alt="Delete This Q&amp;A"
                        title="delete"/></a></td>
            </tr>


                <?php
            }
            ?>
            </tbody>
        </table>
        <style>
            .order {
                text-align: center;
                border: 1px solid #F1F1F1;
            }

            #SortSave {
                display: none;
                border-top: 1px solid #F1F1F1;
            }
        </style>
        <div class="clearfix" id="SortSave">
            <h1 class="ci-admin-header">
                Save Sorting
                <a href="/editor/q_a_sort_save/"><img src="/static/img/icons/Save.png" height="36" width="36" alt="Save Sorting" style="vertical-align: middle;"/></a>
            </h1>
        </div>
    </div>
</div>
