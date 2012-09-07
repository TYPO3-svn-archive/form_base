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
 * A base form element interface, which can be the starting point for creating
 * custom (PHP-based) Form Elements.
 *
 * A *FormElement* is a part of a *Page*, which in turn is part of a FormDefinition.
 * See {@link FormDefinition} for an in-depth explanation.
 *
 * **Often, you should rather subclass {@link AbstractFormElement} instead of
 * implementing this interface.**
 */
interface Tx_FormBase_Core_Model_FormElementInterface extends Tx_FormBase_Core_Model_Renderable_RenderableInterface {

	const PATTERN_IDENTIFIER = '/^[a-z][a-zA-Z0-9-_]+$/';

	/**
	 * Will be called as soon as the element is (tried to be) added to a form
	 * @see registerInFormIfPossible()
	 *
	 * @return void
	 * @internal
	 */
	public function initializeFormElement();

	/**
	 * Returns a unique identifier of this element.
	 * While element identifiers are only unique within one form,
	 * this includes the identifier of the form itself, making it "globally" unique
	 *
	 * @return string the "globally" unique identifier of this element
	 * @api
	 */
	public function getUniqueIdentifier();

	/**
	 * Get the default value with which the Form Element should be initialized
	 * during display.
	 *
	 * @return mixed the default value for this Form Element
	 * @api
	 */
	public function getDefaultValue();

	/**
	 * Set the default value with which the Form Element should be initialized
	 * during display.
	 *
	 * @param mixed $defaultValue the default value for this Form Element
	 * @api
	 */
	public function setDefaultValue($defaultValue);

	/**
	 * Set an element-specific configuration property.
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 * @api
	 */
	public function setProperty($key, $value);

	/**
	 * Get all element-specific configuration properties
	 *
	 * @return array
	 * @api
	 */
	public function getProperties();

	/**
	 * Set a rendering option
	 *
	 * @param string $key
	 * @param mixed $value
	 * @api
	 */
	public function setRenderingOption($key, $value);

	/**
	 * Returns the child validators of the ConjunctionValidator that is registered for this element
	 *
	 * @internal
	 * @return Tx_Extbase_Persistence_ObjectStorage<Tx_Extbase_Validation_Validator_ValidatorInterface>
	 */
	public function getValidators();

	/**
	 * Registers a validator for this element
	 *
	 * @param Tx_Extbase_Validation_Validator_ValidatorInterface $validator
	 * @return void
	 * @api
	 */
	public function addValidator(Tx_Extbase_Validation_Validator_ValidatorInterface $validator);

	/**
	 * Set the target data type for this element
	 *
	 * @param string $dataType the target data type
	 * @return void
	 * @api
	 */
	public function setDataType($dataType);

	/**
	 * Whether or not this element is required
	 *
	 * @internal
	 * @return boolean
	 * @api
	 */
	public function isRequired();

	/**
	 * This callback is invoked by the FormRuntime whenever values are mapped and validated
	 * (after a form page was submitted)
	 * @see Tx_FormBase_Core_Runtime_FormRuntime::mapAndValidate()
	 *
	 * @param Tx_FormBase_Core_Runtime_FormRuntime $formRuntime
	 * @param mixed $elementValue submitted value of the element *before post processing*
	 * @api
	 */
	public function onSubmit(Tx_FormBase_Core_Runtime_FormRuntime $formRuntime, &$elementValue);
}
?>