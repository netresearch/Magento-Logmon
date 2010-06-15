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
class Netresearch_Logmon_Model_Log extends Mage_Core_Model_Abstract
{
    const DEFAULT_TYPE_LOG        = 'log';
    const DEFAULT_TYPE_EXCEPTION  = 'exception';
    const DEFAULT_LEVEL_LOG       = Zend_Log::INFO;
    const DEFAULT_LEVEL_EXCEPTION = Zend_Log::ERR;
    
    public $config;

    protected function _construct()
    {
        $this->_init('logmon/log');
        $this->config = Mage::getStoreConfig('dev/logmon');
    }
    
    /**
     * log something
     * parameters
     * - message    (optional)
     * - type       (optional)
     * - data       (optional)
     * - error_key  (optional)
     * - exception  (optional)
     * - log_level  (optional)
     * - stacktrace (optional)
     * - timestamp  (optional)
     * 
     * @param array $log_data Associative array with fields defined above
     * 
     * @return void
     */
    public function log($log_data)
    {
        try {
            $this->_data = $log_data;
            if (false == isset($this->_data['type'])) {
                $this->setType(
                    empty($this->_data['exception'])
                        ? self::DEFAULT_TYPE_LOG
                        : self::DEFAULT_TYPE_EXCEPTION
                );
            }
            if (false == isset($this->_data['log_level'])) {
                $this->setLogLevel(empty($this->_data['exception'])
                    ? self::DEFAULT_LEVEL_LOG
                    : self::DEFAULT_LEVEL_EXCEPTION);
            }
            if (false == isset($this->_data['timestamp'])) {
                $now = Zend_Date::now();
                $now->setTimezone('UTC');
                
                $this->_data['timestamp'] = $now->toString('Y-M-d H:m:s');
            }
            if (isset($this->_data['exception']) and false == is_null($this->_data['exception'])) {
                $exception = $this->_data['exception'];
                if (empty($this->_data['message'])) {
                    $this->_data['message'] = $exception->getMessage();
                }
                if (empty($this->_data['stack'])) {
                    $this->_data['stack'] = $exception->getTraceAsString();
                }
                $this->_data['exception'] = get_class($exception);
            }
            if (isset($this->_data['stack']) and false == is_null($this->_data['stack']) and false == is_string($this->_data['stack'])) {
                $this->_data['stack'] = json_encode($this->_data['stack']);
            }
            if (isset($this->_data['data']) and false == is_null($this->_data['data'])) {
                $this->_data['data'] = json_encode($this->_data['data']);
            }
            
            $this->save();
            
            if ($this->getLogLevel() <= (int) $this->config['mailMaxLevel']) {
                $this->sendMail();
            }
        } catch (Exception $e) {
            // no logging if logging fails...
        }
    }
    
    /**
     * get log entry description
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->getShortDescription();
    }
    
    /**
     * get short description of the log entry
     * 
     * @return string
     */
    public function getShortDescription()
    {
        $string = 'Log #' . $this->getId();
        if (false == empty($this->_data['exception'])) {
            $string .= ': ' . $this->getException();
        } elseif (false == empty($this->_data['message'])) {
            $string .= ': ' . $this->getMessage();
        }
        if (false == empty($this->_data['module'])) {
            $string .= ' in ' . $this->getModule();
        }
        return $string;
    }
    
    /**
     * send a mail concerning the log entry
     * 
     * @return void
     */
    public function sendMail()
    {
        $vars = $this->_data;
        $vars['short_description'] = $this->getShortDescription();
        
        if (isset($vars['stack']) and false == is_null($vars['stack'])) {
            $vars['stack'] = print_r(json_decode($vars['stack']), true);
        }
        if (isset($vars['data']) and false == is_null($vars['data'])) {
            $vars['data'] = print_r(json_decode($vars['data']), true);
        }
        
        // Recipients
        $recipient = $this->config['receiverMailAddress'];

        $sender = array(
            'name'  => Mage::getStoreConfig('trans_email/ident_general/name'),
            'email' => Mage::getStoreConfig('trans_email/ident_general/email'),
        );

        /* @var $mail Mage_Core_Model_Email_Template */
        $mail = Mage::getModel('core/email_template');
        $mail->setTemplateSubject($vars['short_description']);
        $mail->sendTransactional((int) $this->config['mailTemplate'],
                $sender, $recipient, null, $vars);
    }
}