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
class Netresearch_Logmon_Block_Adminhtml_Log_View extends Mage_Adminhtml_Block_Widget_Container
{
    protected $log;
    
    public function __construct()
    {
        parent::__construct();
        $this->_objectId = 'id';
        $this->_blockGroup = 'logmon';
        $this->_controller = 'adminhtml_log';

        if( $this->getRequest()->getParam($this->_objectId) ) {
            $this->log = Mage::getModel('logmon/log')
                ->load($this->getRequest()->getParam($this->_objectId));
            Mage::register('log_data', $this->log);
        }
    }

    public function _toHtml()
    {
        $output = '<table>';
        foreach($this->log->_data as $key=>$value) {
            if ($this->isJson($value)) {
                $value = '<pre>' . Zend_Json::prettyPrint($value) . '</pre>';
            } elseif ($this->isSerializedArray($value)) {
                $value_lines = unserialize($value);
                $value_output = '';
                foreach ($value_lines as $key=>$line) {
                    $value_output .= sprintf('<tr><td>%s</td><td>%s</td></tr>',
                        $key,
                        $line
                    );
                }
                $value = '<table>' . $value_output . '</table>';
            } else {
                $value = '<pre>' . $value . '</pre>';
            }
            $output .= sprintf('<tr><th>%s</th><td>%s</td></tr>', $key, $value); 
        }
        $output .= '</table>';
        echo $output;
    }
    
    protected function isJson($value)
    {
        try {
            Zend_Json::decode($value);
            return true;
        } catch (Zend_Json_Exception $e) {
            return false;
        }
    }
    
    protected function isSerializedArray($value)
    {
        $value = @unserialize($value);
        $serialized = ($value == serialize(false) || $value !== false);
        return ($serialized and is_array($value));
    }
}