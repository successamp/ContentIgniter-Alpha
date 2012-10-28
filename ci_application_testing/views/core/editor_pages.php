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
            Add / Update / Edit Pages
            <small style="float:right;">New Page: <a href="/editor/pages_add_page/"><img src="/static/img/icons/Add.png"
                                                                                         height="36" width="36"
                                                                                         alt="Create A New Page"
                                                                                         style="vertical-align: middle;"/></a>
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
                <th style="width:20%;">Page ID</th>
                <th style="width:60%;">SERP</th>
                <th style="width:5%;">Delete</th>
            </tr>
            <?php
            foreach ($page_admin_data as $item) {
                $config_no_subdomain = $this->config->item('no_subdomain');

                $url       = $item['url'] != 'home' ? $item['url'] : '';
                $subdomain = empty($item['subdomain']) ? '*' : $item['subdomain'];
                if (empty($config_no_subdomain)) {
                    $url_subdomain = empty($item['subdomain']) ? 'www' : $item['subdomain'];
                } else {
                    $url_subdomain = '';
                }
                $url_page     = ($item['url'] == $item['subdomain']) ? '' : $item['url'] . '/';
                $url_domain   = empty($config_no_subdomain) ? '.' . $this->config->item('rootdomain') : $this->config->item('rootdomain');
                $active_class = empty($item['active']) ? ' error' : '';
                ?>
                <tr class="subdom sub<?=$item['subdomain'] . $active_class;?>">
                    <td class="buttons"><a href="/editor/pages_edit/<?=$item['id'];?>/"><img
                            src="/static/img/icons/Info.png" height="24" width="24" alt="Page Info"/></a> <a
                            href="/editor/pages_content/<?=$item['id'];?>/" class="ci_admin_content_box"><img
                            src="/static/img/icons/Modify.png" height="24" width="24" alt="Content"/></a></td>
                    <td><?=$subdomain;?></td>
                    <td><?=$url;?></td>
                    <td>
                        <div class="google_preview">
                            <h3 class="google title"><a
                                    href="http://<?=$url_subdomain;?><?=$url_domain;?>/<?=$url_page;?>" target="_blank"
                                    id="google_title_a"><?=$item['title'];?></a></h3>

                            <div class="google_below">
                                <cite class="google url" id="google_url_cite"><?=$url_subdomain;?><?=$url_domain;?>
                                    /<?=$url_page;?></cite><br>
                                <span class="google description"
                                      id="google_description_span"><?=$item['description'];?></span>
                            </div>
                    </td>
                    <td class="buttons"><a href="/editor/pages_delete/<?=$item['id'];?>/" class="ci_admin_delete"><img
                            src="/static/img/icons/Delete.png" height="24" width="24" alt="Delete This Page"
                            title="delete /<?=$url;?><?=empty($url) ? '' : '/';?>"/></a></td>
                </tr>


                <?php
            }
            ?>

        </table>
    </div>
</div>
