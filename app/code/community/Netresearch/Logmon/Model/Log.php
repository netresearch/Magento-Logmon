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
    
    public static $error_key;

    protected function _construct()
    {
        $this->_init('logmon/log');
        $this->config = Mage::getStoreConfig('dev/logmon');
    }

    /**
     * generate key for this request
     * 
     * @return string
     */
    protected function getErrorKey() {
        if (empty(self::$error_key)) {
            self::$error_key = uniqid();
        }
        return self::$error_key;
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
     * @return int
     */
    public function log($log_data)
    {
        try {
            $this->_data = $log_data;
            
            $this->handleType();
            $this->handleModule();
            $this->handleLoglevel();
            $this->handleTimestamp();
            $this->handleException();
            $this->handleStack();
            
            if (isset($this->_data['data']) and false == is_null($this->_data['data'])) {
                $this->_data['data'] = Zend_Json::encode($this->_data['data']);
            }
            $this->_data['error_key'] = $this->getErrorKey();

            $this->save();

            if ($this->getLogLevel() <= (int) $this->config['mailMaxLevel']) {
                $this->sendMail();
            }
        } catch (Exception $e) {
            // no logging if logging fails...
        }

        return $this->getId();
    }
    
    /**
     * set log type if not given
     *
     * @return void
     */
    protected function handleType()
    {
        if (false == isset($this->_data['type'])) {
            $this->setType(
                empty($this->_data['exception'])
                    ? self::DEFAULT_TYPE_LOG
                    : self::DEFAULT_TYPE_EXCEPTION
            );
        }
    }
    
    /**
     * set log level if not given
     *
     * @return void
     */
    protected function handleLoglevel()
    {
        if (false == isset($this->_data['log_level'])) {
            $this->setLogLevel(empty($this->_data['exception'])
                ? self::DEFAULT_LEVEL_LOG
                : self::DEFAULT_LEVEL_EXCEPTION);
        }
    }
    
    /**
     * set timestamp to current time if not given
     *
     * @return void
     */
    protected function handleTimestamp()
    {
        if (false == isset($this->_data['timestamp'])) {
            $now = Zend_Date::now();
            $now->setTimezone('UTC');

            $this->_data['timestamp'] = $now->toString('Y-M-d H:m:s');
        }
    }
    
    protected function handleException()
    {
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
    }
    
    protected function handleStack()
    {
        if (
            isset($this->_data['stack'])
            and false == is_null($this->_data['stack'])
            and false == is_string($this->_data['stack'])
        ) {
            $this->_data['stack'] = @Zend_Json::encode($this->_data['stack']);
        }
    }
    
    protected function handleModule()
    {
        if (false == isset($this->_data['module'])) {
            $this->_data['module'] = '(unknown)';
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
        
        if (isset($this->_data['exception']) and 'Zend_Mail_Transport_Exception' == $this->_data['exception']) {
            return;
        }

        $this->prepareValueForMail($vars, 'stack');
        $this->prepareValueForMail($vars, 'data');

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
    
    /**
     * convert arrays and object values into json
     * 
     * @param array  $vars
     * @param string $key
     * 
     * @return void
     */
    protected function prepareValueForMail(&$vars, $key)
    {
        if (isset($vars[$key])
            and false == is_null($vars[$key])
            and (is_array($vars[$key]) or is_object($vars[$key]))
        ) {
            $vars[$key] = print_r(json_encode($vars[$key]), true);
        }
    }
}