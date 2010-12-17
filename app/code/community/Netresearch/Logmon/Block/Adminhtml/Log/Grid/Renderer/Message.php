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
class Netresearch_Logmon_Block_Adminhtml_Log_Grid_Renderer_Message extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    const MESSAGE_LENGTH = 60;
    public function render(Varien_Object $row) {
        $value = $row->getData($this->getColumn()->getIndex());
        return str_replace('---snip---' , '<br />', substr($value, 0, self::MESSAGE_LENGTH))
            . (self::MESSAGE_LENGTH < strlen($value) ? '<span style="color:red;font-weight:bold">...</span>' : '')
            . '<br /><a href="' . $this->getUrl('*/*/view', array('id' => $row->getId())) . '">more</a>';
    }
}
