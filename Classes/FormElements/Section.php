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
 * A Section, being part of a bigger Page
 *
 * **This class is not meant to be subclassed by developers.**
 *
 * This class contains multiple FormElements ({@link FormElementInterface}).
 *
 * Please see {@link FormDefinition} for an in-depth explanation.
 *
 * Once we support traits, the duplicated code between AbstractFormElement and Section could be extracted to a Trait.
 */
class Tx_FormBase_FormElements_Section extends Tx_FormBase_Core_Model_AbstractSection implements Tx_FormBase_Core_Model_FormElementInterface {

	public function initializeFormElement() {
	}

	public function getUniqueIdentifier() {
		$formDefinition = $this->getRootForm();
		return sprintf('%s-%s', $formDefinition->getIdentifier(), $this->identifier);
	}

	public function getDefaultValue() {
		return NULL;
	}
	public function getProperties() {
		return array();
	}
	public function setDefaultValue($defaultValue) {

	}
	public function setProperty($key, $value) {

	}
	public function setRenderingOption($key, $value) {
		$this->renderingOptions[$key] = $value;
	}

	public function getValidators() {
		$formDefinition = $this->getRootForm();
		return $formDefinition->getProcessingRule($this->getIdentifier())->getValidators();
	}

	public function addValidator(Tx_Extbase_Validation_Validator_ValidatorInterface $validator) {
		$formDefinition = $this->getRootForm();
		$formDefinition->getProcessingRule($this->getIdentifier())->addValidator($validator);
	}

	public function isRequired() {
		foreach ($this->getValidators() as $validator) {
			if ($validator instanceof Tx_Extbase_Validation_Validator_NotEmptyValidator) {
				return TRUE;
			}
		}
		return FALSE;
	}
}
?>