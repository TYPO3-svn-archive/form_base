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
 * A generic file upload form element
 */
class Tx_FormBase_FormElements_FileUpload extends Tx_FormBase_Core_Model_AbstractFormElement {

	/**
	 * @return void
	 */
	public function initializeFormElement() {
		$this->setDataType('Tx_FormBase_Resource_Resource');
		$fileTypeValidator = new Tx_FormBase_Validation_FileTypeValidator(array('allowedExtensions' => $this->properties['allowedExtensions']));
		$this->addValidator($fileTypeValidator);
	}
}
?>