<?php
/**
 * Netresearch_Logmon extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Logging_Monitoring
 * @package    Netresearch_Logmon
 * @copyright  Copyright (c) 2010 Netresearch GmbH & Co.KG <http://www.netresearch.de/>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Netresearch_Logmon_Helper_Data
 *
 * @category   Logging_Monitoring
 * @package    Netresearch_Logmon
 * @author     Thomas Kappel <thomas.kappel@netresearch.de>
 */
class Netresearch_Logmon_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Write a simple string, exception or array to log.
     *
     * @param  string|array|Exception    $log       Data to log
     * @param  integer                   $level     Log level
     * @param  string                    $module    Name of the module, where the message comes from
     * @param  mixed                     $data      Some additional data
     * @param  boolean                   $stack     Save stack, otherwise debug backtrace is used
     * @return integer
     */
    public function log($log, $level = null, $module = null, $data = null,
        $stack = false)
    {
        if (!is_array($log)) {
            if ($log instanceof Exception) {
                // Write an exception to the log.
                $log = array('exception' => $log);
            } else {
                // Write a simple message to the log.
                $log = array('message'   => $log);
            }
            // Append additional data to log.
            $log['data']      = $data;
            $log['module']    = $module;
            $log['log_level'] = $level;
            $log['stack']     = ($stack ? debug_backtrace() : null);
        }
        if (array_key_exists('stack', $log) and true === $log['stack']) {
            $log['stack'] = debug_backtrace();
        }
        return Mage::getModel('logmon/log')->log($log);
    }
}
