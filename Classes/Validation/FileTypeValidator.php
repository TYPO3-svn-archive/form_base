<?php

/*                                                                        *
 * This script is backported from the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */


/**
 * The given $value is valid if it is an Tx_FormBase_Resource_Resource of the configured resolution
 * Note: a value of NULL or empty string ('') is considered valid
 */
class Tx_FormBase_Validation_FileTypeValidator extends Tx_Extbase_Validation_Validator_AbstractValidator {
					
	/**
	 * The Extbase object manager
	 * 
	 * @var Tx_Extbase_Object_ObjectManager
	 * @inject
	 */
	protected $objectManager;

	/**
	 * The given $value is valid if it is an Tx_FormBase_Resource_Resource of the configured resolution
	 * Note: a value of NULL or empty string ('') is considered valid
	 *
	 * @param Tx_FormBase_Resource_Resource $resource The resource object that should be validated
	 * @return void
	 * @api
	 */
	protected function isValid($resource) {
		$this->validateOptions();

		if (!$resource instanceof Tx_FormBase_Resource_Resource) {
			$this->addError('The given value was not a Resource instance.', 1327865587);
			return;
		}
		$fileExtension = $resource->getFileExtension();
		if ($fileExtension === NULL || $fileExtension === '') {
			$this->addError('The file has no file extension.', 1327865808);
			return;
		}
		if (!in_array($fileExtension, $this->options['allowedExtensions'])) {
			$this->addError('The file extension "%s" is not allowed.', 1327865764, array($resource->getFileExtension()));
			return;
		}
	}

	/**
	 * @return void
	 * @throws Tx_FormBase_Validation_Exception_InvalidValidationOptionsException if the configured validation options are incorrect
	 */
	protected function validateOptions() {
		if (!isset($this->options['allowedExtensions'])) {
			throw $this->objectManager->create('Tx_FormBase_Validation_Exception_InvalidValidationOptionsException','The option "allowedExtensions" was not specified.', 1327865682);
		} elseif (!is_array($this->options['allowedExtensions']) || $this->options['allowedExtensions'] === array()) {
			throw $this->objectManager->create('Tx_FormBase_Validation_Exception_InvalidValidationOptionsException','The option "allowedExtensions" must be an array with at least one item.', 1328032876);
		}
	}
}

?>