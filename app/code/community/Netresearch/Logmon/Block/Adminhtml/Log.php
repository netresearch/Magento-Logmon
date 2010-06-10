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
class Netresearch_Logmon_Block_Adminhtml_Log extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'logmon';
        $this->_controller = 'adminhtml_log';
        $this->_headerText = Mage::helper('logmon')->__('View Logs');
        //$this->_addButtonLabel = Mage::helper('logmon')->__('Add New Location');
        parent::__construct();
    }
}