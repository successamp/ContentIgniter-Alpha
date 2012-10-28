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
?><?php
if (!empty($rssdata) && is_array($rssdata)) {

    foreach ($rssdata as $page) {
        $url = '/news/' . $page['url'] . '/';
        ?>
    <item>
        <title><?php echo xml_convert($page['header']); ?></title>
        <link>
        http://<?=$rssdomain . $url;?></link>
        <description><![CDATA[
            <?php echo $this->parse_content($page['intro_text']);?>

            ]]>
        </description>
        <pubDate><?php echo date('r', strtotime($page['published']));?></pubDate>
    </item>
    <?php

    }
}
?>

