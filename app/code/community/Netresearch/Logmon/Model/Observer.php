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

    /**
     * prepare log file (creating directory and file)
     * 
     * @param int $level
     *
     * @return boolean Success
     */
    protected function prepareLogFile($level)
    {
        $file = $this->getLogFile($level);
        $dir  = dirname($file);
        if (false == is_dir($dir)) {
            if (false == @mkdir($dir)) {
                Mage::log('could not create logmon archive directory ' . $dir);
                return false;
            }
        }
        if (false == file_exists($file)) {
            if (false == touch($file)) {
                Mage::log('could not create logmon archive file ' . $file);
                return false;
            }
        }
        return true;
    }

    /**
     * archive old logs
     *
     * @return void
     */
    public function archive()
    {
        $foundLogsToArchive = false;
        foreach ($this->getArchiveConfig() as $level=>$days) {
            if ((int) $level !== 0) {
                if (false === $this->prepareLogFile($level)) {
                    continue;
                }
                $archiveDate = time() - ( $days * 24 * 60 * 60);
                echo "Archiving Level $level older than " . date('Y.m.d', $archiveDate) . "\n";
                $logs = Mage::getModel('logmon/log')->getCollection();
                if (preg_match('/[1-9]+-[1-9]+/', $level)) {
                    list($min, $max) = explode('-', $level);
                    $logs->addFieldToFilter('log_level', array('from' => $min, 'to' => $max));
                } else {
                    $logs->addFieldToFilter('log_level', $level);
                }
                $logs->addFieldToFilter(
                    'timestamp',
                    array('lt' => date('Y:m:d H:i:s', $archiveDate))
                );
                if (0 == $logs->count()) {
                    echo "No logs found.\n";
                    continue;
                }
                if (false == $foundLogsToArchive) {
                    $foundLogsToArchive = true;
                    $this->backupOldLogs();
                }
                $insert = sprintf('INSERT INTO %s (id, timestamp, type, log_level, module, '
                    . 'exception, message, stack, data, error_key) VALUES ',
                    Mage::getSingleton('core/resource')->getTableName('logmon/log')
                );
                $insert .= "\n";
                $writtenBytes = file_put_contents(
                    $this->getLogFile($level),
                    $insert,
                    FILE_APPEND
                );
                if (0 == $writtenBytes) {
                    throw new Exception('Could not archive logmon data to ' . $this->getLogFile($level));
                }
                $remaining = $logs->count();
                $archivedLogs = 0;
                foreach ($logs as $log) {
                    $insert = "\n" . $this->getInsertDataForLog($log);
                    $remaining--;
                    $insert .= (0 == $remaining) ? ';' : ',';
                    
                    $writtenBytes = file_put_contents(
                        $this->getLogFile($level),
                        $insert,
                        FILE_APPEND
                    );
                    if (0 < $writtenBytes) {
                        $log->delete();
                        ++$archivedLogs;
                    }
                }
                echo 'Archived ' . $archivedLogs . ' logs for level ' . $level . ".\n";
            }
        }
    }

    /**
     * create string for insert statement of one log entry
     *
     * @param Netresearch_Logmon_Model_Log $log Log entry
     *
     * @return string
     */
    protected function getInsertDataForLog(Netresearch_Logmon_Model_Log $log)
    {
        return sprintf('(%d, "%s", "%s", %d, "%s", "%s", "%s", "%s", "%s", "%s")',
            $log->getId(),
            addslashes($log->getTimestamp()),
            addslashes($log->getType()),
            $log->getLogLevel(),
            addslashes($log->getModule()),
            addslashes($log->getExceptionString()),
            addslashes($log->getMessage()),
            addslashes($log->getStackString()),
            addslashes($log->getDataString()),
            $log->getErrorKey()
        );
    }

    /**
     * @todo
     * backup existing log archives
     *
     * @return void
     */
    protected function backupOldLogs()
    {
        // to be implemented
    }
}
