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
class Netresearch_Logmon_Block_Adminhtml_Log_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('logGrid');
        $this->setDefaultSort('timestamp');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $this->setCollection(Mage::getModel('logmon/log')->getCollection());
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header'    => Mage::helper('logmon')->__('ID'),
            'align'     => 'right',
            'width'     => '50px',
            'index'     => 'id',
            'type'      => 'number',
        ));

        $this->addColumn('module', array(
            'header'    => Mage::helper('logmon')->__('Module'),
            'align'     => 'left',
            'index'     => 'module',
        ));

        $this->addColumn('timestamp', array(
            'header'    => Mage::helper('logmon')->__('Timestamp'),
            'align'     => 'right',
            'index'     => 'timestamp',
            'width'     => '50px',
        ));

        $this->addColumn('type', array(
            'header'    => Mage::helper('logmon')->__('Type'),
            'align'     => 'right',
            'index'     => 'type',
            'width'     => '50px',
        ));

        $this->addColumn('exception', array(
            'header'    => Mage::helper('logmon')->__('Exception'),
            'align'     => 'left',
            'index'     => 'exception',
        ));

        $this->addColumn('message', array(
            'header'    => Mage::helper('logmon')->__('Message'),
            'index'     => 'message',
            'renderer'  => 'Netresearch_Logmon_Block_Adminhtml_Log_Grid_Renderer_Message'
        ));

        $this->addColumn('error_key', array(
            'header'    => Mage::helper('logmon')->__('Error key'),
            'index'     => 'error_key',
        ));

        $this->addColumn('log_level', array(
            'header'    => Mage::helper('logmon')->__('Log level'),
            'align'     => 'right',
            'index'     => 'log_level',
            'width'     => '50px',
            'type'      => 'number',
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/view', array('id' => $row->getId()));
    }
}