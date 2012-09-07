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
 * A processing Rule contains information for property mapping and validation.
 *
 * **This class is not meant to be subclassed by developers.**
 */
class Tx_FormBase_Core_Model_ProcessingRule {
					
	/**
	 * The Extbase object manager
	 * 
	 * @var Tx_Extbase_Object_ObjectManager
	 * @inject
	 */
	protected $objectManager;

	/**
	 * The target data type the data should be converted to
	 *
	 * @var string
	 */
	protected $dataType;

	/**
	 * @inject
	 * @var Tx_Extbase_Property_PropertyMappingConfiguration
	 */
	protected $propertyMappingConfiguration;

	/**
	 * @inject
	 * @var Tx_Extbase_Validation_Validator_ConjunctionValidator
	 */
	protected $validator;

	/**
	 * @var Tx_Extbase_Error_Result
	 */
	protected $processingMessages;

	/**
	 * @inject
	 * @var Tx_Extbase_Property_PropertyMapper
	 * @internal
	 */
	protected $propertyMapper;

	/**
	 * @return Tx_Extbase_Property_PropertyMappingConfiguration
	 */
	public function getPropertyMappingConfiguration() {
		return $this->propertyMappingConfiguration;
	}

	/**
	 * @return string
	 */
	public function getDataType() {
		return $this->dataType;
	}

	/**
	 * @param string $dataType
	 */
	public function setDataType($dataType) {
		$this->dataType = $dataType;
	}

	/**
	 * Returns the child validators of the ConjunctionValidator that is bound to this processing rule
	 *
	 * @internal
	 * @return Tx_Extbase_Persistence_ObjectStorage<Tx_Extbase_Validation_Validator_ValidatorInterface>
	 */
	public function getValidators() {
		return $this->validator->getValidators();
	}

	/**
	 * @param Tx_Extbase_Validation_Validator_ValidatorInterface $validator
	 * @return void
	 */
	public function addValidator(Tx_Extbase_Validation_Validator_ValidatorInterface $validator) {
		$this->validator->addValidator($validator);
	}

	/**
	 * @param mixed $value
	 * @return mixed
	 */
	public function process($value) {
		if ($this->dataType !== NULL) {
			$value = $this->propertyMapper->convert($value, $this->dataType, $this->propertyMappingConfiguration);
			$messages = $this->propertyMapper->getMessages();
		} else {
			$messages = $this->objectManager->create('Tx_Extbase_Error_Result');
		}

		$validationResult = $this->validator->validate($value);
		$messages->merge($validationResult);

		$this->processingMessages = $messages;
		return $value;
	}

	/**
	 * @return Tx_Extbase_Error_Result
	 */
	public function getProcessingMessages() {
		return $this->processingMessages;
	}
}
?>