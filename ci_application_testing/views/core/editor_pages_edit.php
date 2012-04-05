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
        <div class="admin_box">
        <h1 class="admin_box_title">Edit Page</h1>
        <a href="/editor/pages/">Return To Editor Pages</a>

<div id="google_preview">
    <h3 class="google title"><a href="#" id="google_title_a"></a></h3>
    <div class="google_below">
        <cite class="google url" id="google_url_cite">/</cite><br />
        <span class="google description" id="google_description_span"></span>
    </div>
</div>

<div id="domain_holder" style="display:none;"><?=$display_rootdomain;?></div>

<script>
$(document).ready(function(){
    $title = $("#ci_admin_edit_form input[name='title']");
    $description = $("#ci_admin_edit_form input[name='description']");
    $subdomain = $("#ci_admin_edit_form select[name='subdomain']");
    $url = $("#ci_admin_edit_form  input[name='url']");
    updateTitle($title.val());
    updateURL();
    updateDesc($description.val());

    $title.change(function(){
        updateTitle($(this).val());
    }).keyup(function(){
        updateTitle($(this).val());
    });

    $description.change(function(){
        updateDesc($(this).val());
    }).keyup(function(){
        updateDesc($(this).val());
    });

    $subdomain.change(function(){
        updateURL();
    }).keyup(function(){
        updateURL();
    });

    $url.change(function(){
        updateURL();
    }).keyup(function(){
        updateURL();
    });

    function updateTitle(newTitle){
      if(newTitle.length > 67){
          var last_space = newTitle.lastIndexOf(" ",67);
          $('#google_title_a').html(newTitle.substring(0,last_space).concat('...'));
          $title.addClass('error');
      }else{
          $('#google_title_a').html(newTitle);
          $title.removeClass('error');
      }
    }

    function updateDesc(newDesc){
      if(newDesc.length > 156){
          var last_space = newDesc.lastIndexOf(" ",153);
          $('#google_description_span').html(newDesc.substring(0,last_space).concat('...'));
          $description.addClass('error');
      }else{
          $('#google_description_span').html(newDesc);
          $description.removeClass('error');
      }
    }

    function updateURL(){
      var newURL = '';
      newURL += $subdomain.val() == '' ? 'www' : $subdomain.val();
      newURL += $('#domain_holder').html();
      if($url.val() != $subdomain.val()){
          newURL += $url.val() == '' ? '' : $url.val()+'/';
      }
      $('#google_url_cite').html(newURL);
    }
});
</script>

            <?php
            echo $page_content;
            ?>

        </div>
    </div>