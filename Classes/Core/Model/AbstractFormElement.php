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
 * A base form element, which is the starting point for creating custom (PHP-based)
 * Form Elements.
 *
 * **This class is meant to be subclassed by developers.**
 *
 * A *FormElement* is a part of a *Page*, which in turn is part of a FormDefinition.
 * See {@link FormDefinition} for an in-depth explanation.
 *
 * Subclassing this class is a good starting-point for implementing custom PHP-based
 * Form Elements.
 *
 * Most of the functionality and API is implemented in {@link Tx_FormBase_Core_Model_Renderable_AbstractRenderable}, so
 * make sure to check out this class as well.
 *
 * Still, it is quite rare that you need to subclass this class; often
 * you can just use the {@link Tx_FormBase_FormElements_GenericFormElement} and replace some templates.
 */
abstract class Tx_FormBase_Core_Model_AbstractFormElement extends Tx_FormBase_Core_Model_Renderable_AbstractRenderable implements Tx_FormBase_Core_Model_FormElementInterface {

	/**
	 * @var mixed
	 */
	protected $defaultValue = NULL;

	/**
	 * @var array
	 */
	protected $properties = array();

	/**
	 * Constructor. Needs this FormElement's identifier and the FormElement type
	 *
	 * @param string $identifier The FormElement's identifier
	 * @param string $type The Form Element Type
	 * @api
	 */
	public function __construct($identifier, $type) {
		if (!is_string($identifier) || strlen($identifier) === 0) {
			throw new Tx_FormBase_Exception_IdentifierNotValidException('The given identifier was not a string or the string was empty.', 1325574803);
		}
		if (preg_match(Tx_FormBase_Core_Model_FormElementInterface::PATTERN_IDENTIFIER, $identifier) !== 1) {
			throw new Tx_FormBase_Exception_IdentifierNotValidException(sprintf('The given identifier "%s" is not valid. It has to be lowerCamelCased.', $identifier), 1329131480);
		}
		$this->identifier = $identifier;
		$this->type = $type;
	}

	/**
	 * Override this method in your custom FormElements if needed
	 */
	public function initializeFormElement() {
	}

	public function getUniqueIdentifier() {
		$formDefinition = $this->getRootForm();
		return sprintf('%s-%s', $formDefinition->getIdentifier(), $this->identifier);
	}

	public function getDefaultValue() {
		return $this->defaultValue;
	}

	public function setDefaultValue($defaultValue) {
		$this->defaultValue = $defaultValue;
	}

	public function isRequired() {
		foreach ($this->getValidators() as $validator) {
			if ($validator instanceof Tx_Extbase_Validation_Validator_NotEmptyValidator) {
				return TRUE;
			}
		}
		return FALSE;
	}

	public function setProperty($key, $value) {
		$this->properties[$key] = $value;
	}

	public function getProperties() {
		return $this->properties;
	}

	/**
	 * Override this method in your custom FormElements if needed
	 */
	public function onSubmit(Tx_FormBase_Core_Runtime_FormRuntime $formRuntime, &$elementValue) {
	}
}
?>