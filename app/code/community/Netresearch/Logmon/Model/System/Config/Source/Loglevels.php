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
class Netresearch_Logmon_Model_System_Config_Source_Loglevels
{
    public static $levels = array(
        Zend_Log::EMERG  => 'emergency',
        Zend_Log::ALERT  => 'alert',
        Zend_Log::CRIT   => 'critical',
        Zend_Log::ERR    => 'error',
        Zend_Log::WARN   => 'warning',
        Zend_Log::NOTICE => 'notice',
        Zend_Log::INFO   => 'info',
        Zend_Log::DEBUG  => 'debur',
    );
    
    public function toOptionArray()
    {
        $options = array();
        
        for ($level=0;$level<Zend_Log::DEBUG+1;$level++) {
            $options[$level]=array(
                'value' => $level,
                'label' => $level . ':  - ' . Mage::helper('logmon')->__(self::$levels[$level]),
            );
        }
        return $options;
    }
}