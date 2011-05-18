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
 * @copyright  Copyright (c) 2011 Netresearch GmbH & Co.KG <http://www.netresearch.de/>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @category   Logging_Monitoring
 * @package    Netresearch_Logmon
 * @author     Thomas Kappel <thomas.kappel@netresearch.de>
 */
class Netresearch_Logmon_Model_Observer extends Mage_Core_Model_Abstract
{
    protected function getArchiveConfig()
    {
        $rules = unserialize(Mage::getStoreConfig('dev/logmon/archive'));
        $levels = array();
        foreach ($rules as $rule) {
            $levels[$rule['level']] = $rule['days'];
        }
        return $levels;
    }
    
    protected function getLogFile($level)
    {
        return Mage::getBaseDir('var').DS.'log'.DS.
            str_replace(
                '{level}',
                $level,
                Mage::getStoreConfig('dev/logmon/archiveFile')
            );
    }
    
    public function archive()
    {
        foreach ($this->getArchiveConfig() as $level=>$days) {
            if ((int) $level !== 0) {
                $logs = Mage::getModel('logmon/log')->getCollection()
                    ->addFieldToFilter('log_level', $level)
                    ->addFieldToFilter('timestamp', array('lt' => date('Y:m:d H:i:s', time())));
                foreach ($logs as $log) {
                    $insert = sprintf('INSERT INTO %s SET
                        id=%d,
                        timestamp="%s",
                        type="%s",
                        log_level=%d,
                        module="%s",
                        exception="%s",
                        message="%s",
                        stack="%s",
                        data="%s",
                        error_key="%s"',
                        Mage::getModel('logmon/log')->getTableName(),
                        $log->getId(),
                        $log->getTimestamp(),
                        $log->getType(),
                        $log->getLogLevel(),
                        $log->getModule(),
                        $log->getException(),
                        $log->getMessage(),
                        $log->getStack(),
                        $log->getData(),
                        $log->getErrorKey()
                    );
                    
                    $writtenBytes = file_put_contents(
                        $this->getLogFile($level),
                        $insert,
                        FILE_APPEND
                    );
                    if (0 < $writtenBytes) {
                        $log->delete();
                    }
                }
            }
        }
    }
}
