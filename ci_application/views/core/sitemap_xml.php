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

if (!empty($sitemapdata) && is_array($sitemapdata)) {
    if (empty($subdomain)) {
        $mapdomain       = $rootdomain;
        $subdomain['id'] = 'home';
    } else {
        $mapdomain = $subdomain['id'] . '.' . $rootdomain;
    }
    foreach ($sitemapdata as $page) {
        if ($page['url'] == $subdomain['id']) {
            $url = '/';
        } else {
            $url = '/' . $page['url'] . '/';

        }
        ?>
    <url>
        <loc>http://<?=$mapdomain . $url;?></loc>
        <?php
        echo empty($page['lastmod']) ? '' : '                    <lastmod>' . substr($page['lastmod'], 0, 10) . '</lastmod>' . "\n";
        echo empty($page['priority']) ? '' : '                    <priority>' . $page['priority'] . '</priority>' . "\n";
        ?>
    </url>
    <?php

    }
}
?>