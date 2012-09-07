<?php

/*                                                                        *
 * This script is backported from the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */


/**
 * A date picker form element
 */
class Tx_FormBase_FormElements_DatePicker extends Tx_FormBase_Core_Model_AbstractFormElement {

	/**
	 * @return void
	 */
	public function initializeFormElement() {
		$this->setDataType('DateTime');
	}
}
?>