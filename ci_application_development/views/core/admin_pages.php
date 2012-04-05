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
?>    <div class="ci_col-5of6">
        <div class=" admin_box">
        <h1 class="admin_box_title">Add / Update / Edit Pages</h1>
             <p>New Page: <a href="/admin/pages_add_page/"><img src="/static/img/icons/Add.png" height="48" width="48" alt="Create A New Page"/></a></p>

              <table style="width:100%;" class="admin_table">
                    <tr>
                          <th style="width:10%;">Subdomain</th>
                          <th style="width:20%;">URL</th>
                          <th style="width:10%;">Functions</th>
                          <th style="width:30%;">Title</th>
                          <th style="width:30%;">Description</th>
                    </tr>
<?php
foreach($page_admin_data as $item){
    $url = $item['url'] != 'home' ? $item['url'] : '';
    $subdomain = empty($item['subdomain']) ? '*' : $item['subdomain'];
?>
                    <tr>
                          <td><?=$subdomain;?></td>
                          <td><a target="_blank" href="/<?=$url;?><?=empty($url)?'':'/';?>">/<?=$url;?><?=empty($url)?'':'/';?></a></td>
                          <td><a href="/admin/pages_edit/<?=$item['id'];?>/" class="ci_admin_edit_box"><img src="/static/img/icons/Info.png" height="24" width="24" alt="Page Info"/></a> <a href="/admin/pages_content/<?=$item['id'];?>/" class="ci_admin_content_box"><img src="/static/img/icons/Modify.png" height="24" width="24" alt="Content"/></a> <a href="/admin/pages_delete/<?=$item['id'];?>/" class="ci_admin_delete"><img src="/static/img/icons/Delete.png" height="24" width="24" alt="Delete This Page" title="delete /<?=$url;?><?=empty($url)?'':'/';?>"/></a></td>
                          <td><?=$item['title'];?></td>
                          <td class="end"><em><?=$item['description'];?></em></td>
                    </tr>
<?php
}
?>

              </table>
        </div>
    </div>
