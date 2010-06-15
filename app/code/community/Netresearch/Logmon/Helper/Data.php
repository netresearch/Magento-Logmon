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
 * @category   Logging_Monitoring
 * @package    Netresearch_Logmon
 * @author     Thomas Kappel <thomas.kappel@netresearch.de>
 */
class Netresearch_Logmon_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * write a simple message to the log
     * 
     * @param string $message Message to log
     * @param int    $level   Log level
     * @param string $module  Name of the module, where the message comes from
     * @param mixed  $data    some additional data
     * 
     * @return int
     */
    public function logMessage($message, $level=null, $module=null, $data=null, $save_stack=false)
    {
        return Mage::getModel('logmon/log')->log(array(
            'message'   => $message,
            'log_level' => $level,
            'data'      => $data,
            'module'    => $module,
            'stack'     => ($save_stack ? debug_backtrace() : null)
        ));
    }
    
    /**
     * write something to the log
     * 
     * @param array $log_data Data to log
     * 
     * @return int
     */
    public function log($log_data)
    {
        return Mage::getModel('logmon/log')->log($log_data);
    }
    
    /**
     * write an exception to the log
     * 
     * @param string $message Message to log
     * @param int    $level   Log level
     * 
     * @return int
     */
    public function logException(Exception $e, $module=null, $data=null)
    {
        return Mage::getModel('logmon/log')->log(array(
            'exception' => $e,
            'module'    => $module,
            'data'      => $data
        ));
    }
}