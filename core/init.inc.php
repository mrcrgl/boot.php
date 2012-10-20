<?php
/**
 * Executes all init scripts in ./init.
 *
 * @license   MIT Licence (see LICENSE file)
 * @copyright 2012 Marc Riegel
 *
 * @author  Marc Riegel <mail@marclab.de>
 * @package Versions.core
 * @subpackage Init
 */

$files = scandir(dirname(__FILE__).DIRECTORY_SEPARATOR.'init');
foreach ($files as $file) {
    if (preg_match('/\.inc\.php$/', $file)) {
        require dirname(__FILE__).DIRECTORY_SEPARATOR
                .'init'.DIRECTORY_SEPARATOR.$file;
      
        if (class_exists('VDebug')) {
            VDebug::_(
                new VDebugMessage(
                    sprintf("loaded file: %s%s", $file, NL),
                    DEBUG_MESSSAGE
                )
            );
        }
    }
}
