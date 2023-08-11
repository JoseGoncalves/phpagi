#!/usr/bin/env/php
<?php
namespace PhpAgi;

if (!class_exists('PhpAgi\\AGI')) {
    require_once('../vendor/autoload.php');
}

/**
 * PHP FastAGI bootstrap
 *
 *  This software is released under the terms of the GNU Lesser General Public License v2.1
 *  a copy of which is available from http://www.gnu.org/copyleft/lesser.html
 *
 * @package PhpAgi
 * @version 3.0
 * @filesource https://github.com/welltime/phpagi
 * @filesource http://phpagi.sourceforge.net/
 * @copyright 2004 - 2010 Matthew Asham <matthew@ochrelabs.com>, David Eder <david@eder.us> and others
 * @copyright 2023 RadiusOne Inc.
 * @license LGPLv2.1
 */

$fastagi = new AGI();
$fastagi->verbose(print_r($fastagi, true));
$basedir = realpath($fastagi->config['fastagi']['basedir'] ?? dirname(__FILE__));
$script = realpath($basedir . DIRECTORY_SEPARATOR . $fastagi->request['agi_network_script']);

// perform some security checks

// in the same directory (or subdirectory)
if (!str_starts_with($script, $basedir)) {
    $fastagi->conlog("$script is not located in $basedir.");
    exit;
}

// make sure it exists
if(!file_exists($script)) {
    $fastagi->conlog("$script does not exist.");

    exit;
}

// drop privileges
if(extension_loaded('posix') && ($fastagi->config['fastagi']['setuid'] ?? false)) {
    $owner = fileowner($script);
    $group = filegroup($script);
    if(
        !posix_setgid($group)
        || !posix_setegid($group)
        || !posix_setuid($owner)
        || !posix_seteuid($owner)
    )
    {
        $fastagi->conlog("failed to lower privileges.");

        exit;
    }
}

// make sure script is still readable
if(!is_readable($script)) {
    $fastagi->conlog("$script is not readable.");

    exit;
}

require_once($script);
