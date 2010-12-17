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
class Netresearch_Logmon_Model_Log_Api extends Mage_Catalog_Model_Api_Resource
{
    /**
     * Retrieve list of products with basic info (id, sku, type, set, name)
     *
     * @param array $filters
     * @param string|int $store
     * @return array
     */
    public function items($filters = null, $store = null)
    {
        $collection = Mage::getModel('logmon/log')->getCollection();

        $result = array();

        foreach ($collection as $log) {
            $result[] = array( // Basic product data
                'id'        => $log->getId(),
                'module'    => $log->getModule(),
                'type'      => $log->getType(),
                'timestamp' => $log->getTimestamp(),
                'exception' => $log->getException(),
                'message'   => $log->getMessage(),
                'stack'     => $log->getStack()
            );
        }

        return $result;
    }

    /**
     * Retrieve product info
     *
     * @param int|string $productId
     * @param string|int $store
     * @param array $attributes
     * @return array
     */
    public function info($id)
    {
        $log = Mage::getModel('logmon/log')->load($id);

        if (!$log->getId()) {
            $this->_fault('not_exists');
        }

        $result[] = array( // Basic product data
            'id'        => $log->getId(),
            'module'    => $log->getModule(),
            'type'      => $log->getType(),
            'timestamp' => $log->getTimestamp(),
            'exception' => $log->getException(),
            'message'   => $log->getMessage(),
            'stack'     => $log->getStack()
        );

        return $result;
    }

    /**
     * Create new log entry.
     *
     * @param string  $message    Error message
     * @param int     $level      Error level
     * @param string  $module     Module name
     * @param array   $data       Some data
     * @param boolean $save_stack If stack trace (of Magento) should be saved
     * 
     * @return int
     */
    public function create($message, $level=null, $module=null, $data=null, $save_stack=null)
    {
        return Mage::helper('logmon')->log($message, $level, $module, $data, $save_stack);
    }
    
    /**
     * Extend log entry
     * 
     * @param int     $id         Log message id
     * @param string  $message    Error message
     * @param string  $separator  String to separate from previous log messages
     */
    public function extend($id, $message, $separator="\n---snip---\n")
    {
        $log = Mage::getModel('logmon/log')->load($id);

        if (!$log->getId()) {
            $this->_fault('not_exists');
        }
        $log->setMessage($log->getMessage() . $separator . $message);
        $log->save();
    }
} // Class Netresearch_Logmon_Model_Log_Api End
