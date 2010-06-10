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
$this->startSetup()->run("
CREATE TABLE {$this->getTable('netresearch_logmon')} (
  `id` int(10) unsigned NOT NULL auto_increment,
  `timestamp` datetime NOT NULL,
  `type` varchar(100) NOT NULL,
  `log_level` int(10) NOT NULL,
  `module` varchar(100),
  `exception` varchar(255),
  `message` text,
  `stack` text,
  `data` text,
  `error_key` varchar(100),
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8
")->endSetup();
