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

/**
 * Functions to wrap FirePHP calls so that they don't cause errors when FirePHP isn't loaded.
 */

function fb_log($Label, $Object = NULL)
{
    // I prefer the look of label first.
    $firephp = FirePHP::getInstance(TRUE);
    if (empty($Object)) {
        $Object = $Label;
        $Label  = NULL;
    }
    $firephp->log($Object, $Label);
}

function fb_info($Label)
{
    $firephp = FirePHP::getInstance(TRUE);
    $firephp->info($Label);
}

function fb_warn($Label)
{
    $firephp = FirePHP::getInstance(TRUE);
    $firephp->warn($Label);
}

function fb_error($Label)
{
    $firephp = FirePHP::getInstance(TRUE);
    $firephp->error($Label);
}

function fb_trace($Label)
{
    $firephp = FirePHP::getInstance(TRUE);
    $firephp->trace($Label);
}

function fb_dump($Key, $Variable)
{
    $firephp = FirePHP::getInstance(TRUE);
    $firephp->dump($Key, $Variable);
}

function fb_table($Label, $Table)
{
    $firephp = FirePHP::getInstance(TRUE);
    $firephp->table($Label, $Table);
}

function fb_group($Name, $Options = NULL)
{
    $firephp = FirePHP::getInstance(TRUE);
    $firephp->group($Name, $Options);
}

function fb_groupEnd()
{
    $firephp = FirePHP::getInstance(TRUE);
    $firephp->groupEnd();
}

/*
$firephp->info('Info Message');     // or FB::
$firephp->warn('Warn Message');     // or FB::
$firephp->error('Error Message');
*/

?>