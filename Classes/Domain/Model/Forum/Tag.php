<?php
/*                                                                    - *
 *  COPYRIGHT NOTICE                                                    *
 *                                                                      *
 *  (c) 2013 Ruven Fehling <r.fehling@mittwald.de>                     *
 *           Mittwald CM Service GmbH & Co KG                           *
 *           All rights reserved                                        *
 *                                                                      *
 *  This script is part of the TYPO3 project. The TYPO3 project is      *
 *  free software; you can redistribute it and/or modify                *
 *  it under the terms of the GNU General Public License as published   *
 *  by the Free Software Foundation; either version 2 of the License,   *
 *  or (at your option) any later version.                              *
 *                                                                      *
 *  The GNU General Public License can be found at                      *
 *  http://www.gnu.org/copyleft/gpl.html.                               *
 *                                                                      *
 *  This script is distributed in the hope that it will be useful,      *
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of      *
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the       *
 *  GNU General Public License for more details.                        *
 *                                                                      *
 *  This copyright notice MUST APPEAR in all copies of the script!      *
 *                                                                      */


/**
 *
 * @author     Ruven Fehling <r.fehling@mittwald.de>
 * @package    MmForum
 * @subpackage Domain_Model_Forum
 * @version    $Id$
 * @license    GNU public License, version 2
 *             http://opensource.org/licenses/gpl-license.php

 */

class Tx_MmForum_Domain_Model_Forum_Tag extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {


	/**
	 * Tame of a tag
	 * @var string
	 */
	protected $name;

	/**
	 * Timestamp of this tag
	 * @var DateTime
	 */
	protected $tstamp;


	/**
	 * Crdate of this tag
	 * @var DateTime
	 */
	protected $crdate;


	/**
	 * The amount of topics which are using this tag
	 * @var int
	 */
	protected $topicCount;


	/**
	 * Get the name of this tag
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}


	/**
	 * Get the timestamp of this tag
	 * @return DateTime
	 */
	public function getTstamp() {
		return $this->tstamp;
	}


	/**
	 * Get the crdate of this tag
	 * @return DateTime
	 */
	public function getCrdate() {
		return $this->crdate;
	}


	/**
	 * Get the amount of topics which are using this tag
	 * @return int
	 */
	public function getTopicCount() {
		return $this->topicCount;
	}

	/**
	 * @param string $name
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * @param DateTime $crdate
	 */
	public function setCrdate($crdate) {
		$this->crdate = $crdate;
	}

	/**
	 * Increases the topic count by 1
	 * @return void
	 */
	public function increaseTopicCount() {
		$this->topicCount++;
	}

	/**
	 * Decreases the topic count by 1
	 * @return void
	 */
	public function decreaseTopicCount() {
		$this->topicCount--;
	}




}